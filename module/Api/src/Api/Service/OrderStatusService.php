<?php

namespace Api\Service;

use Api\Exception\ApiException;
use Api\Service\BaseService;
use Api\Table\OrderStatusTable;

class OrderStatusService extends BaseService
{

    protected $orderDetail;
    protected $currentStatus;
    protected $parameter;

    public function setOrderStatus($parameter)
    {
        $this->parameter = $parameter;
        
        // get order details
        $orderTable = $this->getTable('Order');
        $this->orderDetail = $orderTable->getByField(array('id' => $parameter['order_id']));
        if (!$this->orderDetail) {
            throw new ApiException('Order not found.', 404);
        }

        // process update status
        $this->currentStatus = $this->getCurrentStatus($parameter['order_id']);
        $this->newStatus = $parameter['status'];
        switch ($parameter['status']) {
            case OrderStatusTable::CONFIRMED :
                $res = $this->confirmOrder($parameter);
                break;
            case OrderStatusTable::CANCELLED :
                $res = $this->cancelOrder($parameter);
                break;
            case OrderStatusTable::READYTOPACK :
                $res = $this->toBePackOrder($parameter);
                break;
            case OrderStatusTable::PACKED :
                $res = $this->packedOrder($parameter);
                break;
            case OrderStatusTable::SHIPPED :
                $res = $this->shippedOrder($parameter);
                break;
            case OrderStatusTable::DELIVERED :
                $res = $this->deliverOrder($parameter);
                break;
            default :
                throw new ApiException('Improper status given.', 403);
        }

        return $res;
    }

    public function confirmOrder($data)
    {
        if ($this->currentStatus !== OrderStatusTable::PENDING) {
            throw new ApiException('You are not allowed to mark this order as confirmed at this stage!', 403);
        }

        $this->updateItems($data, 1);

        //set status
        return $this->updateStatus($data);
    }

    public function packedOrder($data)
    {
        if ($this->currentStatus != OrderStatusTable::READYTOPACK) {
            throw new ApiException('Improper status!', 403);
        }

        $this->updateItems($data, 0);

        //set status
        return $this->updateStatus($data);
    }

    public function shippedOrder($data)
    {
        if ($this->currentStatus != OrderStatusTable::PACKED) {
            throw new ApiException('Improper status!', 403);
        }

        //set status
        return $this->updateStatus($data);
    }

    public function deliverOrder($data)
    {
        if ($this->currentStatus !== OrderStatusTable::SHIPPED) {
            throw new ApiException('You are not allowed to mark this order as delivered at this stage!', 403);
        }

        if (!isset($data['amount'])) {
            $data['amount'] = 0;
        }

        if ($data['amount'] < 0) {
            throw new ApiException('Amount cannot be negative!', 403);
        }

        $returnTotalAmount = $this->getReturnAmount($data);
        $amountCollected = $data['amount'];
        $orginalAmountTobePaid = $this->orderDetail['net_amount'];
        $amountToBeCollected = $orginalAmountTobePaid - $returnTotalAmount;
        if ($amountToBeCollected >= 0 && round($amountCollected, 2) !== round($amountToBeCollected, 2)) {
            throw new ApiException('Amount received is not valid, it should be ₱' . $amountToBeCollected, 400);
        }

        //returned items
        if (isset($data['is_return'], $data['items'])) {
            $res2 = $this->addReturnItems($data);
            if ($res2 === false) {
                throw new ApiException('Unable to add return items, please try again!', 500);
            }
        }

        // processing
        $this->processPriceCalculation($data);

        // update staus of an order
        $orderStatusRes = $this->updateStatus($data);

        // give loyalty points
        $this->grantLoyaltyPointsToReferal();

        return $orderStatusRes;
    }

    public function cancelOrder($data)
    {
        if ($this->currentStatus === OrderStatusTable::SHIPPED || $this->currentStatus === OrderStatusTable::DELIVERED) {
            throw new ApiException('You are not allowed to mark this order as Cancel at this stage!', 403);
        }

        // return the loyalty points back to user
        $loyalityService = $this->sm->get('LoyalityService');
        $loyalityService->returnForCancelOrder($this->orderDetail);
        
        // close tasks if exits
        $orderTaskTable = $this->getTable('OrderTask');
        $res1 = $orderTaskTable->updateOrderTask(array('is_finished' => 1), array('order_id' => $this->orderDetail['id']));
        if ($res1 === false) {
            throw new ApiException('Unable to close task, please try again!', 500);
        }

        // update order
        $orderTable = $this->getTable('Order');
        $res = $orderTable->updateOrder(array('net_amount' => $this->orderDetail['net_amount'] + $this->orderDetail['loyalty_points_used'], 'loyalty_points_used' => 0), array('id' => $this->orderDetail['id']));
        if ($res === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }

        $this->updateStatus($data);

        return $this->success('Order has been successfully cancelled!');
    }

    public function toBePackOrder($data)
    {
        if ($this->currentStatus != OrderStatusTable::CONFIRMED) {
            throw new ApiException('You are not allowed to mark this order Ready To Be Packed as at this stage!', 403);
        }

        return $this->updateStatus($data);
    }

    /*
     * Private methods
     */
    private function processPriceCalculation($data)
    {
        $priceCalc = new PriceCalculatorService($this->sm);
        $orderPrice = $priceCalc->calculate($this->orderDetail['id'], 1);

        $orginalAmountTobePaid = (($orderPrice['amount'] - $orderPrice['discount']) + $this->orderDetail['delivery_charges']) - $this->orderDetail['loyalty_points_used'];
        $returnTotalAmount = $this->getReturnAmount($data);
        $amountCollected = round($data['amount'], 2);

        // updating order amount
        $param = array(
            'amount_collected' => $amountCollected,
            'returned_item_amount' => $returnTotalAmount
        );

        // update loyalty points if used in orders is less than the actual amoutn
        if (!empty($this->orderDetail['loyalty_points_used']) && $returnTotalAmount > $orginalAmountTobePaid) {
            $loyaltyEarned = round(($returnTotalAmount - $orginalAmountTobePaid), 2);

            $loyalityService = $this->sm->get('LoyalityService');
            $loyalityService->returnForOrder($this->orderDetail, $loyaltyEarned);

            $param['loyalty_points_used'] = $this->orderDetail['loyalty_points_used'] - $loyaltyEarned;
        }

        // credit loyalty points
        $loyalityService = $this->sm->get('LoyalityService');
        $param['loyalty_points_earn'] = $loyalityService->creditForOrder($this->orderDetail['id'], $amountCollected);

        // update order
        $orderTable = $this->getTable('Order');
        $res = $orderTable->updateOrder($param, array('id' => $this->orderDetail['id']));
        if ($res === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }

        return true;
    }

    private function getReturnAmount($param)
    {
        if (!isset($param['items'])) {
            return 0;
        }

        $returnItemIds = array();
        foreach ($param['items'] as $retItem) {
            $returnItemIds[] = $retItem['item_id'];
        }

        $orderItemTable = $this->getTable('OrderItem');
        $filter = array(
            'order_id' => $param['order_id'],
            'item_ids' => $returnItemIds
        );
        $itemData = $orderItemTable->getItemsByIds($filter);

        $returnTotalAmount = 0;
        foreach ($itemData as $item) {
            foreach ($param['items'] as $val) {
                if ($val['item_id'] == $item['id']) {
                    $returnTotalAmount += $item['price'] * $val['quantity'];
                }
            }
        }

        return $returnTotalAmount;
    }

    private function addReturnItems($data)
    {
        $returnTable = $this->getTable('OrderReturnedItem');
        foreach ($data['items'] as $item) {
            $param = array(
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'reason' => $item['reason'],
                'image' => !empty($item['image']) ? $item['image'] : '',
            );
            $returnTable->addReturnedItem($param);
        }

        // updating status in order
        $orderTable = $this->getTable('Order');
        $param = array(
            'is_returned' => 1,
        );

        return $orderTable->updateOrder($param, array('id' => $data['order_id']));
    }

    public function getCurrentStatus($orderId)
    {
        $orderStatusTable = $this->getTable('OrderStatus');
        $data = $orderStatusTable->getCurrentOrderStatus($orderId);
        if ($data) {
            return $data['status'];
        }

        return false;
    }

    private function updateStatus($data)
    {
        $param = array(
            'order_id' => $data['order_id'],
            'status' => $data['status']
        );

        $statusTable = $this->getTable('OrderStatus');
        $res = $statusTable->addOrderStatus($param);
        if ($res === false) {
            throw new ApiException('Unable to update status, please try again!', 500);
        }

        return $this->success('Order status successfully updated');
    }

    private function updateItemsAvailablility($items)
    {
        // setup items
        $this->itemIdsNotAvailable = array();
        $this->itemIdsLimited = array();
        $this->isModified = array();

        // mark items avaibility in items table
        $orderItemTable = $this->getTable('OrderItem');
        foreach ($items as $item) {
            if (!$item['quantity'] && $item['total_quantity']) {
                $this->itemIdsNotAvailable[] = $item['item_id'];
            } else if ($item['quantity'] < $item['total_quantity']) {
                $this->itemIdsLimited[] = $item['item_id'];
            }
            $isModified = null;
            $by = null;
            if ($item['quantity'] != $item['total_quantity']) {
                $this->isModified[] = $item['item_id'];
                $isModified = 1;
                $by = 'wh';
            }
            $orderItemTable->setAvailabilityStatus($item['item_id'], $item['quantity'], $isModified, $by);
        }

        return true;
    }

    private function updateItems($data, $isTask = 0)
    {
        if (!isset($data['item'])) {
            return;
        }

        $this->updateItemsAvailablility($data['item']);

        // calculate prices
        $priceCalc = new PriceCalculatorService($this->sm);
        $orderPrice = $priceCalc->calculate($this->orderDetail['id'], 1);
        $netAmount = (($orderPrice['amount'] - $orderPrice['discount']) + $this->orderDetail['delivery_charges']) - $this->orderDetail['loyalty_points_used'];
        if ($netAmount < 0) {
            $netAmount = 0;
        }

        // update order price
        $orderTable = $this->getTable('Order');
        $res3 = $orderTable->updateOrder(array(
            'amount' => $orderPrice['amount'],
            'discount' => $orderPrice['discount'],
            'net_amount' => $netAmount,
            'no_of_boxes' => !empty($data['no_of_boxes']) ? (int) $data['no_of_boxes'] : 1
        ), array('id' => $this->orderDetail['id']));
        if ($res3 === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }
        
        $this->updateStockAvailabiity();
        $this->checkUnfinishedTaskExists();
        if (!isset($this->parameter['callcenter']) && $isTask) {
            $this->validateOrderAvailabiityAndCreateTask($orderPrice);
        }
    }

    private function grantLoyaltyPointsToReferal()
    {
        $process = $this->getServiceLocator()->get("Base\Utils\Process");
        $process->start('referral-bonus --order_id="' . $this->orderDetail['id'] . '"');
    }
    
    private function checkUnfinishedTaskExists()
    {
        $orderTaskTable = $this->getTable('OrderTask');
        $orderTaskObj = $orderTaskTable->getCurrentOrderTask($this->orderDetail['id']);
        if ($orderTaskObj) {
            throw new ApiException('Order is updated but status not updated to ' . $this->newStatus . ', since some task is already assigned to this order, call center guys are looking into it.', 403);
        }
    }

    private function validateOrderAvailabiityAndCreateTask($orderPrice)
    {
        if (count($this->isModified)) {
            $this->createOrderTask();
            throw new ApiException('Order is updated but status not updated to ' . $this->newStatus . ', since some item(s) are not available. Informed call center to confirm this.', 403);
        }
        
        // no changes from previous state
        if (!count($this->itemIdsNotAvailable) && !count($this->itemIdsLimited)) {
            return;
        }
        
        if (count($this->itemIdsNotAvailable)) {
            $this->createOrderTask();
            throw new ApiException('Order is updated but status not updated to ' . $this->newStatus . ', since some item(s) are not available. Informed call center to confirm this.', 403);
        }

//        $finalOrderValue = $orderPrice['amount'] - $orderPrice['discount'];
//        $initialOrderValue = $this->orderDetail['initial_order_value'];
//        $percentage = ($finalOrderValue / $initialOrderValue) * 100;
//        if ($percentage < 90) {
//            $this->createOrderTask();
//            throw new ApiException('Order is updated but status not updated to ' . $this->newStatus . ', since more than 10% of items cost is not avialable. Informed call center to confirm this.', 403);
//        }
    }

    private function createOrderTask()
    {
        $orderTaskTable = $this->getTable('OrderTask');
        $res3 = $orderTaskTable->addOrderTask(array(
            'order_id' => $this->orderDetail['id'],
            'remarks' => $this->newStatus
        ));
        if ($res3 === false) {
            throw new ApiException('Unable to create order task, please try again!', 500);
        }
    }
    
    private function updateStockAvailabiity()
    {
        $itemIds = array_merge($this->itemIdsNotAvailable, $this->itemIdsLimited);
        
        $orderTaskTable = $this->getTable('Product');
        $orderTaskTable->markAsNotAvailable($itemIds);
    }

}

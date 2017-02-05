<?php

namespace Api\Service;

use Api\Exception\ApiException;
use Api\Service\BaseService;
use Api\Table\LoyaltyPointTable;

class EditOrderService extends BaseService
{

    public function addItems($orderId, $productsList, $isModified = null)
    {
        foreach ($productsList as $item) {
            $this->createOrderItem($orderId, $item, $isModified);
        }

        // calculate prices & update order 
        $priceCalc = new PriceCalculatorService($this->sm);
        $orderParam = $priceCalc->calculate($orderId, 1);

        $res = $this->getOrderTable()->updateOrder($orderParam, array('id' => $orderId));
        if (!$res) {
            throw new ApiException('Unable to update order information, please try again!', 500);
        }

        return true;
    }

    public function changeDeliveryDate($orderId, $deliveredBy)
    {
        if ($deliveredBy && $this->isValidDate($deliveredBy) && strtotime('today UTC') <= strtotime($deliveredBy)) {
            $res = $this->getOrderTable()->updateOrder(array('delivered_by' => $deliveredBy), array('id' => $orderId));
            if (!$res) {
                throw new ApiException('Unable to update order delivery date, please try again!', 500);
            }
        } else {
            throw new ApiException('Please enter valid date!', 400);
        }

        return true;
    }
    
    public function applyLoyaltyPoints($orderId, $usePoints, $remarks = '')
    {
        //get order details
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $orderDetails = $orderTable->getOrderDetails($orderId, array());
        if ($orderDetails === false) {
            throw new ApiException('Order not found!', 400);
        } 
        
        if ($orderDetails['net_amount'] < $usePoints) {
            return $this->errorRes('Loyalty points exceeds total order cost.');
        }
        
        // debit loyalty points
        $remark = LoyaltyPointTable::REMARK_DEBIT_ORDER;
        if (!empty($remarks)) {
            $remark .= ' (' . $remarks . ')';
        }
        $loyaltyService = new LoyalityService($this->getServiceLocator());
        $loyaltyService->debitForOrder($orderId, $usePoints, $remark);
        
        // apply points
        $res = $orderTable->applyPointsToOrder($orderId, $usePoints);
        if($res === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }
        
        return true;
    }
    
    public function editItem($data)
    {
        $orderItemTable = $this->getOrderItemTable();
        $orderData = $orderItemTable->getByField(array(
                'id' => $data['product_id'],'order_id' => $data['order_id'] 
            ));
        if($orderData === false) {
            throw new ApiException('Order not found!', 404);
        }
        
        $orderItemTable->setAvailabilityStatus($data['product_id'], $data['quantity'], 1);
        if($orderData === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }
        
        return $this->updateOrderPrice($data['order_id']);
    }
    
    public function deleteItem($data)
    {
        $orderItemTable = $this->getOrderItemTable();
        $orderData = $orderItemTable->getByField(array(
                'id' => $data['item_id'],'order_id' => $data['order_id'] 
            ));
        if($orderData === false) {
            throw new ApiException('Order not found!', 404);
        }
        
        $orderItemTable->updateOrderItem(['is_deleted' => 1], ['id' => $data['item_id']]);
        if($orderData === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }
        
        return $this->updateOrderPrice($data['order_id']);
    }
    
    private function updateOrderPrice($orderId)
    {
        // calculate prices
        $priceCalc = new PriceCalculatorService($this->sm);
        $orderPrice = $priceCalc->calculate($orderId, 1);
        $netAmount = $orderPrice['amount'] - $orderPrice['discount'];
        if ($netAmount < 0) {
            $netAmount = 0;
        }
        
        // update order price
        $orderTable = $this->getTable('Order');
        $res3 = $orderTable->updateOrderDetails(array(
            'amount' => $orderPrice['amount'],
            'discount' => $orderPrice['discount'],
            'net_amount' => $netAmount
        ), array('id' => $orderId));
        if ($res3 === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }
        
        return true;
    }

    private function getOrderTable()
    {
        if (isset($this->orderTable)) {
            return $this->orderTable;
        }

        $this->orderTable = $this->getTable('Order');

        return $this->orderTable;
    }

    private function getOrderItemTable()
    {
        if (isset($this->orderItemTable)) {
            return $this->orderItemTable;
        }

        $this->orderItemTable = $this->getTable('OrderItem');

        return $this->orderItemTable;
    }

    private function createOrderItem($orderId, $data, $isModified = null)
    {
        $item = (array) $data;
        if (!isset($item['product_id'], $item['quantity'])) {
            return false;
        }

        // get product details
        $productTable = $this->getTable('Product');
        $productData = $productTable->getProductOrderDetails($orderId, $item['product_id']);
        if (!$productData) {
            throw new ApiException('Product not found!', 400);
        }
        
        if ($productData['item_id']) {
            throw new ApiException('Item already in the list, please choose another one!', 403);
        }
        
        $actual = $productData['price'] * $item['quantity'];
        $total = $productData['srp'] * $item['quantity'];
        $parameter = array(
            'order_id' => $orderId,
            'product_id' => $productData['id'],
            'requested_quantity' => $item['quantity'],
            'quantity' => $item['quantity'],
            'super8_price' => $productData['super8_price'],
            'price' => $productData['price'],
            'srp' => $productData['srp'],
            'amount' => $total,
            'discount' => $total - $actual,
            'net_amount' => $actual,
            'is_available' => 1,
            'is_added_by_cc' => 1,
        );
        if (isset($isModified) && $isModified) {
            $parameter['is_modified'] = 1;
            $parameter['quantity_by_cc'] = $item['quantity'];
        }

        //create order status
        $orderItemTable = $this->getOrderItemTable();
        $res = $orderItemTable->addOrderItem($parameter);

        return ($res) ? true : false;
    }

    private function isValidDate($date, $format = 'Y-m-d')
    {
        return $date == date($format, strtotime(trim($date)));
    }

}

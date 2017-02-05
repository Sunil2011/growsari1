<?php

namespace Api\Service;

use Api\Exception\ApiException;
use Api\Service\BaseService;
use Api\Table\OrderStatusTable;
use DateTime;

class CreateOrderService extends BaseService
{

    protected $userId;
    protected $productList;
    protected $productsData;
    protected $deliveredBy;
    protected $lastUpdatedAt;
    protected $loyalityPoints;
    protected $smsSender;
    protected $storeAssocId;
    protected $orderId;
    protected $netAmount;
    protected $byCC;

    public function create($userId, $productList, $deliveredBy, $lastUpdatedAt, $loyalityPoints = 0, $smsSender = null, $byCC = 0)
    {
        $this->userId = $userId;
        $this->productList = $productList;
        $this->deliveredBy = $deliveredBy;
        $this->lastUpdatedAt = $lastUpdatedAt;
        $this->loyalityPoints = $loyalityPoints;
        $this->smsSender = $smsSender;
        $this->byCC = $byCC;

        $validateResp = $this->validateInputData();
        if ($validateResp !== true) {
            return $validateResp;
        }

        $this->createNewOrder();
        $this->addItems();
        $this->confirmOrder();
        $this->updateOrderWithPriceAndDelivery();

        return $this->success('Order placed successfully', array('order_id' => $this->orderId, 'net_amount' => $this->netAmount));
    }

    private function validateInputData()
    {
        if (!count($this->productList)) {
            throw new ApiException('Please add products to cart!', 400);
        }

        $this->validateProductList();        

        $storeAssoc = $this->getAssocId($this->userId);
        $this->storeAssocId = $storeAssoc['associate_id'];
        if (!$this->storeAssocId) {
            throw new ApiException('No warehouse is mapped to your account, please contact admin!', 403);
        }

        return true;
    }

    private function createNewOrder()
    {
        $this->orderId = $this->getOrderTable()->addOrder(array(
            'associate_id' => $this->storeAssocId,
            'sms_sender' => (!empty($this->smsSender)) ? $this->smsSender : null,
            'is_added_by_cc' => (!empty($this->byCC)) ? 1 : 0,
        ));

        if (!$this->orderId) {
            throw new ApiException('Unable to create order, please try again!', 500);
        }
    }

    private function validateProductList()
    {
        $productIds = array();
        foreach ($this->productList as $pdata) {
            if (isset($pdata['product_id'])) {
                $productIds[] = $pdata['product_id'];
            }
        }

        if (empty($productIds)) {
            throw new ApiException('Please add products to cart!', 400);
        }

        $productTable = $this->getTable('Product');
        $productsData = $productTable->getProductList(array('get_deleted_also' => 1, 'no_limit' => 1), $productIds);
        $this->productsData = $productsData['list'];
        
        if ((int)$productsData['totalCount'] !== count($productIds)) {
            throw new ApiException('Some items are missing, also please check for duplicates!', 400);
        }
    }
    
    private function getAssocId($userId)
    {
        $storeAcTable = $this->getTable('Store');
        return $storeAcTable->getStoreAssocId($userId);
    }

    private function addItems()
    {
        $error = false;
        foreach ($this->productList as $item) {
            $price = $this->createOrderItem($item);
            if ($price === false || empty($price)) {
                $error = true;
                break;
            }
        }

        if ($error === true) {
            $statusParam = array();
            $statusParam['status'] = OrderStatusTable::CANCELLED;
            $statusParam['reason'] = 'Some error occured on insert.';
            $statusParam['order_id'] = $this->orderId;
            $this->getOrderStatusTable()->addOrderStatus($statusParam);

            throw new ApiException('Unable to create order, please try again!', 500);
        }
    }

    private function confirmOrder()
    {
        $this->getOrderStatusTable()->addOrderStatus(array(
            'order_id' => $this->orderId,
            'status' => OrderStatusTable::PENDING
        ));
    }

    private function createOrderItem($data)
    {
        $item = (array) $data;
        if (!isset($item['product_id'], $item['quantity'])) {
            return false;
        }

        // get product details
        $key = array_search($item['product_id'], array_column($this->productsData, 'id'));
        $productData = $this->productsData[$key];
        if (!$productData) {
            return false;
        }

        $actual = $productData['price'] * $item['quantity'];
        $total = $productData['srp'] * $item['quantity'];
        $parameter = array(
            'order_id' => $this->orderId,
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
            'is_added_by_cc' => $this->byCC
        );

        //create order status
        $orderItemTable = $this->getOrderItemTable();
        $res = $orderItemTable->addOrderItem($parameter);

        return ($res) ? true : false;
    }

    private function updateOrderWithPriceAndDelivery()
    {
        // calculate prices & update order 
        $priceCalc = new PriceCalculatorService($this->sm);
        $orderParam = $priceCalc->calculate($this->orderId);
        $orderParam['initial_order_value'] = $orderParam['net_amount']; // (items_cost+delivery charges)
        
        // use the loyality points
        $config = $this->sm->get('Config');
        if ($this->loyalityPoints && $this->loyalityPoints >= $config['app_settings']['min_balance_for_using_loyality_points']) {
            $orderParam['loyalty_points_used'] = 0;
            
            try {
                $loyalityService = $this->sm->get('LoyalityService');
                $loyalityService->debitForOrder($this->orderId, $this->loyalityPoints);
                
                $orderParam['loyalty_points_used'] = $this->loyalityPoints;
            } catch (ApiException $e) {
            }
            
            $orderParam['net_amount'] = $orderParam['net_amount'] - $orderParam['loyalty_points_used'];
        }

        $orderParam['delivered_by'] = $this->getDeliveryByDate();
        $res = $this->getOrderTable()->updateOrder($orderParam, array('id' => $this->orderId));

        if (!$res) {
            throw new ApiException('Unable to update order information, please try again!', 500);
        }

        $this->netAmount = $orderParam['net_amount'];
    }

    private function getDeliveryByDate()
    {
        if ($this->deliveredBy && $this->isValidDate($this->deliveredBy)) {
            return $this->deliveredBy;
        }

        // redundant
        date_default_timezone_set('Asia/Manila');

        // sunday orders will be delivered day after tomorrow
        if (date('N') === 7) {
            $datetime = new DateTime('tomorrow + 1day');
        } else if (date('H') < 18) {
            // working day & before 6pm            
            $datetime = new DateTime('tomorrow');
            if ((int) $datetime->format('N') === 7) {
                $datetime = new DateTime('tomorrow + 1day');
            }
        } else {
            // working day but after 6pm
            // so it should be considered as next day order
            // if next day or day after is weekend, add another date
            $datetime = new DateTime('tomorrow + 1day');
            if ((int) $datetime->format('N') === 0 || (int) $datetime->format('N') === 7) {
                $datetime = new DateTime('tomorrow + 2day');
            }
        }

        $deliveryDate = $datetime->format('Y-m-d');
        date_default_timezone_set('UTC');

        return $deliveryDate . ' 15:59:59';
    }

    private function isValidDate($date, $format = 'Y-m-d')
    {
        return $date == date($format, strtotime(trim($date)));
    }

    private function getOrderTable()
    {
        if (isset($this->orderTable)) {
            return $this->orderTable;
        }

        $this->orderTable = $this->getTable('Order');

        return $this->orderTable;
    }

    private function getOrderStatusTable()
    {
        if (isset($this->orderStatusTable)) {
            return $this->orderStatusTable;
        }

        $this->orderStatusTable = $this->getTable('OrderStatus');

        return $this->orderStatusTable;
    }

    private function getOrderItemTable()
    {
        if (isset($this->orderItemTable)) {
            return $this->orderItemTable;
        }

        $this->orderItemTable = $this->getTable('OrderItem');

        return $this->orderItemTable;
    }

}

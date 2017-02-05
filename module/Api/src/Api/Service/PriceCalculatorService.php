<?php

namespace Api\Service;

use Api\Service\BaseService;

class PriceCalculatorService extends BaseService
{

    protected $amount = 0; // price for items
    protected $discount = 0;
    protected $netAmount = 0;
    protected $deliveryCharges = 0;
    protected $promoId = 0;
    protected $loyalityPointsUsed = 0;
    protected $loyalityPointsEarn = 0;
    protected $returnItemAmount = 0;

    private function reset()
    {
        $this->amount = 0;
        $this->discount = 0;
        $this->netAmount = 0;
        $this->deliveryCharges = 0;
        $this->promoId = 0;
        $this->loyalityPointsUsed = 0;
        $this->loyalityPointsEarn = 0;
        $this->returnItemAmount = 0;
    }

    public function calculate($orderId, $available = 0)
    {
        $this->reset();

        $items = $this->getOrderItems($orderId, $available);
        $config = $this->sm->get('Config');
        foreach ($items as $item) {
            $this->amount += $item['amount'];
            $this->discount += $item['discount'];
        }

        $this->netAmount = ($this->amount - $this->discount);

        if ($this->getNetAmount() < $config['app_settings']['delivery_charges_applicable_below_amount']) {
            $this->deliveryCharges = $config['app_settings']['delivery_charges'];
            $this->netAmount = $this->netAmount + $this->deliveryCharges;
        }

        return $this->toArray();
    }

    private function getOrderItems($orderId, $available)
    {
        $orderItemTable = $this->getTable('OrderItem');
        $params = array('order_id' => $orderId);
        if ($available) {
            $params['is_available'] = 1;
        }
        $items = $orderItemTable->getOrderItemDetails($params);

        return $items['list'];
    }

    public function toArray()
    {
        $data = array();
        $data['amount'] = $this->getAmount();
        $data['discount'] = $this->getDiscount();
        $data['net_amount'] = $this->getNetAmount();
        $data['delivery_charges'] = $this->getDeliveryCharges();
        $data['promo_id'] = $this->getPromoId();
        $data['loyality_points_used'] = $this->getLoyalityPointsUsed();
        $data['loyality_points_earn'] = $this->getLoyalityPointsEarn();
        $data['return_item_amount'] = $this->getReturnItemAmount();

        return $data;
    }

    protected function roundPrice($price)
    {
        return (double) round($price, 2);
    }

    public function getAmount()
    {
        return $this->roundPrice($this->amount);
    }

    public function getDiscount()
    {
        return $this->roundPrice($this->discount);
    }

    public function getNetAmount()
    {

        return $this->roundPrice($this->netAmount);
    }

    public function getDeliveryCharges()
    {
        return $this->roundPrice($this->deliveryCharges);
    }

    public function getPromoId()
    {
        return $this->roundPrice($this->promoId);
    }

    public function getLoyalityPointsUsed()
    {
        return $this->roundPrice($this->loyalityPointsUsed);
    }

    public function getLoyalityPointsEarn()
    {
        return $this->roundPrice($this->loyalityPointsEarn);
    }

    public function getReturnItemAmount()
    {
        return $this->roundPrice($this->returnItemAmount);
    }

}

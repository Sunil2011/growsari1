<?php

namespace Api\Model;

use Base\Model\BaseModel;

class OrderItem extends BaseModel
{
    public $id;
    public $order_id;
    public $product_id;
    public $requested_quantity;
    public $quantity;
    public $super8_price;
    public $price;
    public $srp;
    public $amount;
    public $discount;
    public $net_amount;
    public $promo;
    public $is_available;
    public $is_added_by_cc;
    public $is_modified;
    public $quantity_by_cc;
    public $quantity_by_wh;
    public $is_deleted;
    public $created_at;
    public $updated_at;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    public function getProductId()
    {
        return $this->product_id;
    }

    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    public function getRequestedQuantity()
    {
        return $this->requested_quantity;
    }

    public function setRequestedQuantity($requested_quantity)
    {
        $this->requested_quantity = $requested_quantity;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getSuper8Price()
    {
        return $this->super8_price;
    }

    public function setSuper8Price($super8_price)
    {
        $this->super8_price = $super8_price;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getSrp()
    {
        return $this->srp;
    }

    public function setSrp($srp)
    {
        $this->srp = $srp;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    public function getNetAmount()
    {
        return $this->net_amount;
    }

    public function setNetAmount($net_amount)
    {
        $this->net_amount = $net_amount;
    }

    public function getPromo()
    {
        return $this->promo;
    }

    public function setPromo($promo)
    {
        $this->promo = $promo;
    }

    public function getIsAvailable()
    {
        return $this->is_available;
    }

    public function setIsAvailable($is_available)
    {
        $this->is_available = $is_available;
    }

    public function getIsAddedByCc()
    {
        return $this->is_added_by_cc;
    }

    public function setIsAddedByCc($is_added_by_cc)
    {
        $this->is_added_by_cc = $is_added_by_cc;
    }

    public function getIsModified()
    {
        return $this->is_modified;
    }

    public function setIsModified($is_modified)
    {
        $this->is_modified = $is_modified;
    }

    public function getQuantityByCc()
    {
        return $this->quantity_by_cc;
    }

    public function setQuantityByCc($quantity_by_cc)
    {
        $this->quantity_by_cc = $quantity_by_cc;
    }

    public function getQuantityByWh()
    {
        return $this->quantity_by_wh;
    }

    public function setQuantityByWh($quantity_by_wh)
    {
        $this->quantity_by_wh = $quantity_by_wh;
    }

    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    public function setIsDeleted($is_deleted)
    {
        $this->is_deleted = $is_deleted;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }
}

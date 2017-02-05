<?php

namespace Api\Model;

use Base\Model\BaseModel;

class LoyaltyPoint extends BaseModel
{
    public $id;
    public $account_id;
    public $order_id;
    public $store_refer_id;
    public $debit;
    public $credit;
    public $remarks;
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

    public function getAccountId()
    {
        return $this->account_id;
    }

    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    public function getStoreReferId()
    {
        return $this->store_refer_id;
    }

    public function setStoreReferId($store_refer_id)
    {
        $this->store_refer_id = $store_refer_id;
    }

    public function getDebit()
    {
        return $this->debit;
    }

    public function setDebit($debit)
    {
        $this->debit = $debit;
    }

    public function getCredit()
    {
        return $this->credit;
    }

    public function setCredit($credit)
    {
        $this->credit = $credit;
    }

    public function getRemarks()
    {
        return $this->remarks;
    }

    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;
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

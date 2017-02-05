<?php

namespace Api\Model;

use Base\Model\BaseModel;

class Survey extends BaseModel
{
    public $id;
    public $account_id;
    public $store_id;
    public $point_x;
    public $point_y;
    public $is_storeowner;
    public $spend_per_week;
    public $has_smartphone;
    public $photo;
    public $name;
    public $customer_name;
    public $address;
    public $contact_no;
    public $is_covered;
    public $funnel_status;
    public $revisit_date;
    public $revisit_time;
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

    public function getStoreId()
    {
        return $this->store_id;
    }

    public function setStoreId($store_id)
    {
        $this->store_id = $store_id;
    }

    public function getPointX()
    {
        return $this->point_x;
    }

    public function setPointX($point_x)
    {
        $this->point_x = $point_x;
    }

    public function getPointY()
    {
        return $this->point_y;
    }

    public function setPointY($point_y)
    {
        $this->point_y = $point_y;
    }

    public function getIsStoreowner()
    {
        return $this->is_storeowner;
    }

    public function setIsStoreowner($is_storeowner)
    {
        $this->is_storeowner = $is_storeowner;
    }

    public function getSpendPerWeek()
    {
        return $this->spend_per_week;
    }

    public function setSpendPerWeek($spend_per_week)
    {
        $this->spend_per_week = $spend_per_week;
    }

    public function getHasSmartphone()
    {
        return $this->has_smartphone;
    }

    public function setHasSmartphone($has_smartphone)
    {
        $this->has_smartphone = $has_smartphone;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCustomerName()
    {
        return $this->customer_name;
    }

    public function setCustomerName($customer_name)
    {
        $this->customer_name = $customer_name;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getContactNo()
    {
        return $this->contact_no;
    }

    public function setContactNo($contact_no)
    {
        $this->contact_no = $contact_no;
    }

    public function getIsCovered()
    {
        return $this->is_covered;
    }

    public function setIsCovered($is_covered)
    {
        $this->is_covered = $is_covered;
    }

    public function getFunnelStatus()
    {
        return $this->funnel_status;
    }

    public function setFunnelStatus($funnel_status)
    {
        $this->funnel_status = $funnel_status;
    }

    public function getRevisitDate()
    {
        return $this->revisit_date;
    }

    public function setRevisitDate($revisit_date)
    {
        $this->revisit_date = $revisit_date;
    }

    public function getRevisitTime()
    {
        return $this->revisit_time;
    }

    public function setRevisitTime($revisit_time)
    {
        $this->revisit_time = $revisit_time;
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

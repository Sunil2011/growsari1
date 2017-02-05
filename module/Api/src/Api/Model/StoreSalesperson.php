<?php

namespace Api\Model;

use Base\Model\BaseModel;

class StoreSalesperson extends BaseModel
{
    public $id;
    public $store_id;
    public $salesperson_account_id;
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

    public function getStoreId()
    {
        return $this->store_id;
    }

    public function setStoreId($store_id)
    {
        $this->store_id = $store_id;
    }

    public function getSalespersonAccountId()
    {
        return $this->salesperson_account_id;
    }

    public function setSalespersonAccountId($salesperson_account_id)
    {
        $this->salesperson_account_id = $salesperson_account_id;
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

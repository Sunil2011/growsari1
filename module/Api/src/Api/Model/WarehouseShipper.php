<?php

namespace Api\Model;

use Base\Model\BaseModel;

class WarehouseShipper extends BaseModel
{
    public $id;
    public $warehouse_id;
    public $shipper_id;
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

    public function getWarehouseId()
    {
        return $this->warehouse_id;
    }

    public function setWarehouseId($warehouse_id)
    {
        $this->warehouse_id = $warehouse_id;
    }

    public function getShipperId()
    {
        return $this->shipper_id;
    }

    public function setShipperId($shipper_id)
    {
        $this->shipper_id = $shipper_id;
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

<?php

namespace Api\Model;

use Base\Model\BaseModel;

class SalespersonTrack extends BaseModel
{
    public $id;
    public $salesperson_account_id;
    public $point_x;
    public $point_y;
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

    public function getSalespersonAccountId()
    {
        return $this->salesperson_account_id;
    }

    public function setSalespersonAccountId($salesperson_account_id)
    {
        $this->salesperson_account_id = $salesperson_account_id;
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

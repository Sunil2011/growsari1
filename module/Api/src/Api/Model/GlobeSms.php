<?php

namespace Api\Model;

use Base\Model\BaseModel;

class GlobeSms extends BaseModel
{
    public $id;
    public $sms_uid;
    public $sms_body;
    public $sms_body_with_header;
    public $status;
    public $order_id;
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

    public function getSmsUid()
    {
        return $this->sms_uid;
    }

    public function setSmsUid($sms_uid)
    {
        $this->sms_uid = $sms_uid;
    }

    public function getSmsBody()
    {
        return $this->sms_body;
    }

    public function setSmsBody($sms_body)
    {
        $this->sms_body = $sms_body;
    }

    public function getSmsBodyWithHeader()
    {
        return $this->sms_body_with_header;
    }

    public function setSmsBodyWithHeader($sms_body_with_header)
    {
        $this->sms_body_with_header = $sms_body_with_header;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
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

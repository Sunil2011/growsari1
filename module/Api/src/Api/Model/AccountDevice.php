<?php

namespace Api\Model;

use Base\Model\BaseModel;

class AccountDevice extends BaseModel
{
    public $id;
    public $account_id;
    public $device_token;
    public $token;
    public $app_version;
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

    public function getDeviceToken()
    {
        return $this->device_token;
    }

    public function setDeviceToken($device_token)
    {
        $this->device_token = $device_token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getAppVersion()
    {
        return $this->app_version;
    }

    public function setAppVersion($app_version)
    {
        $this->app_version = $app_version;
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

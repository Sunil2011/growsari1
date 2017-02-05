<?php

namespace Api\Service;

use Api\Globe\GlobeApi;
use Exception;

class SmsService
{

    protected $config;
    protected $logger;
    protected $sm;

    public function __construct($sm)
    {
        $this->sm = $sm;
        $this->config = $sm->get('config');
        $this->logger = $sm->get('logger');
    }

    public function sendSms($number, $message)
    {
        $config = $this->config;

        try {
            $globe = new GlobeApi();
            $sms = $globe->sms($config['globe']['short_code']);

            $accessToken = $this->getAccessToken($number);
            if (!$accessToken) {
                return false;
            }

            $response = $sms->sendMessage($accessToken, $number, $message);

            return $response;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    private function getAccessToken($number)
    {
        $storeGlobeTokenTable = $this->sm->get('Api\Table\GlobeStoreTokenTable');
        $tokenObj = $storeGlobeTokenTable->getAccessToken($number);
        if (!$tokenObj) {
            return false;
        }

        return $tokenObj['access_token'];
    }

}

<?php

namespace Base\Gateway;

use ZendService\Google\Gcm\Client;
use ZendService\Google\Gcm\Message;

class PnsService
{

    protected $config;
    protected $logger;

    public function __construct($sm)
    {
        $this->config = $sm->get('config');
        $this->logger = $sm->get('logger');
    }

    public function sendAndroidPns($parameter)
    {
        $config = $this->config;
        $apiKey = $config['google_api']['gcm_key'];

        try {
            $client = new Client();
            $client->getHttpClient()->setOptions(array('sslverifypeer' => false));
            $client->setApiKey($apiKey);

            $message = new Message();

            // up to 100 registration ids can be sent to at once
            $message->setRegistrationIds($parameter['device_token']);
            unset($parameter['device_token']);
            $message->setDelayWhileIdle(false);
            $message->setTimeToLive(600);
            $message->setDryRun(false);

            $message->setData($parameter);
            $message->toJson();

            $response = $client->send($message);

            return array(
                'success' => true,
                'successful' => $response->getSuccessCount(),
                'failure' => $response->getFailureCount(),
                'canonical' => $response->getCanonicalCount(),
                'message' => 'successful'
            );
        } catch (\Exception $e) {
            // Failed to connect to server. Throw an exception with a customized message.
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
            return array('success' => 'false', 'message' => $e->getMessage() . PHP_EOL);
        }
    }

}

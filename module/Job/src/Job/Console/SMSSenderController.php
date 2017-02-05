<?php

namespace Job\Console;

use Api\Service\SmsService;
use Base\Console\BaseController;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class SMSSenderController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        
        $this->logger = $this->getServiceLocator()->get('logger');

        $this->sender = $request->getParam('sender');
        $this->message = $request->getParam('message');
        if (!$this->sender || !$this->message) {
            $this->logger->debug('SMSSenderController: Required parameters missing');
            return "Required parameters missing";
        }

        echo "Processing \n";
        $this->logger->debug('SMSSenderController sender:message '. $this->sender .':'.$this->message);
        $this->processSms();
        echo "Processed \n";
    }

    public function processSms()
    {
        $smsService = new SmsService($this->serviceLocator);
        $smsService->sendSms($this->sender, $this->message);
    }

}

<?php

namespace Job\Console;

use Base\Console\BaseController;
use Base\Gateway\PnsService;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class GCMSenderController extends BaseController
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

        $this->orderId = $request->getParam('order_id');
        $this->deviceToken = $request->getParam('device_token');
        $this->pnsType = $request->getParam('pns_type');
        $this->message = $request->getParam('message');
        if (!$this->deviceToken || !$this->message) {
            $this->logger->debug('GCMSenderController: Required parameters missing');
            return "Required parameters missing";
        }
        
        $this->logger->debug('GCMSenderController deviceToken:message '. $this->deviceToken .':'.$this->message);
        echo "Processing \n";
        $this->processSms();
        echo "Processed \n";
    }

    public function processSms()
    {
        $pnObj = new PnsService($this->serviceLocator);
        return $pnObj->sendAndroidPns(array(
            'order_id' => $this->orderId,
            'device_token' => array($this->deviceToken),
            'pns_type' => array($this->pnsType),
            'message' => array($this->message),
        ));
    }

}

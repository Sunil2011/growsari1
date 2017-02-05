<?php

namespace Api\Console;

use Base\Console\BaseController;
use Base\Gateway\PnsService;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class PNSTestController extends BaseController
{

    protected $prettyPrinter;
    protected $tableName;

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->orderId = $request->getParam('order_id');
        if (!$this->orderId) {
            return "order id missing \n";
        }
        
        $this->status = $request->getParam('status', 'delivered_order');
        
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $tok = $orderTable->getStoreUserId($this->orderId);
        if (!$tok || !$tok['device_token']) {
            return "No token found\n";
        }

        $pnParam = array(
            'order_id' => $this->orderId,
            'device_token' => array($tok['device_token'])
        );

        if ($this->status == 'dispatched') {
            $pnParam['pns_type'] = 'dispatched_order';
            $pnParam['message'] = 'Your order #'.$this->orderId.' is out for delivery.';
        } else {
            $pnParam['pns_type'] = 'delivered_order';
            $pnParam['message'] = 'Your order #'.$this->orderId.' has been successfully delivered';
        }

        $pnObj = new PnsService($this->serviceLocator);
        $response = $pnObj->sendAndroidPns($pnParam);
        
        print_r($response);
        exit;
    }


}

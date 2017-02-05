<?php

namespace Job\Console;

use Base\Console\BaseController;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ReferralBonusController extends BaseController
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
        if (!$this->orderId) {
            $this->logger->debug('ReferalBonusController: Required parameters missing');
            return "Required parameters missing";
        }

        echo "Processing \n";
        $this->processSms();
        echo "Processed \n";
    }

    public function processSms()
    {
        // check first order or not
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $this->orderDetail = $orderTable->isFirstOrder($this->orderId);
        if (!$this->orderDetail || $this->orderDetail['no_of_orders']>1) {
            echo $msg = 'ReferalBonusController: Not first order skipping '. $this->orderId ."\n";
            $this->logger->debug($msg);
            return;
        }
        
        if ($this->orderDetail['no_of_orders'] == 0) {
            echo $msg = 'ReferalBonusController: Order not delivered yet '. $this->orderId ."\n";
            $this->logger->debug($msg);
            return;
        }
        
        // get the refer store id
        $storeReferTable = $this->getServiceLocator()->get('Api\Table\StoreReferTable');
        $data = $storeReferTable->getStoreReferredDetails($this->orderDetail['store_id']);
        if (!$data) {
            return false;
        }
        
        if (empty($data['sore_refer_id']) || empty($data['refered_account_id'])) { 
            echo $msg = 'ReferalBonusController: Not referral found'. $this->orderId ."\n";
            $this->logger->debug($msg);
            return false;
        }
        
        $config = $this->getServiceLocator()->get('Config');
        
        // credit loyalty points
        $loyalityService = $this->getServiceLocator()->get('LoyalityService');
        $loyalityService->creditForReferal($data['refered_account_id'], $data['sore_refer_id'], $config['app_settings']['loyalty_referal_bonus']);
       
        echo $msg = 'ReferalBonusController: succssfully given for this referral:'. $this->sore_refer_id ."\n";
        $this->logger->debug($msg);
    }

}

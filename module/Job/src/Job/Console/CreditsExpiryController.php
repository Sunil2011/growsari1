<?php

namespace Job\Console;

use Api\Service\SmsService;
use Base\Console\BaseController;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class CreditsExpiryController extends BaseController
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

        echo "Processing \n";
        $this->process();
        echo "Processed \n";
    }

    public function process()
    {
        //If they order within 7 days, the full points stay
        //If they don't order 7 days after sign up, the points disappear
        //send sms before 1-2days if not order placed
        $orderTable = $this->getServiceLocator()->get('Api\Table\LoyaltyPointTable');
        $accounts = $orderTable->getAccountsForCreditExpiry();
        if (!$accounts) {
            echo $msg = 'CreditsExpiryController: Not orders available, skipping '. $this->orderId ."\n";
            $this->logger->debug($msg);
            return;
        }
        
        foreach ($accounts['list'] as $account) {
            if ($account['no_order_days_since_signup'] == 5) {
                $this->sendSms($account['contact_no']);
            } else if ($account['no_order_days_since_signup'] >= 7) {
                $this->removeCredits($account['account_id'], $account['points']);
            }            
        }
    }
    
    private function removeCredits($accountId, $points)
    {
        echo 'in remove credits';
        $loyalityService = $this->getServiceLocator()->get('LoyalityService');
        $loyalityService->signUpCreditsExpired($accountId, $points);
       
        echo $msg = 'CreditsExpiryController: succssfully removed credits for this :'. $accountId ."\n";
        $this->logger->debug($msg);
    }
    
    private function sendSms($sender)
    {
        echo 'in sms';
        $smsService = new SmsService($this->serviceLocator);
        $smsService->sendSms($sender, "Your signup credits will expire in two days, place an order within two days to avail them.");
       
        echo $msg = 'CreditsExpiryController: succssfully sent sms for this :'. $sender ."\n";
        $this->logger->debug($msg);
    }

}

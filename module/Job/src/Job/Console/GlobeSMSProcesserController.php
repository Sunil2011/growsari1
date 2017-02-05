<?php

namespace Job\Console;

use Api\Exception\ApiException;
use Api\Service\CreateOrderService;
use Base\Console\BaseController;
use DateTime;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class GlobeSMSProcesserController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->id = $request->getParam('id', 0);
        if (!$this->id) {
            return "File not found. Plese provide proper file path";
        }
        
        $this->logger = $this->getServiceLocator()->get('logger');

        echo "Processing \n";
        $this->logger->debug('GlobeSMSProcesserController Started with '. $this->id);
        $this->processSms();
        echo "Processed \n";
    }

    public function processSms()
    {
        $globeSmsTable = $this->getServiceLocator()->get('Api\Table\GlobeSmsTable');
        $smsObj = $globeSmsTable->getByField(array(
            'sms_uid' => $this->id,
            'status' => 'CREATED'
        ), true);
        if (!$smsObj) {
            $this->logger->debug('GlobeSMSProcesserController No record found or already processed. ID: '. $this->id);
            return;
        }
        
        if ($this->process($smsObj)) {
            $globeSmsTable->updateGlobeSms(array(
                'status' => 'PROCESSED'
            ), array('sms_uid' => $this->id));
        }
    }

    private function fromJson($jsonString)
    {
        $body = stripslashes($jsonString);
        if (!empty($body)) {
            $json = json_decode($body, true);
            if (!empty($json)) {
                return $json;
            }
        }

        return false;
    }
    
    private function process($smsArray)
    {
        $productListStrArr = array();
        foreach ($smsArray as $sms) {
            $data = $this->fromJson($sms['sms_body_with_header']);
            
            if (empty($data["inboundSMSMessageList"])) {
                $this->logger->debug('GlobeSMSProcesserController Not valid message '. $this->id);
                return false;
            }
            
            foreach ($data["inboundSMSMessageList"]["inboundSMSMessage"] as $entry) {
                $msg = $entry["message"];
                $sender = $entry["senderAddress"];
                
                // if msg contains | 3 times, assume its an order request 
                $messageArray = explode(">", $msg);
                $headerArray = explode("|", $messageArray[0]);
                if (count($headerArray) !== 3) {
                    return false;
                }
                
                $productListStrArr[$headerArray[2] - 1] = $messageArray[1];
            }
        }
        
        ksort($productListStrArr);
        $bodyArray = explode('|', implode('', $productListStrArr));
        if (count($bodyArray) !== 4) {
            return false;
        }
        
        $orderId = $this->createOrderSms($bodyArray, $sender);
        if (!$orderId) {
            return false;
        }
        
        // update the sms
        $globeSmsTable = $this->getServiceLocator()->get('Api\Table\GlobeSmsTable');
        $globeSmsTable->updateGlobeSms(array(
            'order_id' => $orderId,
        ), array(
            'sms_uid' => $this->id
        ));
        
        return true;
    }

    private function createOrderSms($messageArray, $sender)
    {
        // parse json product array & prepare data
        $parameter['user_id'] = $messageArray[0];
        $parameter['use_loyality_points'] = (!empty($messageArray[3])) ? $messageArray[3] : 0;
        $parameter['last_updated_at'] = $this->getDateTime();
        $parameter['delivered_by'] = (!empty($messageArray[2])) ? $messageArray[2] : '';
        $parameter['product_list'] = $this->parseSMSString((!empty($messageArray[1])) ? $messageArray[1] : '');
        if ($parameter['product_list'] === false) {
            return false;
        }
        
        try {
            // create Order
            $sender = $this->formatSenderAddrss($sender);
            $createOrderService = new CreateOrderService($this->serviceLocator);
            $res = $createOrderService->create(
                $parameter['user_id'], 
                $parameter['product_list'], 
                $parameter['delivered_by'], 
                $parameter['last_updated_at'], 
                $parameter['use_loyality_points'], 
                $sender
            );

            // send confirmation sms to sender
            if (!empty($res['data']['order_id'])) {
                $message = "Your order has been placed, Order id #" . $res['data']['order_id'] .". Order value is PHP" . $res['data']['net_amount'];
                $process = $this->getServiceLocator()->get("Base\Utils\Process");
                $process->start('sms-sender --sender="' . $sender. '" --message="'. $message .'"');
            } else {
                return false;
            }
        } catch (ApiException $e) {
            $this->logger->debug($e->getMessage(). "; ID: ". $this->id);
            return false;
        }

        return !empty($res['data']['order_id']) ? $res['data']['order_id'] : false;
    }

    private function parseSMSString($string)
    {
        if (!$string) {
            return false;
        }

        $productsList = array();
        // 1:10;2:5
        $items = explode(';', $string);
        foreach ($items as $item) {
            $itemArray = explode(':', $item);
            
            $productItem = array(
                'product_id' => $itemArray[0],
                'quantity' => $itemArray[1]
            );
            $productsList[] = $productItem;
        }

        return count($productsList) ? $productsList : false;
    }
    
    public function getDateTime()
    {
        $date = new DateTime();
        return $date->format('Y-m-d H:i:s');
    }
    
    private function formatSenderAddrss($sender)
    {
        $prefix = 'tel:';
        if (substr($sender, 0, strlen($prefix)) == $prefix) {
            $sender = substr($sender, strlen($prefix));
        } 
        
        $prefix = '+63';
        if (substr($sender, 0, strlen($prefix)) == $prefix) {
            $sender = substr($sender, strlen($prefix));
        } 
        
        $this->logger->debug("Sender: ". $sender);
            
        return $sender;
    }

}

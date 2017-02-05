<?php

namespace Api\Console;

use Base\Console\BaseController;
use Base\Gateway\PnsService;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class SendPNSController extends BaseController
{

    protected $pns;

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->message = $request->getParam('message');
        if (!$this->message) {
            return "Please provide message\n";
        }
        
//        $this->deviceTokens = array("crdWAFTHeXk:APA91bHnlk3qp2K718ajCnngWstB9Wm1rQFGoLyih7UMv0S_3_PvHGrWxCeHYidPj-fs7ibAxrwq8aGTdoguSqPJfrgXb8-uloJD1VOjvXYgMIxIvZAzj6tjw2f3cNxOiUKyClskUUs8");
//        $this->pns = new PnsService($this->serviceLocator); 
//        $this->sendPNS();exit;
        
        $this->process();
    }

    private function process()
    {
        $this->pns = new PnsService($this->serviceLocator);
        $this->tokens = $this->getDeviceTokens();

        $count = count($this->tokens['list']);
        $this->deviceTokens = array();
        for ($i = 0; $i < $count; $i++) {            
            if ($i != 0 && $i % 100 === 0) {
                $this->sendPNS();                
            }
            
            $this->deviceTokens[] = $this->tokens['list'][$i]['device_token'];
        }


        $this->sendPNS();
        
        return;
    }

    private function sendPNS()
    {
        if (!count($this->deviceTokens)) {
            return;
        }
        
        $pnParam = array(
            'order_id' => "GrowSari promo",
            'device_token' => $this->deviceTokens,
            'message' => $this->message,
            'pns_type' => 'confirm_order'
        );
        var_dump($this->pns->sendAndroidPns($pnParam));
        
        $this->deviceTokens = array();
    }

    private function getDeviceTokens($page = 1)
    {
        $accountDeviceTable = $this->getServiceLocator()->get('Api\Table\AccountDeviceTable');
        $tokens = $accountDeviceTable->getAccountDeviceList($page);

        return $tokens;
    }

}

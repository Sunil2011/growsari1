<?php

namespace Api\Console;

use Base\Console\BaseController;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class LoyaltyPointCreditForOrderController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->orderId = $request->getParam('order_id');

        echo "Started processing\n";
        $this->process();
        return "Successfully processed\n";
    }

    public function process()
    {
        $orderTable =  $this->getServiceLocator()->get('Api\Table\OrderTable');
        $orders = $orderTable->getOrdersMissingLoyaltyCredits($this->orderId);
        if ($orders === false) {
            echo "No Missing orders found\n";
            return;
        }
        
        foreach ($orders['list'] as $order) {
            $this->creditPoints($order);
        }
    }

    public function creditPoints($order)
    {
        if (!$order) {
            return;
        }
        var_dump($order);
        echo "Processing order" . $order['id'] . "\n";
        
        // credit loyalty points
        $param = array();
        $loyalityService =  $this->getServiceLocator()->get('LoyalityService');
        $param['loyalty_points_earn'] = $loyalityService->creditForOrder($order['id'], $order['amount_collected']);

        // update order
        $orderTable =  $this->getServiceLocator()->get('Api\Table\OrderTable');
        $res = $orderTable->updateOrder($param, array('id' => $order['id']));
        if ($res === false) {
            throw new ApiException('Unable to update order, please try again!', 500);
        }
        
        var_dump($param);

        return true;
    }

}

<?php

namespace Api\Console;

use Api\Table\OrderStatusTable;
use Base\Console\BaseController;
use RuntimeException;
use Zend\Console\Request as ConsoleRequest;

class AutoCancelController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        // get the order which have delivery date set
        // not confirmed yet by user
        // and 6 hours has been passed
        $orderStatusTable = $this->getServiceLocator()->get('Api\Table\OrderStatusTable');
        $orders = $orderStatusTable->getNonConfirmedOrders(6);


        $orderService = $this->getServiceLocator()->get('OrderService');
        foreach ($orders as $order) {
            $orderService->setOrderStatus(array(
                'status' => OrderStatusTable::CANCELLED,
                'order_id' => $order['order_id'],
            ));
        }
    }

}

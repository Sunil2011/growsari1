<?php

namespace Api\Controller;

use Api\Exception\ApiException;
use Api\Service\CreateOrderService;
use Api\Service\EditOrderService;
use Api\Service\OrderStatusService;
use Api\Table\AccountTable;
use Api\Table\OrderStatusTable;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class OrderController extends BaseApiController
{

    /**
     * @SWG\Post(
     *     path="/api/order",
     *     description="create order",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="last_updated_at",
     *         in="formData",
     *         description="last updated time (yyyy-MM-dd H:i:s)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="products",
     *         in="formData",
     *         description="List of product : [{'product_id' : id, 'quantity' : q}]",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="delivered_by",
     *         in="formData",
     *         description="delivery date eg: yyyy-mm-dd",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="use_loyality_points",
     *         in="formData",
     *         description="amount should be used from loyality",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function create()
    {
        if (!$this->isMobileApp()) {
            $config = $this->getServiceLocator()->get('Config');
            return new JsonModel(array(
                'success' => false,
                'message' => $config['app_clients']['store_upgrade_msg'],
                'error' => array('type' => 'errro', 'message' => $config['app_clients']['store_upgrade_msg'], 'code' => 400, 'is_update' => false)
            ));
        }
        
        $parameter = $this->getCreateOrderParameters();

        $storeUser = $this->checkStoreSession();
        $parameter['user_id'] = $storeUser['id'];

        // create Order
        $createOrderService = new CreateOrderService($this->serviceLocator);
        $res = $createOrderService->create(
                $parameter['user_id'], $parameter['product_list'], $parameter['delivered_by'], $parameter['last_updated_at'], $parameter['use_loyality_points']
        );

        if (!empty($res['data']['order_id']) && !empty($storeUser['store']['contact_no'])) {
            $message = "Your order has been placed, Order id #" . $res['data']['order_id'] . ". Order value is PHP" . $res['data']['net_amount'];
            $process = $this->getServiceLocator()->get("Base\Utils\Process");
            $process->start('sms-sender --sender="' . $storeUser['store']['contact_no']. '" --message="'. $message .'"');
        }

        return new JsonModel($res);
    }

    /**
     * @SWG\Get(
     *     path="/api/order",
     *     description="order list",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="user_id",
     *         in="formData",
     *         description="User Id",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="query",
     *         description="order current status",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="is_saved",
     *         in="query",
     *         description="To get saved orders : true or false",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="page number",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="items per page",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getList()
    {
        $user = $this->checkUserSession();
        $parameter = $this->getParameter($this->params()->fromQuery());
        $parameter['user_id'] = $user['id'];
        if ($this->isDeliveryMobileApp()) {
            $parameter['limit'] = 50;
        }

        //get associat id
        if ($user['type'] === AccountTable::TYPE_STORE) {
            $accountTable = $this->getServiceLocator()->get('Api\Table\AccountDeviceTable');
            $data = $accountTable->getStoreAssocId($parameter['user_id']);
            if (!$data) {
                throw new ApiException('Unauthorized Access!', 403);
            }
            $parameter['associate_id'] = $data['associate_id'];
        }
        if ($user['type'] === AccountTable::TYPE_SHIPPER) {
            $parameter['is_shipper'] = 1;
            if ($user['role'] === AccountTable::ROLE_USER) {
                $parameter['shipper_team_account_id'] = $parameter['user_id'];
            }
        }

        //get order detail
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $data = $orderTable->getOrderList($parameter);
        if ($data === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        $data = $this->convertImageNameToUrl($data, array("store_photo" => 'store'));
 
        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/order/{id}",
     *     description="order details",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="order id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="user_id",
     *         in="formData",
     *         description="UserId",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="user_type",
     *         in="formData",
     *         description="Type of user",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function get($id)
    {
        $user = $this->checkUserSession();
        $param = $this->getParameter($this->params()->fromQuery());
        $param['user_id'] = $user['id'];

        //get associate id
        if ($user['type'] === AccountTable::TYPE_STORE) {
            $accountTable = $this->getServiceLocator()->get('Api\Table\AccountDeviceTable');
            $accData = $accountTable->getStoreAssocId($param['user_id']);
            if (!$accData) {
                throw new ApiException('Unauthorized Access!', 403);
            }
            $param['associate_id'] = $accData['associate_id'];
        }

        $data = $this->getOrderFullInformation($id, $param);

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/order/{id}/invoice",
     *     description="order invoice details",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="order id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function invoiceAction()
    {
        $this->checkUserSession();
        $param = $this->getParameter($this->params()->fromRoute());
        $id = $param['id'];

        $data = $this->getOrderFullInformation($id, $param);

        $data['order']['non_available_items'] = $this->getOrderItemDetails(array(
            'not_available' => 1,
            'order_id' => $id
        ));

        $viewModel = new ViewModel();
        $viewModel->setVariables(array('order' => $data['order']))
                ->setTerminal(true);

        return $viewModel;
    }
    
    /**
     * @SWG\Get(
     *     path="/api/order/{id}/picklist/{type}",
     *     description="order invoice details",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="order id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="path",
     *         description="all or new",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function picklistAction()
    {
        $this->checkUserSession();
        $param = $this->getParameter($this->params()->fromRoute());
        
        $data = $this->getOrderItemInformation($param);
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array('order' => $data))
            ->setTerminal(true);

        return $viewModel;
    }

    /**
     * @SWG\Post(
     *     path="/api/order/add-items-to-existing",
     *     description="add-items-to-order",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="Order Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="products",
     *         in="formData",
     *         description="List of product : [{'product_id' : id, 'quantity' : q}]",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function addItemsToExistingAction()
    {
        $this->checkCallCenterSession();

        $parameter = $this->params()->fromPost();
        if (!isset($parameter['products']) || !isset($parameter['order_id'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        // parse json product array & prepare data
        $parameter['product_list'] = $this->parseJSONString($parameter['products']);
        if ($parameter['product_list'] === false) {
            throw new ApiException('Please check the product list!', 400);
        }

        $orderService = new EditOrderService($this->serviceLocator);
        $res = $orderService->addItems($parameter['order_id'], $parameter['product_list'], 1);
        if (!$res) {
            throw new ApiException('Unable to add items, please try again!', 400);
        }

        return $this->successRes('Successfully added!');
    }

    /**
     * @SWG\Post(
     *     path="/api/order/change-delivery-date",
     *     description="change-delivery-date",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="Order Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="delivered_by",
     *         in="formData",
     *         description="delivered_by yyy-mm-dd",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function changeDeliveryDateAction()
    {
        $this->checkCallCenterSession();

        $parameter = $this->params()->fromPost();
        if (!isset($parameter['delivered_by']) || !isset($parameter['order_id'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        $orderService = new EditOrderService($this->serviceLocator);
        $res = $orderService->changeDeliveryDate($parameter['order_id'], $parameter['delivered_by']);
        if (!$res) {
            throw new ApiException('Unable to set delivery date, please try again!', 400);
        }

        return $this->successRes('Successfully updated!');
    }

    /**
     * @SWG\Post(
     *     path="/api/order/update-status",
     *     description="update status",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="Order Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="formData",
     *         description="cancelled or confirmed or packed or dispatched or delivered",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="item_status",
     *         in="formData",
     *         description="item status. required on confirmed status : [{item_id : id, is_available : TRUE/FALSE}]",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="amount",
     *         in="formData",
     *         description="amount collected, required if status is delivered",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="is_return",
     *         in="formData",
     *         description="if any return : true or false",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="return_items",
     *         in="formData",
     *         description="list of item returned : [{'item_id': 'id', 'quantity' : 'q', 'reason' : '$reason', 'image' : '$image'}], required if any item returned on delivered",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function updateStatusAction()
    {
        $user = $this->checkUserSession();
        $parameter = $this->getParameter($this->params()->fromPost());
        if (!isset($parameter['order_id'], $parameter['status'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        //return item array
        if (isset($parameter['is_return'], $parameter['return_items']) && $parameter['is_return']) {
            $parameter['items'] = $this->parseJSONString($parameter['return_items']);
            if ($parameter['items'] === false) {
                $error['is_update'] = FALSE;
                return $this->errorRes('Return item field is not proper', $error, 400);
            }
        }

        if (isset($parameter['item_status'])) {
            $parameter['item'] = $this->parseJSONString($parameter['item_status']);
        }
        
        if ($user['type'] === AccountTable::TYPE_CALLCENTER) {
            $parameter['callcenter'] = 1;
        }

        $orderService = new OrderStatusService($this->serviceLocator);
        $res = $orderService->setOrderStatus($parameter);
        if (!isset($res['error']) && ($parameter['status'] == 'dispatched' || $parameter['status'] == 'delivered')) {
            $this->sendPushNotification($parameter['order_id'], $parameter['status']);
            $this->sendSMS($parameter['order_id'], $parameter['status']);
        }

        return new JsonModel($res);
    }

    /**
     * @SWG\Post(
     *     path="/api/order/save-order",
     *     description="save order",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="order id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function saveOrderAction()
    {
        $this->checkStoreSession();

        $parameter = $this->getParameter($this->params()->fromPost());
        if (!isset($parameter['order_id'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $res = $orderTable->updateOrder(array('is_saved' => 1), array('id' => $parameter['order_id']));
        if ($res === false) {
            throw new ApiException('Unable to update, please try again!', '500');
        }

        return $this->successRes('Successfully Added');
    }

    /**
     * @SWG\Post(
     *     path="/api/order/assign-shipper",
     *     description="save order",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="order id",
     *         required=true,
     *         type="integer"
     *     ),
     *      @SWG\Parameter(
     *         name="shipper_team_id",
     *         in="formData",
     *         description="shipper_team_id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function assignShipperAction()
    {
        $this->checkShipperSession();

        $parameter = $this->getParameter($this->params()->fromPost());
        if (!isset($parameter['order_id']) || !isset($parameter['shipper_team_id'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $res = $orderTable->getOrderDetails($parameter['order_id'], array());
        if ($res === false) {
            throw new ApiException('No record found!', 404);
        }

        $resUpdate = $orderTable->updateOrder(array('shipper_team_id' => $parameter['shipper_team_id']), array('id' => $parameter['order_id']));
        if ($resUpdate === false) {
            throw new ApiException('Unable to update, please try again!', 500);
        }

        return $this->successRes('Successfully assigned');
    }

    /**
     * @SWG\Post(
     *     path="/api/order/upload-return",
     *     description="unpack items in boxes",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="photo",
     *         in="formData",
     *         description="photo",
     *         required=true,
     *         type="file"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  )
     */
    public function uploadReturnAction()
    {
        $this->checkShipperSession();

        $photo = $this->upload('photo', "uploads/returns/");
        if (!$photo) {
            throw new ApiException('Unable to process uploaded image, please try again!', '500');
        }

        return $this->successRes('Successfully uploaded', array('image' => $photo));
    }

    /**
     * @SWG\Get(
     *     path="/api/order/get-order-status-count",
     *     description="order count",
     *     tags={"order"},
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getOrderStatusCountAction()
    {
        $user = $this->checkUserSession();

        if ($user['type'] === AccountTable::TYPE_WAREHOUSE
            || $user['type'] === AccountTable::TYPE_SHIPPER
        ) {
            $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
            $res = $orderTable->getCounts();
            if ($res === false) {
                return $this->errorRes('Some error occured', array(), 500);
            }

            $count = $res['confirmed_orders_count'] + $res['readytopack_orders_count'];
            $res['all_confirmed_orders_count'] = ($count) ? $count : null;
        }

        if ($user['type'] === AccountTable::TYPE_SHIPPER) {
            $shipperCount = $orderTable->getAssignShipperCounts();
            $res['assign_shipper_count'] = ($shipperCount) ? $shipperCount : null;
        }
        
        if ($user['type'] === AccountTable::TYPE_CALLCENTER) {
            $orderTaskTable = $this->getServiceLocator()->get('Api\Table\OrderTaskTable');
            $taskCount = $orderTaskTable->getTaskCount();
            $res['task_count'] = ($taskCount) ? $taskCount : null;
        }

        return $this->successRes('Successfully fetched', $res);
    }

    /**
     * @SWG\Post(
     *     path="/api/order/add-order",
     *     description="add order by cust care",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="last_updated_at",
     *         in="formData",
     *         description="last updated time (yyyy-MM-dd H:i:s)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="store_id",
     *         in="formData",
     *         description="store id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="products",
     *         in="formData",
     *         description="List of product : [{'product_id' : id, 'quantity' : q}]",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="delivered_by",
     *         in="formData",
     *         description="delivery date eg: yyyy-mm-dd",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="use_loyality_points",
     *         in="formData",
     *         description="amount should be used from loyality",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function addOrderAction()
    {
        $this->checkCallCenterSession();
        
        $parameter = $this->getCreateOrderParameters();
        
        // get store details
        $storeId = $this->params()->fromPost('store_id');
        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $storeData = $storeTable->getStoreDetails($storeId);
        if ($storeData === false || empty($storeData)) {
            throw new ApiException('Store not found!', 400);
        }

        // parse json product array & prepare data
        $parameter['user_id'] = $storeData['account_id'];

        // create Order
        $createOrderService = new CreateOrderService($this->serviceLocator);
        $res = $createOrderService->create(
            $parameter['user_id'], 
            $parameter['product_list'], 
            $parameter['delivered_by'], 
            $parameter['last_updated_at'], 
            $parameter['use_loyality_points'], 
            null, 
            1
        );

        if (!empty($res['data']['order_id']) && !empty($storeData['contact_no'])) {
            $message = "Your order has been placed, Order id #" . $res['data']['order_id'] . ". Order value is PHP" . $res['data']['net_amount'];
            $process = $this->getServiceLocator()->get("Base\Utils\Process");
            $process->start('sms-sender --sender="' . $storeData['contact_no']. '" --message="'. $message .'"');
        }

        return new JsonModel($res);
    }
    
    /**
     * @SWG\Post(
     *     path="/api/edit-order-item",
     *     description="edit order item",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="product_id",
     *         in="formData",
     *         description="Item Id to be added",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="Order Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="quantity",
     *         in="formData",
     *         description="quantity of items",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function editOrderItemAction()
    {
        $this->checkCallCenterSession();
        
        $parameter = $this->params()->fromPost();
        if (!isset($parameter['product_id'],$parameter['order_id'],$parameter['quantity'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }
        
        $orderService = new EditOrderService($this->serviceLocator);
        $orderService->editItem($parameter);
        
        return $this->successRes('Successfully updated!');
    }
    
    /**
     * @SWG\Post(
     *     path="/api/delete-order-item",
     *     description="delete order item",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="item_id",
     *         in="formData",
     *         description="Item Id to be added",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="Order Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function deleteOrderItemAction()
    {
        $this->checkCallCenterSession();
        
        $parameter = $this->params()->fromPost();
        if (!isset($parameter['item_id'],$parameter['order_id'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }
        
        $orderService = new EditOrderService($this->serviceLocator);
        $orderService->deleteItem($parameter);
        
        return $this->successRes('Successfully updated!');
    }

    /**
     * @SWG\Post(
     *     path="/api/order/add-loyalty-point",
     *     description="add loyalty points to order",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="Order id",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="loyalty_point",
     *         in="formData",
     *         description="amount to be use as loyalty point",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="remark",
     *         in="formData",
     *         description="remark",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function addLoyaltyPointAction()
    {
        $this->checkCallCenterSession();
        $parameter = $this->params()->fromPost();

        if (!isset($parameter['order_id'], $parameter['loyalty_point'], $parameter['remark']) || empty($parameter['order_id']) || empty($parameter['loyalty_point'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        //adding points to order
        $orderService = new EditOrderService($this->serviceLocator);
        $orderService->applyLoyaltyPoints($parameter['order_id'], $parameter['loyalty_point'], $parameter['remark']);

        return $this->successRes('Loyalty points successfully added.');
    }
    
    /**
     * @SWG\Post(
     *     path="/api/order/task-finish",
     *     description="task-finish",
     *     tags={"order"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="order id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="task_id",
     *         in="formData",
     *         description="task_id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function taskFinishAction()
    {
        $this->checkCallCenterSession();

        $parameter = $this->getParameter($this->params()->fromPost());
        if (!isset($parameter['order_id'], $parameter['task_id'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }
        
        $orderTaskTable = $this->getServiceLocator()->get('Api\Table\OrderTaskTable');
        $orderTaskObj = $orderTaskTable->getCurrentOrderTask($parameter['order_id']);
        if (!$orderTaskObj) {
            throw new ApiException('No task found for this order!', 404);
        }
        
        if ($orderTaskObj['id'] != $parameter['task_id']) {
            throw new ApiException('Task id is not matching!', 403);
        }

        $res = $orderTaskTable->updateOrderTask(array('is_finished' => 1), array('id' => $orderTaskObj['id']));
        if ($res === false) {
            throw new ApiException('Unable to update, please try again!', '500');
        }

        return $this->successRes('Successfully finished the task!');
    }

    private function sendPushNotification($orderId, $status)
    {
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $tok = $orderTable->getStoreUserId($orderId);
        if (empty($tok['device_token'])) {
            return;
        }

        $pnParam = array(
            'order_id' => $orderId,
            'device_token' => $tok['device_token']
        );
        if ($status == OrderStatusTable::SHIPPED) {
            $pnParam['pns_type'] = 'dispatched_order';
            $pnParam['message'] = 'Your order #' . $orderId . ' is out for delivery.';
        } else if ($status == OrderStatusTable::DELIVERED) {
            $pnParam['pns_type'] = 'delivered_order';
            $pnParam['message'] = 'Your order #' . $orderId . ' has been successfully delivered.';
        } else if ($status == OrderStatusTable::CONFIRMED) {
            $pnParam['pns_type'] = 'confirm_order';
            $pnParam['message'] = 'Your order has been confirmed!';
        }

        $params = '';
        foreach ($pnParam as $key => $value) {
            $params .= ' --' . $key . '="' . $value . '"';
        }

        $process = $this->getServiceLocator()->get("Base\Utils\Process");
        $process->start("gcm-sender " . $params);
    }

    private function sendSMS($orderId, $status)
    {
        $orderObj = $this->getOrderDetails($orderId, array());
        if (!$orderObj || !$orderObj['sms_sender']) {
            return false;
        }

        if ($status == OrderStatusTable::DELIVERED) {
            $loyaltyPointTable = $this->getServiceLocator()->get('Api\Table\LoyaltyPointTable');
            $pointObj = $loyaltyPointTable->getUserPointsByAccountId($orderObj['account_id']);
            $points = !empty($pointObj['points']) ? $pointObj['points'] : 0;

            $message = 'Your order #' . $orderId . ' has been successfully delivered.';
            $message .= "Your total loyal points are " . $points . ".";

            $process = $this->getServiceLocator()->get("Base\Utils\Process");
            $process->start('sms-sender --sender="' . $orderObj['sms_sender'] . '" --message="' . $message . '"');
        }

        return false;
    }

    private function getOrderItemDetails($itemFilter)
    {
        $orderItemTable = $this->getServiceLocator()->get('Api\Table\OrderItemTable');
        $resOrderItem = $orderItemTable->getOrderItemDetails($itemFilter);
        if ($resOrderItem === false) {
            throw new ApiException('Unable to fetch order item details, please try again!', '500');
        }

        return $resOrderItem;
    }

    private function getOrderDetails($id, $param)
    {
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $resOrderDetails = $orderTable->getOrderDetails($id, $param);
        if ($resOrderDetails === false) {
            throw new ApiException('Unable to fetch order details, please try again!', '500');
        }

        return $resOrderDetails;
    }

    private function getOrderFullInformation($id, $param)
    {
        $data = array();

        //get order detail
        $data['order'] = $this->getOrderDetails($id, $param);
        $data['order'] = $this->convertImageNameToUrl($data['order'], array("store_photo" => 'store'));
        $data['order']['app_status'] = $this->formatStatusField($data['order']['status']);

        $params = array(
            'order_id' => $id,
        );
        if (!in_array($data['order']['status'], array(
                    OrderStatusTable::PENDING,
                    OrderStatusTable::CONFIRMED,
                    OrderStatusTable::READYTOPACK
                ))) {
            $params['is_available'] = 1;
        }

        //get order items details
        $data['order']['items'] = $this->getOrderItemDetails($params);

        return $data;
    }
    
    private function getOrderItemInformation($param)
    {
        $data = array();
        if(!$param['id']) {
            throw new ApiException('Unable to fetch order details, please try again!', '400');
        }
        
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $orderDet = $orderTable->getByField(array('id' => $param['id']));
        if ($orderDet === false) {
            throw new ApiException('Unable to fetch order item details, please try again!', '500');
        }
        
        $filter = array(
            'order_id' => $param['id'],
        );
        $data['type'] = 'all';
        if(isset($param['type']) && $param['type'] === 'new') {
            $filter['is_modified'] = 1;
            $data['type'] = 'new';
        }
        $filter['sort_by'] = 'category';
        //get order items details
        $data['items'] = $this->getOrderItemDetails($filter);
        $data['order_id'] = $orderDet['id'];
        $data['delivery_date'] = $this->getDateFormat($orderDet['delivered_by']);

        return $data;
    }

    private function formatStatusField($status)
    {
        switch ($status) {
            case OrderStatusTable::PENDING:
                $status = 'Processing';
                break;
            case OrderStatusTable::CONFIRMED:
                $status = 'Confirmed';
                break;
            case OrderStatusTable::READYTOPACK:
            case OrderStatusTable::PACKED:
            case OrderStatusTable::SHIPPED:
                $status = 'On the way';
                break;
            case OrderStatusTable::DELIVERED:
            case OrderStatusTable::COMPLETED:
                $status = 'Completed';
                break;
            case OrderStatusTable::CANCELLED:
                $status = 'Cancelled';
                break;
        }

        return $status;
    }

    private function getCreateOrderParameters()
    {
        $parameter = $this->getParameter($this->params()->fromPost());
        if (!isset($parameter['products'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }
        
        // parse json product array & prepare data
        $parameter['use_loyality_points'] = (!empty($parameter['use_loyality_points'])) ? $parameter['use_loyality_points'] : 0;
        $parameter['last_updated_at'] = (!empty($parameter['last_updated_at'])) ? $parameter['last_updated_at'] : 0;
        $parameter['delivered_by'] = (!empty($parameter['delivered_by'])) ? $parameter['delivered_by'] : '';
        $parameter['product_list'] = $this->parseJSONString($parameter['products']);
        if ($parameter['product_list'] === false) {
            throw new ApiException('Please check the product list!', 400);
        }

        return $parameter;
    }
    
    private function getDateFormat($date)
    {
        $dateObj = new \DateTime($date);
        return $dateObj->format('jS F Y');
    }

}

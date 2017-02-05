<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class FeedbackController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/feedback",
     *     description="get all feedbacks",
     *     tags={"feedback"},
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="page no.",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="count per page",
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
        $this->checkGrowsariSession();
        $page = $this->params()->fromQuery('page', 1);
        $limit = $this->params()->fromQuery('limit', 20);

        $orderFeedbackTable = $this->getServiceLocator()->get('Api\Table\OrderFeedbackTable');
        $data = $orderFeedbackTable->getOrderFeedbackList($page, $limit);
        if ($data === false) {
            throw new ApiException('Unable to fetch data, please try again!', 500);
        }

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/feedback/{id}",
     *     description="feedback details",
     *     tags={"feedback"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="feedback id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function get($id)
    {
        $this->checkGrowsariSession();

        $orderFeedbackTable = $this->getServiceLocator()->get('Api\Table\OrderFeedbackTable');
        $res = $orderFeedbackTable->getOrderFeedbackDetails($id);
        if ($res === false) {
            throw new ApiException('No recrod found!', 404);
        }

        return $this->successRes('Successfully fetched', $res);
    }
    
    /**
     * @SWG\Get(
     *     path="/api/feedback/pending",
     *     description="get pending feedback details",
     *     tags={"feedback"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="feedback id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function pendingAction()
    {
        $user = $this->checkStoreSession();

        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $feedback = $orderTable->getStoreOrderFeedbackPending($user['store']['id']);
        if ($feedback === false) {
            throw new ApiException('No recrod found!', 404);
        }

        return $this->successRes('Successfully fetched', $feedback);
    }

    /**
     * @SWG\Post(
     *     path="/api/feedback",
     *     description="create feedback for order",
     *     tags={"feedback"},
     *     @SWG\Parameter(
     *         name="order_id",
     *         in="formData",
     *         description="order_id",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="rating",
     *         in="formData",
     *         description="rating",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="experience",
     *         in="formData",
     *         description="experience",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="remarks",
     *         in="formData",
     *         description="remarks",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function create()
    {
        $user = $this->checkStoreSession();
        $params = $this->getParameter($this->params()->fromPost());
        $params['store_id'] = $user['store']['id'];
        
        // check if order belong to that user
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $resOrderDetails = $orderTable->getOrderDetails($params['order_id'], array());
        if ($resOrderDetails === false) {
            throw new ApiException('Unable to fetch order details, please try again!', '500');
        }
       
        if ((int) $resOrderDetails['store_id'] !== (int)$params['store_id']) {
            throw new ApiException('Order doesn\'t belongs to you!', 403);
        }
        
        if ($resOrderDetails['feedback_given']) {
            throw new ApiException('You have alredy given feedback, thank you!', 403);
        }
        
        $orderFeedbackTableTable = $this->getServiceLocator()->get('Api\Table\OrderFeedbackTable');
        $res = $orderFeedbackTableTable->addOrderFeedback($params);
        if ($res === false) {
            throw new ApiException('Unable to create/update, please try again!', 500);
        }
        $data = array('id' => $res);

        //updating order feedback flag
        $this->setFeedbackGiven($params['order_id']);
        
        // get loyality points
        $loyaltyPointTable = $this->getServiceLocator()->get('Api\Table\LoyaltyPointTable');
        $pointObj = $loyaltyPointTable->getUserPointsByAccountId($user['id']);
        $data['loyalty_point'] = !empty($pointObj['points']) ? $pointObj['points'] : 0;

        return $this->successRes('Successfully inserted.', $data);
    }

    private function setFeedbackGiven($orderId)
    {
        $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
        $resOrder = $orderTable->updateOrder(array('feedback_given' => 1), array('id' => $orderId));
        if ($resOrder === false) {
            return false;
        }

        return true;
    }

}

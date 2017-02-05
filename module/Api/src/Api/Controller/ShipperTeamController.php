<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class ShipperTeamController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/shipper/team",
     *     description="get all shipper memebers",
     *     tags={"shipper"},
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
        $user = $this->checkShipperSession();
        $data = array();
        
        $shipperTable = $this->getServiceLocator()->get('Api\Table\ShipperTeamTable');
        $data['shipper'] = $shipperTable->getShipperTeamList($user['id']);
        if ($data['shipper'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched', $data);
    }
    
    
    /**
     * @SWG\Post(
     *     path="/api/shipper/team",
     *     description="create shipper member",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="account_id",
     *         in="formData",
     *         description="account_id",
     *         required=true,
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
        $user = $this->checkUserSession();
        $params = $this->getParameter($this->params()->fromPost());
        
        $shipperTable = $this->getServiceLocator()->get('Api\Table\ShipperTable');
        $shipper = $shipperTable->getShipperDetails($user['id']);
        if ($shipper === false) {
            throw new ApiException('No record found!', 404);
        }
      
        // create warehouse and shipper mapping
        $shipperTeamTable = $this->getServiceLocator()->get('Api\Table\ShipperTeamTable');
        $resWarehouseShipper = $shipperTeamTable->addShipperTeam(array(
            'account_id' => $params['account_id'],
            'shipper_id' => $shipper['id']
        ));
        if ($resWarehouseShipper === false) {
            throw new ApiException('Unable to create/update, please try again!', 500);
        }
        
        return $this->successRes('Successfully inserted.', array('id' => $resWarehouseShipper));
    }
    

}

<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class ShipperController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/shipper",
     *     description="get all shipper",
     *     tags={"shipper"},
     *     @SWG\Parameter(
     *         name="warehouse_id",
     *         in="query",
     *         description="warehouse_id",
     *         required=false,
     *         type="integer"
     *     ),
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
        $data = array();
        $parameter = $this->getParameter($this->params()->fromQuery());
        
        if (isset($parameter['warehouse_id'])) {
            $warehouseShipperTable = $this->getServiceLocator()->get('Api\Table\WarehouseShipperTable');
            $data['shipper'] = $warehouseShipperTable->getShipperList($parameter['warehouse_id']);
        } else {
            $shipperTable = $this->getServiceLocator()->get('Api\Table\ShipperTable');
            $data['shipper'] = $shipperTable->getShipperList();
        }

        if ($data['shipper'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched', $data);
    }
    

}

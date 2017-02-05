<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class WarehouseController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/warehouse",
     *     description="get all warehouse",
     *     tags={"warehouse"},
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

        $warehouseTable = $this->getServiceLocator()->get('Api\Table\WarehouseTable');
        $data['warehouse'] = $warehouseTable->getWarehouseList($parameter);
        if ($data['warehouse'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched', $data);
    }

}

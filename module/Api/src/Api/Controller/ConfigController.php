<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class ConfigController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/config",
     *     description="get all configs",
     *     tags={"config"},
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getList()
    {
        $this->checkUserSession();

        $storeTable = $this->getServiceLocator()->get('Api\Table\ConfigTable');
        $data = $storeTable->getAdminConfigList();
        if ($data === false) {
            throw new ApiException('Unable to fetch data, please try again!', 500);
        }

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/config/{id}",
     *     description="config details",
     *     tags={"config"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="config id",
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
        $this->checkUserSession();

        $storeTable = $this->getServiceLocator()->get('Api\Table\ConfigTable');
        $res = $storeTable->getConfigDetails($id);
        if ($res === false) {
            throw new ApiException('No recrod found!', 404);
        }

        return $this->successRes('Successfully fetched', $res);
    }

    /**
     * @SWG\Post(
     *     path="/api/config",
     *     description="create config or update config",
     *     tags={"config"},
     *      @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="id",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="field",
     *         in="formData",
     *         description="field",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="value",
     *         in="formData",
     *         description="point_x",
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
        $this->checkGrowsariSession();
        $params = $this->getParameter($this->params()->fromPost());

        $configTable = $this->getServiceLocator()->get('Api\Table\ConfigTable');
        if (!isset($params['id'])) {
            $res = $configTable->addConfig($params); // id of last inserted data 
        } else {
            $res = $configTable->updateConfig($params, array('id' => (int) $params['id']));
        }

        if ($res === false) {
            throw new ApiException('Unable to create/update, please try again!', 500);
        }

        if (!isset($params['id'])) {
            return $this->successRes('Successfully inserted.', array('id' => $res));
        } else {
            return $this->successRes('Successfully updated.');
        }
    }

}

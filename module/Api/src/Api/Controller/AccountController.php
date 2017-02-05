<?php

namespace Api\Controller;

use Api\Controller\BaseApiController as BaseController;
use Api\Exception\ApiException;
use Api\Service\CreateAccountService;
use Zend\View\Model\JsonModel;

class AccountController extends BaseController
{

    /**
     * @SWG\Post(path="/api/account",
     *   tags={"account"},
     *   summary="Register user into the system",
     *   description="",
     *   operationId="registerUser",
     *   produces={"application/xml", "application/json"},
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="Email of the user",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="The password for login in clear text",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="Name of the user",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     description="Phone number of the user",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="type",
     *     in="formData",
     *     description="Type of user ('GROWSARI',  'WAREHOUSE',  'SHIPPER',  'STORE',  'SALESPERSON', 'CALLCENTER')",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     *   @SWG\Response(response=400, description="Invalid username/password supplied")
     * )
     */
    public function createAction()
    {
        $this->checkUserSession();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $createAccountService = new CreateAccountService($this->getServiceLocator());
            $accountId = $createAccountService->create(
                $this->params()->fromPost('name'),
                $this->params()->fromPost('email'),
                $this->params()->fromPost('password'),
                $this->params()->fromPost('email'),
                $this->params()->fromPost('phone'),
                $this->params()->fromPost('type')
            );
            
            if (is_integer($accountId)) {
                return new JsonModel(array(
                    'account_id' => $accountId,
                    'msg' => 'Succussfully registered'
                ));
            }
            
            return $accountId;
        }

        $this->methodNotAllowed();
    }

    /**
     * @SWG\Post(path="/api/account/create-token",
     *   tags={"account"},
     *   summary="Add User Device token",
     *   description="",
     *   operationId="registerUser",
     *   produces={"application/xml", "application/json"},
     *   @SWG\Parameter(
     *     name="device_token",
     *     in="formData",
     *     description="Device token",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     *   @SWG\Response(response=400, description="Invalid username/password supplied")
     * )
     */
    public function createTokenAction()
    {
        $user = $this->checkUserSession();
        
        $parameter = $this->getParameter($this->params()->fromPost());
        $parameter['account_id'] = $user['id'];
        if (!isset($parameter['account_id'], $parameter['device_token'])) {
            throw new ApiException('Insufficient parameters', 400);
        }

        $sessionTable = $this->getServiceLocator()->get('Api\Table\AccountDeviceTable');
        $data = $sessionTable->getByField(array('account_id' => $parameter['account_id']));
        $param = array(
            'device_token' => $parameter['device_token']
        );
        
        $request = $this->getRequest();
        $appVersionObj = $request->getHeaders('X-Growsari-Version');
        if ($appVersionObj) {
            $param['app_version']  = $appVersionObj->getFieldValue();
        }

        if ($data) {
            $res = $sessionTable->updateAccountDevice($param, array('account_id' => $parameter['account_id']));
        } else {
            $param['account_id'] = $parameter['account_id'];
            $res = $sessionTable->addAccountDevice($param);
        }
        
        if ($res === false) {
            throw new ApiException('Unabel to create/update token', '500');
        }
        
        return $this->successRes('Successfully Added');
    }

    /**
     * @SWG\Get(path="/api/account/get-user-detail",
     *   tags={"account"},
     *   summary="get User Details",
     *   description="",
     *   operationId="User",
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     *   @SWG\Response(response=400, description="Invalid username/password supplied")
     * )
     */
    public function getUserDetailAction()
    {
        $user = $this->checkStoreSession();

        $accountTable = $this->getServiceLocator()->get('Api\Table\AccountDeviceTable');
        $data = $accountTable->getStoreDetails($user['id']);

        if ($data === false) {
            throw new ApiException('Unable to fetch data', '500');
        }
        
        return $this->successRes('Successfully fetched', $data);
    }
    
    /**
     * @SWG\Get(path="/api/account/get-salesperson",
     *   tags={"account"},
     *   summary="get salesperson list",
     *   description="",
     *   operationId="User",
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     *   @SWG\Response(response=400, description="Invalid username/password supplied")
     * )
     */
    public function getSalespersonAction()
    {
        $user = $this->checkGrowsariSession();
        
        $accountTable = $this->getServiceLocator()->get('Api\Table\AccountDeviceTable');
        $data = $accountTable->getSalespersonList();

        if ($data === false) {
            throw new ApiException('Unable to fetch data', '500');
        }
        
        return $this->successRes('Successfully fetched', $data);
    }

}

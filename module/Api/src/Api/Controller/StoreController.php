<?php

namespace Api\Controller;

use Api\Exception\ApiException;
use Api\Service\CreateAccountService;
use Api\Service\LoyalityService;
use Api\Table\AccountTable;
use Api\Table\LoyaltyPointTable;
use Zend\Validator\EmailAddress;
use Zend\View\Model\JsonModel;

class StoreController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/store",
     *     description="get all store",
     *     tags={"store"},
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

        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $data['store'] = $storeTable->getStoreList($parameter);
        if ($data['store'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        $data['store'] = $this->convertImageNameToUrl($data['store'], array("photo" => 'store'));

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/store/{id}",
     *     description="store details",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="store id",
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
        $data = array();

        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $data['store'] = $storeTable->getStoreDetails($id);
        if ($data['store'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        $data['store'] = $this->convertImageNameToUrl($data['store'], array("photo" => 'store'));

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Post(
     *     path="/api/store",
     *     description="create store",
     *     tags={"store"},
     *     @SWG\Parameter(
     *        name="email",
     *        in="formData",
     *        description="Email of the user",
     *        required=false,
     *        type="string"
     *     ),
     *     @SWG\Parameter(
     *        name="password",
     *        in="formData",
     *        description="The password for login in clear text",
     *        required=true,
     *        type="string"
     *     ),
     *     @SWG\Parameter(
     *        name="name",
     *        in="formData",
     *        description="Name of the store",
     *        required=true,
     *        type="string"
     *     ),
     *     @SWG\Parameter(
     *        name="phone",
     *        in="formData",
     *        description="Phone number of the user",
     *        required=true,
     *        type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="customer_name",
     *         in="formData",
     *         description="customer_name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="address",
     *         in="formData",
     *         description="address",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="photo",
     *         in="formData",
     *         description="photo",
     *         required=false,
     *         type="file"
     *     ),
     *      @SWG\Parameter(
     *         name="point_x",
     *         in="formData",
     *         description="point_x",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="point_y",
     *         in="formData",
     *         description="point_y",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="is_storeowner",
     *         in="formData",
     *         description="is_storeowner 1 or 0",
     *         required=false,
     *         type="string"
     *     ), 
     *     @SWG\Parameter(
     *         name="spend_per_week",
     *         in="formData",
     *         description="spend_per_week",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="has_smartphone",
     *         in="formData",
     *         description="has_smartphone 1 or 0",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="is_covered",
     *         in="formData",
     *         description="is_covered 1 or 0",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="funnel_status",
     *         in="formData",
     *         description="funnel_status",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="revisit_date",
     *         in="formData",
     *         description="revisit_date",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="revisit_time",
     *         in="formData",
     *         description="revisit_time",
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
     *     @SWG\Parameter(
     *         name="locality",
     *         in="formData",
     *         description="locality",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="city",
     *         in="formData",
     *         description="city",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="province",
     *         in="formData",
     *         description="province",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="country",
     *         in="formData",
     *         description="country",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="refered_by",
     *         in="formData",
     *         description="refered_by",
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
        $this->salesperson = 1; // default salesperson superadmin
        if ($this->isMobileApp()) {
            $this->salesperson = 55; // independent signups salesperson
        }

        $params = $this->getParameter($this->params()->fromPost());
        if (!isset($params['name'], $params['customer_name'], $params['password'], $params['phone'], $params['address'])) {
            throw new ApiException("Please submit all required fields", 400);
        }

        $accountResponse = $this->createAccount();
        if (!is_integer($accountResponse)) {
            return new JsonModel($accountResponse);
        }

        $params['is_storeowner'] = (!empty($params['is_storeowner'])) ? 1 : 0;
        $params['has_smartphone'] = (!empty($params['has_smartphone'])) ? 1 : 0;
        $params['is_covered'] = (!empty($params['is_covered'])) ? 1 : 0;
        $params['is_deleted'] = 0;
        $params['account_id'] = $accountResponse;
        $params['contact_no'] = $params['phone'];
        $params['photo'] = $this->upload('photo', "uploads/store/");
        $params['signup_time'] = $this->getDateTime();

        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $storeId = $storeTable->addStore($params); // id of last inserted data 
        if ($storeId === false) {
            throw new ApiException('Unable to create store, please try again!', 500);
        }

        // create warehouse and shipper mapping
        $storeWarehouseShipperTable = $this->getServiceLocator()->get('Api\Table\StoreWarehouseShipperTable');
        $resWarehouseShipper = $storeWarehouseShipperTable->addStoreWarehouseShiper(array(
            'warehouse_shipper_id' => 1,
            'store_id' => $storeId
        ));
        if ($resWarehouseShipper === false) {
            throw new ApiException('Unable to create store warehouse shipper, please try again!', 500);
        }

        // map store to salesperson
        $storeSalespersonTable = $this->getServiceLocator()->get('Api\Table\StoreSalespersonTable');
        $resStoreSalesperson = $storeSalespersonTable->addStoreSalesperson(array(
            'store_id' => $storeId,
            'salesperson_account_id' => $this->salesperson
        ));
        if ($resStoreSalesperson === false) {
            throw new ApiException('Unable to create store as mapping to salesperson failed, please try again!', 500);
        }

        // referal
        if (!empty($params['refered_by'])) {
            $storeObj = $storeTable->getStoreByEmail($params['refered_by']);
            if ($storeObj) {
                $storeReferTable = $this->getServiceLocator()->get('Api\Table\StoreReferTable');
                $resStoreSalesperson = $storeReferTable->addStoreRefer(array(
                    'store_id' => $storeId,
                    'refered_by' => $storeObj['id']
                ));
            }
        }

        return $this->successRes('Successfully inserted.', array('id' => $storeId, 'email' => $this->email));
    }

    /**
     * @SWG\Post(
     *     path="/api/store/delete",
     *     description="store details",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="store id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function deleteAction()
    {
        $this->checkUserSession();
        $storeId = (int) $this->params()->fromPost('id', 0);

        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $storeDetails = $storeTable->getStoreDetails($storeId);
        if ($storeDetails === false) {
            throw new ApiException('No record found!', 404);
        }

        $msg = $storeTable->deleteStore($storeId, $storeDetails['account_id']);
        if ($msg === false) {
            throw new ApiException('Unable to delete, please try again!', 500);
        }

        return $this->successRes('Successfully deleted!');
    }

    /**
     * @SWG\Post(
     *     path="/api/store/update",
     *     description="update store",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="store id",
     *         required=true,
     *         type="string"
     *     ), 
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="point_x",
     *         in="formData",
     *         description="point_x",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="point_y",
     *         in="formData",
     *         description="point_y",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="is_storeowner",
     *         in="formData",
     *         description="is_storeowner 1 or 0",
     *         required=true,
     *         type="string"
     *     ), 
     *     @SWG\Parameter(
     *         name="spend_per_week",
     *         in="formData",
     *         description="spend_per_week",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="has_smartphone",
     *         in="formData",
     *         description="has_smartphone 1 or 0",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="photo",
     *         in="formData",
     *         description="photo",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="contact_no",
     *         in="formData",
     *         description="contact_no",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="is_covered",
     *         in="formData",
     *         description="is_covered 1 or 0",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="funnel_status",
     *         in="formData",
     *         description="funnel_status",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="revisit_date",
     *         in="formData",
     *         description="revisit_date",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="revisit_time",
     *         in="formData",
     *         description="revisit_time",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="remarks",
     *         in="formData",
     *         description="remarks",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="address",
     *         in="formData",
     *         description="address",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="locality",
     *         in="formData",
     *         description="locality",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="city",
     *         in="formData",
     *         description="city",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="province",
     *         in="formData",
     *         description="province",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="country",
     *         in="formData",
     *         description="country",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function updateAction()
    {
        $this->checkUserSession();
        $parameter = $this->params()->fromPost();
        $photo = $this->upload('photo', "uploads/store/");
        if ($photo) {
            $parameter['photo'] = $photo;
        } else {
            unset($parameter['photo']);
        }
        $id = $this->params()->fromPost('id', 0);
        unset($parameter['id']);

        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $res = $storeTable->updateStore($parameter, array('id' => $id));
        if ($res === false) {
            throw new ApiException('Unable to update, please try again!', 500);
        }

        return $this->successRes('Successfully updated !', $parameter);
    }

    /**
     * @SWG\Post(
     *     path="/api/store/valid-referrer",
     *     description="is valid referrer",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="email",
     *         in="formData",
     *         description="email",
     *         required=true,
     *         type="string"
     *     ), 
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function validReferrerAction()
    {
        $email = $this->params()->fromPost('email');
        $response = array('exists' => false);

        $storeReferTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $storeObj = $storeReferTable->getStoreByEmail($email);
        if ($storeObj) {
            $response['exists'] = true;
        }

        return $this->successRes('', $response);
    }

    /**
     * @SWG\Get(
     *     path="/api/store/get-wallet-details",
     *     description="store wallet details",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="store_id",
     *         in="formData",
     *         description="store id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getWalletDetailsAction()
    {
        //$user = $this->checkCallCenterSession();
        $storeId = $this->params()->fromQuery('store_id', null);

        if (!isset($storeId)) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        //get wallet details
        $storeTable = $this->serviceLocator->get('Api\Table\StoreTable');
        $res = $storeTable->getStoreWalletDet($storeId);
        if ($res === false) {
            throw new ApiException('Unable to fetch details.', 500);
        }

        return $this->successRes('Successfully fetched.', $res);
    }

    /**
     * @SWG\Post(
     *     path="/api/store/update-wallet",
     *     description="Add money to store wallet",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="store_id",
     *         in="formData",
     *         description="store Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="amount",
     *         in="formData",
     *         description="amount to be added",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="remark",
     *         in="formData",
     *         description="remarks",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="is_loan",
     *         in="formData",
     *         description="if amount is given as loan",
     *         required=false,
     *         type="boolean"
     *     ), 
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function updateWalletAction()
    {
        $this->checkGrowsariSession();
        $parameter = $this->params()->fromPost();

        if (!isset($parameter['store_id'], $parameter['amount']) || empty($parameter['store_id'])) {
            throw new ApiException('Please submit all required parameters!', 400);
        }

        // verify account
        $storeTable = $this->serviceLocator->get('Api\Table\StoreTable');
        $walletDet = $storeTable->getStoreWalletDet($parameter['store_id']);
        if ($walletDet === false) {
            throw new ApiException('Unable to fetch store account.', 500);
        }

        if (empty($walletDet) || !$walletDet['account_id']) {
            throw new ApiException('No user account associated with store.', 500);
        }

        $remark = '';
        if (isset($parameter['remark'])) {
            $remark = $parameter['remark'];
        }
        $pointsType = LoyaltyPointTable::REMARK_PROMO;
        $loyaltyService = new LoyalityService($this->serviceLocator);
        //update in loan table
        if (isset($parameter['is_loan']) && $parameter['is_loan'] == 1) {
            $pointsType = LoyaltyPointTable::REMARK_LOAN;
            $loyaltyService->addMoneyAsLoanToStore($walletDet['account_id'], $parameter['amount'], $remark);
        }
        //update wallet
        $remark = $pointsType . '(' . $remark . ')';
        $loyaltyService->addMoneyToStore($walletDet['account_id'], $parameter['amount'], $remark);

        return $this->successRes('Successfully added');
    }

    /**
     * @SWG\Get(
     *     path="/api/get-stores",
     *     description="store list for dropdown",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="search",
     *         in="path",
     *         description="store search parameter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getStoresAction()
    {
        $search = $this->params()->fromQuery('search');

        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $storeList = $storeTable->getStores($search);
        if ($storeList === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched.', $storeList);
    }

    private function createAccount()
    {
        $this->email = $this->username = $this->params()->fromPost('email');
        if ($this->email && !$this->isValidEmail($this->email)) {
            throw new ApiException('Please enter valid email!', 400);
        }

        if (!$this->email) {
            $this->username = $this->generateUsername();
            $this->email = $this->username . '@growsari.com';
        }

        $createAccountService = new CreateAccountService($this->getServiceLocator());
        $accountResponse = $createAccountService->create(
                $this->params()->fromPost('name'), $this->email, $this->params()->fromPost('password'), $this->username, $this->params()->fromPost('phone'), AccountTable::TYPE_STORE
        );

        return $accountResponse;
    }

    private function generateUsername()
    {
        // get salesperson info and his users count.
        $storeSalespersonTable = $this->getServiceLocator()->get('Api\Table\StoreSalespersonTable');
        $salespersonInfo = $storeSalespersonTable->getSalespersonDetails($this->salesperson);
        if (!$salespersonInfo) {
            throw new ApiException('Unable to crete store, as salesperson is not found!', 500);
        }

        $start = $salespersonInfo['count'] + 1;

        $userId = 'sm' . $this->salesperson . 'u' . $start;

        return $userId;
    }

    private function isValidEmail($email)
    {
        $validator = new EmailAddress();
        if ($validator->isValid($email)) {
            return true;
        }

        return false;
    }

}

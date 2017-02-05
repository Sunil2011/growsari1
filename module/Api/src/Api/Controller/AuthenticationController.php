<?php

namespace Api\Controller;

use Api\Controller\BaseApiController as BaseController;
use Api\Exception\ApiException;
use Api\Table\AccountTable;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class AuthenticationController extends BaseController
{

    /**
     * @SWG\Post(path="/api/auth/login",
     *   tags={"user"},
     *   summary="Logs user into the system",
     *   description="",
     *   operationId="loginUser",
     *   produces={"application/xml", "application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="The user name for login",
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
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     *   @SWG\Response(response=400, description="Invalid username/password supplied")
     * )
     */
    public function loginAction()
    {
        $versionResponse = $this->checkAppVersion();
        if ($versionResponse !== true) {
            return $versionResponse;
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array(
                'identity' => $this->getRequest()->getPost('username'),
                'credential' => $this->getRequest()->getPost('password')
            );

            $this->getRequest()->getPost()->set('identity', $data['identity']);
            $this->getRequest()->getPost()->set('credential', $data['credential']);

            $form = $this->serviceLocator->get('zfcuser_login_form');
            $form->setData($data);

            if (!$form->isValid()) {
                $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage("Bad Request");
                throw new ApiException("Please enter valid email address!", 400);
            } else {
                $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
                $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

                $adapter = $this->zfcUserAuthentication()->getAuthAdapter();

                $adapter->prepareForAuthentication($this->getRequest());
                $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);
                $authMessage = $auth->getMessages();
                if (!$auth->isValid()) {
                    $adapter->resetAdapters();
                    throw new ApiException('Invalid credentials', 400);
                }

                return new JsonModel($this->getUserData($auth));
            }
        }

        $this->methodNotAllowed();
    }

    /**
     * @SWG\Get(path="/api/auth/logout",
     *   tags={"user"},
     *   summary="Logs out current logged in user session",
     *   description="",
     *   operationId="logoutUser",
     *   produces={"application/xml", "application/json"},
     *   parameters={},
     *   @SWG\Response(response="default", description="successful operation")
     * )
     */
    public function logoutAction()
    {
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return new JsonModel(array(
            'msg' => "You have successfully logged out"
        ));
    }

    /**
     * @SWG\Get(path="/api/auth/me",
     *   tags={"user"},
     *   summary="Get loggedin user details",
     *   description="",
     *   operationId="logoutUser",
     *   produces={"application/xml", "application/json"},
     *   parameters={},
     *   @SWG\Response(response="default", description="successful operation")
     * )
     */
    public function meAction()
    {
        return new JsonModel($this->getUserData());
    }

    private function getUserData($auth = null)
    {
        $data = (!empty($auth)) ? array('id' => $auth->getIdentity()) : array();

        $account = $this->formatAccountInfo($this->zfcUserAuthentication()->getIdentity());
        if (!empty($account)) {
            unset($account->password);

            if ($account->type === AccountTable::TYPE_STORE) {
                $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
                $account->store = $storeTable->getStoreDetailsFromUserId($account->id);

                $storeSession = new Container('store');
                $storeSession->store = $account->store;
                
                // get store feeback data
                $orderTable = $this->getServiceLocator()->get('Api\Table\OrderTable');
                $feedback = $orderTable->getStoreOrderFeedbackPending($storeSession->store['id']);
                $data['feedback'] = $feedback;

                // get loyality points
                $loyaltyPointTable = $this->getServiceLocator()->get('Api\Table\LoyaltyPointTable');
                $pointObj = $loyaltyPointTable->getUserPointsByAccountId($account->id);
                $data['loyalty_point'] = !empty($pointObj['points']) ? $pointObj['points'] : 0;

                // adding first login
                if (!$account->store['first_loggedin_time'] || $account->store['first_loggedin_time'] === '0000-00-00 00:00:00') {
                    $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
                    $storeTable->updateStore(array('first_loggedin_time' => $this->getDateTime()), array('id' => $account->store['id']));
                }
                
                $account->store = $this->formatStoreInfo($account->store);
            }
        }
        $data['account'] = $account;

        // get config data
        $data['config'] = $this->getConfig();

        return $data;
    }
    
    private function getConfig()
    {
        $config = $this->getServiceLocator()->get('Config');
        $settings = $config['app_settings'];
        
        unset($settings['min_balance_for_using_loyality_points']);
        unset($settings['loyalty_percent']);
        unset($settings['loyality_for_signup']);
        $settings['banner'] = $config['path']['thumb_path'] . '/banner/'. $settings['banner'];
        $globe = array(
            'globe_short_code' => $config['globe']['short_code'],
            'globe_short_code_cross_telco' => $config['globe']['short_code_cross_telco'],
        );
        $storeVersion = array(
            'min_version' => $config['app_clients']['store_min_version'],
            'upgrade_msg' => $config['app_clients']['store_upgrade_msg'],
        );
        
        return  $settings + $globe + $storeVersion;
    }
    
    private function formatStoreInfo($store)
    {
        $store  = (array) $store;
        
        $storeData = array();
        $storeData['id'] = $store['id'];
        $storeData['account_id'] = $store['account_id'];
        $storeData['name'] = $store['name'];
        $storeData['photo'] = $store['photo'];
        $storeData['address'] = $store['address'];
        $storeData['contact_no'] = $store['contact_no'];
        $storeData['photo'] = $store['photo'];
        
        return $storeData;
    }
    
    private function formatAccountInfo($account)
    {
        unset($account->createdAt);
        unset($account->updatedAt);
        unset($account->state);
        unset($account->phone);
        unset($account->displayName);
        
        return $account;
    }


}

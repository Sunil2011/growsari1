<?php

namespace Api\Controller;

use Api\Exception\ApiException;
use Api\Table\AccountTable;
use DateTime;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

/**
 * @SWG\Swagger(
 *     basePath=BASE_PATH,
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Grow Sari",
 *         description="all allowed operations"
 *     )
 * )
 */
class BaseApiController extends AbstractRestfulController
{

    protected $serviceManager;
    protected $logger;

    protected function methodNotAllowed()
    {
        $this->response->setStatusCode(405);
        throw new ApiException('Method Not Allowed', 405);
    }

    public function checkUserSession()
    {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return (array) $this->zfcUserAuthentication()->getIdentity();
        }

        throw new ApiException('Unauthorized, Please login!', 401);
    }

    public function checkSalesPersonSession()
    {
        $user = $this->checkUserSession();
        if ($user['type'] === AccountTable::TYPE_SALESPERSON) {
            return $user;
        }

        throw new ApiException('Forbidden, please login with salesperson account!', 403);
    }

    public function checkStoreSession()
    {
        $user = $this->checkUserSession();
        if ($user['type'] === AccountTable::TYPE_STORE) {
            $storeSession = new Container('store');
            $user['store'] = (array) $storeSession->store;

            return $user;
        }

        throw new ApiException('Forbidden, please login with store account!', 403);
    }

    public function checkShipperSession()
    {
        $user = $this->checkUserSession();
        if ($user['type'] === AccountTable::TYPE_SHIPPER) {
            return $user;
        }

        throw new ApiException('Forbidden, please login with shipper account!', 403);
    }

    public function checkGrowsariSession()
    {
        $user = $this->checkUserSession();
        if ($user['type'] === AccountTable::TYPE_GROWSARI) {
            return $user;
        }

        throw new ApiException('Forbidden, please login with growsari account!', 403);
    }
    
    public function checkCallCenterSession()
    {
        $user = $this->checkUserSession();
        if ($user['type'] === AccountTable::TYPE_CALLCENTER) {
            return $user;
        }

        throw new ApiException('Forbidden, please login with callcenter account!', 403);
    }

    public function successRes($msg, $data = array())
    {
        return new JsonModel(array(
            'success' => true,
            'message' => $msg,
            'data' => $data
        ));
    }

    public function errorRes($msg, $error = array(), $code = 500)
    {
        $this->getResponse()->setStatusCode($code);

        return new JsonModel(array(
            'error' => array_merge(
                    array(
                "type" => "Api\\Exception\\ApiException",
                'message' => $msg,
                "code" => $code
                    ), $error
            ),
        ));
    }

    public function getDateTime()
    {
        $date = new DateTime();

        return $date->format('Y-m-d H:i:s');
    }

    public function getParameter($params)
    {
        $parameter = array();
        foreach ($params as $key => $value) {
            if ($value) {
                $parameter[$key] = $value;
            }
        }

        return $parameter;
    }
    
    public function parseSMSJson($jsonString)
    {
        $body = stripslashes($jsonString);
        
        return $this->parseJSONString($body);
    }

    protected function getService($serviceName)
    {
        $sm = $this->getServiceLocator();
        $service = $sm->get($serviceName);
        return $service;
    }

    protected function convertImageNameToUrl($data, $keys = array())
    {
        if (isset($data['list'])) {
            foreach ($data['list'] as &$row) {
                $row = $this->updateImageNameToUrl($keys, $row);
            }
        } else {
            $data = $this->updateImageNameToUrl($keys, $data);
        }

        return $data;
    }

    private function updateImageNameToUrl($keys, $row)
    {
        $config = $this->getServiceLocator()->get('config');
        foreach ($keys as $key => $folder) {
            if (!empty($row[$key])) {
                $folderPath = $config['path']['thumb_path'] . '/' . $folder . '/';
                $row[$key] = $folderPath . $row[$key];
            }
        }
        
        return $row;
    }

    protected function checkAppVersion()
    {
        $config = $this->getServiceLocator()->get('Config');

        $request = $this->getRequest();
        $appVersion = $request->getHeaders('X-Growsari-Version');
        $app = $request->getHeaders('X-Growsari-App');

        if ($app && $appVersion) {
            if ($app->getFieldValue() === 'store' && version_compare($appVersion->getFieldValue(), $config['app_clients']['store_min_version'], '<')) {
                return $this->errorRes($config['app_clients']['store_upgrade_msg'], array('force_upgrade' => 1), 403);
            }
        }

        return true;
    }

    protected function isMobileApp()
    {
        $app = $this->getRequest()->getHeaders('X-Growsari-App');
        if ($app) {
            return true;
        }

        return false;
    }
    
    protected function isDeliveryMobileApp()
    {
        $app = $this->getRequest()->getHeaders('X-Growsari-App');
        if ($app && $app->getFieldValue() !== 'store') {
           return true;
        }

        return false;
    }

    protected function parseJSONString($string)
    {
        $itemData = json_decode($string, TRUE);
        if ($itemData === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $itemData;
    }

    protected function upload($key, $path)
    {
        try {
            $file = $this->params()->fromFiles($key);
            if ($file) {
                $s3Upload = $this->getServiceLocator()->get('S3UploadService');
                $fileData = $s3Upload->moveUploadedFile($file, $path);

                return $fileData['newName'];
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

}

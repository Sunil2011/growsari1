<?php

namespace Api\Console;

use Api\Exception\ApiException;
use Base\Console\BaseController;
use DateTime;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class UploadSurveyController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->filePath = $request->getParam('file');
        $this->accountId = $request->getParam('account_id', 45);
        if (!file_exists($this->filePath)) {
            return "File not found. Plese provide proper file path";
        }

        echo "Started processing\n\n";
        $this->import();

        return "Successfully imported";
    }

    public function import()
    {
        $keys = array();
        $row = 1;
        if (($handle = fopen($this->filePath, "r")) !== FALSE) {
            while (($rowData = fgetcsv($handle)) !== FALSE) {
                echo "looping through records:  " . $row . "\n";
                if ($row == 1) {
                    $keys = $rowData;
                    $row++;
                    continue;
                }

                $params = array_combine($keys, $rowData);
                $params['account_id'] = $this->accountId;
                $params['is_storeowner'] = $this->mapValueToInt('is_storeowner', $params['is_storeowner']);
                $params['has_smartphone'] = $this->mapValueToInt('has_smartphone', $params['has_smartphone']);
                $params['is_covered'] = $this->mapValueToInt('is_covered', $params['is_covered']);
                $params['name'] = $params['store_name'];
                unset($params['store_id']);
                
                try {
                    $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
                    $resp = $surveyTable->getByField(array('contact_no' => $params['contact_no']));
                    if ($resp) {
                        echo "Survey already found: \n ";
                        $row++;
                        continue;
                    }
                    
//                    $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
//                    if (isset($params['user_name'])) {
//                        $storeInfo = $storeTable->getStoreSurveyDetails(trim($params['user_name']));
//                        if ($storeInfo) {
//                            echo "store found ". $storeInfo['store_id'] . " \n";
//                            
//                            // check whether store id was already mapped to survey?
//                            if ($storeInfo['survey_id']) {
//                                throw new ApiException('Store was already assigned, please select another store!', 403);
//                            }
//
//                            // check whether store id belongs to this salesperson
//                            if (!$storeInfo['salesperson_account_id'] || $storeInfo['salesperson_account_id'] != $params['account_id']) {
//                                throw new ApiException('This store doesn\'t belongs to you!', 403);
//                            }
//
//                            $storeParams = $params;
//                            unset($storeParams['id']);
//                            unset($storeParams['account_id']);
//                            unset($storeParams['store_id']);
//                            unset($storeParams['is_deleted']);
//                            $storeParams['signup_time'] = $this->getDateTime();
//
//                            $whereArray = array('id' => $storeInfo['store_id']);
//                            $storeTable->updateStore($storeParams, $whereArray);
//
//                            $params['store_id'] = $storeInfo['store_id'];
//                        }
//                    }
                    
                    $res = $surveyTable->addSurvey($params); // id of last inserted data 
                    if ($res === false) {
                        throw new ApiException('Unable to create/update, please try again!', 500);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $row++;
            }

            fclose($handle);
        }
    }

    private function mapValueToInt($key, $value)
    {
        switch ($key) {
            case 'has_smartphone':
                return $this->stringMatch($value, 'yes');
                break;

            case 'is_storeowner':
                return $this->stringMatch($value, 'owner');
                break;

            case 'is_covered':
                return !$this->stringMatch($value, 'Un');
                break;
        }

        return 0;
    }

    private function stringMatch($string, $match)
    {
        if (strpos(strtolower($string), $match) !== FALSE) {
            return 1;
        }

        return 0;
    }
    
    public function getDateTime()
    {
        $date = new DateTime();
        return $date->format('Y-m-d H:i:s');
    }

}

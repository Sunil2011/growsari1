<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class SurveyController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/survey",
     *     description="get all surveys",
     *     tags={"survey"},
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
        $salesPerson = $this->checkSalesPersonSession();
        $params = $this->params()->fromQuery();
        $params['salesperson_id'] = $salesPerson['id'];

        $storeTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $data = $storeTable->getSurveyList($params);
        if ($data === false) {
            throw new ApiException('Unable to fetch data, please try again!', 500);
        }
        
        $data = $this->convertImageNameToUrl($data, array("photo" => 'survey'));

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/survey/{id}",
     *     description="survey details",
     *     tags={"survey"},
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
        $salesPerson = $this->checkSalesPersonSession();

        $storeTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $res = $storeTable->getSurveyDetails($id);
        if ($res === false) {
            throw new ApiException('No recrod found!', 404);
        }

        if ($salesPerson['id'] !== $res['account_id']) {
            throw new ApiException('You cannot access this resource', 403);
        }
        
        $res = $this->convertImageNameToUrl($res, array("photo" => 'survey'));

        return $this->successRes('Successfully fetched', $res);
    }

    /**
     * @SWG\Post(
     *     path="/api/survey",
     *     description="create survey or update survey",
     *     tags={"survey"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="id",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="store_username",
     *         in="formData",
     *         description="store username",
     *         required=false,
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
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="photo",
     *         in="formData",
     *         description="photo",
     *         required=false,
     *         type="file"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="customer_name",
     *         in="formData",
     *         description="customer_name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="contact_no",
     *         in="formData",
     *         description="contact_no",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="address",
     *         in="formData",
     *         description="address",
     *         required=false,
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
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function create()
    {
        $salesPerson = $this->checkSalesPersonSession();

        $params = $this->getParameter($this->params()->fromPost());
        $params['account_id'] = $salesPerson['id'];
        $params['is_storeowner'] = $this->mapValueToInt('is_storeowner', $params['is_storeowner']);
        $params['has_smartphone'] = $this->mapValueToInt('has_smartphone', $params['has_smartphone']);
        $params['is_covered'] = $this->mapValueToInt('is_covered', $params['is_covered']);
        $params['photo'] = $this->upload('photo', "uploads/survey/");
        
        // get store details from store id 
        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        if (isset($params['store_username'])) {
            $storeInfo = $storeTable->getStoreSurveyDetails(trim($params['store_username']));
            if ($storeInfo) {
                // check whether store id was already mapped to survey?
                if ($storeInfo['survey_id']) {
                    throw new ApiException('Store was already assigned, please select another store!', 403);
                }

                // check whether store id belongs to this salesperson
                if (!$storeInfo['salesperson_account_id'] || $storeInfo['salesperson_account_id'] != $params['account_id']) {
                    throw new ApiException('This store doesn\'t belongs to you!', 403);
                }

                // copy picture also
                if (!empty($params['photo'])) {
                    $this->copyS3File("uploads/survey/" . $params['photo'], "uploads/store/" . $params['photo']);
                }

                $storeParams = $params;
                unset($storeParams['id']);
                unset($storeParams['account_id']);
                unset($storeParams['store_id']);
                unset($storeParams['is_deleted']);
                $storeParams['signup_time'] = $this->getDateTime();

                $whereArray = array('id' => $storeInfo['store_id']);
                $storeTable->updateStore($storeParams, $whereArray);

                $params['store_id'] = $storeInfo['store_id'];
            }
        }
        
        // create survey
        $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        if (!isset($params['id'])) {
            $res = $surveyTable->addSurvey($params); // id of last inserted data 
        } else {
            $res = $surveyTable->updateSurvey($params, array('id' => (int) $params['id']));
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
    
    /**
     * @SWG\Post(
     *     path="/api/survey/update",
     *     description="update store",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="survey id",
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
     *         name="customer_name",
     *         in="formData",
     *         description="customer_name",
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
        $photo = $this->upload('photo', "uploads/survey/");;
        if ($photo) {
            $parameter['photo'] = $photo;
        } else {
            unset($parameter['photo']);
        }
        $id = $this->params()->fromPost('id', 0);
        unset($parameter['id']);
        
        // when store is linked
        if (isset($parameter['store_id'])) {
            $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
            $storeInfo = $storeTable->getStoreSurveyDetails(null, $parameter['store_id']);
            if (!$storeInfo) {
                throw new ApiException('No record found!', 404);
            }
            
            // check whether store id was already mapped to survey?
            if ($storeInfo['survey_id']) {
                throw new ApiException('Store was already assigned, please select another store!', 403);
            }
            
            // copy picture also
            if (!empty($parameter['photo'])) {
                $this->copyS3File("uploads/survey/" . $parameter['photo'], "uploads/store/" . $parameter['photo']);
            }
            
            $storeParams = $parameter;
            unset($storeParams['id']);
            unset($storeParams['customer_name']);
            unset($storeParams['account_id']);
            unset($storeParams['store_id']);
            unset($storeParams['is_deleted']);
            $storeParams['signup_time'] = $this->getDateTime();

            $whereArray = array('id' => $parameter['store_id']);
            $res = $storeTable->updateStore($storeParams, $whereArray);
            if ($res === false) {
                throw new ApiException('Unable to update, please try again!', 500);
            }
        }
        
        $storeTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $res = $storeTable->updateSurvey($parameter, array('id' => $id));
        if ($res === false) {
            throw new ApiException('Unable to update, please try again!', 500);
        }
        
        return $this->successRes('Successfully updated!', $parameter);        
    }
    
    /**
     * @SWG\Get(
     *     path="/api/survey/get-unassigned-store",
     *     description="get salesperson unassigned stores",
     *     tags={"survey"},
     *     @SWG\Parameter(
     *         name="salesperson_id",
     *         in="query",
     *         description="salesman account id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getUnassignedStoreAction()
    {
        $this->checkSalesPersonSession();
        $parameter = $this->getParameter($this->params()->fromQuery());
        
        if(!isset($parameter['salesperson_id'])) {
            throw new ApiException('Account Id is required.', '400');
        }

        $surveyTable = $this->getServiceLocator()->get('Api\Table\StoreSalespersonTable');
        $data = $surveyTable->getSalesPersonStores($parameter);
        
        if ($data === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched', $data);
    }
    
    /**
     * @SWG\Get(
     *     path="/api/survey/get-salesperson-report",
     *     description="get salesperson report",
     *     tags={"survey"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="query",
     *         description="salesman account id",
     *         required=true,
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
     *     @SWG\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="start date filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="end date filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getSalespersonReportAction()
    {
        $user = $this->checkGrowsariSession();
        $parameter = $this->getParameter($this->params()->fromQuery());
        
        if (!isset($parameter['id'])) {
            throw new ApiException('Account Id is required.', '400');
        }
        $data = array();
        $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $data['report'] = $surveyTable->getSalesPersonReport($parameter);
        if ($data['report'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched', $data);
    }
    
    /**
     * @SWG\Get(
     *     path="/api/survey/get-salesperson-detail-report",
     *     description="get salesperson detail report",
     *     tags={"survey"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="query",
     *         description="salesman account id",
     *         required=true,
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
     *     @SWG\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="start date filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="end date filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getSalespersonDetailReportAction()
    {
        $user = $this->checkGrowsariSession();
        $parameter = $this->getParameter($this->params()->fromQuery());
        
        if(!isset($parameter['id'])) {
            throw new ApiException('Account Id is required.', '400');
        }
        $data = array();
        $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $data['report'] = $surveyTable->getSalesPersonDetailReport($parameter);
        if ($data['report'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched', $data);
    }
    
    /**
     * @SWG\Get(
     *     path="/api/survey/export-brief-report",
     *     description="export salesperson survey report",
     *     tags={"survey"},
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function exportBriefReportAction()
    {
        $this->checkGrowsariSession();
        
        $parameter = $this->getParameter($this->params()->fromQuery());
        if (!isset($parameter['id'])) {
            throw new ApiException('Account Id is required.', '400');
        }
        
        $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $reports = $surveyTable->getSalesPersonReport($parameter);
        if ($reports === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }
        
        $data = $this->processData($reports, 'brief');
        
        $response = $this->getResponse();
        ob_start();
        $fh = @fopen( 'php://output', 'w' );
        fputcsv($fh, array_keys($this->columnsFromDb('brief')), ',');
        if (count($data) > 0) {
            foreach ($data as $result) {
                fputcsv($fh, $result);
            }
        }
        fclose($fh);
        $response->setContent(ob_get_clean());
        
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"salesperson_report_" . (int) $parameter['id'] . "_".time().".csv\"");

        return $response;
    }
    
    /**
     * @SWG\Post(
     *     path="/api/survey/export-brief-report",
     *     description="export salesperson survey report",
     *     tags={"survey"},
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function exportDetailReportAction()
    {
        $this->checkGrowsariSession();
        
        $parameter = $this->getParameter($this->params()->fromQuery());
        if(!isset($parameter['id'])) {
            throw new ApiException('Account Id is required.', '400');
        }
        
        $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $reports = $surveyTable->getSalesPersonDetailReport($parameter);
        if ($reports === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }
        
        $data = $this->processData($reports,'detail');
        
        $response = $this->getResponse();
        ob_start();
        $fh = @fopen( 'php://output', 'w' );
        fputcsv($fh, array_keys($this->columnsFromDb('detail')), ',');
        if (count($data) > 0) {
            foreach ($data as $result) {
                fputcsv($fh, $result);
            }
        }
        fclose($fh);
        $response->setContent(ob_get_clean());
        
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"salesperson_report_" . (int) $parameter['id'] . "_".time().".csv\"");

        return $response;
    }
    
     /**
     * @SWG\Post(
     *     path="/api/survey/delete",
     *     description="survey details",
     *     tags={"survey"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="survey id",
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
        $surveyId = (int) $this->params()->fromPost('id', 0);

        $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $surveyDetails = $surveyTable->getSurveyDetails($surveyId);
        if ($surveyDetails === false) {
            throw new ApiException('No record found!', 404);
        }

        $msg = $surveyTable->deleteSurvey($surveyId, $surveyDetails['account_id']);
        if ($msg === false) {
            throw new ApiException('Unable to delete, please try again!', 500);
        }

        return $this->successRes('Successfully deleted!');
    }
    
    /**
     * @SWG\Post(
     *     path="/api/survey/upload-photo",
     *     description="Upload survey photo",
     *     tags={"survey"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="Survey Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="photo",
     *         in="formData",
     *         description="photo",
     *         required=true,
     *         type="file"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  )
     */
    public function uploadPhotoAction()
    {
        $this->checkSalesPersonSession();
        $surveyId = (int) $this->params()->fromPost('id', 0);
        
        $surveyTable = $this->getServiceLocator()->get('Api\Table\SurveyTable');
        $surveyDetails = $surveyTable->getSurveyDetails($surveyId);
        if ($surveyDetails === false) {
            throw new ApiException('No record found!', 404);
        }
        
        if ($surveyDetails['photo']) {
            throw new ApiException('You have already uploaded photo', 403);
        }

        $photo = $this->upload('photo', "uploads/survey/");;
        if (!$photo) {
            throw new ApiException('Unable to process uploaded image, please try again!', 500);
        }
        
        // update survey
        $resSurvey = $surveyTable->updateSurvey(array('photo' => $photo), array('id' => $surveyId));
        if ($resSurvey === false) {
            throw new ApiException('Unable to update, please try again!', 500);
        }
        
        // update store if store is mapped
        if ($surveyDetails['store_id']) {
            // copy picture also
            $this->copyS3File("uploads/survey/" . $photo, "uploads/store/" . $photo);
            
            $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
            $res = $storeTable->updateStore(array('photo' => $photo), array('id' => $surveyDetails['store_id']));
            if ($res === false) {
                throw new ApiException('Unable to update, please try again!', 500);
            }
        }

        return $this->successRes('Successfully uploaded', array('photo' => $photo));
    }
    
    private function processData($data,$type)
    {
        $dataTobeUsed = array();
        foreach ($data['list'] as $value) {
           $dataTobeUsed[] = $this->processRow($value,$type);
        }

        return $dataTobeUsed;
    }
    
    private function processRow($row,$type)
    {
        $columns = array_flip($this->columnsFromDb($type));
        
        $dataTobeUsed = array();
        foreach ($columns as $key => $value) {
            if($key == 'num_of_survey' || $key == 'num_of_store_signup') {
                $dataTobeUsed[$value] = (!empty($row[$key])) ? $row[$key] : 0;
            } else {
                $dataTobeUsed[$value] = (!empty($row[$key])) ? $row[$key] : '';
            }
        }
        
        return $dataTobeUsed;
    }
    
    private function columnsFromDb($type)
    {
        $data = array(); 
        if($type == 'detail') {
            $data = array(
                'Sales Person' => 'display_name',
                'Store Name' => 'survey_name',
                'Address' => 'address',
                'Phone' => 'store_phone',
                'Created At' => 'created_at',
                'Store Sign Up Time' => 'signup_time',
                'Store First Login Time' => 'first_loggedin_time',
            );
        } else if($type == 'brief') {
            $data = array(
                'Name' => 'display_name',
                'Date' => 'date',
                'No. of Surveys Taken' => 'num_of_survey',
                'No. of Store Sign Up' => 'num_of_store_signup'
            );
        }
        return $data;
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
    
    private function copyS3File($source, $destination)
    {
        $config = $this->getServiceLocator()->get('Config');
        try {
            $s3Upload = $this->getServiceLocator()->get('S3UploadService');

            $s3Upload->getS3Object()->copyObject(array(
                'Bucket'     => $config['aws']['bucket'],
                'Key'        => $destination,
                'CopySource' => $config['aws']['bucket'] ."/" . $source,
                'ACL' => 'public-read'                
            ));
        } catch (\Exception $e) {
        }
    }

}

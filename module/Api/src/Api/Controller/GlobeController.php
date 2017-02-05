<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class GlobeController extends BaseApiController
{

    /**
     * @SWG\GET(
     *     path="/api/globe/register",
     *     description="globe callback",
     *     tags={"globe"},
     *     @SWG\Parameter(
     *         name="access_token",
     *         in="query",
     *         description="access_token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="subscriber_number",
     *         in="query",
     *         description="subscriber_number",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function registerAction()
    {
        //TODO: validate the authenticity. No provision for that now in globe labs API
        $params = $this->params()->fromQuery();

        $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
        $store = $storeTable->getByField(array('contact_no' => $params['subscriber_number']));
        if ($store) {
            $params['store_id'] = $store['id'];
        }

        $storeGlobeTokenTable = $this->getServiceLocator()->get('Api\Table\GlobeStoreTokenTable');
        $storeGlobeTokenTable->addGlobeStoreToken($params);

        return $this->successRes('Successfully registered.');
    }

    /**
     * @SWG\Post(
     *     path="/api/globe/sms",
     *     description="globe callback",
     *     tags={"globe"},
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function smsAction()
    {
        $body = $this->getRequest()->getContent();
        if (!$body) {
            throw new ApiException("Unable to read the data");
        }
        
        $smsId = $this->createGlobeSms($body);
        
        $res = $this->parseSmsDetails($body);
        if (!$res) {
            throw new ApiException("Not in proper format.");
        }

        $globeSmsTable = $this->getServiceLocator()->get('Api\Table\GlobeSmsTable');
        $globeSmsTable->updateGlobeSms(array(
            'sms_uid' => $res['uid'],
            'sms_body_with_header' => $res['body'],
        ), array(
            'id' => $smsId
        ));
        
        $packages = $globeSmsTable->getByField(array('sms_uid' => $res['uid']), true);
        if ($packages && $res['no_of_packages'] == count($packages)) {
            $process = $this->getServiceLocator()->get("Base\Utils\Process");
            $process->start("globe-sms-processer --id=" . $res['uid']);
        }

        return $this->successRes('Successfully processed.');
    }

    private function parseSmsDetails($body)
    {
        $data = $this->parseSMSJson($body);
        if (empty($data["inboundSMSMessageList"])) {
            return false;
        }

        $msgDet = array();
        foreach ($data["inboundSMSMessageList"]["inboundSMSMessage"] as &$entry) {
            $msg = $entry["message"];
            $messageArray = explode("|", $msg);
            
            $isHeader = strpos($msg, '>');
            if(!$isHeader) {
                $timeStr = $this->getTimeStampString();
                $msg = $timeStr . '_' .$messageArray[0] . '|1|1>' .  $msg;
            }
            $entry["message"] = $msg;
            $messageArray = explode(">", $msg);
            $headerArray = explode("|", $messageArray[0]);
            $sender = $entry["senderAddress"];

            $msgDet['uid'] = $headerArray[0];
            $msgDet['no_of_packages'] = $headerArray[1];
            $msgDet['package_seriel_num'] = $headerArray[2];
        }
        $msgDet['body'] = json_encode($data);
        
        return $msgDet;
    }
    
    private function getTimeStampString()
    {
        return time();
    }
    
    private function createGlobeSms($body)
    {
        $globeSmsTable = $this->getServiceLocator()->get('Api\Table\GlobeSmsTable');
        $smsId = $globeSmsTable->addGlobeSms(array(
            'sms_body' => $body,
            'status' => 'CREATED'
        ));
        
        return $smsId;
    }

}

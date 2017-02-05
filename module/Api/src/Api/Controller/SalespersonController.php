<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class SalespersonController extends BaseApiController
{

    /**
     *  @SWG\Post(
     *     path="/api/salesperson/track",
     *     description="upload track info",
     *     tags={"salesperson"},
     *     @SWG\Parameter(
     *         name="location",
     *         in="formData",
     *         description="List of product : [{'point_x' : 12.7, 'point_y' : 112}]",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function trackAction()
    {
        $user = $this->checkSalesPersonSession();
        $params = $this->getParameter($this->params()->fromPost());
        $locations = $this->parseJSONString($params['location']);
        $params['salesperson_account_id'] = $user['id'];
        
        $salespersonTrackTable = $this->getServiceLocator()->get('Api\Table\SalespersonTrackTable');
        foreach ($locations as $location) {
            if (isset($location['point_x'], $location['point_y'])) {
                $params['point_x'] = $location['point_x'];
                $params['point_y'] = $location['point_y'];
                $res = $salespersonTrackTable->addSalespersonTrack($params);
            }
        }
        
        if ($res === false) {
            throw new ApiException('Unable to create/update, please try again!', 500);
        }

        return $this->successRes('Successfully inserted');
    }

}

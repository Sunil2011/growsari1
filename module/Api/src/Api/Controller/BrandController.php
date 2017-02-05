<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class BrandController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/brand",
     *     description="get all brand",
     *     tags={"brand"},
     *     @SWG\Parameter(
     *         name="last_updated_at",
     *         in="formData",
     *         description="last updated time (yyyy-MM-dd H:i:s)",
     *         required=false,
     *         type="string"
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

        $brandTable = $this->getServiceLocator()->get('Api\Table\BrandTable');
        $data['brand'] = $brandTable->getBrandList($parameter); 
        if ($data['brand'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }
        
        $data['brand'] = $this->convertImageNameToUrl($data['brand'], array("brand_image" => 'brand'));
        $data['updated_at'] = $this->getDateTime();

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/brand/{id}",
     *     description="brand details",
     *     tags={"brand"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="barnd id",
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

        $brandTable = $this->getServiceLocator()->get('Api\Table\BrandTable');
        $data['brand'] = $brandTable->getByField(array('id' => $id));
        if ($data['brand'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }
        
        $data['brand'] = $this->convertImageNameToUrl($data['brand'], array("image" => 'brand'));

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Post(
     *     path="/api/brand",
     *     description="create brand",
     *     tags={"brand"},
     *     @SWG\Parameter(
     *         name="file",
     *         in="formData",
     *         description="image upload",
     *         required=false,
     *         type="file"
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="brand id",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="brand name",
     *         required=false,
     *         type="integer"
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
        $parameter = $this->getParameter($this->params()->fromPost());
        if ($this->params()->fromFiles('file')) {
            $parameter['image'] = $this->upload("file", "uploads/brand/");
            if ($parameter['image'] === false) {
                throw new ApiException('Unable to process uploaded image, please try again!', 500);
            }
        }

        $brandTable = $this->getServiceLocator()->get('Api\Table\BrandTable');
        if (isset($parameter['id'])) {
            $parameter['updated_at'] = date("Y-m-d H:i:s");
            $res = $brandTable->updateBrand($parameter, array('id' => $parameter['id']));
        } else {
            //check if brand already exist.
            $brandDet = $brandTable->getByField(array('name' => $parameter['name']));
            if (!empty($brandDet)) {
                throw new ApiException('Brand name already exist!', 403);
            }

            $res = $brandTable->addBrand($parameter);
        }

        if ($res === false) {
            throw new ApiException('Unable to create/update!', 500);
        }

        return $this->successRes('Successfully updated.', array('id' => $res));
    }

    /**
     * @SWG\Post(
     *     path="/api/brand/delete",
     *     description="delete brand",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="brand id",
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
        $this->checkGrowsariSession();

        $parameter = $this->getParameter($this->params()->fromPost());
        $brandTable = $this->getServiceLocator()->get('Api\Table\BrandTable');
        $msg = $brandTable->deleteBrand($parameter['id']);

        if ($msg) {
            return $this->successRes('Successfully deleted brand!');
        } else {
            throw new ApiException('Unable to delete!', 500);
        }
    }

}

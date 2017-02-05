<?php

namespace Api\Controller;

use Api\Exception\ApiException;
use ArrayObject;

class CategoryController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/category",
     *     description="get all category",
     *     tags={"category"},
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

        $categoryTable = $this->getServiceLocator()->get('Api\Table\CategoryTable');
        $data['category'] = $categoryTable->getCategoryList($parameter);
        if ($data['category'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        $categories = array();
        foreach ($data['category']['list'] as $key => $category) {
            if (!in_array($category['mega_category'], $categories)) {
                $categories[$category['mega_category_id']] = $category['mega_category'];
            }
        }
        
        $data['category'] = $this->convertImageNameToUrl($data['category'], array("thumb_url" => 'category'));
        $data['mega_category'] = (count($categories)) ? $categories : new ArrayObject;
        $data['updated_at'] = $this->getDateTime();

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/category/{id}",
     *     description="category details",
     *     tags={"category"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="category id",
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

        $categoryTable = $this->getServiceLocator()->get('Api\Table\CategoryTable');
        $data['category'] = $categoryTable->getByField(array('id' => $id), false);
        if ($data['category'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }
        
        $data['category'] = $this->convertImageNameToUrl($data['category'], array("thumb_url" => 'category'));

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Post(
     *     path="/api/category",
     *     description="create category",
     *     tags={"category"},
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
     *         description="category id",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="category name",
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
            $parameter['thumb_url'] = $this->upload("file", "uploads/category/");
            if ($parameter['thumb_url'] === false) {
                throw new ApiException('Unable to process uploaded image, please try again!', '500');
            }
        }
        if (!isset($parameter['name'])) {
            throw new ApiException('Please submit all required parameters, category name is missing!', '400');
        }

        $categoryTable = $this->getServiceLocator()->get('Api\Table\CategoryTable');
        if (isset($parameter['id'])) {
            $parameter['updated_at'] = date("Y-m-d H:i:s");
            $res = $categoryTable->updateCategory($parameter, array('id' => $parameter['id']));
        } else {
            //check if brand already exist.
            $categoryDet = $categoryTable->getByField(array('name' => $parameter['name']));
            if (!empty($categoryDet)) {
                throw new ApiException('Category name already exists!', '403');
            }

            $res = $categoryTable->addCategory($parameter);
        }

        if ($res === false) {
            throw new ApiException('Unable to create/update, please try again!', '500');
        }

        return $this->successRes('Successfully updated.', array('id' => $res));
    }

    /**
     * @SWG\Post(
     *     path="/api/category/delete",
     *     description="delete category",
     *     tags={"store"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="category id",
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
        $categoryTable = $this->getServiceLocator()->get('Api\Table\CategoryTable');
        $msg = $categoryTable->deleteCategory($parameter['id']);

        if ($msg) {
            return $this->successRes('Successfully deleted Category!');
        } else {
            throw new ApiException('Unable to delete, please try again!', '500');
        }
    }

}

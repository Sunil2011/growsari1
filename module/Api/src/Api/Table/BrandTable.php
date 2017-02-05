<?php

namespace Api\Table;

use Base\Table\BaseTable;
use Api\Model\Brand;
use Zend\Db\Sql\Where as Where;

class BrandTable extends BaseTable
{
    public function addBrand($parameter)
    {
        try {
            $BrandModel=  new Brand();
            $this->getModelForAdd($BrandModel, $parameter);
            return $this->addModel($BrandModel);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function updateBrand($parameter, $whereArray)
    {
        try {
            $BrandModel = new Brand();
            $this->getModelForAdd($BrandModel, $parameter);
            return $this->updateModel($BrandModel, $whereArray);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getBrandList($parameter)
    {
        try {
            $operation = array();

            $where = new Where();
            if (isset($parameter['last_updated_at'])) {
                $where->greaterThanOrEqualTo('brand.updated_at', $parameter['last_updated_at']);
            }
            $operation['where'] = $where;
            
            $operation['column'] = array(
                'brand_id' => 'id',
                'brand_name' => 'name',
                'brand_image' => 'image',
                'updated_at',
            );
            
            if(isset($parameter['page'])) {
                $operation['page'] = $parameter['page'];
                $operation['limit'] = isset($parameter['limit']) ? $parameter['limit'] : 20;
            }
            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getBrandDetails($brandId){
        
         try {
            $operation = array();

            $where = new Where();
            $where->equalTo('brand.id', $brandId);
            
            
            $operation['where'] = $where;
            
           
            return $this->getList($operation);
            
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
        
    }


    public function getProductDetails($productId){
       
        try{
            $operation = array();
            
            $where = new Where() ;
            $where->equalTo('product.id', $productId);
            
            $where->equalTo('product.is_deleted', 0) ;
            
            $operation['where'] = $where;
            
            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'category',
                'condition' => 'product.category_id = category.id',
                'columns' => array('line' => 'name'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'brand',
                'condition' => 'product.brand_id = brand.id',
                'columns' => array('variant' => 'name'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;
            
            $operation['first_row'] = $joinParameters;

            return $this->getList($operation);
            
        } catch (Exception $ex) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false ;
        
    }
    
    public function deleteBrand($id)
    {
        try {
            $this->tableGateway->delete(array('id' => (int) $id)); 
            
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
}

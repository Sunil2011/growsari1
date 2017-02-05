<?php

namespace Api\Table;

use Api\Model\Category;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Where as Where;


class CategoryTable extends BaseTable
{
    
    public function addCategory($parameter)
    {
        try {
            $categoryModel=  new Category();
            $this->getModelForAdd($categoryModel, $parameter);
            return $this->addModel($categoryModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function updateCategory($parameter, $whereArray)
    {
        try {
            $categoryModel = new Category();
            $this->getModelForAdd($categoryModel, $parameter);
            return $this->updateModel($categoryModel, $whereArray);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getCategoryList($parameter)
    {
        try {
            $operation = array();

            $where = new Where();
            if(isset($parameter['last_updated_at']) && $parameter['last_updated_at']) {
                $where->greaterThanOrEqualTo('category.updated_at', $parameter['last_updated_at']);
            }
            $operation['where'] = $where;
            
            $operation['column'] = array(
                'category_id' => 'id',
                'category_name' => 'name',
                'thumb_url',
                'updated_at',
                'mega_category_id'
            );
            
            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'mega_category',
                'condition' => 'category.mega_category_id = mega_category.id',
                'columns' => array('mega_category' => 'name'),
                'type' => 'inner'
            );
            $operation['join'] = $joinParameters;
            
            if(isset($parameter['page'])) {
                $operation['page'] = $parameter['page'];
                $operation['limit'] = isset($parameter['limit']) ? $parameter['limit'] : 20;
            }
            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function deleteCategory($id)
    {
        try {
            $this->tableGateway->delete(array('id' => (int) $id)); 
            
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
}

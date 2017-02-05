<?php

namespace Api\Table;

use Api\Model\Product;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Where as Where;

class ProductTable extends BaseTable
{

    public function getProductList($parameter, $productIds = array())
    {
        try {
            $operation = array();

            $where = new Where();
            if (isset($parameter['last_updated_at'])) {
                $where->greaterThanOrEqualTo('product.updated_at', $parameter['last_updated_at']);
            } else if (!isset($parameter['get_deleted_also'])) {
                // when asked for last updated at, send deleted also
                // else load non deleted
                $where->equalTo('product.is_deleted', 0);
            }
            if (isset($parameter['category_id'])) {
                $where->equalTo('product.category_id', $parameter['category_id']);
            }
            if (isset($parameter['brand_id'])) {
                $where->equalTo('product.brand_id', $parameter['brand_id']);
            }
            if (isset($parameter['search'])) {
                $where->nest
                                ->like('product.item_code', '%' . $parameter['search'] . '%')
                                ->or
                                ->like('product.sku', '%' . $parameter['search'] . '%')
                                ->or
                                ->like('product.super8_name', '%' . $parameter['search'] . '%')
                        ->unnest;
            }

            if (!empty($productIds)) {
                $where->in('product.id', $productIds);
            }
            $operation['where'] = $where;

            $operation['column'] = array(
                '*',
                'thumb_image' => 'image'
            );

            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'category',
                'condition' => 'product.category_id = category.id',
                'columns' => array('line' => 'name'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'mega_category',
                'condition' => 'category.mega_category_id = mega_category.id',
                'columns' => array('mega_category' => 'name'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'brand',
                'condition' => 'product.brand_id = brand.id',
                'columns' => array('variant' => 'name', 'brand_image' => 'image'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;

            if (isset($parameter['page'])) {
                $operation['page'] = $parameter['page'];

                if (!isset($parameter['no_limit'])) {
                    $operation['limit'] = isset($parameter['limit']) ? $parameter['limit'] : 20;
                }
            }


            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getProductDetails($productId)
    {

        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('product.id', $productId);

            $where->equalTo('product.is_deleted', 0);

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
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getProductOrderDetails($orderId, $productId)
    {

        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('product.id', $productId);
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
            $whereString = 'order_item.order_id = ' . (int) $orderId;
            $joinParameters[] = array(
                'table' => 'order_item',
                'condition' => new Expression('order_item.product_id = product.id AND order_item.is_deleted=0 AND ' . $whereString),
                'columns' => array('item_id' => 'id'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;

            $operation['first_row'] = $joinParameters;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function addProduct($parameter)
    {

        try {
            $ProductModel = new Product();
            $this->getModelForAdd($ProductModel, $parameter);
            return $this->addModel($ProductModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateProduct($parameter, $whereArray)
    {

        try {
            $ProductModel = new Product();
            $this->getModelForAdd($ProductModel, $parameter);
            return $this->updateModel($ProductModel, $whereArray);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function deleteProduct($id)
    {

        try {
            // $this->tableGateway->delete(array('id' => (int) $id)); 
            // do not delete the data just change the flag value 'is_deleted' to 1
            $data = array(
                'is_deleted' => 1,
                'updated_at' => date("Y-m-d H:i:s")
            );

            $this->tableGateway->update($data, array('id' => $id));
            return 'Product with id ' . (int) $id . ' is deleted !';
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function markAsNotAvailable($itemIds)
    {
        try {
            /* create a new statement object with the current adapter */
            $statement = $this->tableGateway->getAdapter()
                ->createStatement(
                    'UPDATE product p
                    LEFT JOIN order_item oi  ON oi.product_id = p.id
                    SET p.is_available = 0, p.updated_at = now()
                    WHERE oi.id IN (?)'
                );

            $resultSet = new ResultSet;
            $resultSet->initialize( $statement->execute( array( implode(',', $itemIds) ) ) );

            return $resultSet->count();
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        
        return false;
    }

}

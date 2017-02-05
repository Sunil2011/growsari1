<?php

namespace Api\Table;

use Api\Model\OrderItem;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Where as Where;

class OrderItemTable extends BaseTable
{

    public function addOrderItem($parameter)
    {
        try {
            $OrderItemModel = new OrderItem();
            $this->getModelForAdd($OrderItemModel, $parameter);
            return $this->addModel($OrderItemModel);
        } catch (Exception$e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function updateOrderItem($parameter, $where)
    {
        try {
            $OrderItemModel = new OrderItem();
            $this->getModelForAdd($OrderItemModel, $parameter);
            return $this->updateModel($OrderItemModel, $where);
        } catch (Exception$e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getOrderItemDetails($filterParam)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('order_item.order_id', $filterParam['order_id']);
            $where->equalTo('order_item.is_deleted', 0);
            if (isset($filterParam['is_available'])) {
                $where->equalTo('order_item.is_available', $filterParam['is_available']);
            }
            if (isset($filterParam['not_available'])) {
                $where->equalTo('order_item.is_available', 0);
            }
            if (isset($filterParam['is_modified'])) {
                $where->equalTo('order_item.is_modified', $filterParam['is_modified']);
            }
            $opreration['where'] = $where;
            

            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'product',
                'condition' => 'order_item.product_id = product.id',
                'columns' => array('item_code', 'sku', 'super8_name', 'variant_color', 'image', 'format', 'promo', 'is_deleted', 'volume'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'category',
                'condition' => 'product.category_id = category.id',
                'columns' => array('category' => 'name'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'brand',
                'condition' => 'product.brand_id = brand.id',
                'columns' => array('brand' => 'name'),
                'type' => 'left'
            );
            $opreration['join'] = $joinParameters;
            if (isset($filterParam['sort_by']) && $filterParam['sort_by'] === 'category') {
                $opreration['order_by'] = 'category.name ASC';
            } else {
                $opreration['order_by'] = 'order_item.is_modified DESC, order_item.id DESC';
            }
            
            
            return $this->getList($opreration);
        } catch (Exception$e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function setAvailabilityStatus($id, $quantity, $isModified = null, $by = 'cc')
    {
        $quantity = (int) $quantity;
        try {
            $sql = $this->getSql();
            $update = $sql->update();
            $update->table('order_item');
            
            $setValues = array(
                'is_available' => ($quantity) ? 1 : 0,
                'quantity' => $quantity,
                'amount' => new Expression("srp * " . $quantity),
                'discount' => new Expression("(srp * " . $quantity . ") - (price * ". $quantity . ")"),
                'net_amount' => new Expression("price * " . $quantity),
                'updated_at' => new Expression('NOW()')
            );
            if ($isModified) {
                $setValues['is_modified'] = $isModified;
                if($by === 'cc') {
                    $setValues['quantity_by_cc'] = $quantity;
                } else if($by === 'wh') {
                    $setValues['quantity_by_wh'] = $quantity;
                }
            }
            
            $update->set($setValues);
            $update->where(array('id' => $id));
            
            $statement  = $sql->prepareStatementForSqlObject($update);
            $results    = $statement->execute();
            
            return $results;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        
        return false;
    }

    public function getItemsByIds($parameter)
    {
        
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from($table);
            
            $where = new Where();
            $where->equalTo('order_id', $parameter['order_id']);
            $where->in('id',$parameter['item_ids']);
            
            $select->where($where);
            
            $result = $this->executeQuery($select);
            
            $data = array();
            foreach ($result as $row) {
                $data[] = $result->current();
            }
            return $data;
            
        } catch (Exception$e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

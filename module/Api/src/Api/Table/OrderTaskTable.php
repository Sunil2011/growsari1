<?php

namespace Api\Table;

use Api\Model\OrderTask;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Where;

class OrderTaskTable extends BaseTable
{

    public function addOrderTask($parameter)
    {
        try {
            $orderTaskModel = new OrderTask();
            $this->getModelForAdd($orderTaskModel, $parameter);
            return $this->addModel($orderTaskModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateOrderTask($parameter, $where)
    {
        try {
            $orderTaskModel = new OrderTask();
            $this->getModelForAdd($orderTaskModel, $parameter);
            return $this->updateModel($orderTaskModel, $where);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getOrderTaskDetails($orderTaskId)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('order_task.id', $orderTaskId);
            $operation['where'] = $where;
            
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }
    
    public function getCurrentOrderTask($orderId)
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from($table)
                    ->where(array('order_id' => $orderId, 'is_finished' => 0))
                    ->order('created_at DESC')
                    ->limit(1);

            return $this->executeQuery($select)->current();
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getTaskCount()
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();

            $select = $sql->select();
            $select->from(array('ot' => $table))
                    ->columns(array(
                        'task_count' => new Expression('COUNT(*)'),
            ))
            ->where(array('is_finished' => 0));

            $row = $this->executeQuery($select)->current();
            return isset($row['task_count']) ? $row['task_count'] : 0;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

<?php

namespace Api\Table;

use Api\Model\OrderFeedback;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Where;

class OrderFeedbackTable extends BaseTable
{

    public function addOrderFeedback($parameter)
    {
        try {
            $orderFeedbackModel = new OrderFeedback();
            $this->getModelForAdd($orderFeedbackModel, $parameter);
            return $this->addModel($orderFeedbackModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateOrderFeedback($parameter, $where)
    {
        try {
            $orderFeedbackModel = new OrderFeedback();
            $this->getModelForAdd($orderFeedbackModel, $parameter);
            return $this->updateModel($orderFeedbackModel, $where);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getOrderFeedbackList($page = 1, $limit = 20)
    {
        try {
            $operation = array();
            
            $operation['order_by'] = 'order_feedback.id DESC';
            $operation['page'] = $page;
            $operation['limit'] = $limit;

            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getOrderFeedbackDetails($orderFeedbackId)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('order_feedback.id', $orderFeedbackId);
            $operation['where'] = $where;
            
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }
    
    public function getCurrentOrderFeedback($orderId)
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from($table)
                    ->where(array('order_id' => $orderId));

            $select->order('created_at DESC');
            return $this->executeQuery($select)->current();
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

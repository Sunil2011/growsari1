<?php

namespace Api\Table;

use Base\Table\BaseTable;
use Api\Model\OrderStatus;
use Zend\Db\Sql\Predicate\Expression as Expression2;

class OrderStatusTable extends BaseTable
{

    CONST PENDING = 'pending'; // order created
    CONST CONFIRMED = 'confirmed'; // order confirmed
    CONST READYTOPACK = 'ready_to_pack'; // order packed
    CONST PACKED = 'packed'; // order packed
    CONST SHIPPED = 'dispatched'; // order shipped
    CONST DELIVERED = 'delivered'; // order delivered
    CONST CANCELLED = 'cancelled'; // order cancelled
    CONST COMPLETED = 'completed'; // order cancelled
    
    public static $statuses = array(
        'pending', 
        'confirmed', 
        'ready_to_pack', 
        'packed',
        'dispatched', 
        'delivered',
        'cancelled', 
        'completed'
    );

    public function addOrderStatus($parameter)
    {
        try {
            $OrderStatusModel = new OrderStatus();
            $this->getModelForAdd($OrderStatusModel, $parameter);
            return $this->addModel($OrderStatusModel);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getCurrentOrderStatus($orderId)
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
    
    public function getNonConfirmedOrders($hours) 
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('os' => $table))
                    ->where(new Expression2('os.created_at < DATE_SUB(NOW(), INTERVAL '. (int)$hours .' HOUR)'))
                    ->where(array('status' => \Api\Table\OrderStatusTable::SHIP_DATE_ADD))
                    ->where->isNull('os2.order_id');
            
            $select->join(
                ["os2" => 'order_status'], 'os.order_id = os2.order_id AND os.id < os2.id',
                [],
                \Zend\Db\Sql\Select::JOIN_LEFT
            );
            
            echo $select->getSqlString();exit;
            $statement = $sql->prepareStatementForSqlObject($select);
            return $statement->execute();
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        
        return array();
    }

}

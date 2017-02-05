<?php

namespace Api\Table;

use Base\Table\BaseTable;
use Zend\Db\Sql\Where as Where;

class StoreWarehouseShipperTable extends BaseTable
{
    public function addStoreWarehouseShiper($parameter)
    {
        try {
            $orderModel = new \Api\Model\StoreWarehouseShipper();
            $this->getModelForAdd($orderModel, $parameter);
            return $this->addModel($orderModel);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getStoreDet($assocId)
    {
        try {
            
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('sws' => $table));
            
            $select->join(
                array('s' => 'store'),
                'sws.store_id = s.id',
                array('account_id'),
                \Zend\Db\Sql\Select::JOIN_LEFT
            );
            
            $where = new Where();
            $where->equalTo('sws.id', $assocId);
            $select->where($where);
            
            return $this->executeQuery($select)->current();
            
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

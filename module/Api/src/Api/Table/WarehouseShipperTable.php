<?php

namespace Api\Table;

use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Where;

class WarehouseShipperTable extends BaseTable
{
    public function getShipperList($warehouseId)
    {
        try {
            $operation = array();
            $operation['column'] = array(
                'warehouse_shipper_id' => 'id',
                'warehouse_id' => 'warehouse_id',
            );
            $where = new Where();
            $where->equalTo('warehouse_shipper.warehouse_id', $warehouseId);
            $operation['where'] = $where;
            
            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'shipper',
                'condition' => 'shipper.id = warehouse_shipper.shipper_id',
                'columns' => array('*'),
                'type' => 'inner'
            );
            $operation['join'] = $joinParameters;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
        
    }
}

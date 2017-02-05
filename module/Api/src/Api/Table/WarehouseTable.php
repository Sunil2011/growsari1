<?php

namespace Api\Table;

use Base\Table\BaseTable;

class WarehouseTable extends BaseTable
{
    
    public function getWarehouseList()
    {
        try {
            $operation = array();

            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
        
    }
}

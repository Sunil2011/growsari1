<?php

namespace Api\Table;

use Base\Table\BaseTable;
use Exception;
use SebastianBergmann\RecursionContext\Exception as Exception2;
use Zend\Db\Sql\Where;

class ShipperTable extends BaseTable
{
    public function getShipperList()
    {
        try {
            $operation = array();

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
        
    }
    
    public function getShipperDetails($accountId)
    {

        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('shipper.account_id', $accountId);

            $operation['where'] = $where;
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception2 $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
}

<?php

namespace Api\Table;

use Api\Model\StoreRefer;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class StoreReferTable extends BaseTable
{

    public function addStoreRefer($parameter)
    {
        try {
            $orderModel = new StoreRefer();
            $this->getModelForAdd($orderModel, $parameter);
            return $this->addModel($orderModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getStoreReferredDetails($storeId)
    {
        try {

            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('a' => 'account'));
            $select->join(array('s' => 'store'), 's.account_id = a.id', array());
            $select->join(array('sr' => 'store_refer'), 'sr.store_id = s.id', array('sore_refer_id' => 'id'), Select::JOIN_LEFT);
            $select->join(array('sb' => 'store'), 'sb.id = sr.refered_by', array('refered_account_id' => 'account_id'), Select::JOIN_LEFT);

            $where = new Where();
            $where->equalTo('s.id', $storeId);
            $select->where($where);
            $select->group('s.id');
            
            
            $result = $this->executeQuery($select);

            return ($result) ? $result->current() : false;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }


}

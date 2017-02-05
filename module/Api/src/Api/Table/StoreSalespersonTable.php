<?php

namespace Api\Table;

use Api\Model\StoreSalesperson;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class StoreSalespersonTable extends BaseTable
{

    public function addStoreSalesperson($parameter)
    {
        try {
            $orderModel = new StoreSalesperson();
            $this->getModelForAdd($orderModel, $parameter);
            return $this->addModel($orderModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getSalespersonDetails($salespersonId)
    {
        try {

            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('a' => 'account'));
            $select->join(array('ss' => 'store_salesperson'), 'ss.salesperson_account_id = a.id', array('count' => new Expression('count(ss.id)')), Select::JOIN_LEFT);

            $where = new Where();
            $where->equalTo('a.id', $salespersonId);
            $select->where($where);

            $select->group('a.id');

            return $this->executeQuery($select)->current();
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return array('count' => 0);
    }

    public function getSalesPersonReport($parameter)
    {
        $page = isset($parameter['page']) ? $parameter['page'] : 1;
        $limit = isset($parameter['limit']) ? $parameter['limit'] : 20;

        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();

            $select = $sql->select();
            $select->from(array('ss' => $table));
            $select->join(array('s' => 'store'), 'ss.store_id = s.id', array('store_name' => 'name', 'store_phone' => 'contact_no', 'signup_time', 'first_loggedin_time', 'area' => new Expression('CONCAT(locality, ", ", city)')), Select::JOIN_LEFT);

            $select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));

            $where = new Where();
            $where->equalTo('ss.salesperson_account_id', $parameter['id']);
            $where->notEqualTo('s.signup_time', 0);
            if (isset($parameter['start_date'])) {
                $where->addPredicate(new Expression('DATE(s.signup_time) >= "' . $parameter['start_date'] . '"'));
            }
            if (isset($parameter['end_date'])) {
                $where->addPredicate(new Expression('DATE(s.signup_time) <= "' . $parameter['end_date'] . '"'));
            }
            $select->where($where);
            $select->order('s.signup_time DESC');

            $select->limit(intval($limit));
            $select->offset((intval($page) - 1) * intval($limit));

            $result = $this->executeQuery($select);

            return $this->getResultArray($result, $page, $limit);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getSalesPersonStores($parameter)
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();

            $select = $sql->select();
            $select->from(array('ss' => $table));
            $select->join(array('s' => 'store'), 'ss.store_id = s.id', array('store_id' => 'id', 'name', 'contact_no'), Select::JOIN_LEFT);
            $select->join(array('su' => 'survey'), 'ss.store_id = su.store_id', array('survey_id' => 'id'), Select::JOIN_LEFT);
            
            $where = new Where();
            $where->equalTo('ss.salesperson_account_id', $parameter['salesperson_id']);
            $where->isNull('su.id');
            $select->where($where);
            
            $select->order('s.name ASC');

            $result = $this->executeQuery($select);
            return $this->getResultArray($result);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

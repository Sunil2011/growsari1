<?php

namespace Api\Table;

use Api\Model\AccountDevice;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Expression as Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where as Where;

class AccountDeviceTable extends BaseTable
{
    
    public function getAccountDeviceList($page = 1, $limit = 200)
    {
        try {
            $operation = array();
            $operation['page'] = $page;
            $operation['limit'] = $limit;
            

            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
        
    }
    
    public function addAccountDevice($parameter)
    {
        try {
            $deviceModel = new AccountDevice();
            $this->getModelForAdd($deviceModel, $parameter);
            return $this->addModel($deviceModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function updateAccountDevice($parameter, $where)
    {
        try {
            $deviceModel = new AccountDevice();
            $this->getModelForAdd($deviceModel, $parameter);
            return $this->updateModel($deviceModel, $where);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getStoreAssocId($accountId)
    {
        
        try {
            
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('a' => 'account'))
                    ->columns(array('account_id' => 'id'));
            
            $select->join(
                array('s' => 'store'),
                'a.id = s.account_id',
                array(),
                Select::JOIN_LEFT
            )
            ->join(
                array('sws' => 'store_warehouse_shipper'),
                's.id = sws.store_id',
                array('associate_id' => 'id'),
                Select::JOIN_LEFT
            );
            
            $where = new Where();
            $where->equalTo('a.id', $accountId);
            $select->where($where);
            
            return $this->executeQuery($select)->current();
            
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getStoreDetails($accountId)
    {
        try {
            
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('a' => 'account'))
                    ->columns(array('account_id' => 'id','username','email','phone'));
            
            $select->join(
                array('lp' => 'loyalty_point'),
                'a.id = lp.account_id',
                array('loyalty_points' => new Expression('SUM(credit) - SUM(debit)')),
                Select::JOIN_LEFT
            );
            
            $where = new Where();
            $where->equalTo('a.id', $accountId);
            $select->where($where);
            
            return $this->executeQuery($select)->current();
            
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getSalespersonList()
    {
        try {
            
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('a' => 'account'))
                    ->columns(array('account_id' => 'id','username','email','phone','state'));
            
            $select->join(
                array('ss' => 'store_salesperson'),
                'a.id = ss.salesperson_account_id',
                array('num_of_stores' => new Expression('COUNT(ss.id)')),
                Select::JOIN_LEFT
            );
            
            $where = new Where();
            $where->equalTo('a.type', AccountTable::TYPE_SALESPERSON);
            $select->where($where);
            
            $select->group('a.id');
            
            $result = $this->executeQuery($select);
            
            return $this->getResultArray($result);
            
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
}

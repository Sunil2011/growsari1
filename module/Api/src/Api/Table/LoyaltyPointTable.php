<?php

namespace Api\Table;

use Api\Model\LoyaltyPoint;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Where;

class LoyaltyPointTable extends BaseTable
{
    CONST REMARK_DEBIT_ORDER = "Debit for an order";
    CONST REMARK_CREDIT_ORDER = "Credit for an order";
    CONST REMARK_CREDIT_ORDER_EXTRA = "Credit for an order (Excess amount refunded)";
    CONST REMARK_CREDIT_ORDER_CANCEL = "Credit for an order (Returned due to order cancel)";
    CONST REMARK_CREDIT_REFERAL = "Referal bonus";
    CONST REMARK_LOAN = "Loan credit";
    CONST REMARK_PROMO = "Promo credit";
    CONST REMARK_CREDIT_SIGNUP = "Signup credits";
    CONST REMARK_DEBIT_SIGNUP_CANCEL = "Signup credits Expired";

    public function addPoints($parameter)
    {
        try {
            $loyaltyModel = new LoyaltyPoint();
            $this->getModelForAdd($loyaltyModel, $parameter);
            return $this->addModel($loyaltyModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updatePoints($parameter, $where)
    {
        try {
            $loyaltyModel = new LoyaltyPoint();
            $this->getModelForAdd($loyaltyModel, $parameter);
            return $this->updateModel($loyaltyModel, $where);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getUserPointsByOrderId($orderId)
    {
        try {

            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('o' => 'order'))
                    ->columns(array())
                    ->join(array('sws' => 'store_warehouse_shipper'), 'o.associate_id = sws.id', array('store_id'))
                    ->join(array('s' => 'store'), 'sws.store_id = s.id', array('account_id'))
                    ->join(array('sta' => 'account'), 'sta.id = s.account_id', array())
                    ->join(array('lp' => 'loyalty_point'), 'lp.account_id = sta.id', array('points' => new Expression(" (SUM(credit) - SUM(debit))")), 'left')
                    ->where(array('o.id' => $orderId));
            $select->group('sta.id');
            
            $stmt = $sql->prepareStatementForSqlObject($select);    
            $result = $stmt->execute();
            
            $row = $result->current();
            return $row;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getUserPointsByAccountId($accountId)
    {
        try {

            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('lp' => 'loyalty_point'))
                    ->columns(array('points' => new Expression(" (SUM(credit) - SUM(debit))")))
                    ->where(array('lp.account_id' => $accountId));
            $select->group('lp.account_id');
            
            $stmt = $sql->prepareStatementForSqlObject($select);   
            $result = $stmt->execute();
            
            $row = $result->current();
            return $row;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    
    public function getAccountsForCreditExpiry()
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('lp' => $table))
                    ->columns(array('account_id', 'points' => new Expression("(SUM(credit) - SUM(debit))")))
                    ->join(array('a' => 'account'), 'a.id = lp.account_id', array('username'))
                    ->join(array('s' => 'store'), 's.account_id = a.id', array('contact_no', 'signup_time', 'no_order_days_since_signup' => new Expression('DATEDIFF(now(), s.signup_time)')))
                    ->join(array('sws' => 'store_warehouse_shipper'), 'sws.store_id = s.id', array('store_id'))
                    ->join(array('o' => 'order'), 'o.associate_id = sws.id', array(), 'left');

            $where = new Where();
            $where->NEST
                    ->isNotNull('s.signup_time')
                    ->AND
                    ->addPredicate(new Expression('DATEDIFF(now(), s.signup_time) >= 5'))
                    ->AND
                    ->like('lp.remarks', LoyaltyPointTable::REMARK_CREDIT_SIGNUP . '%')
                   ->UNNEST;
            
            $where->isNull('o.id');

            $select->order('a.id DESC');
            $select->group('a.id');
            $select->having('points > 0');

            $select->where($where);

            $result = $this->executeQuery($select);
            return $this->getResultArray($result);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

}

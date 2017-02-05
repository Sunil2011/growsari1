<?php

namespace Api\Table;

use Api\Model\Store;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Expression as Expression;
use Zend\Db\Sql\Where;
use Zend\Validator\EmailAddress;

class StoreTable extends BaseTable
{

    public function updateAccountPassword($username, $password)
    {
        try {
            $sql = $this->getSql();
            $update = $sql->update();
            $update->table('account');
            $update->set(array(
                'password' => $password
            ));
            $update->where(array('username' => $username));

            $statement = $sql->prepareStatementForSqlObject($update);
            $results = $statement->execute();

            return $results;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    public function getStoreList($parameter, $storeIds = array())
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('store.is_deleted', 0);

            if (!empty($storeIds)) {
                $where->in('store.id', $storeIds);
            }
            if (isset($parameter['search'])) {
                $where->nest
                                ->like('store.name', '%' . $parameter['search'] . '%')
                                ->or
                                ->like('account.username', '%' . $parameter['search'] . '%')
                        ->unnest;
            }
            $operation['where'] = $where;

            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'account',
                'condition' => 'account.id = store.account_id',
                'columns' => array('username'),
                'type' => 'inner'
            );
            $operation['join'] = $joinParameters;

            if (isset($parameter['page'])) {
                $operation['page'] = $parameter['page'];
                $operation['limit'] = isset($parameter['limit']) ? $parameter['limit'] : 20;
            }

            $operation['order_by'] = 'store.id DESC';

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getStoreDetails($storeId)
    {

        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('store.id', $storeId);

            $where->equalTo('store.is_deleted', 0);

            $operation['where'] = $where;
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function addStore($parameter)
    {

        try {
            $storeModel = new Store();
            $this->getModelForAdd($storeModel, $parameter);
            return $this->addModel($storeModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateStore($parameter, $whereArray)
    {

        try {
            $storeModel = new Store();
            $this->getModelForAdd($storeModel, $parameter);
            return $this->updateModel($storeModel, $whereArray);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function deleteStore($id, $accountId)
    {

        try {
            // do not delete the data just change the flag value 'is_deleted' to 1
            $data = array(
                'is_deleted' => 1,
            );

            $this->tableGateway->update($data, array('id' => $id));

            // update store state to 0
            $sql = $this->getSql();
            $update = $sql->update();
            $update->table('account');
            $update->set(array('state' => 0));
            $update->where(array('id' => $accountId));

            $statement = $sql->prepareStatementForSqlObject($update);
            $statement->execute();

            return true;
        } catch (Exception2 $ex) {
            return false;
        }
    }

    public function getStoreDetailsFromUserId($userId)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('store.account_id', $userId);
            $operation['where'] = $where;

            $operation['first_row'] = true;

            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getStoreAssocId($userId)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('store.account_id', $userId);
            $operation['where'] = $where;

            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'store_warehouse_shipper',
                'condition' => 'store.id = store_warehouse_shipper.store_id',
                'columns' => array('associate_id' => 'id'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;
            $operation['first_row'] = true;
            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getStoreSurveyDetails($storeUserName, $storeId = null)
    {
        try {
            $operation = array();

            $where = new Where();
            if ($storeId) {
                $where->equalTo('store.id', $storeId);
            } else if ($storeUserName) {
                $where->like('account.username', '%' . $storeUserName . '%');
            } else {
                return false;
            }

            $where->equalTo('store.is_deleted', 0);

            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'account',
                'condition' => 'store.account_id = account.id',
                'columns' => array('account_id' => 'id'),
                'type' => 'inner'
            );
            $joinParameters[] = array(
                'table' => 'store_salesperson',
                'condition' => 'store.id = store_salesperson.store_id',
                'columns' => array('salesperson_account_id', 'store_id'),
                'type' => 'inner' //assuming store will be assigned to salesperson always
            );
            $joinParameters[] = array(
                'table' => 'survey',
                'condition' => 'store.id = survey.store_id',
                'columns' => array('survey_id' => 'id'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;

            $operation['where'] = $where;
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getStoreByEmail($email)
    {
        try {
            $operation = array();
            $where = new Where();
            $validator = new EmailAddress();
            if ($validator->isValid($email)) {
                $where->like('account.email', trim($email));
            } else {
                $where->like('account.username', trim($email));
            }

            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'account',
                'condition' => 'store.account_id = account.id',
                'columns' => array(),
                'type' => 'inner'
            );
            $operation['join'] = $joinParameters;

            $operation['where'] = $where;
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    public function getStoreWalletDet($storeId)
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('s' => $table))
                    ->join(array('a' => 'account'), 's.account_id = a.id', array('username'))
                    ->join(array('lp' => 'loyalty_point'), 'a.id = lp.account_id', array(
                        'points' => new Expression(" (SUM(credit) - SUM(debit))")
                            ), 'left')
                    ->columns(array('store_id' => 'id', 'name', 'account_id'));

            $where = new Where();
            $where->equalTo('s.id', $storeId);

            $select->group('a.id');
            $select->where($where);

            $result = $this->executeQuery($select);
            if ($result) {
                return $result->current();
            }
            return array();
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    public function getStores($search)
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('s' => $table))
                    ->join(array('a' => 'account'), 'a.id = s.account_id', array('username'), 'inner')
                    ->join(array('lp' => 'loyalty_point'), 'a.id = lp.account_id', array(
                        'points' => new Expression(" (SUM(credit) - SUM(debit))")
                            ), 'left')
                    ->columns(array('store_id' => 'id', 'name', 'full_name' => new Expression('CONCAT(name, " - ", username)')));

            $where = new Where();
            $where->equalTo('s.is_deleted', 0);
            if ($search) {
                $where->nest
                                ->like('s.name', '%' . $search . '%')
                                ->or
                                ->like('a.username', '%' . $search . '%')
                        ->unnest;
            }
            
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

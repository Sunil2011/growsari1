<?php

namespace Api\Table;

use Base\Table\BaseTable;
use Zend\Db\Sql\Where as Where;
use Zend\Db\Sql\Predicate\Expression;
use Api\Model\Survey;

class SurveyTable extends BaseTable
{

    public function getSurveyList($data)
    {
        $page = isset($data['page']) ? $data['page'] : 1;
        $limit = isset($data['limit']) ? $data['limit'] : 20;
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('survey.account_id', $data['salesperson_id']);
            $where->equalTo('survey.is_deleted', 0);
            if(isset($data['search']) && $data['search']) {
                $where->nest()
                        ->like('survey.name', '%' . $data['search'] . '%')
                        ->OR
                        ->like('survey.customer_name', '%' . $data['search'] . '%')
                        ->OR
                        ->like('survey.contact_no', '%' . $data['search'] . '%')
                        ->unnest();
            }
            
            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'store',
                'condition' => 'store.id = survey.store_id',
                'columns' => array('store_signup_time' => 'signup_time', 'store_first_loggedin_time' => 'first_loggedin_time'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;
            
            $operation['where'] = $where;
            $operation['page'] = $page;
            $operation['limit'] = $limit;

            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getSurveyDetails($surveyId)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('survey.id', $surveyId);
            $operation['where'] = $where;
            
            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'store',
                'condition' => 'store.id = survey.store_id',
                'columns' => array('store_account_id' => 'account_id', 'store_name' => 'name'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'account',
                'condition' => 'account.id = store.account_id',
                'columns' => array('username'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;
            
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    public function addSurvey($parameter)
    {
        try {
            $surveyModel = new Survey();
            $this->getModelForAdd($surveyModel, $parameter);
            return $this->addModel($surveyModel);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateSurvey($parameter, $whereArray)
    {

        try {
            $surveyModel = new Survey();
            $this->getModelForAdd($surveyModel, $parameter);
            return $this->updateModel($surveyModel, $whereArray);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function deleteSurvey($id)
    {
        try {
            // do not delete the data just change the flag value 'is_deleted' to 1
            $data = array(
                'is_deleted' => 1,
            );

            $this->tableGateway->update($data, array('id' => $id));
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getSalesPersonReport($parameter)
    {
        $page = isset($parameter['page']) ? $parameter['page'] : NULL;
        $limit = isset($parameter['limit']) ? $parameter['limit'] : 20;

        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();

            $select = $sql->select();
            $select->from(array('su' => $table));
            //$select->join(array('s' => 'store'), 'su.store_id = s.id', array(), Select::JOIN_LEFT);
            $select->join(array('a' => 'account'), 'su.account_id = a.id', array('username', 'display_name'), 'left');
            $select->columns(array('num_of_survey' => new Expression('SUM(IF(su.id,1,0))'), 'num_of_store_signup' =>  new Expression('SUM(IF(su.store_id,1,0))'), 'date' => new Expression('DATE(su.created_at)')));
            
            $select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));

            $where = new Where();
            $where->equalTo('su.account_id', $parameter['id']);
            $where->equalTo('su.is_deleted', 0);
            //$where->notEqualTo('s.signup_time', 0);
            if (isset($parameter['start_date'])) {
                $where->addPredicate(new Expression('DATE(su.created_at) >= "' . $parameter['start_date'] . '"'));
            }
            if (isset($parameter['end_date'])) {
                $where->addPredicate(new Expression('DATE(su.created_at) <= "' . $parameter['end_date'] . '"'));
            }
            $select->where($where);
            $select->order('su.created_at DESC');
            $select->group(new Expression('DATE(su.created_at)'));

            if(isset($page)) {
                $select->limit(intval($limit));
                $select->offset((intval($page) - 1) * intval($limit));
            }

            $result = $this->executeQuery($select);

            return $this->getResultArray($result, $page, $limit);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getSalesPersonDetailReport($parameter)
    {
        $page = isset($parameter['page']) ? $parameter['page'] : NULL;
        $limit = isset($parameter['limit']) ? $parameter['limit'] : 20;

        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();

            $select = $sql->select();
            $select->from(array('su' => $table));
            $select->join(array('s' => 'store'), 'su.store_id = s.id', array('store_name' => 'name', 'store_phone' => 'contact_no', 'signup_time', 'first_loggedin_time'), 'left');
            $select->join(array('a' => 'account'), 'su.account_id = a.id', array('username', 'display_name'), 'left');
            $select->columns(array('survey_name' => 'name', 'contact_no', 'address', 'created_at'));
            
            $select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));

            $where = new Where();
            $where->equalTo('su.account_id', $parameter['id']);
            $where->equalTo('su.is_deleted', 0);
            //$where->notEqualTo('s.signup_time', 0);
            if (isset($parameter['start_date'])) {
                $where->addPredicate(new Expression('DATE(su.created_at) >= "' . $parameter['start_date'] . '"'));
            }
            if (isset($parameter['end_date'])) {
                $where->addPredicate(new Expression('DATE(su.created_at) <= "' . $parameter['end_date'] . '"'));
            }
            $select->where($where);
            $select->order('su.created_at DESC');

            if(isset($page)) {
                $select->limit(intval($limit));
                $select->offset((intval($page) - 1) * intval($limit));
            }

            $result = $this->executeQuery($select);

            return $this->getResultArray($result, $page, $limit);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

<?php

namespace Base\Table;

use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Log\Logger;

class BaseTable
{

    protected $tableGateway;
    protected $logger;
    protected $sql;

    public function __construct(TableGateway $tableGateway, Logger $logger)
    {
        $this->tableGateway = $tableGateway;
        $this->logger = $logger;
    }

    protected function getSql()
    {
        try {
            $dbAdapter = $this->tableGateway->adapter;
            $sql = new Sql($dbAdapter);
            return $sql;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    protected function formatDBRecord($row)
    {
        if (is_array($row)) {
            foreach ($row as $key => $value) {
                $row[$key] = html_entity_decode($value);
            }
        }
        return $row;
    }

    protected function getFoundRows()
    {
        $sql = $this->getSql();
        $query = $sql->select()
                ->columns(array(
            'total' => new Expression("FOUND_ROWS()")
        ));

        /* execute the select and extract the total */
        $result = $this->executeQuery($query);
        if ($result) {
            $row = $result->current();
            return $row['total'];
        } else {
            return 0;
        }
    }

    protected function getModelForAdd($model, $data)
    {        
        $modelVariables = $model->getArrayCopy();
        foreach ($modelVariables as $key => $value) {
            if (isset($data[$key])) {
                $model->$key = $data[$key];
            } else {
                unset($model->$key);
            }
        }
    }

    protected function addModel($model)
    {
        try {
            $isValid = $model->isValid();
            if ($isValid === true) {
                $data = $model->getArrayCopy();
                $data['created_at'] = new Expression('UTC_TIMESTAMP()');
                $data['updated_at'] = new Expression('UTC_TIMESTAMP()');
                
                $status = $this->tableGateway->insert($data);
                $id = $this->tableGateway->lastInsertValue;
                if ($status) {
                    return $id;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {            
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    protected function updateModel($model, $whereArray = array())
    {
        try {
            $isValid = $model->isValid();
            if ($isValid === true) {
                $data = $model->getArrayCopy();
                $data['updated_at'] = new Expression('UTC_TIMESTAMP()');
                $result = $this->tableGateway->update($data, $whereArray);
                if ($result) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    protected function executeRawQuery($sql)
    {
        $statement = $this->tableGateway->adapter->query($sql);
        return $statement->execute();
    }

    protected function created($var)
    {
        $var->created_at = new Expression('NOW()');
        $var->updated_at = new Expression('NOW()');
        return true;
    }

    protected function updated($var)
    {
        $var->updated_at = new Expression('NOW()');
        return true;
    }

    protected function logException(\Exception $e)
    {
        if ($e instanceof \Zend\Db\Adapter\ExceptionInterface) {
            throw new DatabaseException();
        }
        $this->logger->crit("Database Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    }

    protected function getArrayFromResultSet($resultSet)
    {
        $result = array();
        foreach ($resultSet as $projectRow) {
            $result[] = $projectRow;
        }

        return $result;
    }

    protected function hydrateObject(&$resultSet, $columns, $objectName)
    {
        // IMPROVEMENT: Hydrate to actual objects
        if (!$resultSet) {
            return;
        }

        $resultSet[$objectName] = array();
        foreach ($columns as $key => $value) {

            // maintain actual field name in result set
            $fieldName = $value;
            if (is_string($key)) {
                $fieldName = $key;
            }

            if (!array_key_exists($fieldName, $resultSet)) {
                continue;
            }

            // use value in object which hydrates to the real name
            $resultSet[$objectName][$value] = $resultSet[$fieldName];
            unset($resultSet[$fieldName]);
        }
    }

    public function insertOrUpdate(array $insertData, array $updateData)
    {
        $sqlStringTemplate = 'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s';
        $adapter = $this->tableGateway->adapter; /* Get adapter from tableGateway */
        $driver = $adapter->getDriver();
        $platform = $adapter->getPlatform();

        $tableName = $platform->quoteIdentifier($this->tableGateway->getTable());
        $parameterContainer = new ParameterContainer();
        $statementContainer = $adapter->createStatement();
        $statementContainer->setParameterContainer($parameterContainer);

        // Preparation insert data
        $insertQuotedValue = [];
        $insertQuotedColumns = [];
        foreach ($insertData as $column => $value) {
            $insertQuotedValue[] = $driver->formatParameterName($column);
            $insertQuotedColumns[] = $platform->quoteIdentifier($column);
            $parameterContainer->offsetSet($column, $value);
        }

        // Preparation update data
        $updateQuotedValue = [];
        foreach ($updateData as $column => $value) {
            $updateQuotedValue[] = $platform->quoteIdentifier($column) . '=' . $driver->formatParameterName('update_' . $column);
            $parameterContainer->offsetSet('update_' . $column, $value);
        }

        // Preparation sql query
        $query = sprintf(
                $sqlStringTemplate, $tableName, implode(',', $insertQuotedColumns), implode(',', array_values($insertQuotedValue)), implode(',', $updateQuotedValue)
        );

        $statementContainer->setSql($query);
        return $statementContainer->execute();
    }

    public function getByField($whereArray, $asArray = false)
    {
        try {
            $result = $this->tableGateway->select($whereArray);

            $returnArray = array();
            foreach ($result as $projectRow) {
                $returnArray[] = $this->formatDBRecord($projectRow->getArrayCopy());
            }
            if (!$asArray) {
                if (count($returnArray) == 1) {
                    return $returnArray[0];
                } else if (count($returnArray) == 0) {
                    return false;
                }
            }

            return $returnArray;
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    public function beginTransaction()
    {
        $this->tableGateway->getAdapter()->getDriver()->getConnection()->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->tableGateway->getAdapter()->getDriver()->getConnection()->commit();
    }

    public function rollbackTransaction()
    {
        $this->tableGateway->getAdapter()->getDriver()->getConnection()->rollback();
    }

    public function getList($opreration)
    {
        try {
            $table = $this->tableGateway->getTable();

            $sql = $this->getSql();
            $sqlSelect = $sql->select();
            $sqlSelect->from(
                    $table
            );
            //where 
            if (isset($opreration['where'])) {
                $sqlSelect->where($opreration['where']);
            }
            //joins
            if (isset($opreration['join'])) {
                foreach ($opreration['join'] as $join) {
                    $sqlSelect->join(
                            $join['table'], $join['condition'], $join['columns'], $join['type']
                    );
                }
            }

            //columns
            if (isset($opreration['column'])) {
                $sqlSelect->columns($opreration['column']);
            }

            // for row count
            $sqlSelect->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));

            //order by
            if (isset($opreration['order_by'])) {
                $sqlSelect->order($opreration['order_by']);
            }

            //group by
            if (isset($opreration['group_by'])) {
                $sqlSelect->group($opreration['group_by']);
            }

            //having
            if (isset($opreration['having'])) {
                $sqlSelect->having($opreration['having']);
            }
            
            //pagination
            if (!isset($opreration['first_row']) && isset($opreration['page'], $opreration['limit'])) {
                $sqlSelect->limit(intval($opreration['limit']));
                $sqlSelect->offset((intval($opreration['page']) - 1) * intval($opreration['limit']));
            }

            //echo $sql->getSqlStringForSqlObject($sqlSelect);exit;
            $result = $this->executeQuery($sqlSelect);
            if (isset($opreration['first_row']) && $opreration['first_row']) {
                $returnArray = $result->current();
            } else {
                $returnData = array();
                $returnData['totalCount'] = $this->getFoundRows();
                $returnData['list'] = array();
                foreach ($result as $projectRow) {
                    $returnData['list'][] = $result->current();
                }
                if (isset($opreration['page'], $opreration['limit'])) {
                    $returnData['page'] = $opreration['page'];
                    $returnData['count_per_page'] = $opreration['limit'];
                }
                $returnArray = $returnData;
            }
            return $returnArray;
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    /*
     * execute query
     */
    protected function executeQuery($query)
    {
        try {
            $sql = $this->getSql();
           // echo $sql->getSqlStringForSqlObject($query);exit;
            $stmt = $sql->prepareStatementForSqlObject($query);
            return $stmt->execute();
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    /*
     * result array
     */
    protected function getResultArray($result, $page = null, $limit = null)
    {
        $data = array();
        try {
            $data['totalCount'] = $this->getFoundRows();
            $data['list'] = [];
            foreach ($result as $row) {
                $data['list'][] = $result->current();
            }
            if(isset($page,$limit)) {
                $data['page'] = $page;
                $data['count_per_page'] = $limit;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
}

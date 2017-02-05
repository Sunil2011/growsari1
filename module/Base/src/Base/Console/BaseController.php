<?php

namespace Base\Console;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Mvc\Controller\AbstractActionController;

class BaseController extends AbstractActionController
{

    protected $adapter;
    protected $connection;

    protected function executeSql($sql, $params = null)
    {
        try {
            $statement = $this->getAdapter()->query($sql);
            if ($params) {
                $statement->execute($params);
            } else {
                $statement->execute();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function executeDDL($sql)
    {
        try {
            $this->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
            $this->connection = $this->adapter->getDriver()->getConnection();
        }

        return $this->adapter;
    }

    public function camelize($string, $capitalizeFirstCharacter = true)
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    protected function insert($tableName, array $insertData)
    {
        $sqlStringTemplate = 'INSERT INTO %s (%s) VALUES (%s)';
        $adapter = $this->getAdapter(); /* Get adapter from tableGateway */
        $driver = $adapter->getDriver();
        $platform = $adapter->getPlatform();

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

        // Preparation sql query
        $query = sprintf(
                $sqlStringTemplate, $tableName, implode(',', $insertQuotedColumns), implode(',', array_values($insertQuotedValue))
        );

        try {
            $result = null;
            $statementContainer->setSql($query);
            $result = $statementContainer->execute();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $result;
    }

    protected function update($tableName, $identifier, array $updateData)
    {
        $sqlStringTemplate = 'UPDATE %s SET %s WHERE %s';
        $adapter = $this->getAdapter(); /* Get adapter from tableGateway */
        $driver = $adapter->getDriver();
        $platform = $adapter->getPlatform();

        $parameterContainer = new ParameterContainer();
        $statementContainer = $adapter->createStatement();
        $statementContainer->setParameterContainer($parameterContainer);

        // Preparation update data
        $updateQuotedValue = [];
        foreach ($updateData as $column => $value) {
            $updateQuotedValue[] = $platform->quoteIdentifier($column) . '=' . $driver->formatParameterName('update_' . $column);

            $parameterContainer->offsetSet('update_' . $column, $value);
        }

        //where
        $updateWhere = $platform->quoteIdentifier('id') . '=' . $driver->formatParameterName('update_identifier');
        $parameterContainer->offsetSet('update_identifier', $identifier);

        // Preparation sql query
        $query = sprintf(
                $sqlStringTemplate, $tableName, implode(',', $updateQuotedValue), $updateWhere
        );

        try {
            $result = null;
            $statementContainer->setSql($query);
            $result = $statementContainer->execute();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $result;
    }

    protected function insertOrUpdate($tableName, array $insertData, array $updateData)
    {
        $sqlStringTemplate = 'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), %s';
        $adapter = $this->getAdapter(); /* Get adapter from tableGateway */
        $driver = $adapter->getDriver();
        $platform = $adapter->getPlatform();

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

        try {
            $result = null;
            $statementContainer->setSql($query);
            $result = $statementContainer->execute();
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            exit;
        }


        return $result;
    }

}

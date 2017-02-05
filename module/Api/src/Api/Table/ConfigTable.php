<?php

namespace Api\Table;

use Base\Table\BaseTable;
use Zend\Db\Sql\Where as Where;
use Api\Model\Config;

class ConfigTable extends BaseTable
{
    public function getAdminConfigList()
    {
        try {
            $operation = array();
            return $this->getList($operation);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function getConfigList()
    {
        try {
            $configList = $this->getList(array());
            $config = array();
            foreach ($configList['list'] as $row) {
                $config[$row['field']] = $row['value'];
            }
            
            return $config;
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return array();
        
    }


    public function getConfigDetails($configId)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('config.id', $configId);
            $operation['where'] = $where;
            
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    public function addConfig($parameter)
    {
        try {
            $configModel = new Config();
            $this->getModelForAdd($configModel, $parameter);
            return $this->addModel($configModel);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateConfig($parameter, $whereArray)
    {

        try {
            $configModel = new Config();
            $this->getModelForAdd($configModel, $parameter);
            return $this->updateModel($configModel, $whereArray);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

<?php

namespace Api\Table;

use Api\Model\GlobeSms;
use Base\Table\BaseTable;
use Exception;

class GlobeSmsTable extends BaseTable
{

    public function addGlobeSms($parameter)
    {
        try {
            $orderModel = new GlobeSms();
            $this->getModelForAdd($orderModel, $parameter);
            return $this->addModel($orderModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
    
    public function updateGlobeSms($parameter ,$whereArray){
        
        try {
            $ProductModel = new GlobeSms();
            $this->getModelForAdd($ProductModel, $parameter);
            return $this->updateModel($ProductModel, $whereArray);
        } catch (\Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
        
    }

}

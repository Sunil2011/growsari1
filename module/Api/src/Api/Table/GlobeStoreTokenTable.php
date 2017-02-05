<?php

namespace Api\Table;

use Api\Model\GlobeStoreToken;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Where;

class GlobeStoreTokenTable extends BaseTable
{

    public function addGlobeStoreToken($parameter)
    {
        try {
            $orderModel = new GlobeStoreToken();
            $this->getModelForAdd($orderModel, $parameter);
            return $this->addModel($orderModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getAccessToken($number)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('globe_store_token.subscriber_number', $number);
            $operation['where'] = $where;

            $operation['order_by'] = 'globe_store_token.id DESC';

            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

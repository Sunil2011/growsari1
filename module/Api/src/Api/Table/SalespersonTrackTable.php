<?php

namespace Api\Table;

use Api\Model\SalespersonTrack;
use Base\Table\BaseTable;
use Exception;

class SalespersonTrackTable extends BaseTable
{

    public function addSalespersonTrack($parameter)
    {
        try {
            $orderModel = new SalespersonTrack();
            $this->getModelForAdd($orderModel, $parameter);
            return $this->addModel($orderModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

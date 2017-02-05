<?php

namespace Api\Table;

use Api\Model\OrderReturnedItem;
use Base\Table\BaseTable;
use Exception;

class OrderReturnedItemTable extends BaseTable
{

    public function addReturnedItem($parameter)
    {
        try {
            $OrderItemModel = new OrderReturnedItem();
            $this->getModelForAdd($OrderItemModel, $parameter);
            return $this->addModel($OrderItemModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

}

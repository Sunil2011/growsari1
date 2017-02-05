<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Table;

use Api\Model\Loan;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Predicate\Expression;

class LoanTable extends BaseTable
{
    CONST STATUS_CLR = "CLEARED";
    CONST STATUS_PEN = "PAYMENT_PENDING";

    public function addLoan($parameter)
    {
        try {
            $loanModel = new Loan();
            $this->getModelForAdd($loanModel, $parameter);
            return $this->addModel($loanModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
}

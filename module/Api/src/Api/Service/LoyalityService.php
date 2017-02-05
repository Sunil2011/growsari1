<?php

namespace Api\Service;

use Api\Exception\ApiException;
use Api\Service\BaseService;
use Api\Table\LoyaltyPointTable;
use Api\Table\LoanTable;

class LoyalityService extends BaseService
{

    public function debitForOrder($orderId, $usePoints, $remark = LoyaltyPointTable::REMARK_DEBIT_ORDER)
    {
        // account id
        $pointsObj = $this->getUserPointsByOrderId($orderId);
        if ($pointsObj['points'] < $usePoints) {
            throw new ApiException("You don't have enough points to use!", 403);
        }

        // TODO: check any debit happend before for this order
        // based on how many points he wants to use.. deduct that much right away
        $loyaltyTable = $this->getTable('LoyaltyPoint');
        $res = $loyaltyTable->addPoints(array(
            'account_id' => $pointsObj['account_id'],
            'order_id' => $orderId,
            'debit' => (int) $usePoints,
            'remarks' => $remark
        ));

        if ($res === false) {
            throw new ApiException("Unable to debit points, please try again!", 500);
        }

        return true;
    }

    public function creditForOrder($orderId, $amountCollected)
    {
        // get order details
        $orderTable = $this->getTable('Order');
        $this->orderDetail = $orderTable->getByField(array('id' => $orderId));
        if (!$this->orderDetail) {
            throw new ApiException('Order not found!', 404);
        }

        // TODO: check any credit happend before for this order
        // update loyalty points if used in orders
        $loyaltyEarned = $this->calLoyaltyPoints($amountCollected);
        $loyaltyEarned = $this->applyPromotions($loyaltyEarned);

        // credit into his account
        if ($loyaltyEarned) {
            $pointsObj = $this->getUserPointsByOrderId($orderId);
            $loyaltyTable = $this->getTable('LoyaltyPoint');
            $res = $loyaltyTable->addPoints(array(
                'account_id' => $pointsObj['account_id'],
                'order_id' => $orderId,
                'credit' => (int) $loyaltyEarned,
                'remarks' => LoyaltyPointTable::REMARK_CREDIT_ORDER
            ));

            if (!$res) {
                throw new ApiException("Unable to credit points, please try again!", 500);
            }
        }

        return $loyaltyEarned;
    }

    public function returnForOrder($orderDetail, $pointsAdd)
    {
        // account id
        $pointsObj = $this->getUserPointsByOrderId($orderDetail['id']);

        // credit into his account
        $loyaltyTable = $this->getTable('LoyaltyPoint');
        $res = $loyaltyTable->addPoints(array(
            'account_id' => $pointsObj['account_id'],
            'order_id' => $orderDetail['id'],
            'credit' => (int) $pointsAdd,
            'remarks' => LoyaltyPointTable::REMARK_CREDIT_ORDER_EXTRA
        ));

        if ($res === false) {
            throw new ApiException("Unable to refund points, please try again!", 500);
        }

        return true;
    }

    public function returnForCancelOrder($orderDetail)
    {
        // account id
        $pointsObj = $this->getUserPointsByOrderId($orderDetail['id']);

        // credit into his account
        if ($orderDetail['loyalty_points_used']) {
            $loyaltyTable = $this->getTable('LoyaltyPoint');
            $res = $loyaltyTable->addPoints(array(
                'account_id' => $pointsObj['account_id'],
                'order_id' => $orderDetail['id'],
                'credit' => (int) $orderDetail['loyalty_points_used'],
                'remarks' => LoyaltyPointTable::REMARK_CREDIT_ORDER_CANCEL
            ));
        
            if ($res === false) {
                throw new ApiException("Unable to refund points, please try again!", 500);
            }
        }

        return true;
    }
    
    public function creditForReferal($accountId, $storeReferId, $loyaltyEarned)
    {
        $loyaltyTable = $this->getTable('LoyaltyPoint');
        $res = $loyaltyTable->addPoints(array(
            'account_id' => $accountId,
            'store_refer_id' => $storeReferId,
            'credit' => (int) $loyaltyEarned,
            'remarks' => LoyaltyPointTable::REMARK_CREDIT_REFERAL
        ));

        if (!$res) {
            throw new ApiException("Unable to credit points, please try again!", 500);
        }

        return true;
    }
    
    public function signUpCreditsExpired($accountId, $points)
    {
        $loyaltyTable = $this->getTable('LoyaltyPoint');
        $res = $loyaltyTable->addPoints(array(
            'account_id' => $accountId,
            'debit' => (int) $points,
            'remarks' => LoyaltyPointTable::REMARK_DEBIT_SIGNUP_CANCEL
        ));

        if (!$res) {
            throw new ApiException("Unable to debit points, please try again!", 500);
        }

        return true;
    }
    
    public function addMoneyToStore($accountId, $amount, $remark)
    {
        $loyaltyTable = $this->getTable('LoyaltyPoint');
        $res = $loyaltyTable->addPoints(array(
            'account_id' => $accountId,
            'credit' => (int) $amount,
            'remarks' => $remark
        ));

        if ($res === false) {
            throw new ApiException("Unable to debit points, please try again!", 500);
        }

        return true;
    }
    
    public function addMoneyAsLoanToStore($accountId, $amount, $remark)
    {
        $loanParam = array(
            'account_id' => $accountId,
            'amount' => $amount,
            'status' => LoanTable::STATUS_PEN,
            'remarks' => $remark
        );
        $loanTable = $this->getTable('Loan');
        $res = $loanTable->addLoan($loanParam);
        if ($res === false) {
            throw new ApiException('Unable to add loan.', 500);
        }

        return true;
    }

    private function calLoyaltyPoints($amountCollected)
    {
        $config = $this->sm->get('config');
        $loyaltyRate = $config['app_settings']['loyalty_percent'];

        $amount = $amountCollected;
        $loyaltyEarned = intval(($amount * $loyaltyRate) / 100);

        return $loyaltyEarned;
    }

    private function getUserPointsByOrderId($orderId)
    {
        $loyalityPointTable = $this->getTable('LoyaltyPoint');
        $pointsObj = $loyalityPointTable->getUserPointsByOrderId($orderId);
        if ($pointsObj === false) {
            throw new ApiException("You don't have points!", 400);
        }

        return $pointsObj;
    }

    private function applyPromotions($loyaltyEarned)
    {
        $date = date("Y-m-d", strtotime($this->orderDetail['created_at']));
        if (in_array($date, array(
            '2016-08-12',
            '2016-08-13',
            '2016-08-14',
        ))) {
            return 2 * $loyaltyEarned;
        }

        return $loyaltyEarned;
    }
    
}

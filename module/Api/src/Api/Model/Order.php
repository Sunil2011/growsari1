<?php

namespace Api\Model;

use Base\Model\BaseModel;

class Order extends BaseModel
{
    public $id;
    public $associate_id;
    public $shipper_team_id;
    public $amount;
    public $discount;
    public $delivery_charges;
    public $initial_order_value;
    public $net_amount;
    public $amount_collected;
    public $returned_item_amount;
    public $loyalty_points_used;
    public $loyalty_points_earn;
    public $delivered_by;
    public $no_of_boxes;
    public $promo_id;
    public $is_saved;
    public $feedback_given;
    public $is_returned;
    public $sms_sender;
    public $is_added_by_cc;
    public $created_at;
    public $updated_at;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getAssociateId()
    {
        return $this->associate_id;
    }

    public function setAssociateId($associate_id)
    {
        $this->associate_id = $associate_id;
    }

    public function getShipperTeamId()
    {
        return $this->shipper_team_id;
    }

    public function setShipperTeamId($shipper_team_id)
    {
        $this->shipper_team_id = $shipper_team_id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    public function getDeliveryCharges()
    {
        return $this->delivery_charges;
    }

    public function setDeliveryCharges($delivery_charges)
    {
        $this->delivery_charges = $delivery_charges;
    }

    public function getInitialOrderValue()
    {
        return $this->initial_order_value;
    }

    public function setInitialOrderValue($initial_order_value)
    {
        $this->initial_order_value = $initial_order_value;
    }

    public function getNetAmount()
    {
        return $this->net_amount;
    }

    public function setNetAmount($net_amount)
    {
        $this->net_amount = $net_amount;
    }

    public function getAmountCollected()
    {
        return $this->amount_collected;
    }

    public function setAmountCollected($amount_collected)
    {
        $this->amount_collected = $amount_collected;
    }

    public function getReturnedItemAmount()
    {
        return $this->returned_item_amount;
    }

    public function setReturnedItemAmount($returned_item_amount)
    {
        $this->returned_item_amount = $returned_item_amount;
    }

    public function getLoyaltyPointsUsed()
    {
        return $this->loyalty_points_used;
    }

    public function setLoyaltyPointsUsed($loyalty_points_used)
    {
        $this->loyalty_points_used = $loyalty_points_used;
    }

    public function getLoyaltyPointsEarn()
    {
        return $this->loyalty_points_earn;
    }

    public function setLoyaltyPointsEarn($loyalty_points_earn)
    {
        $this->loyalty_points_earn = $loyalty_points_earn;
    }

    public function getDeliveredBy()
    {
        return $this->delivered_by;
    }

    public function setDeliveredBy($delivered_by)
    {
        $this->delivered_by = $delivered_by;
    }

    public function getNoOfBoxes()
    {
        return $this->no_of_boxes;
    }

    public function setNoOfBoxes($no_of_boxes)
    {
        $this->no_of_boxes = $no_of_boxes;
    }

    public function getPromoId()
    {
        return $this->promo_id;
    }

    public function setPromoId($promo_id)
    {
        $this->promo_id = $promo_id;
    }

    public function getIsSaved()
    {
        return $this->is_saved;
    }

    public function setIsSaved($is_saved)
    {
        $this->is_saved = $is_saved;
    }

    public function getFeedbackGiven()
    {
        return $this->feedback_given;
    }

    public function setFeedbackGiven($feedback_given)
    {
        $this->feedback_given = $feedback_given;
    }

    public function getIsReturned()
    {
        return $this->is_returned;
    }

    public function setIsReturned($is_returned)
    {
        $this->is_returned = $is_returned;
    }

    public function getSmsSender()
    {
        return $this->sms_sender;
    }

    public function setSmsSender($sms_sender)
    {
        $this->sms_sender = $sms_sender;
    }

    public function getIsAddedByCc()
    {
        return $this->is_added_by_cc;
    }

    public function setIsAddedByCc($is_added_by_cc)
    {
        $this->is_added_by_cc = $is_added_by_cc;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }
}

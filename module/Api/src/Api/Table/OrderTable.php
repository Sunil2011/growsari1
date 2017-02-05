<?php

namespace Api\Table;

use Api\Model\Order;
use Base\Table\BaseTable;
use Exception;
use Zend\Db\Sql\Expression as Expression;
use Zend\Db\Sql\Predicate\Expression as Expression2;
use Zend\Db\Sql\Predicate\Expression as Predicate_Expression;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where as Where;

class OrderTable extends BaseTable
{

    public function addOrder($parameter)
    {
        try {
            $orderModel = new Order();
            $this->getModelForAdd($orderModel, $parameter);
            return $this->addModel($orderModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateOrder($parameter, $where)
    {
        try {
            $OrderModel = new Order();
            $this->getModelForAdd($OrderModel, $parameter);
            return $this->updateModel($OrderModel, $where);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getOrderList($parameter)
    {
        $page = isset($parameter['page']) ? $parameter['page'] : 1;
        $limit = isset($parameter['limit']) ? $parameter['limit'] : 10;
        $sortBy = isset($parameter['sort_by']) && $parameter['sort_by'] == 'delivery_at' ? 'order_task_id DESC, o.delivered_by DESC': 'order_task_id DESC, o.created_at DESC';

        try {

            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('o' => $table))
                    ->columns(array(
                        '*',
                        'current_status' => new Expression('(SELECT status FROM order_status AS os WHERE os.order_id = o.id ORDER BY os.created_at DESC LIMIT 1)')
            ));

            $select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));

            $select->join(array('sws' => 'store_warehouse_shipper'), 'o.associate_id = sws.id', array('store_id'), Select::JOIN_LEFT)
                    ->join(array('s' => 'store'), 'sws.store_id = s.id', array('store_name' => 'name', 'store_address' => 'address', 'store_point_x' => 'point_x', 'store_point_y' => 'point_y', 'store_contact_no' => 'contact_no', 'store_photo' => 'photo'), Select::JOIN_LEFT)
                    ->join(array('sa' => 'account'), 'sa.id = s.account_id', array('store_username' => 'username'), Select::JOIN_LEFT)
                    ->join(array('of' => 'order_feedback'), 'o.id = of.order_id', array('feedback_rating' => 'rating', 'feedback_remarks' => 'remarks', 'feedback_experience' => 'experience'), Select::JOIN_LEFT)
                    ->join(array('oi' => 'order_item'), new Expression('o.id = oi.order_id and oi.is_deleted = 0'), array('total_item' => new Expression('SUM(IF(oi.is_available,oi.is_available,0))'), 'is_modified_num' => new Expression('SUM(IF(oi.is_modified,oi.is_modified,0))'), 'items_string' => new Expression('GROUP_CONCAT(CONCAT(oi.product_id,":",oi.quantity) SEPARATOR ";")')), Select::JOIN_LEFT)
                    ->join(array('st' => 'shipper_team'), 'o.shipper_team_id = st.id', array(), Select::JOIN_LEFT)
                    ->join(array('sta' => 'account'), 'sta.id = st.account_id', array('shipper_team_name' => 'display_name'), Select::JOIN_LEFT)
                    ->join(array('ot1' => 'order_task'), new Expression('ot1.order_id = o.id AND ot1.is_finished=0'), array('order_task_id' => 'id'), Select::JOIN_LEFT)
                    ->join(array('ot2' => 'order_task'), 'ot2.order_id = o.id AND ot1.id < ot2.id', array(), Select::JOIN_LEFT)
                    ->join(array('ot3' => 'order_task'), 'ot3.order_id = o.id', array(), Select::JOIN_LEFT);

            $where = new Where();
            $where->isNull('ot2.id');
            $where->equalTo('s.is_deleted', 0);
            if (isset($parameter['is_saved']) && $parameter['is_saved']) {
                $where->equalTo('is_saved', 1);
            }
            if (isset($parameter['associate_id'])) {
                $where->equalTo('associate_id', $parameter['associate_id']);
            }
            if (isset($parameter['is_replace_task']) && $parameter['is_replace_task']) {
                $where->isNotNull('ot3.id');
                $sortBy = 'order_task_id ASC, o.delivered_by DESC';
            }
            if (isset($parameter['search'])) {
                $where->nest()
                        ->like('o.id', $parameter['search'])
                        ->OR
                        ->like('s.name', '%' . $parameter['search'] . '%')
                        ->OR
                        ->like('sa.username', '%' . $parameter['search'] . '%')
                        ->OR
                        ->like('s.contact_no', '%' . $parameter['search'] . '%')
                        ->unnest();
            }

            if (isset($parameter['delivery_date']) && $parameter['delivery_date']) {
                $time = strtotime($parameter['delivery_date']);
                $newformat = date('Y-m-d', $time);
                $where->equalTo('o.delivered_by', $newformat);
            }

            // send only assigned orders to shipper user
            if (isset($parameter['shipper_team_account_id'])) {
                $where->equalTo('sta.id', $parameter['shipper_team_account_id']);
            }

            $select->order($sortBy);
            $select->group('o.id');

            // check the status
            if (isset($parameter['status']) && $parameter['status']) {
                $statusArray = explode(',', $parameter['status']);
                $select->having(new In('current_status', $statusArray));
            }

            // for assigning shipper to order
            if (isset($parameter['assign_shipper'])) {
                $select->having(new In('current_status', array(
                    'confirmed',
                    'ready_to_pack',
                    'packed'
                        )
                ));
            }

            $select->where($where);

            //page
            $select->limit(intval($limit));
            $select->offset((intval($page) - 1) * intval($limit));

            $result = $this->executeQuery($select);
            return $this->getResultArray($result, $page, $limit);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getOrderDetails($orderId, $param)
    {
        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('order.id', $orderId);
            if (isset($param['associate_id'])) {
                $where->equalTo('order.associate_id', $param['associate_id']);
            }
            $operation['where'] = $where;

            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'store_warehouse_shipper',
                'condition' => 'order.associate_id = store_warehouse_shipper.id',
                'columns' => array('store_id'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'warehouse_shipper',
                'condition' => 'store_warehouse_shipper.warehouse_shipper_id = warehouse_shipper.id',
                'columns' => array('warehouse_id', 'shipper_id'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'store',
                'condition' => 'store_warehouse_shipper.store_id = store.id',
                'columns' => array('account_id', 'name', 'address', 'locality', 'contact_no', 'store_name' => 'name', 'store_address' => 'address', 'store_point_x' => 'point_x', 'store_point_y' => 'point_y', 'store_contact_no' => 'contact_no', 'store_photo' => 'photo'),
                'type' => 'left'
            );
            $joinParameters[] = array(
                'table' => 'order_feedback',
                'condition' => 'order.id = order_feedback.order_id',
                'columns' => array('feedback_rating' => 'rating', 'feedback_remarks' => 'remarks', 'feedback_experience' => 'experience'),
                'type' => 'left'
            );

            $joinParameters[] = array(
                'table' => 'order_status',
                'condition' => 'order.id = order_status.order_id',
                'columns' => array('status', 'reason'),
                'type' => 'left'
            );
            $operation['join'] = $joinParameters;

            $operation['order_by'] = 'order_status.created_at DESC';
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getStoreUserId($orderId)
    {

        try {

            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('o' => $table))
                    ->columns(array());

            $select->join(
                            array('sws' => 'store_warehouse_shipper'), 'o.associate_id = sws.id', array('store_id'), Select::JOIN_LEFT
                    )
                    ->join(
                            array('s' => 'store'), 'sws.store_id = s.id', array('store_name' => 'name', 'account_id'), Select::JOIN_LEFT
                    )
                    ->join(
                            array('asa' => 'account_device'), 's.account_id = asa.account_id', array('device_token'), Select::JOIN_LEFT
            );

            $where = new Where();
            $where->equalTo('o.id', $orderId);
            $select->where($where);

            return $this->executeQuery($select)->current();
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getStoreOrderFeedbackPending($storeId)
    {
        try {

            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('o' => $table))
                    ->columns(array(
                        '*',
                        'current_status' => new Expression('(SELECT status FROM order_status AS os WHERE os.order_id = o.id ORDER BY os.created_at DESC LIMIT 1)')
            ));

            $select->join(
                    array('sws' => 'store_warehouse_shipper'), 'o.associate_id = sws.id', array('store_id'), Select::JOIN_LEFT
            );

            $select->join(
                    array('s' => 'store'), 'sws.store_id = s.id', array('store_name' => 'name', 'account_id'), Select::JOIN_LEFT
            );

            $where = new Where();
            $where->equalTo('sws.store_id', $storeId);
            $where->equalTo('o.feedback_given', 0);
            $select->where($where);

            $select->order('o.created_at DESC');
            $select->group('o.id');

            $select->having(new Predicate_Expression('current_status = "' . OrderStatusTable::DELIVERED . '"'));


            $stmt = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();

            return $this->getArrayFromResultSet($result);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return array();
    }

    public function getCounts()
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();

            $subQuery = $sql->select();
            $subQuery->from(array('o' => $table))
                    ->columns(array(
                        'id',
                        'current_status' => new Expression('(SELECT status FROM order_status AS os WHERE os.order_id = o.id ORDER BY os.created_at DESC LIMIT 1)'),
                    ))
                    ->group('o.id');


            $select = $sql->select();
            $select->from(array('o' => $table))
                    ->columns(array());

            $select->join(array('of' => $subQuery), 'o.id = of.id', array('new_orders_count' => new Expression('SUM(IF(of.current_status = "pending",1,null))'),
                'confirmed_orders_count' => new Expression('SUM(IF(of.current_status = "confirmed",1,null))'),
                'readytopack_orders_count' => new Expression('SUM(IF(of.current_status = "ready_to_pack",1,null))'),
                'pickup_orders_count' => new Expression('SUM(IF(of.current_status = "packed",1,null))'),
                'dispatched_orders_count' => new Expression('SUM(IF(of.current_status = "dispatched",1,null))'),
                    ), 'left'
            )
            ->join(array('ot' => 'order_task'), new Expression('ot.order_id = of.id AND of.current_status = "pending" AND ot.is_finished = 1'), array('replacement_confirmed_count' => new Expression('SUM(IF(ot.id,1,0))')));

            return $this->executeQuery($select)->current();
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getAssignShipperCounts()
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();

            $select = $sql->select();
            $select->from(array('o2' => $table))
                    ->columns(array(
                        'assign_shipper_count' => new Expression('COUNT(*)'),
            ));

            $subQuery = $sql->select();
            $subQuery->from(array('o' => $table))
                    ->columns(array(
                        'id',
                        'current_status' => new Expression('(SELECT status FROM order_status AS os WHERE os.order_id = o.id ORDER BY os.created_at DESC LIMIT 1)'),
                    ))
                    ->group('o.id');

            $select->join(array('of' => $subQuery), 'o2.id = of.id', array(), 'left');

            $where = new Where();
            $where->nest->isNull('shipper_team_id')
                            ->or
                            ->equalTo('shipper_team_id', 0)
                    ->unnest;
            $where->in('current_status', array(
                'confirmed',
                'ready_to_pack',
                'packed'
                    )
            );

            $where->addPredicate(new Expression2('DATE(delivered_by) IN (CURDATE(), CURDATE() + INTERVAL 1 DAY)'));
            $select->where($where);

            $row = $this->executeQuery($select)->current();
            return isset($row['assign_shipper_count']) ? $row['assign_shipper_count'] : 0;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getOrdersMissingLoyaltyCredits($orderId = null)
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('o' => $table))
                    ->columns(array(
                        'id',
                        'net_amount',
                        'amount_collected', 'loyalty_points_used', 'loyalty_points_earn'
            ));

            $select->join(array('sws' => 'store_warehouse_shipper'), 'o.associate_id = sws.id', array('store_id'))
                    ->join(array('s' => 'store'), 'sws.store_id = s.id', array())
                    ->join(array('sa' => 'account'), 'sa.id = s.account_id', array('username'))
                    ->join(array('os1' => 'order_status'), 'os1.order_id = o.id', array('delivered_at' => 'created_at'))
                    ->join(array('os2' => 'order_status'), 'os2.order_id = o.id AND os1.id < os2.id', array(), Select::JOIN_LEFT);

            $where = new Where();
            $where->isNull('os2.id');
            $where->equalTo('os1.status', OrderStatusTable::DELIVERED);
            $where->equalTo('o.loyalty_points_earn', 0);
            if ($orderId) {
                $where->equalTo('o.id', $orderId);
            }

            $select->order('o.id DESC');
            $select->group('o.id');

            $select->where($where);

            $result = $this->executeQuery($select);
            return $this->getResultArray($result);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }

    public function isFirstOrder($orderId)
    {
        try {
            $table = $this->tableGateway->getTable();
            $sql = $this->getSql();
            $select = $sql->select();
            $select->from(array('o' => $table))
                    ->columns(array(
                        'no_of_orders' => new Expression("(SELECT count(*) FROM `order` AS oi 
                                                            JOIN order_status os1 ON os1.order_id = oi.id
                                                            LEFT JOIN order_status os2 ON os2.order_id = oi.id AND os1.id < os2.id
                                                            WHERE os2.id IS NULL AND os1.status = 'delivered' AND oi.associate_id = o.associate_id)"),
            ));

            $select->join(array('sws' => 'store_warehouse_shipper'), 'o.associate_id = sws.id', array('store_id'));

            $where = new Where();
            $where->equalTo('o.id', $orderId);
            $select->order('o.id DESC');
            $select->group('o.id');
            $select->where($where);

            $result = $this->executeQuery($select);
            
            return ($result) ? $result->current() : false;
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return false;
    }
    
    public function applyPointsToOrder($orderId, $usePoints)
    {
        //params
        $param = array(
            'net_amount' => new Expression('net_amount - ' . $usePoints),
            'loyalty_points_used' => new Expression('loyalty_points_used + ' . $usePoints)
        );
        
        return $this->updateOrder($param, ['id' => $orderId]);
    }
    
    public function updateOrderDetails($param, $where)
    {
        $param['net_amount'] = new Expression($param['net_amount'] . ' + delivery_charges - loyalty_points_used');
        return $this->updateOrder($param, $where);
    }

}

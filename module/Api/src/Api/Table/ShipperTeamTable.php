<?php

namespace Api\Table;

use Api\Model\ShipperTeam;
use Base\Table\BaseTable;
use SebastianBergmann\RecursionContext\Exception;
use Zend\Db\Sql\Where;

class ShipperTeamTable extends BaseTable
{
    public function addShipperTeam($parameter)
    {

        try {
            $shipperModel = new ShipperTeam();
            $this->getModelForAdd($shipperModel, $parameter);
            return $this->addModel($shipperModel);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function updateShipperTeam($parameter, $whereArray)
    {

        try {
            $shipperModel = new ShipperTeam();
            $this->getModelForAdd($shipperModel, $parameter);
            return $this->updateModel($shipperModel, $whereArray);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function deleteShipperTeam($id)
    {

        try {
            $this->tableGateway->delete(array('id' => (int) $id)); 
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function getShipperTeamList($shipperAccountId, $page = null, $limit = null)
    {
        try {
            $operation = array();

            if (isset($page)) {
                $operation['page'] = $page;
                $operation['limit'] = isset($limit) ? $limit : 20;
            }
            
            $where = new Where();
            $where->equalTo('shipper_team.account_id', $shipperAccountId);
            
            $joinParameters = array();
            $joinParameters[] = array(
                'table' => 'account',
                'condition' => 'shipper_team.account_id = account.id',
                'columns' => array('name' => 'display_name', 'email', 'phone'),
                'type' => 'inner'
            );
            $operation['join'] = $joinParameters;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }

    public function getShipperTeamDetails($shipperId)
    {

        try {
            $operation = array();

            $where = new Where();
            $where->equalTo('shipper_team.id', $shipperId);

            $operation['where'] = $where;
            $operation['first_row'] = 1;

            return $this->getList($operation);
        } catch (Exception $e) {
            $this->logger->ERR($e->getMessage() . "\n" . $e->getTraceAsString());
        }
        return false;
    }
}

<?php

namespace Api\Console;

use Api\Table\LoyaltyPointTable;
use Base\Console\BaseController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Predicate\Expression;
use ZFTool\Diagnostics\Exception\RuntimeException;

class CreateStoreAccountController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->no = $request->getParam('no', 30);
        $this->salesperson = $request->getParam('salesperson');
        $this->warehouseShipperId = $request->getParam('warehouse_shipper_id', 1);
        if (!$this->salesperson) {
            return "Please provide salesperson id \n";
        }
        
        // get salesperson info and his users count.
        $storeSalespersonTable = $this->getServiceLocator()->get('Api\Table\StoreSalespersonTable');
        $salespersonInfo = $storeSalespersonTable->getSalespersonDetails($this->salesperson);
        if (!$salespersonInfo) {
            return "Please provide valid salesperson id \n";
        }
        
        echo $this->start = $salespersonInfo['count'] + 1;

        echo "Started processing \n";
        $this->create();
        
        $fp = fopen('./data/logs/stores_'. $this->salesperson.'_'. time() . '.csv', 'w');
        foreach($this->usersInfo as $line){
            fputcsv($fp, $line);
        }
        fclose($fp);

        return "Successfully imported";
    }

    public function create()
    {
        $this->usersInfo = array();
        for ($i = $this->start; $i < $this->start + $this->no; $i++) {
            $userId = 'sm'. $this->salesperson . 'u' . $i;
            $email = $userId . '@growsari.com';
            $password = $this->randomPassword();
            $phone = '1231231231';
            $this->usersInfo[] = array('username' => $userId, 'password' => $password);

            // Validate the data
            $accountClass = $this->getServiceLocator()->get('zfcuser_module_options')->getUserEntityClass();
            $account = new $accountClass();
            $account->setCreatedAt(new Expression('NOW()'));
            $account->setUpdatedAt(new Expression('NOW()'));
            $account->setDisplayName($userId);
            $account->setUsername($userId);
            $account->setEmail($email);
            $account->setPhone($phone);
            $account->setType('STORE');
            $account->setRole('USER');
            $account->setState(1);

            $bcrypt = new Bcrypt;
            $bcrypt->setCost(14);
            $account->setPassword($bcrypt->create($password));

            $this->getServiceLocator()->get('zfcuser_user_mapper')->insert($account);
            if (!$account->getId()) {
                echo "unable to create account \n ";
                return;
            }

            $params = array();
            $params['account_id'] = $account->getId();
            $params['name'] = $userId;
            $params['cotact_no'] = $phone;

            $storeTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
            $res = $storeTable->addStore($params); // id of last inserted data 
            if ($res === false) {
                echo "unable to create store \n ";
                return;
                // may be remove account
            }

            $storeWarehouseShipperTable = $this->getServiceLocator()->get('Api\Table\StoreWarehouseShipperTable');
            $resWarehouseShipper = $storeWarehouseShipperTable->addStoreWarehouseShiper(array(
                'warehouse_shipper_id' => $this->warehouseShipperId,
                'store_id' => $res
            )); // id of last inserted data 
            if ($resWarehouseShipper === false) {
                echo "unable to create store warehouse shipper \n ";
                return;
                // may be remove account
            }
            
            $storeSalespersonTable = $this->getServiceLocator()->get('Api\Table\StoreSalespersonTable');
            $resStoreSalesperson = $storeSalespersonTable->addStoreSalesperson(array(
                'store_id' => $res,
                'salesperson_account_id' => $this->salesperson
            ));
            if ($resStoreSalesperson === false) {
                echo "unable to add store salesperson \n ";
                return;
            }
            
            $loyaltyTable = $this->getServiceLocator()->get('Api\Table\LoyaltyPointTable');
            $config = $this->getServiceLocator()->get('Config');
            $resLoyality = $loyaltyTable->addPoints(array(
                'account_id' => $params['account_id'],
                'order_id' => null,
                'credit'   => $config['app_settings']['loyality_for_signup'],
                'remarks'  => LoyaltyPointTable::REMARK_CREDIT_SIGNUP
            ));
            if ($resLoyality === false) {
                echo "unable to add loyality points \n ";
                return;
            }
        }
    }
    
    public function randomPassword($length = 6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $password = ''; 
        for ($i = 0; $i < $length/2; $i++) {
            $char = substr( str_shuffle( $chars ), 0, 1 );
            $password .= $char . $char;
        }
        
        return $password;
    }

}

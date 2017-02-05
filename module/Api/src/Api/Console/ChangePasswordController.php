<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use Zend\Crypt\Password\Bcrypt;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ChangePasswordController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->filePath = $request->getParam('file');
        $this->salesperson = $request->getParam('salesperson', 1);
        $this->useExisting = $request->getParam('use-existing', 0);
        if (!file_exists($this->filePath)) {
            return "File not found. Plese provide proper file path";
        }

        echo "Started processing";
        $this->import();
        
        $fp = fopen('./data/logs/stores_pwd_' . $this->salesperson . '_' . time() . '.csv', 'w');
        foreach ($this->usersInfo as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);

        return "Successfully imported";
    }

    public function import()
    {
        $row = 1;
        $this->usersInfo = array();
        if (($handle = fopen($this->filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                echo "looping through records:  " . $row . "\n";
                try {
                    $newPass = ($this->useExisting) ? $data[1] : $this->randomPassword();

                    $bcrypt = new Bcrypt;
                    $bcrypt->setCost(14);
                    $pass = $bcrypt->create($newPass);
                    
                    $accountTable = $this->getServiceLocator()->get('Api\Table\StoreTable');
                    $resWarehouseShipper = $accountTable->updateAccountPassword($data[0], $pass); 
                    if ($resWarehouseShipper === false) {
                        echo "unable to update password \n ";
                    }
                    
                    $this->usersInfo[] = array('username' => $data[0], 'password' => $newPass);
                    
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $row++;
            }

            fclose($handle);
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

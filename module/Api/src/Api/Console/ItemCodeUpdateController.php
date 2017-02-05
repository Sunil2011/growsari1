<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ItemCodeUpdateController extends BaseController
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
        if (!file_exists($this->filePath)) {
            return "File not found. Plese provide proper file path";
        }

        echo "Started processing";
        $this->import();

        return "Successfully imported";
    }

    public function import()
    {
        $keys = array();
        $row = 1;
        $rowExists = 0;
        if (($handle = fopen($this->filePath, "r")) !== FALSE) {
            while (($rowData = fgetcsv($handle, 1000, ',')) !== FALSE) {
                echo "looping through records:  " . $row . "\n";
                
                if ($row == 1) {
                    $keys = $rowData;
                    $row++;
                    continue;
                }
                
                $data = array_combine($keys, $rowData);

                try {
                    $skuExists = $this->isProductExists($data['super8_name']);
                    if ($skuExists) {
                        $rowExists++;
                        $this->update('product', $skuExists, array(
                            'item_code' => $data['item_code'],  
                            'updated_at' => date("Y-m-d H:i:s")
                        ));
                    } 
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $row++;
            }

            fclose($handle);
        }
        echo "rows updated = " . $rowExists;
    }

    public function isProductExists($super8Name)
    {
        $sql = "SELECT * FROM product WHERE ( super8_name = :super8Name ) AND item_code=''  LIMIT 1";
        $productSql = $this->getAdapter()->query($sql);
        $productbj = $productSql->execute(array(':super8Name' => $super8Name));

        if ($productbj->count() == 0) {
            return false;
        }
        
        $row = $productbj->current();

        return $row['id'];
    }

}

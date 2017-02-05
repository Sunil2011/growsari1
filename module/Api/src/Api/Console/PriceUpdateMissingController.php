<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class PriceUpdateMissingController extends BaseController
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
        } else {
            file_put_contents('./data/super8/s8-inventory-'. date("Y-m-d_H:i:s") . '.txt', file_get_contents($this->filePath));
        }

        echo "Started processing";
        $this->import();

        return "Successfully imported";
    }

    public function import()
    {
        $keys = array();
        $row = 1;
        if (($handle = fopen($this->filePath, "r")) !== FALSE) {
            while (($rowData = fgetcsv($handle, 1000, '~')) !== FALSE) {
                echo "looping through records:  " . $row . "\n";
                
                if ($row == 1) {
                    $keys = $rowData;
                    $row++;
                    continue;
                }
                
                $data = array_combine($keys, $rowData);

                try {
                    $skuExists = $this->isProductExists($data['DESCRIPTION']);
                    if ($skuExists) {
                        $this->update('product', $skuExists, array(
                            'price' => $data['PRICE'],
                            'srp' => $data['PRICE'] * 1.05, //5 percent
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
        
    }

    public function isProductExists($super8Name)
    {
        $sql = "SELECT * FROM product WHERE ( super8_name = :super8Name ) AND price='0.00'  LIMIT 1";
        $productSql = $this->getAdapter()->query($sql);
        $productbj = $productSql->execute(array(':super8Name' => $super8Name));

        if ($productbj->count() == 0) {
            return false;
        }
        
        $row = $productbj->current();

        return $row['id'];
    }

}

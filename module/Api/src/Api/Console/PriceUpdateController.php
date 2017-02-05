<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class PriceUpdateController extends BaseController
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
        $this->date = $request->getParam('date', date("Y-m-d"));
        if (!file_exists($this->filePath)) {
            return "File not found. Plese provide proper file path";
        } else {
            $s3Upload = $this->getServiceLocator()->get('S3UploadService');
            $s3Upload->moveNormalFile($this->filePath, 'super8/s8-inventory-' . date("Y-m-d_H:i:s") . '.txt');
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
            while (($rowData = fgetcsv($handle, 1000, '~')) !== FALSE) {
                echo "looping through records:  " . $row . "\n";

                if ($row == 1) {
                    $keys = $rowData;
                    $row++;
                    continue;
                }

                $data = array_combine($keys, $rowData);
                try {
                    $skuExists = $this->isProductExists($data['ITEMCODE']);
                    if ($skuExists) {
                        $rowExists++;
                        $this->processData($skuExists, $data);
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

    private function processData($productRecord, $data)
    {
        $available = strtolower(trim($data['AVAILABILITY']));
        $productHisData = array(
            'product_id' => $productRecord['id'],
            'price' => $data['PRICE'],
            'is_available' => ($available === 'available') ? 1 : 0,
            'date' => $this->date,
            'updated_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s"),
        );
        $this->insert('product_super8_price_history', $productHisData);

        if ( $data['PRICE'] !=  $productRecord['price']
            || $productHisData['is_available'] != $productRecord['is_available']
            || $data['BCODE'] != $productRecord['barcode']    
        ) {
            $productData = array(
                'super8_price' => $data['PRICE'],
                'barcode' => $data['BCODE'],
                'is_available' =>  ($available === 'available') ? 1 : 0,
                'updated_at' => date("Y-m-d H:i:s"),                
            );
            
            if (!$productRecord['is_locked']) {
                $productData['price'] = $data['PRICE'];
                $productData['srp'] = $data['PRICE'] * 1.05; //5 percent
            }

            $this->update('product', $productRecord['id'], $productData);
        }
    }

    public function isProductExists($itemCode)
    {
        $sql = "SELECT * FROM product WHERE ( item_code = :item_code ) order by id desc LIMIT 1";
        $productSql = $this->getAdapter()->query($sql);
        $productbj = $productSql->execute(array(':item_code' => trim($itemCode)));

        if ($productbj->count() == 0) {
            return false;
        }

        $row = $productbj->current();

        return $row;
    }

}

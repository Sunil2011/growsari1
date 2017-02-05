<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class PriceUpdateHistoryImportController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        echo "Started processing\n";
        $this->date = date("Y-m-d");
        $this->upload();

        return "Successfully imported";
    }
    
    public function upload()
    {
        $this->path = "data/super8";
        $this->prefix = "data/super8/";
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path), RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $file => $object){
            if (is_dir($file) !== true) {
                if (substr($file, 0, strlen($this->prefix)) == $this->prefix) {
                    $fileName = substr($file, strlen($this->prefix));
                } 
                
                if (!empty($fileName)) {
                    $this->uploadFile($file, $fileName);
                }
            }
            
        }
    }
    
    public function uploadFile($file, $fileName)
    {
        try {
            //$s3Upload = $this->getServiceLocator()->get('S3UploadService');
            //$s3Upload->moveNormalFile($file, "super8/".$fileName);
            
            echo "Uploaded $fileName \n";
            
            preg_match('/^s8-inventory-(.*?)_/', $fileName, $matches);
            
            $this->date = isset($matches[1]) ? $matches[1] : $this->date;
            $this->filePath = $file;
            $this->import();
        } catch (Aws\Exception\S3Exception $e) {
            echo "There was an error uploading the file ($file).\n";
            exit;
        }
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
        $productHisData = array(
            'product_id' => $productRecord['id'],
            'price' => $data['PRICE'],
            'date' => $this->date,
            'updated_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s"),
        );
        $this->insert('product_super8_price_history', $productHisData);
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

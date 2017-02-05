<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ProductImportImageController extends BaseController
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
            while (($rowData = fgetcsv($handle)) !== FALSE) {
                echo "looping through records:  " . $row . "\n";
                if ($row == 1) {
                    $keys = $rowData;
                    $row++;
                    continue;
                }

                $this->data = array_combine($keys, $rowData);

                try {
                    $productRecord = $this->isProductExists($this->data['Item Code']);
                    if ($productRecord) {
                        $rowExists++;
                        $this->update('product', $productRecord['id'], array(
                            'image' => trim($this->data['SKU Image']),
                            'updated_at' => $this->getDate()
                        ));
                    }
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $row++;
            }

            fclose($handle);
        }

        echo "row exists = " . $rowExists;
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

    private function getDate()
    {
        return date("Y-m-d H:i:s");
    }

}

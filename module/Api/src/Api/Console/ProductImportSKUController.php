<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ProductImportSKUController extends BaseController
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

                $data = array_combine($keys, $rowData);
                $this->data = $this->processData($data);                

                try {
                    $skuExists = $this->isProductSKUExists($this->data['sku_id']);
                    if ($skuExists) {
                        $rowExists++;
                        $this->update('product', $skuExists, $this->data);
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

    private function processData($rawData)
    {
        $data = $this->convertToDbColumns($rawData);

        $newData['sku_id'] = $this->generateSkuId($data['sku']);
        $newData['sku'] = $this->updateSKU($data);
        $newData['updated_at'] = $this->getDate();

        return $newData;
    }

    private function convertToDbColumns($data)
    {
        $columns = array(
            'Item Code' => 'item_code',
            'GS Brand id' => 'brand',
            'Super 8 Name' => 'super8_name',
            'Commercial Name (Brand - Product Description - Format Volume Quantity)' => 'sku',
            'Categories' => 'category',
            'Mega category' => 'mega_category',
            'Predominant Color' => 'variant_color',
            'Unit volume' => 'volume',
            'Format' => 'format',
            'Quantity' => 'quantity',
            'Promo / Regular' => 'promotion_type', // Reg / Promo / New
            'Price' => 'price',
            'Availability' => 'status',
            'Regular Price' => 'srp'
        );
        $dataTobeUsed = array();
        foreach ($columns as $key => $value) {
            $dataTobeUsed[$value] = (!empty($data[$key])) ? $data[$key] : '';
        }

        return $dataTobeUsed;
    }

    private function generateSkuId($string)
    {
        // http://stackoverflow.com/a/2103815/598424
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

    private function updateSku($data)
    {
        $string = $data['sku'];
        $string = str_replace($data['volume'], "", $string);

        return trim($string);
    }

    public function isProductSKUExists($skuId)
    {
        $sql = "SELECT * FROM product WHERE ( sku_id LIKE :sku_id )  LIMIT 1";
        $productSql = $this->getAdapter()->query($sql);
        $productbj = $productSql->execute(array(':sku_id' => $skuId));

        if ($productbj->count() == 0) {
            return false;
        }

        $row = $productbj->current();

        return $row['id'];
    }

    private function normalizeName($name)
    {
        //remove spaces
        //convert to lower case
        return preg_replace('/\s+/', '', strtolower($name));
    }

    private function getDate()
    {
        return date("Y-m-d H:i:s");
    }

}

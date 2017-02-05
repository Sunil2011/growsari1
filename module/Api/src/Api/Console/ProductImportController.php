<?php

namespace Api\Console;

use Base\Console\BaseController;
use Exception;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ProductImportController extends BaseController
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
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $keys = array();
        $row = 1;
        $rowExists = 0;
        $newProducts = array();
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
                    $skuExists = $this->isProductSKUExists($this->data['item_code'], $this->data['super8_name'], $this->data['sku_id']);
                    if ($skuExists) {
                        $rowExists++;
                        if (!$this->data['item_code']) {
                            unset($this->data['item_code']);
                        }
                        
                        $this->update('product', $skuExists, $this->data);
                    } else {
                        $newProducts[] = $this->data;
                        $this->data['created_at'] = $this->getDate();
                        $this->insert('product', $this->data);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $row++;
            }

            fclose($handle);
        }

        $fp = fopen('./data/logs/new_products_import_' . time() . '.csv', 'w');
        foreach ($newProducts as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);
    }

    private function processData($rawData)
    {
        $data = $this->convertToDbColumns($rawData);
        
        //item code: remove - from,becuase excel is aadding that
        $data['item_code'] = str_replace('-', "", trim($data['item_code']));

        //brand
        $data['brand_id'] = 0;
        $brandName = $data['brand'];
        if (empty($data['brand'])) {
            $data['brand'] = 'other';
        }

        $brand = $this->getBrand(trim($data['brand']));
        $data['brand_id'] = isset($brand) ? $brand : 0;
        unset($data['brand']);

        // create mega category
        $megaCategory = $this->getMegaCategory(trim($data['mega_category']));
        $megaCategoryId = isset($megaCategory) ? $megaCategory : 0;
        unset($data['mega_category']);

        //category
        $data['category_id'] = 0;
        if (empty($data['category'])) {
            $data['category'] = 'other';
        }
        $category = $this->getCategory(trim($data['category']), $megaCategoryId);
        $data['category_id'] = isset($category) ? $category : 0;
        unset($data['category']);

        // varinat colr & sku id
        $data['variant_color'] = $this->getHexFromColor($data['variant_color']);
        $data['sku'] = $this->updateSKU($data);
        $data['sku_id'] = $this->generateSkuId($data);
        
        // update promotional type
        $data['is_promotional'] = ($data['promotion_type'] == 'Promo') ? 1 : 0;
        $data['is_new'] = ($data['promotion_type'] == 'New') ? 1 : 0;
        unset($data['promotion_type']);

        // price update
        if (empty($data['price'])) {
            unset($data['price']);
        } else if (empty($data['srp'])) {
            $data['srp'] = $data['price'] * 1.05; //5 percentage
        }

        if (empty($data['srp'])) {
            unset($data['srp']);
        }

        $data['updated_at'] = $this->getDate();

        return $data;
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

    private function getHexFromColor($color)
    {
        $colors = array(
            'F0F8FF' => 'aliceblue'
            , 'FAEBD7' => 'antiquewhite'
            , '00FFFF' => 'aqua'
            , '7FFFD4' => 'aquamarine'
            , 'F0FFFF' => 'azure'
            , 'F5F5DC' => 'beige'
            , 'FFE4C4' => 'bisque'
            , '000000' => 'black'
            , 'FFEBCD' => 'blanchedalmond'
            , '0000FF' => 'blue'
            , '8A2BE2' => 'blueviolet'
            , 'A52A2A' => 'brown'
            , 'DEB887' => 'burlywood'
            , '5F9EA0' => 'cadetblue'
            , '7FFF00' => 'chartreuse'
            , 'D2691E' => 'chocolate'
            , 'FF7F50' => 'coral'
            , '6495ED' => 'cornflowerblue'
            , 'FFF8DC' => 'cornsilk'
            , 'DC143C' => 'crimson'
            , '00FFFF' => 'cyan'
            , '00008B' => 'darkblue'
            , '008B8B' => 'darkcyan'
            , 'B8860B' => 'darkgoldenrod'
            , 'A9A9A9' => 'darkgray'
            , '006400' => 'darkgreen'
            , 'BDB76B' => 'darkkhaki'
            , '8B008B' => 'darkmagenta'
            , '556B2F' => 'darkolivegreen'
            , 'FF8C00' => 'darkorange'
            , '9932CC' => 'darkorchid'
            , '8B0000' => 'darkred'
            , 'E9967A' => 'darksalmon'
            , '8FBC8F' => 'darkseagreen'
            , '483D8B' => 'darkslateblue'
            , '2F4F4F' => 'darkslategray'
            , '00CED1' => 'darkturquoise'
            , '9400D3' => 'darkviolet'
            , 'FF1493' => 'deeppink'
            , '00BFFF' => 'deepskyblue'
            , '696969' => 'dimgray'
            , '1E90FF' => 'dodgerblue'
            , 'B22222' => 'firebrick'
            , 'FFFAF0' => 'floralwhite'
            , '228B22' => 'forestgreen'
            , 'FF00FF' => 'fuchsia'
            , 'DCDCDC' => 'gainsboro'
            , 'F8F8FF' => 'ghostwhite'
            , 'FFD700' => 'gold'
            , 'DAA520' => 'goldenrod'
            , '808080' => 'gray'
            , '008000' => 'green'
            , 'ADFF2F' => 'greenyellow'
            , 'F0FFF0' => 'honeydew'
            , 'FF69B4' => 'hotpink'
            , 'CD5C5C' => 'indianred'
            , '4B0082' => 'indigo'
            , 'FFFFF0' => 'ivory'
            , 'F0E68C' => 'khaki'
            , 'E6E6FA' => 'lavender'
            , 'FFF0F5' => 'lavenderblush'
            , '7CFC00' => 'lawngreen'
            , 'FFFACD' => 'lemonchiffon'
            , 'ADD8E6' => 'lightblue'
            , 'F08080' => 'lightcoral'
            , 'E0FFFF' => 'lightcyan'
            , 'FAFAD2' => 'lightgoldenrodyellow'
            , '90EE90' => 'lightgreen'
            , 'D3D3D3' => 'lightgrey'
            , 'FFB6C1' => 'lightpink'
            , 'FFA07A' => 'lightsalmon'
            , '20B2AA' => 'lightseagreen'
            , '87CEFA' => 'lightskyblue'
            , '778899' => 'lightslategray'
            , 'B0C4DE' => 'lightsteelblue'
            , 'FFFFE0' => 'lightyellow'
            , '00FF00' => 'lime'
            , '32CD32' => 'limegreen'
            , 'FAF0E6' => 'linen'
            , 'FF00FF' => 'magenta'
            , '800000' => 'maroon'
            , '66CDAA' => 'mediumaquamarine'
            , '0000CD' => 'mediumblue'
            , 'BA55D3' => 'mediumorchid'
            , '9370DB' => 'mediumpurple'
            , '3CB371' => 'mediumseagreen'
            , '7B68EE' => 'mediumslateblue'
            , '00FA9A' => 'mediumspringgreen'
            , '48D1CC' => 'mediumturquoise'
            , 'C71585' => 'mediumvioletred'
            , '191970' => 'midnightblue'
            , 'F5FFFA' => 'mintcream'
            , 'FFE4E1' => 'mistyrose'
            , 'FFE4B5' => 'moccasin'
            , 'FFDEAD' => 'navajowhite'
            , '000080' => 'navy'
            , 'FDF5E6' => 'oldlace'
            , '808000' => 'olive'
            , '6B8E23' => 'olivedrab'
            , 'FFA500' => 'orange'
            , 'FF4500' => 'orangered'
            , 'DA70D6' => 'orchid'
            , 'EEE8AA' => 'palegoldenrod'
            , '98FB98' => 'palegreen'
            , 'AFEEEE' => 'paleturquoise'
            , 'DB7093' => 'palevioletred'
            , 'FFEFD5' => 'papayawhip'
            , 'FFDAB9' => 'peachpuff'
            , 'FFDAB9' => 'peach'
            , 'CD853F' => 'peru'
            , 'FFC0CB' => 'pink'
            , 'DDA0DD' => 'plum'
            , 'B0E0E6' => 'powderblue'
            , '800080' => 'purple'
            , 'FF0000' => 'red'
            , 'BC8F8F' => 'rosybrown'
            , '4169E1' => 'royalblue'
            , '8B4513' => 'saddlebrown'
            , 'FA8072' => 'salmon'
            , 'FAA460' => 'sandybrown'
            , '2E8B57' => 'seagreen'
            , 'FFF5EE' => 'seashell'
            , 'A0522D' => 'sienna'
            , 'C0C0C0' => 'silver'
            , '87CEEB' => 'skyblue'
            , '6A5ACD' => 'slateblue'
            , '708090' => 'slategray'
            , 'FFFAFA' => 'snow'
            , '00FF7F' => 'springgreen'
            , '4682B4' => 'steelblue'
            , 'D2B48C' => 'tan'
            , '008080' => 'teal'
            , 'D8BFD8' => 'thistle'
            , 'FF6347' => 'tomato'
            , '40E0D0' => 'turquoise'
            , 'EE82EE' => 'violet'
            , 'F5DEB3' => 'wheat'
            , 'FFFFFF' => 'white'
            , 'F5F5F5' => 'whitesmoke'
            , 'FFFF00' => 'yellow'
            , '9ACD32' => 'yellowgreen'
        );

        $key = array_search(preg_replace('/\s+/', '', strtolower($color)), $colors);

        return ($key) ? $key : '000000';
    }

    private function generateSkuId($data)
    {
        $string = $data['super8_name'] . ' ' . $data['volume'] . ' ' . $data['format']. ' ' . $data['quantity'];
        // http://stackoverflow.com/a/2103815/598424
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

    private function updateSku($data)
    {
        $string = $data['sku'];
        $string = str_replace($data['volume'], "", $string);
        $string = str_replace($data['format'], "", $string);

        return trim($string);
    }

    public function getBrand($brand)
    {
        $sql = "SELECT * FROM brand WHERE ( Lower(name) LIKE :brand )  LIMIT 1";
        $brandSql = $this->getAdapter()->query($sql);
        $brandObj = $brandSql->execute(array(':brand' => strtolower(trim($brand))));

        if ($brandObj->count() == 0) {
            $date = $this->getDate();
            $this->insert('brand', array(
                'name' => $brand,
                'image' => $this->normalizeName($brand) . '.jpg',
                'created_at' => $date,
                'updated_at' => $date
            ));
            $id = $this->getAdapter()->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            $brandRow = $brandObj->current();
            $id = $brandRow['id'];
        }

        return $id;
    }

    public function getCategory($category, $megaCategoryId)
    {
        $sql = "SELECT * FROM category WHERE (Lower(name) LIKE :category AND mega_category_id = :mega_category_id)  LIMIT 1";
        $categorySql = $this->getAdapter()->query($sql);
        $categoryObj = $categorySql->execute(array(':category' => strtolower(trim($category)), ':mega_category_id' => $megaCategoryId));

        if ($categoryObj->count() == 0) {
            $date = $this->getDate();
            $this->insert('category', array(
                'name' => $category,
                'thumb_url' => $this->normalizeName($category) . '.png',
                'mega_category_id' => $megaCategoryId,
                'created_at' => $date,
                'updated_at' => $date
            ));
            $id = $this->getAdapter()->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            $categoryRow = $categoryObj->current();
            $id = $categoryRow['id'];
        }

        return $id;
    }

    public function getMegaCategory($category)
    {
        $sql = "SELECT * FROM mega_category WHERE (Lower(name) LIKE :category)  LIMIT 1";
        $categorySql = $this->getAdapter()->query($sql);
        $categoryObj = $categorySql->execute(array(':category' => strtolower(trim($category))));

        if ($categoryObj->count() == 0) {
            $date = $this->getDate();
            $this->insert('mega_category', array(
                'name' => $category,
                'thumb_url' => $this->normalizeName($category) . '.png',
                'created_at' => $date,
                'updated_at' => $date
            ));
            $id = $this->getAdapter()->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            $categoryRow = $categoryObj->current();
            $id = $categoryRow['id'];
        }

        return $id;
    }

    public function isProductSKUExists($itemCode, $super8Name, $skuId)
    {
        if ($itemCode) {
            $sql = "SELECT * FROM product WHERE ( trim(item_code) = :item_code ) ORDER BY id DESC  LIMIT 1";
            $productSql = $this->getAdapter()->query($sql);
            $productbj = $productSql->execute(array(':item_code' => $itemCode));
        } else if ($super8Name) {
            $sql = "SELECT * FROM product WHERE ( trim(super8_name) = :super8_name ) ORDER BY id DESC LIMIT 1";
            $productSql = $this->getAdapter()->query($sql);
            $productbj = $productSql->execute(array(':super8_name' => $super8Name));
        } else {
            $sql = "SELECT * FROM product WHERE ( trim(sku_id) = :sku_id ) ORDER BY id DESC LIMIT 1";
            $productSql = $this->getAdapter()->query($sql);
            $productbj = $productSql->execute(array(':sku_id' => $skuId));
        }

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

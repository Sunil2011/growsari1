<?php

namespace Api\Controller;

use Api\Exception\ApiException;

class ProductController extends BaseApiController
{

    /**
     * @SWG\Get(
     *     path="/api/product",
     *     description="get all product",
     *     tags={"product"},
     *     @SWG\Parameter(
     *         name="last_updated_at",
     *         in="query",
     *         description="last updated time (yyyy-MM-dd H:i:s)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="category id ",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="brand id ",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="page no.",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="count per page",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function getList()
    {
        $data = array();
        $parameter = $this->getParameter($this->params()->fromQuery());

        $productTable = $this->getServiceLocator()->get('Api\Table\ProductTable');
        $data['product'] = $productTable->getProductList($parameter);
        if ($data['product'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }
        
        $data['product'] = $this->convertImageNameToUrl($data['product'], array("brand_image" => 'brand', 'image' => 'product', 'thumb_image' => 'product/thumb'));
        $data['updated_at'] = $this->getDateTime();

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Get(
     *     path="/api/product/{id}",
     *     description="product details",
     *     tags={"product"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="product id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function get($id)
    {
        $data = array();

        $productTable = $this->getServiceLocator()->get('Api\Table\ProductTable');
        $data['product'] = $productTable->getProductDetails($id);
        if ($data['product'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        return $this->successRes('Successfully fetched', $data);
    }

    /**
     * @SWG\Post(
     *     path="/api/product",
     *     description="create product",
     *     tags={"product"},
     *     @SWG\Parameter(
     *         name="sku",
     *         in="formData",
     *         description="",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="volume",
     *         in="formData",
     *         description="des-volume",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="price",
     *         in="formData",
     *         description="price of product",
     *         required=true,
     *         type="number"
     *     ),
     *     @SWG\Parameter(
     *         name="srp",
     *         in="formData",
     *         description="srp of product",
     *         required=true,
     *         type="string"
     *     ), 
     *     @SWG\Parameter(
     *         name="variant_color",
     *         in="formData",
     *         description="",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="category_id",
     *         in="formData",
     *         description="category of product",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="brand_id",
     *         in="formData",
     *         description="brand of product",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="format",
     *         in="formData",
     *         description="",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function create()
    {
        $this->checkGrowsariSession();
        $parameter = $this->params()->fromPost();

        $productTable = $this->getServiceLocator()->get('Api\Table\ProductTable');
        if (empty($parameter['id'])) {
            $parameter['sku_id'] = $this->generateSkuId($parameter['sku'] . ' ' . $parameter['volume'] . ' ' . $parameter['format']);
            $parameter['variant_color'] = $this->getHexFromColor($parameter['variant_color']);
            $res = $productTable->addProduct($parameter); // id of last inserted data 
            if ($res === false) {
                throw new ApiException('SKU ID already exists, please check the sku name!', 500);
            }
        } else {
            $res = $productTable->updateProduct($parameter, array('id' => (int) $parameter['id']));
        }

        if ($res === false) {
            throw new ApiException('Unable to create/update, please try again!', 500);
        }

        return $this->successRes('Successfully updated.', array('id' => $res));
    }

    /**
     * @SWG\Post(
     *     path="/api/product/delete",
     *     description="product details",
     *     tags={"product"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="product id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function deleteAction()
    {
        $this->checkGrowsariSession();
        $routeId = (int) $this->params()->fromPost('id', 0);

        $productTable = $this->getServiceLocator()->get('Api\Table\ProductTable');
        $msg = $productTable->deleteProduct($routeId);
        if ($msg === false) {
            throw new ApiException('Unable to delete, please try again!', 500);
        }

        return $this->successRes($msg);
    }

    /**
     * @SWG\Post(
     *     path="/api/product/update",
     *     description="update product",
     *     tags={"product"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="product id",
     *         required=true,
     *         type="string"
     *     ), 
     *     @SWG\Parameter(
     *         name="sku",
     *         in="formData",
     *         description="",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="volume",
     *         in="formData",
     *         description="des-volume",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="price",
     *         in="formData",
     *         description="price of product",
     *         required=true,
     *         type="number"
     *     ),
     *     @SWG\Parameter(
     *         name="srp",
     *         in="formData",
     *         description="srp of product",
     *         required=true,
     *         type="string"
     *     ), 
     *     @SWG\Parameter(
     *         name="variant_color",
     *         in="formData",
     *         description="",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="category_id",
     *         in="formData",
     *         description="category of product",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="brand_id",
     *         in="formData",
     *         description="brand of product",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="format",
     *         in="formData",
     *         description="",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     *  ) 
     */
    public function updateAction()
    {
        $this->checkGrowsariSession();
        $parameter = $this->params()->fromPost();
        $parameter['updated_at'] = date("Y-m-d H:i:s");
        $id = $this->params()->fromPost('id', 0);
        unset($parameter['id']);

        $productTable = $this->getServiceLocator()->get('Api\Table\ProductTable');
        $res = $productTable->updateProduct($parameter, array('id' => $id));
        if ($res === false) {
            throw new ApiException('Unable to update, please try again!', 500);
        }

        return $this->successRes('Successfully updated!', $parameter);
    }

    /**
     * @SWG\Post(
     *     path="/api/product/export",
     *     description="export product details",
     *     tags={"product"},
     *     @SWG\Response(
     *         response=200,
     *         description="response"
     *     )
     * )
     */
    public function exportAction()
    {
        $this->checkGrowsariSession();

        $productTable = $this->getServiceLocator()->get('Api\Table\ProductTable');
        $data['product'] = $this->processData($productTable->getProductList(array()));
        if ($data['product'] === false) {
            throw new ApiException('Unable to fetch data, please try again!', '500');
        }

        $response = $this->getResponse();
        ob_start();
        $fh = @fopen('php://output', 'w');
        fputcsv($fh, array_keys($this->columnsFromDb()), ',');
        if (count($data['product']) > 0) {
            foreach ($data['product'] as $result) {
                fputcsv($fh, $result);
            }
        }
        fclose($fh);
        $response->setContent(ob_get_clean());

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"products_" . time() . ".csv\"");

        return $response;
    }

    private function processData($data)
    {
        $dataTobeUsed = array();
        foreach ($data['list'] as $value) {
            $dataTobeUsed[] = $this->processRow($value);
        }

        return $dataTobeUsed;
    }

    private function processRow($row)
    {
        $columns = array_flip($this->columnsFromDb());

        $dataTobeUsed = array();
        foreach ($columns as $key => $value) {
            if ($key === 'promotion_type') {
                $dataTobeUsed[$value] = 'Reg';
                if ($row['is_promotional']) {
                    $dataTobeUsed[$value] = 'Promo';
                } else if ($row['is_new']) {
                    $dataTobeUsed[$value] = 'New';
                }
            } else if ($key === 'variant_color') {
                $dataTobeUsed[$value] = $this->getColorFromHex($row[$key]);
            } else {
                $dataTobeUsed[$value] = (!empty($row[$key])) ? $row[$key] : '';
            }
        }

        return $dataTobeUsed;
    }

    private function columnsFromDb()
    {
        return array(
            'Item Code' => 'item_code',
            'GS Brand id' => 'variant',
            'Super 8 Name' => 'super8_name',
            'Commercial Name (Brand - Product Description - Format Volume Quantity)' => 'sku',
            'Categories' => 'line',
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
    }

    private function getColorFromHex($hex)
    {
        $colors = $this->getColors();

        return (isset($colors[$hex])) ? $colors[$hex] : '000000';
    }

    private function getHexFromColor($color)
    {
        $colors = $this->getColors();

        $key = array_search(preg_replace('/\s+/', '', strtolower($color)), $colors);

        return ($key) ? $key : '000000';
    }

    private function getColors()
    {
        return array(
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
    }

    private function generateSkuId($string)
    {
        // http://stackoverflow.com/a/2103815/598424
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

}

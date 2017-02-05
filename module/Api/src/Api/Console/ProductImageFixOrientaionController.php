<?php

namespace Api\Console;

use Base\Console\BaseController;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ProductImageFixOrientaionController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        
        $this->file = $request->getParam('file');
        if (!$this->file) {
            return "plz provide file";
        }
        
        echo "Started processing";
        $this->import();

        return "Successfully imported";
    }

    public function import()
    {
        $missing = array();
        $row = 1;
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                echo "looping through records:  " . $row . "\n";

                try {
                    $productData = $this->isProductExists($data[0]);
                    if ($productData) {
                        $this->process($productData['image']);
                    } else {
                        $missing[] = $row;
                    }
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                $row++;
            }

            fclose($handle);
        }

        print_r($missing);
    }

    public function process($image)
    {
        $this->s3UploadService = $this->getServiceLocator()->get('S3UploadService');
        $this->client = $this->s3UploadService->getS3Object();
        $this->bucket = 'growsari';
        $this->prefix = 'SKU Images Hi-Res/';
        
        $this->resize($this->prefix . $image);
        
        echo "after loon\n";
    }
    
    public function resize($file)
    {
        try {
            $fileName = '';
            if (substr($file, 0, strlen($this->prefix)) == $this->prefix) {
                $fileName = substr($file, strlen($this->prefix));
            }
            
            if (!$fileName) {
                return;
            }
            
            echo $filePath = '/tmp/' . $fileName;
            $this->client->getObject(array(
                'Bucket' => $this->bucket,
                'Key'    => $file,
                'SaveAs' => $filePath
            ));
            echo "downloadd\n";
            $this->s3UploadService->resize($filePath, $filePath, 500, 500, 200, 200);            
            $this->uploadFile($filePath, 'uploads/product/'. $fileName);
                        
            $this->s3UploadService->resize($filePath, $filePath);            
            $this->uploadFile($filePath, 'uploads/product/thumb/'. $fileName);
            
            unlink($filePath);
            
        } catch (Aws\Exception\S3Exception $e) {
            echo "There was an error uploading the file ($fileName).\n";
        }
    }
    
    public function uploadFile($filePath, $fileName)
    {
        try {
            $s3Upload = $this->getServiceLocator()->get('S3UploadService');
            $s3Upload->moveNormalFile($filePath, $fileName, 'public-read');
            
            echo "Uploaded $fileName \n";
        } catch (Aws\Exception\S3Exception $e) {
            echo "There was an error uploading the file ($fileName).\n";
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

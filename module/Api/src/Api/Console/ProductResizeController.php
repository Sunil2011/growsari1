<?php

namespace Api\Console;

use Base\Console\BaseController;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ProductResizeController extends BaseController
{

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        
        $this->force = $request->getParam('force', 30);
        $this->start = $request->getParam('start');
        if (!$this->force) {
            return "If you want to upload local copy, use force";
        }
        
        $this->process();
        return "Successfully imported";
    }

    public function process()
    {
        $this->s3UploadService = $this->getServiceLocator()->get('S3UploadService');
        $this->client = $this->s3UploadService->getS3Object();
        $this->bucket = 'growsari';
        $this->prefix = 'uploads/product/';

        $iterator = $this->client->getIterator('ListObjects', array(
            'Bucket' => $this->bucket,
            "Prefix" => $this->prefix
        ));
        
        $skip = false;
        if ($this->start) {
            $skip = true;
        }
        foreach ($iterator as $object) {
            if ($this->start && $this->start == $object['Key']) {
                $skip = false;
            }
            
            if ($skip) {
                continue;
            }
            
            echo $object['Key']. "\n";
            $this->resize($object['Key']);
        }
        
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
            
            $filePath = '/tmp/' . $fileName;
            $this->client->getObject(array(
                'Bucket' => $this->bucket,
                'Key'    => $file,
                'SaveAs' => $filePath
            ));
            
            $this->s3UploadService->resize($filePath, $filePath);
            
            $this->uploadFile($filePath, $fileName);
            
            unlink($filePath);
            
        } catch (Aws\Exception\S3Exception $e) {
            echo "There was an error uploading the file ($fileName).\n";
        }
    }
    
    public function uploadFile($filePath, $fileName)
    {
        try {
            $s3Upload = $this->getServiceLocator()->get('S3UploadService');
            $s3Upload->moveNormalFile($filePath, 'uploads/product/thumb/'. $fileName, 'public-read');
            
            echo "Uploaded $fileName \n";
        } catch (Aws\Exception\S3Exception $e) {
            echo "There was an error uploading the file ($fileName).\n";
        }
    }
}

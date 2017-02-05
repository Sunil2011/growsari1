<?php

namespace Api\Console;

use Aws\S3\S3Client;
use Base\Console\BaseController;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Zend\Console\Request as ConsoleRequest;
use ZFTool\Diagnostics\Exception\RuntimeException;

class S3UploadController extends BaseController
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
        $this->path = $request->getParam('path', 'public/uploads/');
        $this->prefix = $request->getParam('prefix', 'public/');
        $this->acl = $request->getParam('acl', 'private');
        if (!$this->force) {
            return "If you want to upload local copy, use force";
        }
        
        $this->upload();
        return "Successfully imported";
    }

    public function upload()
    {
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
            $s3Upload = $this->getServiceLocator()->get('S3UploadService');
            $s3Upload->moveNormalFile($file, $fileName, $this->acl);
            
            echo "Uploaded $fileName \n";
        } catch (Aws\Exception\S3Exception $e) {
            echo "There was an error uploading the file ($file).\n";
            exit;
        }
    }
}

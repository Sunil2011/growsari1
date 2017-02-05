<?php

namespace Api\Service;

use Aws\S3\S3Client;
use Base\Handler\ImageException;
use Base\Handler\ImageHandler;

class S3UploadService extends ImageHandler
{

    protected $config;
    
    protected function moveFile()
    {
        try {
            $this->getS3Object()->putObject([
                'Bucket' => $this->config['aws']['bucket'],
                'Key' => $this->filePath,
                'Body' => fopen($this->file['tmp_name'], 'r'),
                'ACL' => 'public-read',
            ]);
        } catch (Aws\Exception\S3Exception $e) {
            throw new ImageException("image: unableToSave");
        }
    }
    
    public function moveNormalFile($source, $destination, $acl = 'private')
    {
        try {
            $this->getS3Object()->putObject([
                'Bucket' => $this->config['aws']['bucket'],
                'Key' => $destination,
                'Body' => fopen($source, 'r'),
                'ACL' => $acl,
            ]);
        } catch (Aws\Exception\S3Exception $e) {
            throw new ImageException("image: unableToSave");
        }
    }

    public function getS3Object()
    {
        if (!$this->config) {
            throw new ImageException("S3UploadService: config missing");
        }
        
        $this->s3 = new S3Client($this->config['aws']);
        
        return $this->s3;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

}

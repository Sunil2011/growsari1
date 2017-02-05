<?php

namespace Base\Handler;

use Exception;
use Imagick;

class ImageHandler
{

    private $maxSize = 5242880;
    private $allowedExts = array('jpg', 'jpeg', 'png', 'bmp', 'gif');

    /**
     * Constructor: initialize image
     *
     * @return void
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Move Uploaded photo
     */
    public function moveUploadedFile(&$file, $uploadDir, $allowedExts = null)
    {
        if ($allowedExts) {
            $this->allowedExts = $allowedExts;
        }

        $this->uploadDir = $uploadDir;
        if (is_uploaded_file($file['tmp_name'])) {
            $this->file = $file;
            $this->checkFileSize();
            $this->checkExtension();
            $this->createFileName();
            $this->moveFile();

            return array(
                'originalName' => $this->file['name'],
                'filename' => $this->newFileName,
                'extension' => $this->extension,
                'newName' => $this->newFileName . '.' . $this->extension
            );
        } else {
            throw new ImageNotSatisfiedException("image: notImage");
        }
    }

    /**
     * Resize an original image
     */
    public function resize($originalImage, $resizedImage, $maxWidth = 120, $maxHeight = 120, $minWidth = 40, $minHeight = 40)
    {
        if (file_exists($originalImage)) {

            //thumbnail creation
            try {

                list($orgWidth, $orgHeight) = getimagesize($originalImage);
                if ($orgWidth >= $minWidth && $orgHeight >= $minHeight) {
                    $thumb = new Imagick($originalImage);
                    if ($orgWidth >= $maxWidth || $orgHeight >= $maxHeight) {
                        if (!$maxWidth || !$maxHeight) {
                            $thumb->scaleImage($maxWidth, $maxHeight);
                        } else {
                            $thumb->scaleImage($maxWidth, $maxHeight, true); //If bestfit parameter is used both width and height must be given.
                        }
                    } else {
                        if ($orgWidth > $orgHeight) {
                            $width = 0;
                            $height = $orgHeight;
                        } else {
                            $height = 0;
                            $width = $orgWidth;
                        }
                        $thumb->scaleImage($width, $height);
                    }
                    $this->autorotate($thumb);
                    $thumb->writeImage($resizedImage);
                    $thumb->clear();
                    $thumb->destroy();
                } else {
                    /* Create empty canvas */
                    $canvas = new Imagick();

                    /* Canvas needs to be large enough to hold the both images */
                    $canvas->newImage($minWidth, $minHeight, "white", "jpeg");

                    $thumb = new Imagick($originalImage);
                    $offsetX = ($minWidth - $orgWidth) / 2;
                    $offsetY = ($minHeight - $orgHeight) / 2;

                    /* Composite the original image and the reflection on the canvas */
                    $canvas->compositeImage($thumb, \imagick::COMPOSITE_OVER, $offsetX, $offsetY);
                    $this->autorotate($thumb);
                    $canvas->writeImage($resizedImage);
                    $canvas->clear();
                    $canvas->destroy();
                }
            } catch (Exception $e) {
                throw new ImageException("image: unableToResize" . $e->getMessage());
            }
            return true;
        } else {
            throw new ImageNotSatisfiedException("image: fileNotExist");
        }
    }

    /**
     * Crop an original image
     */
    public function crop($originalImage, $resizedImage, $width = 270, $height = 270, $cropWidth = '', $cropHeight = '', $cropX = '', $cropY = '')
    {
        if (file_exists($originalImage)) {

            try {
                //thumbnail creation
                list($oldWidth, $oldHeight) = getimagesize($originalImage);
                $image = new Imagick($originalImage);
                $image->cropImage($cropWidth, $cropHeight, $cropX, $cropY);
                //$image->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
                $image->writeImage($resizedImage);
                $image->clear();
                $image->destroy();
            } catch (Exception $e) {
                throw new ImageException("image: unableToResize" . $e->getMessage());
            }

            return true;
        } else {
            throw new ImageNotSatisfiedException("image: fileNotExist");
        }
    }

    protected function checkFileSize()
    {
        if ($this->file['size'] > $this->maxSize) {
            throw new ImageNotSatisfiedException("image: sizeLimit");
        }
    }

    protected function checkExtension()
    {
        $this->orgFileName = preg_replace("/[^a-zA-Z0-9_.]/", '_', $this->file['name']);
        $this->extension = pathinfo($this->file['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($this->extension), $this->allowedExts)) {
            throw new ImageNotSatisfiedException("image: notExtension" . ' ' . implode(', ', $this->allowedExts));
        }
    }

    protected function createFileName()
    {
        $this->newFileName = uniqid() . time();
        $this->filePath = $this->uploadDir . $this->newFileName . '.' . $this->extension;
    }

    protected function moveFile()
    {
        try {
            move_uploaded_file($this->file['tmp_name'], $this->filePath);
        } catch (Exception $e) {
            throw new ImageException("image: unableToSave");
        }
    }

    protected function autorotate(Imagick $image)
    {
        switch ($image->getImageOrientation()) {
            case Imagick::ORIENTATION_TOPLEFT:
                break;
            case Imagick::ORIENTATION_TOPRIGHT:
                $image->flopImage();
                break;
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateImage("#000", 180);
                break;
            case Imagick::ORIENTATION_BOTTOMLEFT:
                $image->flopImage();
                $image->rotateImage("#000", 180);
                break;
            case Imagick::ORIENTATION_LEFTTOP:
                $image->flopImage();
                $image->rotateImage("#000", -90);
                break;
            case Imagick::ORIENTATION_RIGHTTOP:
                $image->rotateImage("#000", 90);
                break;
            case Imagick::ORIENTATION_RIGHTBOTTOM:
                $image->flopImage();
                $image->rotateImage("#000", 90);
                break;
            case Imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateImage("#000", -90);
                break;
            default: // Invalid orientation
                break;
        }
        
        $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
        
        return $image;
    }

}

class ImageException extends Exception
{
    
}

class ImageNotSatisfiedException extends Exception
{
    
}

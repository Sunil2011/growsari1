<?php

namespace Api\Console;

use Base\Console\BaseController;
use Memio\Memio\Config\Build;
use Memio\Model\File;
use Memio\Model\FullyQualifiedName;
use Memio\Model\Object;
use Zend\Db\Metadata\Metadata;

class TableGeneratorController extends BaseController
{

    protected $prettyPrinter;

    public function indexAction()
    {
        //return new ViewModel();
        $this->prettyPrinter = Build::prettyPrinter();
        $this->prettyPrinter->addTemplatePath(__DIR__ . '/../../../view/twig');

        $metadata = new Metadata($this->getAdapter());
        $tableNames = $metadata->getTableNames();
        foreach ($tableNames as $tableName) {
            echo "Generating table class for table: $tableName\n";
            $this->generateModelCode($tableName);
        }
    }

    public function generateModelCode($modelName)
    {
        $object = new Object('Api\\Table\\' . $this->camelize($modelName) . 'Table');
        $object->extend(new Object('Base\Table\BaseTable'));

        return $this->generateFile($modelName, $object);
    }

    public function generateFile($modelName, $object)
    {
        $fileName = 'module/Api/src/Api/Table/' . $this->camelize($modelName) . 'Table.php';
        $myFile = File::make('module/Api/src/Api/Table/' . $this->camelize($modelName) . 'Table')
                ->addFullyQualifiedName(new FullyQualifiedName('Base\Table\BaseTable'))
                ->setStructure($object);

        file_put_contents($fileName, $this->prettyPrinter->generateCode($myFile));
        echo "Saved file here: $fileName\n";
    }

}

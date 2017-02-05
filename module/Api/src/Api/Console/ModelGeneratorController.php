<?php

namespace Api\Console;

use Base\Console\BaseController;
use Memio\Memio\Config\Build;
use Memio\Model\Argument;
use Memio\Model\Contract;
use Memio\Model\File;
use Memio\Model\FullyQualifiedName;
use Memio\Model\Method;
use Memio\Model\Object;
use Memio\Model\Property;
use Zend\Console\Request as ConsoleRequest;
use Zend\Db\Metadata\Metadata;
use ZFTool\Diagnostics\Exception\RuntimeException;

class ModelGeneratorController extends BaseController
{

    protected $prettyPrinter;
    protected $tableName;

    public function indexAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->table = $request->getParam('table');

        $this->prettyPrinter = Build::prettyPrinter();
        $this->prettyPrinter->addTemplatePath(__DIR__ . '/../../../view/twig');

        $metadata = new Metadata($this->getAdapter());
        $tableNames = $metadata->getTableNames();
        foreach ($tableNames as $tableName) {
            if ($this->table && $tableName !== $this->table) {
                continue;
            }

            echo "Generating model class for table: $tableName\n";
            $table = $metadata->getTable($tableName);
            $this->generateModelCode($tableName, $table->getColumns());
        }
    }

    public function generateModelCode($modelName, $columns)
    {
        $this->tableName = $modelName;

        $object = new Object('Api\\Model\\' . $this->camelize($modelName));

        if ($this->tableName == 'account') {
            $object->implement(new Contract('ZfcUser\Entity\UserInterface'));
        } else {
            $object->extend(new Object('Base\Model\BaseModel'));
        }

        foreach ($columns as $columnObj) {
            $column = $columnObj->getName();
            $property = Property::make($this->camelizeColumn($column));
            $property->makePublic(); //to avoid table gateway issue..chck it later

            $object->addProperty($property);
            $getter = Method::make('get' . $this->camelize($column))->setBody('return $this->' . $this->camelizeColumn($column) . ';');
            $object->addMethod($getter);

            $setterArgument = Argument::make('string', $this->camelizeColumn($column));
            $setter = Method::make('set' . $this->camelize($column))->addArgument($setterArgument)->setBody('$this->' . $this->camelizeColumn($column) . ' = $' . $this->camelizeColumn($column) . ';');
            $object->addMethod($setter);
        }

        return $this->generateFile($modelName, $object);
    }

    public function generateFile($modelName, $object)
    {
        $fileName = 'module/Api/src/Api/Model/' . $this->camelize($modelName) . '.php';
        $myFile = File::make('module/Api/src/Api/Model/' . $this->camelize($modelName));

        if ($this->tableName == 'account') {
            $myFile = $myFile->addFullyQualifiedName(new FullyQualifiedName('ZfcUser\Entity\UserInterface'));
        } else {
            $myFile = $myFile->addFullyQualifiedName(new FullyQualifiedName('Base\Model\BaseModel'));
        }

        $myFile = $myFile->setStructure($object);

        file_put_contents($fileName, $this->prettyPrinter->generateCode($myFile));
        echo "Saved file here: $fileName\n";
    }

    public function camelizeColumn($column)
    {
        if ($this->tableName == 'account') {
            return $this->camelize($column, false);
        }

        return $column;
    }

}

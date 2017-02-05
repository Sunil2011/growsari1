<?php

namespace Base\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class TableGatewayAbstractFactory implements AbstractFactoryInterface
{

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $array = explode('\\', $requestedName);
        return (count($array) === 3 && (fnmatch('*TableGateway', $requestedName))) ? true : false;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $array = explode('\\', $requestedName);
        $entityName = substr($array[2], 0, strpos($array[2], 'Table'));
        
        $tableName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $entityName)), '_');
        $entityName = $array[0] . '\\Model\\' . $entityName;
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        
        $resultSetPrototype = new ResultSet();
        if (class_exists($entityName)) {
            $resultSetPrototype->setArrayObjectPrototype(new $entityName());
        }
        
        return new TableGateway($tableName, $dbAdapter, null, $resultSetPrototype);
    }

}

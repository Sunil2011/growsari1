<?php

use Api\Model\Account;
use Api\Service\LoyalityService;
use Api\Service\OrderService;
use Api\Service\S3UploadService;
use ZfcUserUserIdToId\Mapper\User;

return array(
    'factories' => array(
        'OrderService' => function ($sm) {
            $serviceController = new OrderService($sm);
            return $serviceController;
        },
                
        'LoyalityService' => function ($sm) {
            $serviceController = new LoyalityService($sm);
            return $serviceController;
        },
                
        'S3UploadService' => function ($sm) {
            $serviceController = new S3UploadService($sm);
            $serviceController->setConfig($sm->get('Config'));
            return $serviceController;
        },
                    
        'zfcuser_user_mapper' => function ($sm) {
            $options = $sm->get('zfcuser_module_options');

            $mapper = new User();
            $mapper->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
            $mapper->setEntityPrototype(new Account());
            $mapper->setHydrator($sm->get('zfcuser_user_hydrator'));
            $mapper->setTableName($options->getTableName());

            return $mapper;
        }
    ),
);


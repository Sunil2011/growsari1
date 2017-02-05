<?php

use Base\Utils\Process;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

return [
    'factories' => [
        'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        
        'Logger' => function () {
            $log = new Logger();
            $writer = new Stream('data/logs/base');
            $log->addWriter($writer);
            
            return $log;
        },
                
        'Base\Utils\Process' => function ($sm) {
            $process = new Process($sm->get('Request'));
            return $process;
        },
    ]
];



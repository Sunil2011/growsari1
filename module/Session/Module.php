<?php

namespace Session;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $session = $e->getApplication()
                ->getServiceManager()
                ->get('Zend\Session\SessionManager');

        $cfg = $e->getApplication()->getServiceManager()->get('Config');
        $cfgSession = $cfg['session'];

        $tableGateway = new TableGateway($cfgSession['table_name'], $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter'));
        $saveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
        $session->setSaveHandler($saveHandler);

        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
            $session->regenerateId(true);
            $container->init = 1;
        }
    }

    public function getConfig()
    {
        return array(
            'service_manager' => array(
                'factories' => array(
                    'Zend\Session\SessionManager' => function ($sm) {
                        $config = $sm->get('config');
                        if (isset($config['session'])) {
                            $session = $config['session'];

                            $sessionConfig = null;
                            if (isset($session['config'])) {
                                $class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                                $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                                $sessionConfig = new $class();
                                $sessionConfig->setOptions($options);
                            }

                            $sessionStorage = null;
                            if (isset($session['storage'])) {
                                $class = $session['storage'];
                                $sessionStorage = new $class();
                            }

                            $sessionSaveHandler = null;
                            if (isset($session['save_handler'])) {
                                $sessionSaveHandler = $sm->get($session['save_handler']);
                            }

                            $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                            if (isset($session['validator'])) {
                                $chain = $sessionManager->getValidatorChain();
                                foreach ($session['validator'] as $validator) {
                                    $validator = new $validator();
                                    $chain->attach('session.validate', array($validator, 'isValid'));
                                }
                            }
                        } else {
                            $sessionManager = new SessionManager();
                        }
                        
                        Container::setDefaultManager($sessionManager);
                        
                        return $sessionManager;
                    },
                )
            )
        );
    }

}
        
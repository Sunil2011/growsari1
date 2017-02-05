<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application;

return array(
    'router' => array(
        'routes' => array(
            'swagger-resources' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/swagger/docs',
                    'defaults' => array(
                        'controller' => 'SwaggerModule\Controller\Documentation',
                        'action' => 'display'
                    )
                )
            ),
            'swagger-resource-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/swagger/docs/:resource',
                    'defaults' => array(
                        'controller' => 'SwaggerModule\Controller\Documentation',
                        'action' => 'details'
                    )
                )
            ),
            'api-index-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/index[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Index',
                    ),
                ),
            ),
            'product-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/product[/:id][/:action]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Product',
                    ),
                ),
            ),
            'order-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/order[/:id][/:action][/:type]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Order',
                    ),
                ),
            ),
            'category-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/category[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Category',
                    ),
                ),
            ),
            'brand-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/brand[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Brand',
                    ),
                ),
            ),
            'api-survey-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/survey[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Survey',
                    ),
                ),
            ),
            'api-salesperson-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/salesperson[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Salesperson',
                    ),
                ),
            ),
            'api-store-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/store[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Store',
                    ),
                ),
            ),
            'api-feedback-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/feedback[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Feedback',
                    ),
                ),
            ),
            'api-config-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/config[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Config',
                    ),
                ),
            ),
            'api-warehouse-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/warehouse[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Warehouse',
                    ),
                ),
            ),
            'api-shipper-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/shipper[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Shipper',
                    ),
                ),
            ),
            'api-shipper-team-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/shipper/team[/:id][/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\ShipperTeam',
                    ),
                ),
            ),
            'api-auth-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/auth[/:action]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Authentication',
                        'action' => 'index',
                    ),
                ),
            ),
            'api-register-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/account[/:action]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Account',
                        'action' => 'create',
                    ),
                ),
            ),
            'api-globe-rest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/globe[/:action]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Globe',
                        'action' => 'create',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Api\Console\ModelGenerator' => 'Api\Console\ModelGeneratorController',
            'Api\Console\TableGenerator' => 'Api\Console\TableGeneratorController',
            'Api\Console\AutoCancel' => 'Api\Console\AutoCancelController',
            'Api\Console\ProductImport' => 'Api\Console\ProductImportController',
            'Api\Console\ProductImportSKU' => 'Api\Console\ProductImportSKUController',
            'Api\Console\ProductImportImage' => 'Api\Console\ProductImportImageController',
            'Api\Console\PriceUpdate' => 'Api\Console\PriceUpdateController',
            'Api\Console\PriceUpdateMissing' => 'Api\Console\PriceUpdateMissingController',
            'Api\Console\ItemCodeUpdate' => 'Api\Console\ItemCodeUpdateController',
            'Api\Console\CreateStoreAccount' => 'Api\Console\CreateStoreAccountController',
            'Api\Console\PNSTest' => 'Api\Console\PNSTestController',
            'Api\Console\ChangePassword' => 'Api\Console\ChangePasswordController',
            'Api\Console\UploadSurvey' => 'Api\Console\UploadSurveyController',
            'Api\Console\LoyaltyPointCreditForOrder' => 'Api\Console\LoyaltyPointCreditForOrderController',
            'Api\Console\S3Upload' => 'Api\Console\S3UploadController',
            'Api\Console\PriceUpdateHistoryImport' => 'Api\Console\PriceUpdateHistoryImportController',
            'Api\Console\BarcodeUpdate' => 'Api\Console\BarcodeUpdateController',
            'Api\Console\SendPNS' => 'Api\Console\SendPNSController',
            'Api\Console\ProductResize' => 'Api\Console\ProductResizeController',
            'Api\Console\ProductImageFixOrientaion' => 'Api\Console\ProductImageFixOrientaionController',
            
            'Api\Controller\Index' => 'Api\Controller\IndexController',
            'Api\Controller\Authentication' => 'Api\Controller\AuthenticationController',
            'Api\Controller\Account' => 'Api\Controller\AccountController',
            'Api\Controller\Product' => 'Api\Controller\ProductController',
            'Api\Controller\Store' => 'Api\Controller\StoreController',
            'Api\Controller\Order' => 'Api\Controller\OrderController',
            'Api\Controller\Category' => 'Api\Controller\CategoryController',
            'Api\Controller\Brand' => 'Api\Controller\BrandController',
            'Api\Controller\Survey' => 'Api\Controller\SurveyController',
            'Api\Controller\Salesperson' => 'Api\Controller\SalespersonController',
            'Api\Controller\Feedback' => 'Api\Controller\FeedbackController',
            'Api\Controller\Config' => 'Api\Controller\ConfigController',
            'Api\Controller\Warehouse' => 'Api\Controller\WarehouseController',
            'Api\Controller\Shipper' => 'Api\Controller\ShipperController',
            'Api\Controller\ShipperTeam' => 'Api\Controller\ShipperTeamController',
            'Api\Controller\Globe' => 'Api\Controller\GlobeController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'template_map' => array(
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'api' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'model-generator' => array(
                    'options' => array(
                        'route' => 'model-generator [--table=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\ModelGenerator',
                            'action' => 'index'
                        )
                    )
                ),
                'table-generator' => array(
                    'options' => array(
                        'route' => 'table-generator',
                        'defaults' => array(
                            'controller' => 'Api\Console\TableGenerator',
                            'action' => 'index'
                        )
                    )
                ),
                'auto-cancel' => array(
                    'options' => array(
                        'route' => 'auto-cancel',
                        'defaults' => array(
                            'controller' => 'Api\Console\AutoCancel',
                            'action' => 'index'
                        )
                    )
                ),
                'upload-survey' => array(
                    'options' => array(
                        'route' => 'upload-survey [--file=] [--account_id=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\UploadSurvey',
                            'action' => 'index'
                        )
                    )
                ),
                'product-resize' => array(
                    'options' => array(
                        'route' => 'product-resize --force=1 [--start=] ',
                        'defaults' => array(
                            'controller' => 'Api\Console\ProductResize',
                            'action' => 'index'
                        )
                    )
                ), 
                'product-image-fix-orientation' => array(
                    'options' => array(
                        'route' => 'product-image-fix-orientation [--file=] ',
                        'defaults' => array(
                            'controller' => 'Api\Console\ProductImageFixOrientaion',
                            'action' => 'index'
                        )
                    )
                ),
                'product-import' => array(
                    'options' => array(
                        'route' => 'product-import [--file=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\ProductImport',
                            'action' => 'index'
                        )
                    )
                ),
                'product-import-sku' => array(
                    'options' => array(
                        'route' => 'product-import-sku [--file=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\ProductImportSKU',
                            'action' => 'index'
                        )
                    )
                ),
                'product-import-image' => array(
                    'options' => array(
                        'route' => 'product-import-image [--file=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\ProductImportImage',
                            'action' => 'index'
                        )
                    )
                ),
                'item-code-update' => array(
                    'options' => array(
                        'route' => 'item-code-update [--file=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\ItemCodeUpdate',
                            'action' => 'index'
                        )
                    )
                ),
                'barcode-update' => array(
                    'options' => array(
                        'route' => 'barcode-update [--file=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\BarcodeUpdate',
                            'action' => 'index'
                        )
                    )
                ),
                'price-update' => array(
                    'options' => array(
                        'route' => 'price-update [--file=] [--date=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\PriceUpdate',
                            'action' => 'index'
                        )
                    )
                ),
                'price-update-missing' => array(
                    'options' => array(
                        'route' => 'price-update-missing [--file=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\PriceUpdateMissing',
                            'action' => 'index'
                        )
                    )
                ),
                'price-update-history-import' => array(
                    'options' => array(
                        'route' => 'price-update-history-import [--file=] [--date=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\PriceUpdateHistoryImport',
                            'action' => 'index'
                        )
                    )
                ),
                'create-store' => array(
                    'options' => array(
                        'route' => 'create-store-account  [--no=] [--salesperson=] [--warehouse_shipper_id=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\CreateStoreAccount',
                            'action' => 'index'
                        )
                    )
                ),
                'change-password' => array(
                    'options' => array(
                        'route' => 'change-password  [--file=] [--salesperson=] [--use-existing=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\ChangePassword',
                            'action' => 'index'
                        )
                    )
                ),
                'pns-test' => array(
                    'options' => array(
                        'route' => 'pns-test  [--order_id=]  [--status=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\PNSTest',
                            'action' => 'index'
                        )
                    )
                ),
                'send-pns' => array(
                    'options' => array(
                        'route' => 'send-pns  [--message=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\SendPNS',
                            'action' => 'index'
                        )
                    )
                ),
                'loyalty-point-credit' => array(
                    'options' => array(
                        'route' => 'loyalty-point-credit [--order_id=] ',
                        'defaults' => array(
                            'controller' => 'Api\Console\LoyaltyPointCreditForOrder',
                            'action' => 'index'
                        )
                    )
                ),
                's3-upload' => array(
                    'options' => array(
                        'route' => 's3-upload --force=1 [--path=] [--prefix=]  [--acl=]',
                        'defaults' => array(
                            'controller' => 'Api\Console\S3Upload',
                            'action' => 'index'
                        )
                    )
                ),
            ),
        ),
    ),
);

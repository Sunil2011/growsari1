<?php
return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'Base\Factory\TableGatewayAbstractFactory',
            'Base\Factory\TableAbstractFactory',
        ),
    ),
    
    'view_helpers' => array(
        'factories' => array(
            'currentRoute' => 'Base\Factory\View\Helper\CurrentRouteFactory',
        ),
        'invokables'=> array(
            
        )
    ),
);
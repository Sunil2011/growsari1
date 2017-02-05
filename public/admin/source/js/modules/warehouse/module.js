define(['angular',
    'angular-couch-potato',
    'angular-bootstrap',
    'angular-ui-router'], function (ng, couchPotato) {

    "use strict";


    var module = ng.module('app.warehouse', ['ui.router', 'ui.bootstrap']);

    couchPotato.configureApp(module);

    module.config(['$stateProvider', '$couchPotatoProvider', '$urlRouterProvider', function ($stateProvider, $couchPotatoProvider, $urlRouterProvider) {
            
            $urlRouterProvider.otherwise('/warehouse/new-order');
            
            $stateProvider
                .state('app.warehouse', {
                    url: '/warehouse/new-order/{page}',
                    templateUrl: './js/modules/warehouse/views/new-orders.html',
                    controller: 'newOrderController',
                    controllerAs:'newOrder',
                    data: {
                        authRequired : true,
                        types: ['WAREHOUSE'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/warehouse/controllers/newOrderController'
                        ])
                    }
                })
                .state('app.replace_order', {
                    url: '/warehouse/replacement-order/{page}',
                    templateUrl: './js/modules/warehouse/views/replacement-orders.html',
                    controller: 'replacementOrderController',
                    controllerAs:'replacementOrder',
                    data: {
                        authRequired : true,
                        types: ['WAREHOUSE'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/warehouse/controllers/replacementOrderController'
                        ])
                    }
                })
                .state('app.confirm_order', {
                    url: '/warehouse/confirm-order/{page}',
                    templateUrl: './js/modules/warehouse/views/confirm-orders.html',
                    controller: 'confirmedOrderController',
                    controllerAs:'confirmOrder',
                    data: {
                        authRequired : true,
                        types: ['WAREHOUSE'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/warehouse/controllers/confirmedOrderController'
                        ])
                    }
                })
                .state('app.pack', {
                    url: '/warehouse/pack-order/{page}',
                    templateUrl: './js/modules/warehouse/views/pack-orders.html',
                    controller: 'packOrderController',
                    controllerAs:'packOrder',
                    data: {
                        authRequired : true,
                        types: ['WAREHOUSE'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/warehouse/controllers/packOrderController'
                        ])
                    }
                })
                .state('app.wh_order', {
                    url: '/warehouse/order/{page}',
                    templateUrl: './js/modules/warehouse/views/all-orders.html',
                    controller: 'orderController',
                    controllerAs:'allOrder',
                    data: {
                        authRequired : true,
                        types: ['WAREHOUSE'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/warehouse/controllers/orderController'
                        ])
                    }
                });
        }]);

    module.run(function ($couchPotato) {
        module.lazy = $couchPotato;
    });

    console.log("layout", module);
    return module;
});


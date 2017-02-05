define(['angular',
    'angular-couch-potato',
    'angular-bootstrap',
    'angular-ui-router'], function (ng, couchPotato) {

    "use strict";


    var module = ng.module('app.callcenter', ['ui.router', 'ui.bootstrap']);

    couchPotato.configureApp(module);

    module.config(['$stateProvider', '$couchPotatoProvider', '$urlRouterProvider', function ($stateProvider, $couchPotatoProvider, $urlRouterProvider) {
            
            $urlRouterProvider.otherwise('/callcenter/order');
            
            $stateProvider
                .state('app.callcenter', {
                    url: '/callcenter/order/{page}',
                    templateUrl: './js/modules/callcenter/views/orders.html',
                    controller: 'orderController',
                    controllerAs:'order',
                    data: {
                        authRequired : true,
                        types: ['CALLCENTER'],
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
                            './js/modules/admin/services/productDataService' ,
                            './js/modules/admin/services/categoryDataService' ,
                            './js/modules/admin/services/brandDataService' ,
                            './js/modules/admin/services/storeDataService' ,
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/callcenter/controllers/orderController',
                            './js/modules/callcenter/controllers/orderAddItemsModalController',
                            './js/modules/admin/controllers/updateWalletModalController'
                        ])
                    }
                })
                .state('app.order_add', {
                    url: '/callcenter/order-add',
                    templateUrl: './js/modules/callcenter/views/add-order.html',
                    controller: 'orderAddController',
                    controllerAs:'orderAdd',
                    data: {
                        authRequired : true,
                        types: ['CALLCENTER'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/admin/services/productDataService' ,
                            './js/modules/admin/services/categoryDataService',
                            './js/modules/admin/services/brandDataService',
                            './js/modules/admin/services/storeDataService',
                            './js/modules/callcenter/controllers/orderAddController',
                            './js/modules/callcenter/controllers/orderAddItemsModalController'
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
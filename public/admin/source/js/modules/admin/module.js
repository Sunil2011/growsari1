define(['angular',
    'angular-couch-potato',
    'angular-bootstrap',
    'angular-ui-router'], function (ng, couchPotato) {

    "use strict";


    var module = ng.module('app.admin', ['ui.router', 'ui.bootstrap']);

    couchPotato.configureApp(module);

    module.config(['$stateProvider', '$couchPotatoProvider', '$urlRouterProvider', function ($stateProvider, $couchPotatoProvider, $urlRouterProvider) {
            $stateProvider
                .state('app.admin', {
                    url: '/gs/products/{page}',
                    params: {
                     category_id : null,
                     brand_id : null,
                     search : null
                    },
                    templateUrl: './js/modules/admin/views/product.html',
                    controller: 'productController',
                    controllerAs:'product',
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/productDataService' ,
                            './js/modules/admin/services/categoryDataService' ,
                            './js/modules/admin/services/brandDataService' ,
                            './js/modules/admin/controllers/productController'
                        ])
                    }
                })
                .state('app.brand', {
                    url: '/gs/brands/{page}',
                    templateUrl: './js/modules/admin/views/brand.html',
                    controller: 'brandController',
                    controllerAs:'brand',
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/brandDataService' ,
                            './js/modules/admin/controllers/brandController'
                        ])
                    }
                })
                .state('app.brandAddEdit',{
                    url: '/gs/brands/add-edit/{brand_id}',
                    templateUrl : './js/modules/admin/views/brand-add-edit.html',
                    controller : 'brandAddEditController',
                    controllerAs :'brdUpdate' ,
                    params: {
                    },
                    data : {
                        authRequired : true ,
                        types : ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve : {
                        deps : $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/brandDataService',
                            './js/modules/layout/directives/fileModel',
                            './js/modules/admin/controllers/brandAddEditController'
                        ])
                    }
                })
                .state('app.category', {
                    url: '/gs/categories/{page}',
                    templateUrl: './js/modules/admin/views/category.html',
                    controller: 'categoryController',
                    controllerAs:'category',
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/categoryDataService' ,
                            './js/modules/admin/controllers/categoryController'
                        ])
                    }
                })
                .state('app.categoryAddEdit',{
                    url: '/gs/categories/add-edit/{category_id}',
                    templateUrl : './js/modules/admin/views/category-add-edit.html',
                    controller : 'categoryAddEditController',
                    controllerAs :'catUpdate' ,
                    params: {
                    },
                    data : {
                        authRequired : true ,
                        types : ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve : {
                        deps : $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/categoryDataService',
                            './js/modules/layout/directives/fileModel',
                            './js/modules/admin/controllers/categoryAddEditController'
                        ])
                    }
                })
                .state('app.productAddEdit',{
                    url: '/gs/products/add-edit/{product_id}',
                    templateUrl : './js/modules/admin/views/product-add-edit.html',
                    controller : 'productAddEditController',
                    controllerAs :'prdUpdate' ,
                    params: { 
                    },
                    data : {
                        authRequired : true ,
                        types : ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve : {
                        deps : $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/productDataService',
                            './js/modules/admin/services/categoryDataService',
                            './js/modules/admin/services/brandDataService',
                            './js/modules/admin/services/productService',
                            './js/modules/admin/controllers/productAddEditController'
                        ])
                    }
                })
                .state('app.gs_order', {
                    url: '/gs/order/{page}',
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    templateUrl: './js/modules/admin/views/order-list.html',
                    controller: 'orderController',
                    controllerAs:'order',
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/admin/controllers/orderController'

                        ])
                    }
                })
                .state('app.store',{
                    url: '/gs/stores/{page}',
                    templateUrl : './js/modules/admin/views/store.html',
                    controller : 'storeController',
                    controllerAs :'store' ,
                    params: { 
                      search: null,
                      page: null                      
                    },
                    data : {
                        authRequired : true ,
                        types : ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve : {
                        deps : $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/controllers/storeController',
                            './js/modules/admin/services/storeDataService',
                            './js/modules/admin/controllers/updateWalletModalController'
                        ])
                    }
                })
                .state('app.storeAddEdit',{
                    url: '/gs/stores/add-edit/{store_id}',
                    templateUrl : './js/modules/admin/views/store-add-edit.html',
                    controller : 'storeAddEditController',
                    controllerAs :'storeAddEdit' ,
                    params: { 
                    },
                    data : {
                        authRequired : true ,
                        types : ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve : {
                        deps : $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/controllers/storeAddEditController',
                            './js/modules/admin/services/storeDataService',
                            './js/modules/layout/directives/fileModel',
                        ])
                    }
                })
                .state('app.config', {
                    url: '/gs/config/{page}',
                    templateUrl: './js/modules/admin/views/config.html',
                    controller: 'configController',
                    controllerAs:'config',
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/configDataService' ,
                            './js/modules/admin/controllers/configController'
                        ])
                    }
                })
                .state('app.configAddEdit',{
                    url: '/gs/config/add-edit/{id}',
                    templateUrl : './js/modules/admin/views/config-add-edit.html',
                    controller : 'configAddEditController',
                    controllerAs :'configUpdate' ,
                    params: {
                    },
                    data : {
                        authRequired : true ,
                        types : ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve : {
                        deps : $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/configDataService',
                            './js/modules/admin/controllers/configAddEditController'
                        ])
                    }
                })
                .state('app.salesperson', {
                    url: '/gs/salesperson/',
                    templateUrl: './js/modules/admin/views/salesperson.html',
                    controller: 'salespersonController',
                    controllerAs:'sales',
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/salesDataService' ,
                            './js/modules/admin/controllers/salespersonController'
                        ])
                    }
                })
                .state('app.salespersonReport', {
                    url: '/gs/salesperson/report/{page}',
                    templateUrl: './js/modules/admin/views/salesperson-report.html',
                    controller: 'salespersonReportController',
                    controllerAs:'salesRep',
                    params: {
                        salesperson_id : null,
                        start_date : null,
                        end_date : null,
                        report_type : 'briefRep'
                    },
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/salesDataService' ,
                            './js/modules/admin/controllers/salespersonReportController'
                        ])
                    }
                })
                .state('app.salespersonDetReport', {
                    url: '/gs/salesperson/det-report/{salesperson_id}/{page}',
                    templateUrl: './js/modules/admin/views/salesperson-report.html',
                    controller: 'salespersonReportController',
                    controllerAs:'salesRep',
                    params: {
                        salesperson_id : null,
                        start_date : null,
                        end_date : null,
                        report_type : 'detailRep'
                    },
                    data: {
                        authRequired : true,
                        types: ['GROWSARI'],
                        roles : ['SUPER_ADMIN']
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/admin/services/salesDataService' ,
                            './js/modules/admin/controllers/salespersonReportController'
                        ])
                    }
                })
        }]);

    module.run(function ($couchPotato) {
        module.lazy = $couchPotato;
    });

    console.log("layout", module);
    return module;
});


define(['angular',
    'angular-couch-potato',
    'angular-bootstrap',
    'angular-ui-router',
    'angular-google-maps',
    'angular-simple-logger'
  ], function (ng, couchPotato) {

    "use strict";

    var module = ng.module('app.shipping', ['ui.router', 'ui.bootstrap', 'uiGmapgoogle-maps', 'nemLogging']);

    couchPotato.configureApp(module);

    module.config(['$stateProvider', '$couchPotatoProvider', '$urlRouterProvider', function ($stateProvider, $couchPotatoProvider, $urlRouterProvider) {
            $stateProvider
                .state('app.pick_up', {
                    url: '/shipping/packed/{page}',
                    templateUrl: './js/modules/shipping/views/packed-orders.html',
                    controller: 'packedOrderController',
                    controllerAs:'packed',
                    data: {
                        authRequired : true,
                        types: ['SHIPPER'],
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
                            './js/modules/shipping/controllers/packedOrderController'
                        ])
                    }
                })
                .state('app.dispatch', {
                    url: '/shipping/dispatched/{page}',
                    templateUrl: './js/modules/shipping/views/dispatch-orders.html',
                    controller: 'dispatchOrderController',
                    controllerAs:'dispatch',
                    data: {
                        authRequired : true,
                        types: ['SHIPPER'],
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
                            './js/modules/shipping/controllers/dispatchOrderController'
                        ])
                    }
                })
                .state('app.deliver', {
                    url: '/shipping/delivered/{page}',
                    templateUrl: './js/modules/shipping/views/delivered-orders.html',
                    controller: 'deliveredOrderController',
                    controllerAs:'delivered',
                    data: {
                        authRequired : true,
                        types: ['SHIPPER'],
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
                            './js/modules/shipping/controllers/deliveredOrderController'
                        ])
                    }
                })
                .state('app.shipper_confirmed_order', {
                    url: '/shipping/confirmed-order/{page}',
                    templateUrl: './js/modules/shipping/views/confirmed-orders.html',
                    controller: 'confirmedOrderController',
                    controllerAs:'penOrder',
                    data: {
                        authRequired : true,
                        types: ['SHIPPER'],
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
                            './js/modules/shipping/controllers/confirmedOrderController'
                        ])
                    }
                })
                .state('app.shipper_team', {
                  url: '/shipping/team/',
                  templateUrl : './js/modules/shipping/views/shipper.html',
                  controller : 'shipperController',
                  controllerAs :'shipperTeam' ,
                  params: { 
                  },
                  data: {
                    authRequired: true,
                    types: ['SHIPPER'],
                    roles : ['SUPER_ADMIN']
                  },
                  resolve: {
                    deps: $couchPotatoProvider.resolveDependencies([
                      './js/modules/shipping/controllers/shipperController',
                      './js/modules/shipping/services/shipperTeamDataService',
                      './js/modules/layout/directives/fileModel'
                    ])
                  }
                })
                .state('app.shipper_teamAddEdit', {
                  url: '/shipping/team/add',
                  templateUrl : './js/modules/shipping/views/shipper-add-edit.html',
                  controller : 'shipperAddEditController',
                  controllerAs :'shipperAddEdit',
                  params: { 
                  },
                  data: {
                    authRequired: true,
                    types: ['SHIPPER'],
                    roles : ['SUPER_ADMIN']
                  },
                  resolve: {
                    deps: $couchPotatoProvider.resolveDependencies([
                      './js/modules/shipping/controllers/shipperAddEditController',
                      './js/modules/shipping/services/shipperTeamDataService',
                      './js/modules/admin/services/storeDataService',
                      './js/modules/layout/directives/fileModel'
                    ])
                  }
                })
                .state('app.shipper_assign_shipper', {
                    url: '/shipping/assign-shipper/{page}',
                    templateUrl: './js/modules/shipping/views/assign-shipper.html',
                    controller: 'assignShipperController',
                    controllerAs:'assignShipper',
                    data: {
                        authRequired : true,
                        types: ['SHIPPER'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/shipping/services/shipperTeamDataService',
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/shipping/controllers/assignShipperController',
                            './js/modules/shipping/controllers/assignShipperBoxModalController',
                        ])
                    }
                })
                .state('app.shipper_assign_shipper_map', {
                    url: '/shipping/assign-shipper/map/{page}',
                    templateUrl: './js/modules/shipping/views/assign-shipper-map.html',
                    controller: 'assignShipperMapController',
                    controllerAs:'assignShipperMap',
                    data: {
                        authRequired : true,
                        types: ['SHIPPER'],
                        roles : ['SUPER_ADMIN']
                    },
                    params: {
                        search: null,
                        delivery_date: null,
                        sort_by: null
                    },
                    resolve: {
                        deps: $couchPotatoProvider.resolveDependencies([
                            './js/modules/shipping/services/shipperTeamDataService',
                            './js/modules/warehouse/services/orderDataService',
                            './js/modules/warehouse/services/orderService',
                            './js/modules/warehouse/directives/orderListDirective',
                            './js/modules/shipping/controllers/assignShipperMapController',
                            './js/modules/shipping/controllers/assignShipperBoxModalController'
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


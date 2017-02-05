define(['angular',
  'angular-couch-potato',
  'angular-ui-router',
  'angular-bootstrap'
], function (angular, couchPotato, uiRouter, bootstrap) {

  "use strict";


  var module = angular.module('app.layout', ['ui.router', 'ui.bootstrap']);


  couchPotato.configureApp(module);

  module.config(['$stateProvider', '$couchPotatoProvider', '$urlRouterProvider', 'USER_TYPES', 'USER_ROLES', function ($stateProvider, $couchPotatoProvider, $urlRouterProvider, USER_TYPES, USER_ROLES) {
      
      $stateProvider
      .state('app', {
        abstract: true,
        template: '<ui-view></ui-view>',
        data: {
          authRequired : true,
          types: Object.keys(USER_TYPES),
          roles: Object.keys(USER_ROLES)
        },
        resolve: {
            deps: $couchPotatoProvider.resolveDependencies([
               './js/modules/layout/directives/gsRating'
            ])
        }
      })
      .state('app.home', {
        url: '/',
        templateUrl: './js/modules/layout/views/test.html',
        controller: 'AppController',
        resolve: {
            deps: $couchPotatoProvider.resolveDependencies([
               './js/modules/layout/controllers/appController'
            ])
        }
      });
     
      $urlRouterProvider.otherwise('/');
    }]);

  module.run(function ($couchPotato) {
    module.lazy = $couchPotato;
  });
  
  console.log("layout", module);
  return module;
});


define(['angular',
  'angular-couch-potato',
  'angular-bootstrap',
  'angular-ui-router'], function (ng, couchPotato) {

  "use strict";


  var module = ng.module('app.salesperson', ['ui.router', 'ui.bootstrap']);

  couchPotato.configureApp(module);

  module.config(['$stateProvider', '$couchPotatoProvider', '$urlRouterProvider', function ($stateProvider, $couchPotatoProvider, $urlRouterProvider) {


      $stateProvider
              .state('app.salesperson_survey', {
                url: '/salesperson/survey/{page}',
                templateUrl: './js/modules/salesperson/views/survey.html',
                controller: 'surveyController',
                controllerAs: 'survey',
                params: {
                    search: null
                },
                data: {
                  authRequired: true,
                  types: ['SALESPERSON']
                },
                resolve: {
                  deps: $couchPotatoProvider.resolveDependencies([
                    './js/modules/salesperson/controllers/surveyController',
                    './js/modules/salesperson/services/surveyDataService'
                  ])
                }
              })
              .state('app.salesperson_surveyEdit', {
                url: '/salesperson/survey/edit/{survey_id}',
                templateUrl : './js/modules/salesperson/views/survey-edit.html',
                controller : 'surveyEditController',
                controllerAs :'surveyEdit' ,
                params: { 
                },
                data: {
                  authRequired: true,
                  types: ['SALESPERSON']
                },
                resolve: {
                  deps: $couchPotatoProvider.resolveDependencies([
                    './js/modules/salesperson/controllers/surveyEditController',
                    './js/modules/salesperson/services/surveyDataService',
                    './js/modules/admin/services/salesDataService',
                    './js/modules/layout/directives/fileModel'
                  ])
                }
              })
              ;
    }]);

  module.run(function ($couchPotato) {
    module.lazy = $couchPotato;
  });

  console.log("layout", module);
  return module;
});


/**
 * loads sub modules and wraps them up into the main module.
 * This should be used for top-level module definitions only.
 */
'use strict';

define([
  'angular',
  'angular-couch-potato',
  'angular-ui-router',
  "angular-sanitize",
  'angular-animate',
  'angular-bootstrap',
  'angular-loading-bar',
  'jquery.ui.widget',
  'pnotify.main',
  'angular-xeditable'
], function (angular, couchPotato) {

  console.log("app js loaded");
  var app = angular.module('app', [
    'ngSanitize',
    'scs.couch-potato',
    'ngAnimate',
    'ui.router',
    'ui.bootstrap',
    'angular-loading-bar',
    'xeditable',
    
    // App
    'app.constants',
    'app.layout',
    'app.auth',
    'app.warehouse',
    'app.shipping',
    'app.admin',
    'app.salesperson',
    'app.callcenter'
  ]);
  
  Date.createFromMysql = function(mysqlString) {
      var t, result = null;

      if (typeof mysqlString === 'string') {
        t = mysqlString.split(/[- :]/);

        //when t[3], t[4] and t[5] are missing they defaults to zero
        result = new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0));
      }

      return result;   
  };
  
  app.controller("BaseController", ['$filter', '$rootScope', '$scope', '$state', '$timeout', 'notify', 'AuthService', 'SessionService', 'orderService', 'orderDataService', 'AUTH_EVENTS', 'USER_TYPES', 'USER_ROLES', 'CONFIG',
    function ($filter, $rootScope, $scope, $state, $timeout, notify, AuthService, SessionService, orderService, orderDataService, AUTH_EVENTS, USER_TYPES, USER_ROLES, CONFIG) {
        var vm = this;
        $scope.headerView = "./js/modules/layout/views/header.html?v=" + cacheBuster;
        $scope.sidebarView = "./js/modules/layout/views/sidebar.html?v=" + cacheBuster;
        $scope.imagePath = CONFIG.ImageUrl;
        $scope.imageBasePath = CONFIG.ImageBasePath;
        $scope.focusedForm = true;
        $scope.layoutLoading = false;
        $scope.sidebarCollapsed = false;

        // user session
        $rootScope.userSession = null;
        $rootScope.CONFIG = CONFIG;
        $scope.userRoles = USER_ROLES;
        $scope.userTypes = USER_TYPES;
        $scope.isAuthorized = AuthService.isAuthorized;

        // layout methods
        $scope.refresh = function () {
            $state.reload();
            if(SessionService.type != null && (SessionService.type === USER_TYPES.WAREHOUSE || SessionService.type === USER_TYPES.SHIPPER || SessionService.type === USER_TYPES.CALLCENTER)) {
                vm.getOrderCounts(1);
            }
        };
        
        // getting continuous order notification 
        pollOrderNotification();

        function pollOrderNotification() {
            if(SessionService.type != null && (SessionService.type === USER_TYPES.WAREHOUSE || SessionService.type === USER_TYPES.SHIPPER || SessionService.type === USER_TYPES.CALLCENTER)) {
                vm.getOrderCounts();
            }
        }
        
        vm.getOrderCounts = function (noTimeout) {
            orderDataService.getOrderStatusCount()
            .then(function(response){
                if(response.success) {
                    var newData = angular.copy(response.data);
                    orderService.isOrderCountUpdated(vm.orderCount, newData, SessionService.type);
                    vm.orderCount = response.data;
                }
                
                if (!noTimeout) {
                  $timeout(pollOrderNotification, 12000);
                }
            });
        };
        
        function formatDate(date) {
            if (!date || date === '0000-00-00 00:00:00') {
              return "";
            }
          
            var dateObj = new Date(Date.createFromMysql(date));
            return $filter('date')(dateObj, 'medium', '+0800');
        }
        $scope.formatDate = formatDate;
        $rootScope.formatDate = formatDate;
        
        $scope.toggleLeftBar = function () {
          $scope.sidebarCollapsed = !$scope.sidebarCollapsed;
        };

        // auth event handling
        function updateSession(event, toState) {
          $scope.focusedForm = !AuthService.isAuthenticated();
          $scope.layoutLoading = AuthService.isAuthenticated();
          $rootScope.userSession = SessionService;
        }

        function getUserRootPage() {
            var homeRoute = 'app.home';
            if (SessionService.type === USER_TYPES.GROWSARI) {
              homeRoute = 'app.admin';
            } else if (SessionService.type === USER_TYPES.WAREHOUSE) {
              homeRoute = 'app.warehouse';
            } else if (SessionService.type === USER_TYPES.SHIPPER) {
              homeRoute = 'app.shipper_confirmed_order';
            } else if (SessionService.type === USER_TYPES.SALESPERSON) {
              homeRoute = 'app.salesperson_survey';
            } else if (SessionService.type === USER_TYPES.CALLCENTER){
              homeRoute = 'app.callcenter';
            } else {
              homeRoute = 'app.home';
            }

            return homeRoute;
        }

        $rootScope.$on(AUTH_EVENTS.NotAuthorized, function () {
          notify.error('Auth', "You are not authorized to access!");
          $state.go('app.logout');
        });

        $rootScope.$on(AUTH_EVENTS.LoginSuccess, function (event, toState) {
          updateSession();
          $state.go(getUserRootPage());
        });

        $rootScope.$on(AUTH_EVENTS.Authenticated, function (event, toState) {
          updateSession();
          var homeRoute = getUserRootPage();
          var route = $rootScope.previousState && $rootScope.previousState.name ? $rootScope.previousState.name : homeRoute;
          var routeParams = (route !== homeRoute) ? $rootScope.previousStateParams : {};
          $state.go(route, routeParams);
        });

        $rootScope.$on(AUTH_EVENTS.NotAuthenticated, function (event, toState) {
          $state.go('app.login');
        });
    }]);

  // improving-angular-performance
  app.config(['$compileProvider', function ($compileProvider) {
      $compileProvider.debugInfoEnabled(false);
    }]);

  app.config(['$logProvider', '$stateProvider', '$urlRouterProvider', '$httpProvider',
    function ($logProvider, $stateProvider, $urlRouterProvider, $httpProvider) {
      $httpProvider.interceptors.push('AuthInterceptorService');
    }]);
  
  app.config(function ($provide, $httpProvider, $locationProvider, uiGmapGoogleMapApiProvider) {
      uiGmapGoogleMapApiProvider.configure({
          libraries: 'weather,geometry,visualization',
          key: 'AIzaSyBw00hpraRbnTuhBGjI30PWCWmjlUdyGoI'
      });
    });
    
  function configureTemplateFactory($provide) {
      // Set a suffix outside the decorator function 
      //var cacheBuster = Date.now().toString();

      function templateFactoryDecorator($delegate) {
          var fromUrl = angular.bind($delegate, $delegate.fromUrl);
          $delegate.fromUrl = function (url, params) {
              if (url !== null && angular.isDefined(url) && angular.isString(url)) {
                  url += (url.indexOf("?") === -1 ? "?" : "&");
                  url += "v=" + cacheBuster;
              }

              return fromUrl(url, params);
          };

          return $delegate;
      }

      $provide.decorator('$templateFactory', ['$delegate', templateFactoryDecorator]);
  }

  app.config(['$provide', configureTemplateFactory]);

  app.run(['$rootScope', '$log', '$location', '$state', 'CONFIG', 'AUTH_EVENTS', 'AuthService', 'SessionService', 'SessionProvider',
    function ($rootScope, $log, $location, $state, CONFIG, AUTH_EVENTS, AuthService, SessionService, SessionProvider) {
      

      $rootScope.$on('$stateChangeStart', function (event, toState, toParams) {
        var isAuthRequired = toState.data.authRequired;
        var authorizedRoles = toState.data.roles;
        var authorizedTypes = toState.data.types;
        if (isAuthRequired && !AuthService.isAuthorized(authorizedTypes, authorizedRoles)) {
          event.preventDefault();
          if (AuthService.isAuthenticated()) {
            // user is not allowed
            $rootScope.$broadcast(AUTH_EVENTS.NotAuthorized);
          } else {
            // user is not logged in
            $rootScope.previousState = toState;
            $rootScope.previousStateParams = toParams;
            $rootScope.$broadcast(AUTH_EVENTS.NotAuthenticated);
            //$state.go('app.login');
            return;
          }
        }

        if (toState.name === 'app.login' && AuthService.isAuthenticated()) {
          event.preventDefault();
          $state.go('app.home');
        }
      });

    }
  ]);
  
  app.filter('gdate', function() {
    return function(input) {
      return new Date(input);
    };
  });

  return app;
});
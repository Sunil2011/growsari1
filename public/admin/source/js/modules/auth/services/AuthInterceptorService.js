define(['./../module'], function (module) {

  module.service('AuthInterceptorService', [ '$q', '$injector', '$rootScope', 'AUTH_EVENTS', function ($q, $injector, $rootScope, AUTH_EVENTS) {
      var sessionCheck = {
          responseError : function(response) {
              if(response.status === 401) {
                  var $state = $injector.get('$state');
                  var sessionService = $injector.get('SessionService');
                  sessionService.destroy();
                  $rootScope.$broadcast(AUTH_EVENTS.NotAuthenticated);
                  $state.go('app.login');
              }
              return $q.reject(response);
          }
      };
      
      return sessionCheck;
  }]);
});

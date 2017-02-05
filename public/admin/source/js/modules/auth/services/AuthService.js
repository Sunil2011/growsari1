define(['./../module'], function (module) {

  module.factory('AuthService', ['CONFIG', 'AUTH_EVENTS', 'SessionService', '$rootScope', '$http', '$q', '$log', '$httpParamSerializer', AuthService]);

  function AuthService(CONFIG, AUTH_EVENTS, SessionService, $rootScope, $http, $q, $log, $httpParamSerializer) {

    return {
      login: login,
      logout: logout,
      getUser: getUser,
      isAuthenticated: isAuthenticated,
      isAuthorized: isAuthorized
    };

    function login(params) {
      return $http({
        url: CONFIG.ApiBaseUrl + '/auth/login',
        method: 'POST',
        data: $httpParamSerializer(params),
        headers: {
          'Content-type': 'application/x-www-form-urlencoded; charset=utf-8' // Note the appropriate header
        }
      }).then(function (response) {
        var data = response.data;
        if (data.id) {
          SessionService.create(data.id, data.account);
          $rootScope.$broadcast(AUTH_EVENTS.LoginSuccess);
        } else {
          SessionService.destroy();
          $rootScope.$broadcast(AUTH_EVENTS.LoginFailed);
        }
        return data;
      });
    }

    function logout() {
      return $http({
        url: CONFIG.ApiBaseUrl + '/auth/logout',
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).then(function (response) {
        $rootScope.$broadcast(AUTH_EVENTS.LogoutSuccess);
        var data = response.data;
        return data;
      });
    }

    function getUser() {
      return $http({
        url: CONFIG.ApiBaseUrl + '/auth/me',
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).then(function (response) {
        var data = response.data;
        return data;
      });
    }
    
    
    function isAuthenticated() {
      return !!SessionService.id;
    };

    function isAuthorized(types, roles) {
      if (!isAuthenticated()) {
        return false;
      }
      
      if (!types) {
        return false;
      }
      
      if (!angular.isArray(types)) {
        types = [types];
      }
      
      if (types.indexOf(SessionService.type.toUpperCase()) === -1) {
        return false;
      }
      
      if (roles && !angular.isArray(roles)) {
        roles = [roles];
      }
      
      if (roles && roles.indexOf(SessionService.role.toUpperCase()) === -1) {
        return false;
      }
      
      return true;
    };    
    
  }
});

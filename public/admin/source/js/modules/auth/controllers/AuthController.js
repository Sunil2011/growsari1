define(['./../module'], function (module) {

  module.registerController('AuthController', ['notify', 'AUTH_EVENTS', 'CONFIG','SessionService', 'AuthService', '$rootScope', '$scope', '$state', '$location', '$timeout', function (notify, AUTH_EVENTS, CONFIG, SessionService, AuthService, $rootScope, $scope, $state, $location, $timeout) {
     
      $scope.credentials = {
        username: '',
        password: ''
      };
      
      $scope.login =  function () {
        AuthService
        .login($scope.credentials)
        .then(function (response) {
          if (response.id) {
            //$state.go('app.home');
          } else {
            $scope.credentials.password =  '';
          }
        })
        .catch(function (response) {
          var status = response.status;
          if (status === -1) {
            notify.error('Auth', 'Please check your network connection');
            return;
          }
          
          var data = response.data;
          if(data && !data.error) {
              notify.success('Auth', data.message);
              $state.reload();
          } else {
              notify.error('Auth', data.error.message);
          }
        });
      };
      
      $scope.logout =  function () {
        AuthService
        .logout()
        .then(function (response) {
          window.location.href = CONFIG.BaseUrl + $state.href('app.login');
        })
        .catch(function (response) {
          window.location.href = CONFIG.BaseUrl + $state.href('app.login');
        });
      };
      
      if ($state.current.name === 'app.logout') {
        $scope.logout();
      }

    }]);

});
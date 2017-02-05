define(['./../module'], function (module) {

  module.registerController('AppController', ['$scope', '$location', '$timeout', function ($scope, $location, $timeout) {
      $scope.hello = "This is test page"
    }]);

});
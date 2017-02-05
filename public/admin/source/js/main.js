// Defer AngularJS bootstrap
window.name = "NG_DEFER_BOOTSTRAP!";

define([
  'require',
  'jquery',
  'angular',
  'domReady',
  'angular-bootstrap',
  'bootstrap',
  './app',
  './includes'
], function (require, $, angular, domReady) {
   domReady(function (document) {
        console.log("in dom ready");
        angular.bootstrap(document, ['app']);
        angular.resumeBootstrap();
    });
});



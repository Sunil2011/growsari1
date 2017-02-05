/**
 * Defines constants for application
 */
define(['angular'], function (angular) {
  return angular.module('app.constants', [])
    .constant('CONFIG', {
       ApiBaseUrl: baseUrl + '/api',
       BaseUrl: baseUrl + '/admin/source',
       ImageUrl: baseUrl + '/admin/source/assets/img/',
       ImageBasePath: baseUrl + '/uploads/'
    })
    .constant('AUTH_EVENTS', {
        Authenticated: 'auth-authenticated',
        LoginSuccess: 'auth-login-success',
        LoginFailed: 'auth-login-failed',
        LogoutSuccess: 'auth-logout-success',
        SessionTimeout: 'auth-session-timeout',
        NotAuthenticated: 'auth-not-authenticated',
        NotAuthorized: 'auth-not-authorized'
    })
    .constant('USER_TYPES', {
      WAREHOUSE: 'WAREHOUSE',
      SHIPPER: 'SHIPPER',
      STORE: 'STORE',
      GROWSARI: 'GROWSARI',
      SALESPERSON: 'SALESPERSON',
      CALLCENTER: 'CALLCENTER'
    })
    .constant('USER_ROLES', {
      ALL: 'ALL',
      ADMIN: 'ADMIN',
      USER: 'USER',
      SUPER_ADMIN: 'SUPER_ADMIN',
      GUEST: 'GUEST'
    })
    .constant('ORDER_EVENTS', {
      ADDED_ITEM: 'ORDER_ADDED_ITEM',
      CANCELLED: 'ORDER_CANCELLED'
    });
});

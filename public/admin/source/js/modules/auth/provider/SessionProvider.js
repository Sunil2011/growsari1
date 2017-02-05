define(['./../module'], function (module) {

    module.provider('SessionProvider', function (CONFIG) {
        this.$get = function ($rootScope, $http, $state, SessionService, AUTH_EVENTS) {
            return $http({
                url: CONFIG.ApiBaseUrl + '/auth/me',
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).success(function (data) {
                if (data && data.account) {
                    SessionService.create(data.account.id, data.account);
                    $rootScope.$broadcast(AUTH_EVENTS.Authenticated);
                } else {
                    $rootScope.$broadcast(AUTH_EVENTS.NotAuthenticated);
                }
            });
        };
    });

});
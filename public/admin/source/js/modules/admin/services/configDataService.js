define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('configDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getConfigList : getConfigList,
                getConfigDetails : getConfigDetails,
                addEditConfig : addEditConfig
            };
            
            function getConfigList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/config',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getConfigDetails(configId) {
                return $http.get(CONFIG.ApiBaseUrl + '/config/' + configId,
                {params: {}})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function addEditConfig(params) {
                return $http({
                        url: CONFIG.ApiBaseUrl + '/config',
                        method: 'POST',
                        data: params,
                        headers: {'Content-Type': undefined},
                        transformRequest: function (params, headersGetter) {
                            var formData = new FormData();
                            angular.forEach(params, function (value, key) {
                                formData.append(key, value);
                            });

                            var headers = headersGetter();
                            delete headers['Content-Type'];

                            return formData;
                        },
                    }).then(function successCallback(response) {
                        var data = response.data;
                        return data;
                    }, function errorCallback(response) {
                        // called asynchronously if an error occurs
                        // or server returns response with an error status.
                    });
                }
            
        }]);
});



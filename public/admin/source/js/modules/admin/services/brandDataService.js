define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('brandDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getBrandList : getBrandList,
                getBrandDetails : getBrandDetails,
                addEditbrand : addEditbrand,
                deleteBrand: deleteBrand
            };
            
            function getBrandList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/brand',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getBrandDetails(brandId) {
                return $http.get(CONFIG.ApiBaseUrl + '/brand/' + brandId,
                {params: {}})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function addEditbrand(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/brand',
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
            
            function deleteBrand(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/brand/delete' ,
                    method : 'POST' ,
                    data : $.param(params),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
        }]);
});



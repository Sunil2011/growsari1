/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('storeDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getStoreList : getStoreList ,
                getWarehouseList : getWarehouseList ,
                getShipperList : getShipperList ,
                getStores : getStores,
                getStoreDetails : getStoreDetails,
                addStore : addStore,
                editStore : editStore,
                deleteStore : deleteStore,
                createAccount : createAccount,
                updateStoreWallet : updateStoreWallet
            };
            
            function createAccount(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/account' ,
                    method : 'POST' ,
                    data : $.param(params),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                   var data = response.data;
                   return data;
                });
            }
            
            function getStoreList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/store',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getStores(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/store/get-stores',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getWarehouseList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/warehouse',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getShipperList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/shipper',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            
             function getStoreDetails(storeId){
                return $http.get(CONFIG.ApiBaseUrl + '/store/' + storeId,
                { params:{} })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function addStore(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/store' ,
                    method : 'POST' ,
                    data : params,
                    headers: {'Content-Type': undefined},
                    transformRequest: function (params, headersGetter) {
                        var formData = new FormData();
                        angular.forEach(params, function (value, key) {
                            formData.append(key, value);
                        });

                        var headers = headersGetter();
                        delete headers['Content-Type'];

                        return formData;
                    }
                })
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    var data = response.data;
                    return data;
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function editStore(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/store/update' ,
                    method : 'POST' ,
                    data : params,
                    headers: {'Content-Type': undefined},
                    transformRequest: function (params, headersGetter) {
                        var formData = new FormData();
                        angular.forEach(params, function (value, key) {
                            formData.append(key, value);
                        });

                        var headers = headersGetter();
                        delete headers['Content-Type'];

                        return formData;
                    }
                })
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    var data = response.data;
                    return data;
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function deleteStore(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/store/delete' ,
                    method : 'POST' ,
                    data : $.param(params),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    var data = response.data;
                    return data;
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function updateStoreWallet(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/store/update-wallet' ,
                    method : 'POST' ,
                    data : $.param(params),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    var data = response.data;
                    return data;
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
        }]);
});




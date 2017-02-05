/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('productDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getproductList : getproductList ,
                getproductDetails : getproductDetails,
                addEditProduct : addEditProduct,
                deleteProduct : deleteProduct,
            };
            
            function getproductList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/product',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            
             function getproductDetails(productId){
                return $http.get(CONFIG.ApiBaseUrl + '/product/' + productId,
                { params:{} })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function addEditProduct(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/product' ,
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
            
            function deleteProduct(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/product/delete' ,
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




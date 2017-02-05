/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('salesDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getSalespersonList : getSalespersonList,
                getSalesReportList : getSalesReportList,
                getSalesDetReportList : getSalesDetReportList,
                getUnassignedStore : getUnassignedStore
            };
            
            function getSalespersonList(){
                return $http.get(CONFIG.ApiBaseUrl + '/account/get-salesperson',
                { params:{} })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getSalesReportList(param){
                return $http.get(CONFIG.ApiBaseUrl + '/survey/get-salesperson-report',
                { params:param })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getSalesDetReportList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/survey/get-salesperson-detail-report',
                { params:param })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getUnassignedStore(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/survey/get-unassigned-store',
                { params:param })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
        }]);
});






/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('shipperTeamDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getShipperTeamList : getShipperTeamList,
                getShipperTeamDetails : getShipperTeamDetails,
                addShipperTeam : addShipperTeam,
                editShipperTeam : editShipperTeam,
                deleteShipperTeam : deleteShipperTeam
            };
            
            
            function getShipperTeamList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/shipper/team',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getShipperTeamDetails(shipperId){
                return $http.get(CONFIG.ApiBaseUrl + '/shipper/team/' + shipperId,
                { params:{} })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function addShipperTeam(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/shipper/team' ,
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
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function editShipperTeam(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/shipper/team/update' ,
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
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function deleteShipperTeam(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/shipper/team/delete' ,
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




/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('surveyDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getSurveyList : getSurveyList ,
                getSurveyDetails : getSurveyDetails,
                addSurvey : addSurvey,
                editSurvey : editSurvey,
                deleteSurvey : deleteSurvey,
                getUnassignedStore : getUnassignedStore
            };
            
            function getSurveyList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/survey',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            
             function getSurveyDetails(surveyId){
                return $http.get(CONFIG.ApiBaseUrl + '/survey/' + surveyId,
                { params:{} })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getUnassignedStore(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/store',
                { params:param })
                .then(function successCallback(response){
                    var data = response.data;
                    return data;
                }, function errorCallback(response){
                    var data = response.data;
                    return data;
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function addSurvey(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/survey' ,
                    method : 'POST' ,
                    data : $.param(params),
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
            
            function editSurvey(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/survey/update' ,
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
            
            function deleteSurvey(params) {
                return $http({
                    url :CONFIG.ApiBaseUrl + '/survey/delete' ,
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




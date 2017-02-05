/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    "use strict";
    
    module.registerFactory('orderDataService' , ['$http', '$q', '$log', 'CONFIG',
        function($http, $q, $log, CONFIG){
            return {
                getorderList : getorderList,
                getorderDetails : getorderDetails,
                assignShipper : assignShipper,
                updateOrderStatus : updateOrderStatus,
                getOrderStatusCount : getOrderStatusCount,
                addItemsToExistingOrder:  addItemsToExistingOrder,
                changeDeliveryDate:  changeDeliveryDate,
                addOrderByCustCare: addOrderByCustCare,
                addOrderLoyaltyPoint: addOrderLoyaltyPoint,
                getWalletDetails: getWalletDetails,
                editOrderItem: editOrderItem,
                taskFinish: taskFinish,
                deleteOrderItem: deleteOrderItem
            };
            
            function getorderList(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/order',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function getorderDetails(orderId) {
                return $http.get(CONFIG.ApiBaseUrl + '/order/' + orderId,
                {params: {}})
                .then(function successCallback(response) {
                    var data = response.data.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function confirmOrder(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/confirm',
                    method: 'POST',
                    data: $.param(params),
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
            
            function addItemsToExistingOrder(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/add-items-to-existing',
                    method: 'POST',
                    data: $.param(params),
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
            
            function addOrderByCustCare(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/add-order',
                    method: 'POST',
                    data: $.param(params),
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
            
            function changeDeliveryDate(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/change-delivery-date',
                    method: 'POST',
                    data: $.param(params),
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
            
            function assignShipper(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/assign-shipper',
                    method: 'POST',
                    data: $.param(params),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    return false;
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function updateOrderStatus(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/update-status',
                    method: 'POST',
                    data: $.param(params),
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
            
            function getOrderStatusCount() {
                return $http.get(CONFIG.ApiBaseUrl + '/order/get-order-status-count',
                {params: {}})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function addOrderLoyaltyPoint(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/add-loyalty-point',
                    method: 'POST',
                    data: $.param(params),
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
            
            function getWalletDetails(param) {
                return $http.get(CONFIG.ApiBaseUrl + '/store/get-wallet-details',
                {params: param})
                .then(function successCallback(response) {
                    var data = response.data;
                    return data;
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }
            
            function editOrderItem(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/edit-order-item',
                    method: 'POST',
                    data: $.param(params),
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
            
            function taskFinish(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/task-finish',
                    method: 'POST',
                    data: $.param(params),
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
                    
            function deleteOrderItem(params) {
                return $http({
                    url: CONFIG.ApiBaseUrl + '/order/delete-order-item',
                    method: 'POST',
                    data: $.param(params),
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



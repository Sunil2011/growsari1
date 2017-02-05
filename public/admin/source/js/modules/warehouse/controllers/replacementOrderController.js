/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {
    
  module.registerController('replacementOrderController', ['notify', '$stateParams', '$scope', '$state', 'orderService', 'orderDataService', 
        function (notify, $stateParams, $scope, $state, orderService, orderDataService) {
            var vm = this;
            orderService.page = $stateParams.page || 1;
            orderService.search = $stateParams.search;
            orderService.setOrderStatus('replacementOrders');
            
            $scope.confirmOrder = function (orderId) {
                var data = orderService.getStockConfirmationParams(orderId);
                if (data === false) {
                  return;
                }
                
                orderDataService.updateOrderStatus(data)
                .then(function (response) {
                  if (response && !response.error) {
                    notify.success('Order #' + orderId, response.message);
                    $state.reload();
                  } else {
                      var msg = response.error.message;
                    if (msg.indexOf("Informed call center") >= 0) {
                        $state.reload();
                    }
                    notify.error('Order #' + orderId, response.error.message);
                  }
                });
              };
            }]);

});
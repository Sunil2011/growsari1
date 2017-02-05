define(['./../module'], function (module) {

  module.registerController('newOrderController', ['notify', '$scope', '$state', '$stateParams', 'orderService', 'orderDataService',
    function (notify, $scope, $state, $stateParams, orderService, orderDataService) {
      var vm = this;
      orderService.page = $stateParams.page || 1;
      orderService.search = $stateParams.search;
      orderService.setOrderStatus('pending');

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

      $scope.cancelOrder = function (orderId) {
        var r = confirm("Are you sure, do you want to cancel?");
        if (r === true) {
          orderDataService.updateOrderStatus({
            order_id: orderId,
            status: 'cancelled'
          })
          .then(function (response) {
            if (response && !response.error) {
              notify.success('Order #' + orderId, response.message);
              $state.reload();
            } else {
              notify.error('Order #' + orderId, response.error.message);
            }
          });
        }
      };
    }]);

});
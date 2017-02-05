define(['./../module'], function (module) {

  module.registerController('packOrderController', ['notify', '$scope', '$state', '$stateParams', '$uibModal', 'orderService', 'orderDataService',
    function (notify, $scope, $state, $stateParams, $uibModal, orderService, orderDataService) {
      var vm = this;
      orderService.page = $stateParams.page || 1;
      orderService.search = $stateParams.search;
      orderService.setOrderStatus('ready_to_pack');

      $scope.pack = function (orderId) {
        var boxCount = orderService.getPackBoxCount(orderId);
        var data = orderService.getStockConfirmationParams(orderId);
        if (data === false) {
          return;
        }

        if (boxCount && data) {
          data['status'] = 'packed';
          data['no_of_boxes'] = boxCount;
          
          if(!confirm("Are you sure order is packed?")) {
            return false;
          }

          orderDataService.updateOrderStatus(data)
          .then(function (response) {
            if (response && !response.error) {
              notify.success('Order #' + orderId, response.message);
              $state.reload();
            } else {
              notify.error('Order #' + orderId, response.error.message);
            }
          });
        } else {
          notify.info('Order #' + orderId, 'Please enter the no of boxes!');
        }
      };


    }]);

});
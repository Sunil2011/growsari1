define(['./../module'], function (module) {
    
  module.registerController('confirmedOrderController', ['notify' ,'$scope', '$state', '$stateParams', 'orderService','orderDataService',
        function (notify, $scope, $state, $stateParams, orderService, orderDataService) {
            var vm = this;
            orderService.page = $stateParams.page || 1;
            orderService.search = $stateParams.search;
            orderService.setOrderStatus('confirmed');
            
            $scope.readyToPack = function(orderId) {
                data = {
                    order_id : orderId,
                    status : 'ready_to_pack'
                };
                
                if(!confirm("Are you sure you want to send order for packing?")) {
                    return false;
                }
                
                orderDataService.updateOrderStatus(data)
                .then(function(response){
                    if(response && !response.error) {
                        notify.success('Order #' + orderId, response.message);
                        $state.reload();
                    } else {
                        notify.error('Order #' + orderId, response.error.message);
                    }
                });
            };
    }]);

});
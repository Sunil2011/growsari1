define(['./../module'], function (module) {
        
    module.registerController('assignShipperBoxModalController', ['notify','$scope', '$state','$uibModalInstance', 'order', 'shipperTeamDataService', 'orderService', 'orderDataService',
        function (notify, $scope, $state, $uibModalInstance, order, shipperTeamDataService, orderService, orderDataService) {
            var vm = this;
            vm.shipper_team_id = 0;
            vm.orderId = order.id;
            vm.order = order;
            
            shipperTeamDataService.getShipperTeamList(vm.shipperParam)
                .then(function(response){
                    if(response.success) {
                        var shipperData = response.data.shipper;
                        vm.shipper  =  shipperData.list;
                        if(shipperData.totalCount == 0) {
                            vm.noResults = true;
                        } else {
                          vm.noResults = false;
                        }
                    } else {
                        notify.error('Shipper List',response.message);
                    }
            });            
           
            vm.assignShipper = function() {
                if (!vm.order.shipper_team_id) {
                  notify.error('Order #' + vm.orderId, 'Please select one salesperson');
                  return;
                }
              
                orderDataService.assignShipper({
                    order_id : vm.orderId,
                    shipper_team_id : vm.order.shipper_team_id
                })
                .then(function(response){
                    if(response.success) {
                        notify.success('Order #' + vm.orderId,response.message);
                        $uibModalInstance.close();
                    } else {
                        notify.error('Order #' + vm.orderId,response.message);
                    }
                });
            };
            
            vm.close = function(boxId) {
                $uibModalInstance.close();
            };
    }]);
});
define(['./../module'], function (module) {
    
  module.registerController('assignShipperController', ['notify' ,'$scope', '$state', '$stateParams', '$uibModal', 'orderService','orderDataService', 'shipperTeamDataService',
        function (notify, $scope, $state, $stateParams, $uibModal, orderService, orderDataService, shipperTeamDataService) {
            var vm = this;
            orderService.page = $stateParams.page || 1;
            orderService.search = $stateParams.search;
            orderService.setOrderStatus('');
            
            $scope.openAssignShipperBoxModal = function (order) {
                var modalInstance = $uibModal.open({
                    animation: $scope.animationsEnabled,
                    templateUrl: './js/modules/shipping/views/assign-shipper-box.html?v='+cacheBuster,
                    controller: 'assignShipperBoxModalController',
                    controllerAs: 'assignShipperBoxModal',
                    size: 'small',
                    backdrop: 'static',
                    resolve: {
                        order: function () {
                            return order;
                        }
                    }
                });
                
                modalInstance.result.finally(function () {
                  $state.reload();
                });
            };
            
            
    }]);

});
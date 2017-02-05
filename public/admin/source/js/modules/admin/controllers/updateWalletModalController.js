/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {

  module.registerController('updateWalletModalController', ['CONFIG', 'notify', '$uibModalInstance', '$scope', '$state', 'orderId', 'storeId', 'orderDataService', 'storeDataService',
    function (CONFIG, notify, $uibModalInstance, $scope, $state, orderId, storeId, orderDataService, storeDataService) {
        var vm = this;
        $scope.orderId = orderId;
        $scope.storeId = storeId;
        vm.close = function() {
            $uibModalInstance.close();
        };
        
        if(storeId) {
            var field = {store_id: storeId};
            orderDataService.getWalletDetails(field)
                .then(function (response) {
                    if (response.success) {
                        vm.store_balance = response.data.points;
                        vm.store_name = response.data.name; 
                    } else {
                        notify.error('Wallet', response.message);
                    }
                });
        }

        vm.addLoyaltyPoint = function(orderId) {
            
            if(vm.loyalty_point == undefined || vm.remark == undefined || !vm.remark) {
                notify.error('Wallet', 'Points or Remark should not be empty.');
                return;
            } else if(!vm.loyalty_point) {
                notify.error('Wallet', 'Loyalty Points should be greater than 0.');
                return;
            } else if(vm.loyalty_point > vm.store_balance) {
                notify.error('Wallet', 'Loyalty Points exceeds store balance.');
                return;
            }
            
            var param = {
                order_id: orderId,
                loyalty_point: vm.loyalty_point,
                remark: vm.remark
            }
            
            if(!confirm("Are you sure you want to use loyalty points for order #" + orderId)) {
                return false;
            }
            
            orderDataService.addOrderLoyaltyPoint(param)
            .then(function (response) {
                if (response && !response.error) {
                    notify.success('Wallet',response.message);
                    $uibModalInstance.close();
                } else {
                    notify.error('Wallet',response.error.message);
                }
            });
        }
        
        vm.addMoney = function(storeId) {
            if(vm.amount == undefined || vm.remark == undefined || !vm.remark) {
                notify.error('Wallet', 'Points or Remark should not be empty.');
                return;
            } else if(!vm.amount) {
                notify.error('Wallet', 'Loyalty Points should be greater than 0.');
                return;
            }
            
            var param = {
                store_id: storeId,
                amount: vm.amount,
                remark: vm.remark,
                is_loan: vm.is_loan == true?1:0
            }
            
            if(!confirm("Are you sure you want to add Money to store #" + storeId)) {
                return false;
            }
            
            storeDataService.updateStoreWallet(param)
            .then(function (response) {
                if (response && !response.error) {
                    notify.success('Wallet',response.message);
                    $uibModalInstance.close();
                } else {
                    notify.error('Wallet',response.error.message);
                }
            });
        }
    }]);
});



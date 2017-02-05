/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {

    module.registerController('configAddEditController',
    ['notify', '$scope', '$state', 'configDataService', '$stateParams',
        function (notify, $scope, $state, configDataService, $stateParams) {
            var vm = this;

            vm.config = {};
            vm.config.id = $stateParams.id || '';

            if(vm.config.id) {
                configDataService.getConfigDetails(vm.config.id)
                .then(function (response) {
                    if (response.success) {
                        vm.config = response.data;
                    } else {
                        alert(response.message);
                    }
                });
            }

            vm.cancel = function () {
                $state.go('app.config');
            };

            vm.submitCategory = function () {
                
                var data = { 
                    'id': vm.config.id,
                    'field': vm.config.field,
                    'value': vm.config.value
                };
                
                configDataService.addEditConfig(data)
                .then(function (response) {
                    if (response.success) {
                        notify.success('Config',response.message);
                        $state.go('app.config');
                    } else {
                        notify.error('Config',response.message);
                    }
                });
            }
        }]);
});






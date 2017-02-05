/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define(['./../module'], function (module) {

    module.registerController('brandAddEditController',
    ['notify', '$scope', '$state', 'brandDataService', '$stateParams',
        function (notify, $scope, $state, brandDataService, $stateParams) {
            var vm = this;

            vm.brand = {};
            vm.brand.id = $stateParams.brand_id || '';

            if(vm.brand.id) {
                brandDataService.getBrandDetails(vm.brand.id)
                .then(function (response) {
                    if (response.success) {
                        vm.brand = response.data.brand;
                    } else {
                        alert(response.message);
                    }
                });
            }

            vm.cancel = function () {
                $state.go('app.brand');
            };

            vm.submitBrand = function () {
                
                var data = { 
                    'id': vm.brand.id,
                    'name': vm.brand.name,
                }
                
                if($scope.file != undefined) {
                    data.file = $scope.file;
                }
                
                brandDataService.addEditbrand(data)
                .then(function (response) {
                    if (response.success) {
                        notify.success('Brand',response.message);
                        $state.go('app.brand');
                    } else {
                        notify.error('Brand',response.message);
                    }
                });
            }
        }]);
});





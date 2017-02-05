/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {

    module.registerController('categoryAddEditController',
    ['notify', '$scope', '$state', 'categoryDataService', '$stateParams',
        function (notify, $scope, $state, categoryDataService, $stateParams) {
            var vm = this;

            vm.category = {};
            vm.category.id = $stateParams.category_id || '';
            
            categoryDataService.getCategoryList()
            .then(function (response) {
                if (response.success) {
                    vm.mega_category = response.data.mega_category;
                }
            });

            if(vm.category.id) {
                categoryDataService.getCategoryDetails(vm.category.id)
                .then(function (response) {
                    if (response.success) {
                        vm.category = response.data.category;
                    } else {
                        alert(response.message);
                    }
                });
            }

            vm.cancel = function () {
                $state.go('app.category');
            };

            vm.submitCategory = function () {
                
                var data = { 
                    'id': vm.category.id,
                    'name': vm.category.name,
                    'mega_category_id': vm.category.mega_category_id,
                };
                
                if($scope.file != undefined) {
                    data.file = $scope.file;
                }
                
                categoryDataService.addEditCategory(data)
                .then(function (response) {
                    if (response.success) {
                        notify.success('Category',response.message);
                        $state.go('app.category');
                    } else {
                        notify.error('Category',response.message);
                    }
                });
            }
        }]);
});






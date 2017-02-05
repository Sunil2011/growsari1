/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {

    module.registerController('salespersonController', ['notify', '$scope', '$state', '$stateParams', 'salesDataService',
        function (notify, $scope, $state, $stateParams, salesDataService) {
            var vm = this;

//      vm.productParam = {
//        category_id: $stateParams.category_id,
//        brand_id: $stateParams.brand_id,
//        page: $stateParams.page || 1,
//        search: $stateParams.search || ''
//      };

        salesDataService.getSalespersonList()
            .then(function (response) {
                if (response.success) {
                    var data = response.data;
                    vm.salesman = data.list;
                    if (data.totalCount == 0) {
                        vm.noResults = true;
                    } else {
                      vm.noResults = false;
                    }
                } else {
                    notify.error('Sales Person List', response.message);
                }
            });

            //$scope.getProducts();


//            $scope.categories = categoryDataService.getCategoryList()
//                    .then(function (response) {
//                        if (response.success) {
//                            // alert(response.message);
//                            $scope.cats = response.data.category.list;
//                        } else {
//                            notify.error('Product List', response.message);
//                        }
//                    });

        vm.getSalesReport = function (id, type) {
            var stateName = '';
            if(type == 'briefRep') {
                stateName = 'app.salespersonReport';
            } else {
                stateName = 'app.salespersonDetReport';
            }
            
            
            $state.go(stateName, {salesperson_id: id});
        };

//      
//
//      $scope.addProduct = function () {
//        $state.go('app.productAddEdit', {});
//      };
//
//      $scope.editProduct = function ($id) {
//        $state.go('app.productAddEdit', {product_id: $id});
//
//      };

//      $scope.deleteProduct = function (id) {
//        var productId = vm.id || '';
//        if (!productId) {
//          notify.error('Product', 'Please select the product');
//          return;
//        }
//
//        var r = confirm("Are you sure, do you want to delete?");
//        if (r === true) {
//          productDataService.deleteProduct({id: productId})
//          .then(function (response) {
//            if (response && response.success) {
//              notify.success('Product', response.message);
//              $state.reload();
//            } else {
//              notify.error('Product', response.error.message);
//            }
//          });
//        }
//      };

        }]);

});
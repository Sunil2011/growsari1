/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {
    
  module.registerController('brandController', ['notify', '$scope', '$state', '$stateParams', 'brandDataService', 'paginationSrvc',
        function (notify, $scope, $state, $stateParams , brandDataService, paginationSrvc) {
            var vm = this;
            
            vm.page = $stateParams.page || 1;
            
            var param = {
                page : vm.page
            }
            brandDataService.getBrandList(param)
                .then(function(response) {
                if(response.success) {
                    var brandData = response.data.brand;
                    vm.brands = response.data.brand.list;
                    paginationSrvc.setPagination(brandData.totalCount,brandData.page,brandData.count_per_page);
                    if(brandData.totalCount == 0) {
                      vm.noResults = true;
                    } else {
                      vm.noResults = false;
                    }
                } else {
                    notify.error('Brand List',response.message);
                }
            });
            
            $scope.addEditBrand = function(brandId){
                var brand_id = brandId || '';
                $state.go('app.brandAddEdit', {brand_id : brand_id});  
            };
            
            $scope.deleteBrand = function(){
                var brand_id = vm.id || '';
                if (!brand_id) {
                  alert('Please select the brand');
                  return;
                }
                
                var r = confirm("Are you sure, do you want to delete?");
                if (r === true) {
                    brandDataService.deleteBrand({id: brand_id})
                      .then(function(response) {
                      if(response.success) {
                          notify.success('Brand', response.message);
                          $state.reload();
                      } else {
                          notify.error('Brand', response.message);
                      }
                  });
                } 
            };
    }]);

});



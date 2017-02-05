/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {
    
  module.registerController('categoryController', ['notify', '$scope', '$state', '$stateParams', 'categoryDataService', 'paginationSrvc',
        function (notify, $scope, $state, $stateParams , categoryDataService, paginationSrvc) {
            var vm = this;
            
            vm.page = $stateParams.page || 1;
            
            var param = {
                page : vm.page
            };
            
            categoryDataService.getCategoryList(param)
                .then(function(response) {
                if(response.success) {
                    var categoryData = response.data.category;
                    vm.categories = response.data.category.list;
                    paginationSrvc.setPagination(categoryData.totalCount,categoryData.page,categoryData.count_per_page);
                    if(categoryData.totalCount == 0) {
                        vm.noResults = true;
                    } else {
                      vm.noResults = false;
                    }
                } else {
                    notify.error('Category List',response.message);
                }
            });
            
            $scope.addEditCategory = function(category_id){
                var category_id = category_id || '';
                $state.go('app.categoryAddEdit', {category_id : category_id});  
            };
            
            $scope.deleteCategory = function(){
                var category_id = vm.id || '';
                if (!category_id) {
                  alert('Please select the category');
                  return;
                }
                
                var r = confirm("Are you sure, do you want to delete?");
                if (r === true) {
                    categoryDataService.deleteCategory({id: category_id})
                      .then(function(response) {
                      if(response.success) {
                          notify.success('Category', response.message);
                          $state.reload();
                      } else {
                          notify.error('Category', response.message);
                      }
                  });
                } 
            };
    }]);

});




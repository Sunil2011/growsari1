/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {
    
  module.registerController('configController', ['notify', '$scope', '$state', '$stateParams', 'configDataService', 'paginationSrvc',
        function (notify, $scope, $state, $stateParams , configDataService, paginationSrvc) {
            var vm = this;
            vm.page = $stateParams.page || 1;
            var param = {
                page : vm.page
            };
            
            configDataService.getConfigList(param)
                .then(function(response) {
                if(response.success) {
                    var configData = response.data;
                    vm.config = response.data.list;
                    paginationSrvc.setPagination(configData.totalCount,configData.page,configData.count_per_page);
                    if(configData.totalCount == 0) {
                        vm.noResults = true;
                    } else {
                      vm.noResults = false;
                    }
                } else {
                    notify.error('Config List',response.message);
                }
            });
            
            $scope.addEditConfig = function(id){
                var config_id = config_id || '';
                $state.go('app.configAddEdit', {id : id});  
            };
    }]);

});




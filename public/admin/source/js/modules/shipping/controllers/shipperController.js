
define(['./../module'], function (module) {
    
  module.registerController('shipperController', ['notify', '$scope', '$state', '$stateParams', 'shipperTeamDataService', 'CONFIG', 'paginationSrvc',
        function (notify, $scope, $state, $stateParams , shipperTeamDataService , CONFIG, paginationSrvc) {
            var vm = this;
            vm.shipperParam = {
                page : $stateParams.page || 1
            };
            
            shipperTeamDataService.getShipperTeamList(vm.shipperParam)
                .then(function(response){
                    if(response.success) {
                        var shipperData = response.data.shipper;
                        vm.shipper  =  shipperData.list;
                        paginationSrvc.setPagination(shipperData.totalCount,shipperData.page,shipperData.count_per_page);
                        if(shipperData.totalCount == 0) {
                            vm.noResults = true;
                        } else {
                          vm.noResults = false;
                        }
                    } else {
                        notify.error('Shipper List',response.message);
                    }
            });
            
            $scope.addShipper = function(){
                $state.go('app.shipper_teamAddEdit',{});  
            };
           
            $scope.editShipper = function($id){
                $state.go('app.shipper_teamAddEdit', {shipper_id : $id});
            };
            
            $scope.deleteShipper = function(){
                var shipperTeamId = vm.id || '';
                if (!shipperTeamId) {
                  alert('Please select the category');
                  return;
                }
                
                var r = confirm("Are you sure, do you want to delete?");
                if (r === true) {
                    shipperTeamDataService.deleteShipperTeam({id: shipperTeamId})
                      .then(function(response) {
                      if(response.success) {
                          notify.success('Shipper', response.message);
                          $state.reload();
                      } else {
                          notify.error('Shipper', response.message);
                      }
                  });
                } 
            };
            
           $scope.BaseUrl = CONFIG.BaseUrl ;
    }]);

});


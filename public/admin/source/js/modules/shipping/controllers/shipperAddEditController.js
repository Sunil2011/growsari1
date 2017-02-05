define(['./../module'], function (module) {

    module.registerController('shipperAddEditController', 
    ['notify', '$scope', '$state', 'shipperTeamDataService', 'storeDataService', 'CONFIG', '$stateParams',
        function (notify, $scope, $state, shipperTeamDataService, storeDataService, CONFIG, $stateParams) {
            var vm = this;
            vm.shipper = {};
            vm.shipper.id = $stateParams.shipper_id || '';
            
            if(vm.shipper.id) {
                shipperTeamDataService.getShipperDetails(vm.shipper.id)
                .then(function (response) {
                    if (response.success) {
                        vm.shipper = response.data.shipper;
                    } else {
                        alert(response.message);
                    }
                });
            }
            
            vm.cancel = function () {
                $state.go('app.shipper_team');
            };

            vm.createShipper = function () {
                var verify = vm.verifyCreateShipper(vm.shipper);
                if(!verify) {
                    notify.info('Shipper','Mandatory parameter required.');
                    return;
                }
                
                storeDataService.createAccount({
                  'email': vm.shipper.username,
                  'password': vm.shipper.password,
                  'name': vm.shipper.name,
                  'phone': vm.shipper.contact_no,
                  'type': 'SHIPPER'
                })
                .then(function (response) {
                    if (response.error) {
                      notify.error('Shipper', response.message);
                      return;
                    }
                  
                    var data = {
                        'id': vm.shipper.id,
                        'account_id': response.account_id
                    };
                    
                    shipperTeamDataService.addShipperTeam(data)
                    .then(function (response) {
                        if (response.success) {
                            notify.success('Shipper', response.message);
                            $state.go('app.shipper_team');
                        } else {
                            notify.error('Shipper', response.message);
                        }
                    });
                });
            };
            
            vm.verifyCreateShipper = function(shipper) {
                if(
                    shipper.name === '' ||
                    shipper.username === '' ||
                    shipper.password === '' ||
                    shipper.contact_no === ''
                ) {
                    return false;
                }
                return true;
            };
        
            vm.submitShipper = function() {
              vm.createShipper();
            };
        }]);
});





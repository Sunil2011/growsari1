define(['./../module'], function (module) {

    module.registerController('storeAddEditController', 
    ['notify', '$scope', '$state', 'storeDataService', 'CONFIG', '$stateParams',
        function (notify, $scope, $state, storeDataService, CONFIG, $stateParams) {
            var vm = this;
            vm.store = {};
            vm.store.id = $stateParams.store_id || '';
            vm.funnel_status = {
              'Owner not there' : 'Owner not there',
              'Rejected' : 'Rejected',
              'Left material' : 'Left material',
              'Download app' : 'Download app',
              'Sign-up' : 'Sign-up',
              '1st order done' : '1st order done'
            };
            vm.spend_per_week = {
              'less than 2k' : 'less than 2k',
              '2k-5k' : '2k-5k',
              '5k-10k' : '5k-10k',
              '10k-30k' : '10k-30k',
              'Sign-up' : 'Sign-up',
              '30k +' : '30k +'
            };

            if(vm.store.id) {
                storeDataService.getStoreDetails(vm.store.id)
                .then(function (response) {
                    if (response.success) {
                        vm.store = response.data.store;
                        vm.store.store_name = vm.store.name;
                    } else {
                        alert(response.message);
                    }
                });
            }
            
            storeDataService.getWarehouseList().then(function(response){
              if(response.success) {
                  vm.warehouse = response.data.warehouse.list;
              }
            });
            
            vm.fetchShipperList = function() {
              storeDataService.getShipperList({warehouse_id: vm.store.warehouse_id})
              .then(function (response) {
                  if (response.success) {
                      vm.shipper = response.data.shipper.list;
                  } else {
                      alert(response.message);
                  }
              });
            };
           
            vm.cancel = function () {
                $state.go('app.store');
            };

            vm.createStore = function () {
                var verify = vm.verifyCreateStore(vm.store);
                if(!verify) {
                    notify.info('Store','Mandatory parameter required.');
                    return;
                }
                
                var data = {
                    'email': vm.store.email || '',
                    'password': vm.store.password,
                    'name': vm.store.name,
                    'phone': vm.store.contact_no,
                    'customer_name': vm.store.customer_name,
                    'address': vm.store.address,
                    'locality': vm.store.locality,
                    'city': vm.store.city,
                    'province': vm.store.province,
                    'country': vm.store.country,
                    'pincode': vm.store.pincode,
                    'point_x': vm.store.point_x,
                    'point_y': vm.store.point_y,
                    'is_covered': vm.store.is_covered,
                    'is_storeowner': vm.store.is_storeowner,
                    'has_smartphone': vm.store.has_smartphone,
                    'photo': vm.store.photo,
                    'spend_per_week': vm.store.spend_per_week,
                    'funnel_status': vm.store.funnel_status,
                    'revisit_date': vm.store.revisit_date,
                    'revisit_time': vm.store.revisit_time,
                    'remarks': vm.store.remarks,
                    'warehouse_shipper_id':vm.store.warehouse_shipper_id
                };

                if ($scope.file !== undefined) {
                    data.photo = $scope.file;
                }

                storeDataService.addStore(data)
                .then(function (response) {
                    if (response && !response.error) {
                        notify.success('Store', response.message);
                        $state.go('app.store');
                    } else {
                        notify.error('Store', response.error.message);
                    }
                });
            };
            
            vm.editStore = function () {
                var verify = vm.verifyEditStore(vm.store);
                if(!verify) {
                    notify.info('Store', 'Mandatory parameter required.');
                    return;
                }
               
                var data = {
                    'id': vm.store.id,
                    'name': vm.store.name,
                    'contact_no': vm.store.contact_no,
                    'address': vm.store.address,
                    'customer_name': vm.store.customer_name,
                    'locality': vm.store.locality,
                    'city': vm.store.city,
                    'province': vm.store.province,
                    'country': vm.store.country,
                    'pincode': vm.store.pincode,
                    'point_x': vm.store.point_x,
                    'point_y': vm.store.point_y,
                    'is_covered': vm.store.is_covered,
                    'is_storeowner': vm.store.is_storeowner,
                    'has_smartphone': vm.store.has_smartphone,
                    'photo': vm.store.photo,
                    'spend_per_week': vm.store.spend_per_week,
                    'funnel_status': vm.store.funnel_status,
                    'revisit_date': vm.store.revisit_date,
                    'revisit_time': vm.store.revisit_time,
                    'remarks': vm.store.remarks
                };
                
                if ($scope.file !== undefined) {
                    data.photo = $scope.file;
                }

                storeDataService.editStore(data)
                .then(function (response) {
                    if (response && !response.error) {
                        notify.success('Store', response.message);
                        $state.go('app.store');
                    } else {
                        notify.error('Store', response.error.message);
                    }
                });
            };
            
            vm.verifyCreateStore = function(store) {
                if(
                    store.name === '' ||
                    store.password === '' ||
                    store.contact_no === '' ||
                    store.address === '' ||
                    store.customer_name === ''
                ) {
                    return false;
                }
                return true;
            };
            
            vm.verifyEditStore = function(store) {
                if(
                    store.id === '' ||
                    store.name === '' ||
                    store.contact_no === '' ||
                    store.address === '' ||
                    store.customer_name === ''
                ) {
                    return false;
                }
                return true;
            };
            
            
            vm.submitStore = function() {
              if (!vm.store.id) {
                vm.createStore();
              } else {
                vm.editStore();
              }
            };
        }]);
});





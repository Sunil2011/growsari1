/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {

    module.registerController('orderAddController', ['ORDER_EVENTS', 'notify', '$scope', '$rootScope', '$state', '$filter', '$stateParams', 'orderDataService', 'storeDataService', '$uibModal',
        function (ORDER_EVENTS, notify, $scope, $rootScope, $state, $filter, $stateParams, orderDataService, storeDataService, $uibModal) {
            var vm = this;
            var _selected;
            $scope.selected = undefined;
            vm.showItems = false;
            vm.selectedItems = [];
            vm.selectedPrdIds = [];

            vm.selectedStore = function (value) {
                if (arguments.length) {
                    vm.getStores(value);
                    _selected = value;
                } else {
                    return _selected;
                }
            };

            $scope.modelOptions = {
                debounce: {
                    default: 500,
                    blur: 250
                },
                getterSetter: true
            };

            vm.getStores = function (search) {
                if (search) {
                    var param = {
                        search: search
                    };
                    storeDataService.getStores(param)
                            .then(function (response) {
                                if (response.success) {
                                    vm.stores = response.data.list;
                                } else {
                                    notify.error('Store List', response.message);
                                }
                            });
                }
            }
            
            vm.updatePoints = function() {
                var selStoreDet = vm.selectedStore();
                vm.available_points = selStoreDet.points || 0;
            }

            vm.submitOrder = function () {
                var selStoreDet = vm.selectedStore();
                if(selStoreDet.store_id === undefined || vm.selectedItems.length == 0) {
                    notify.error('Order', 'Select store and item.');
                    return false;
                }
                var items = [];
                $(vm.selectedItems).each(function(index, value) {
                    items.push({product_id: value.id,quantity: value.quantity});
                });
                
                var param = {
                    store_id: selStoreDet.store_id,
                    products: JSON.stringify(items),
                    use_loyality_points: vm.loyalty_point,
                    delivered_by: $filter('date')(vm.delivery_date, 'yyyy-MM-dd') || ''
                }
                orderDataService.addOrderByCustCare(param)
                    .then(function (response) {
                        if (response.success) {
                            notify.success('Order', response.message);
                            $state.go('app.callcenter');
                        } else {
                            notify.error('Order', response.error.message);
                        }
                });
            }

            vm.addItem = function () {
                var modalInstance = $uibModal.open({
                    animation: $scope.animationsEnabled,
                    templateUrl: './js/modules/callcenter/views/add-items.html?v='+cacheBuster,
                    controller: 'orderAddItemsModalController',
                    controllerAs: 'orderAddItem',
                    size: 'lg',
                    backdrop: 'static',
                    resolve: {
                        orderId: function () {
                            return null;
                        },
                        orderAction: function () {
                            return 'add';
                        }
                    }
                });
            }
            
            vm.removeItem = function(key) {
                vm.selectedItems.splice(key, 1);
                notify.success('Order','Item removed successfully.');
            }

            $rootScope.$on('itemDetails', function (event, data) {
                var prd = angular.copy(data);
                var isAdded = false;
                
                $(vm.selectedItems).each(function(index, value) {
                    if(value.id == prd.id) {
                        isAdded = true;
                        return false;
                    }
                });
                
                if(isAdded) {
                    notify.error('Order','Item already added');
                    return false;
                }
                
                vm.selectedItems.push(prd);
                if(vm.selectedItems.length) {
                    vm.showItems = true;
                }
                
                notify.success('Order','Item added succesfully!');
            });
            
            vm.openDate = function() {
                vm.isDateOpen = true;
            }
        }]);

});



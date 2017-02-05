
define(['./../module'], function (module) {

  module.registerController('storeController', ['notify', '$scope', '$state', '$stateParams', '$uibModal', 'storeDataService', 'CONFIG', 'paginationSrvc',
    function (notify, $scope, $state, $stateParams, $uibModal, storeDataService, CONFIG, paginationSrvc) {
            var vm = this;
            vm.storeParam = {
                page: $stateParams.page || 1,
                search: $stateParams.search || ''
            };

            $scope.getStores = function () {
                storeDataService.getStoreList(vm.storeParam)
                        .then(function (response) {
                            if (response.success) {
                                var storeData = response.data.store;
                                vm.store = storeData.list;
                                paginationSrvc.setPagination(storeData.totalCount, storeData.page, storeData.count_per_page);
                                if (storeData.totalCount == 0) {
                                    vm.noResults = true;
                                } else {
                                    vm.noResults = false;
                                }
                            } else {
                                notify.error('Store List', response.message);
                            }
                        });
            };

            $scope.getStores();

            vm.addMoney = function (storeId) {
                if (storeId) {
                    var modalInstance = $uibModal.open({
                        animation: $scope.animationsEnabled,
                        templateUrl: './js/modules/admin/views/update-store-wallet.html?v='+cacheBuster,
                        controller: 'updateWalletModalController',
                        controllerAs: 'updateWallet',
                        size: 'md',
                        backdrop: 'static',
                        resolve: {
                            orderId: function () {
                                return null;
                            },
                            storeId: function () {
                                return storeId;
                            }
                        }
                    });
                }
            }

            $scope.addStore = function () {
                $state.go('app.storeAddEdit', {});
            };

            $scope.editStore = function ($id) {
                $state.go('app.storeAddEdit', {store_id: $id});
            };

            $scope.deleteStore = function (id) {
                var storeId = vm.id || '';
                if (!storeId) {
                    alert('Please select the store');
                    return;
                }

                var r = confirm("Are you sure, do you want to delete?");
                if (r === true) {
                    storeDataService.deleteStore({id: storeId})
                            .then(function (response) {
                                if (response && response.success) {
                                    notify.success('Store', response.message);
                                    $state.reload();
                                } else {
                                    notify.error('Store', response.error.message);
                                }
                            });
                }
            };

            $scope.BaseUrl = CONFIG.BaseUrl;
        }]);
});


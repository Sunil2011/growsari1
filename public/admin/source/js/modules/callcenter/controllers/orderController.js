/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {

  module.registerController('orderController', ['$q','ORDER_EVENTS', 'notify', '$scope', '$rootScope', '$state', '$stateParams', '$filter', 'orderService', '$uibModal', 'orderDataService',
    function ($q, ORDER_EVENTS, notify, $scope, $rootScope, $state, $stateParams, $filter, orderService, $uibModal, orderDataService) {
      var vm = this;
      orderService.page = $stateParams.page || 1;
      orderService.search = $stateParams.search;
      orderService.setOrderStatus('callCenterOrders');

      $scope.addItems = function (orderId) {
        if (orderId) {
          var modalInstance = $uibModal.open({
              animation: $scope.animationsEnabled,
              templateUrl: './js/modules/callcenter/views/add-items.html?v='+cacheBuster,
              controller: 'orderAddItemsModalController',
              controllerAs: 'orderAddItem',
              size: 'lg',
              backdrop: 'static',
              resolve: {
                  orderId: function () {
                      return orderId;
                  },
                  orderAction: function () {
                       return 'edit';
                  }
              }
          });

          modalInstance.result.finally(function () {
            $rootScope.$broadcast(ORDER_EVENTS.ADDED_ITEM, { orderId: orderId});
          });
        }

      };
      
      $scope.addPoints = function (orderId, storeId) {
        if (orderId) {
            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: './js/modules/callcenter/views/add-loyalty-point.html?v='+cacheBuster,
                controller: 'updateWalletModalController',
                controllerAs: 'updateWallet',
                size: 'md',
                backdrop: 'static',
                resolve: {
                    orderId: function () {
                        return orderId;
                    },
                    storeId: function () {
                        return storeId;
                    }
                }
            });
            
            modalInstance.result.finally(function () {
              $state.reload();
            });
        }
      };
      
      $scope.cancelOrder = function (orderId) {
        var r = confirm("Are you sure, do you want to cancel?");
        if (r === true) {
          orderDataService.updateOrderStatus({
            order_id: orderId,
            status: 'cancelled'
          })
          .then(function (response) {
            if (response && !response.error) {
              notify.success('Order #' + orderId, response.message);
              $state.reload();
            } else {
              notify.error('Order #' + orderId, response.error.message);
            }
          });
        }
      };
      
      $scope.confirmOrder = function (orderId) {
        var data = orderService.getStockConfirmationParams(orderId);
        if (data === false) {
          return;
        }

        orderDataService.updateOrderStatus(data)
        .then(function (response) {
          if (response && !response.error) {
            notify.success('Order #' + orderId, response.message);
            $state.reload();
          } else {
            notify.error('Order #' + orderId, response.error.message);
          }
        });
      };
      
      $scope.pack = function (orderId) {
        var boxCount = orderService.getPackBoxCount(orderId);
        var data = orderService.getStockConfirmationParams(orderId);
        if (data === false) {
          return;
        }

        if (boxCount && data) {
          data['status'] = 'packed';
          data['no_of_boxes'] = boxCount;

          orderDataService.updateOrderStatus(data)
          .then(function (response) {
            if (response && !response.error) {
              notify.success('Order #' + orderId, response.message);
              $state.reload();
            } else {
              notify.error('Order #' + orderId, response.error.message);
            }
          });
        } else {
          notify.info('Order #' + orderId, 'Please enter the no of boxes!');
        }
      };
      
      $scope.taskFinish = function (orderId, taskId) {
        var r = confirm("Are you sure, do you want to finish the task?");
        if (r === true) {
          orderDataService.taskFinish({
            order_id: orderId,
            task_id: taskId
          })
          .then(function (response) {
            if (response && !response.error) {
              notify.success('Order #' + orderId, response.message);
              $state.reload();
            } else {
              notify.error('Order #' + orderId, response.error.message);
            }
          });
        }
      };
      
      $scope.addOrder = function() {
          $state.go('app.order_add');
      };
        
      $scope.deleteOrderItem = function (orderId, itemId) {
          var r = confirm("Are you sure, do you want to delete item?");
          if (r === true) {
              var d = $q.defer();

              orderDataService.deleteOrderItem({
                  order_id: orderId,
                  item_id: itemId
              }).then(function (response) {
                  if (response.success) {
                      notify.success('Order', response.message);
                      d.resolve();
                      $rootScope.$broadcast(ORDER_EVENTS.ADDED_ITEM, {orderId: orderId});
                  } else {
                      notify.error('Order', response.error.message);
                      d.reject();
                  }
              });

              return d.promise;
          }
      }

      $scope.editOrderItem = function(orderId, itemId, quantity, isAvailable, selectedQuantity) {
          var r = confirm("Are you sure, do you want to save changes?");
          if (r === true) {
              var d = $q.defer();
              var quantity = quantity;

              if (selectedQuantity !== undefined) {
                  quantity = selectedQuantity;
              }

              if (isAvailable === false) {
                  quantity = 0;
              }

              orderDataService.editOrderItem({
                  order_id: orderId,
                  product_id: itemId,
                  quantity: quantity
              })
              .then(function (response) {
                  if (response.success) {
                      notify.success('Order', response.message);
                      d.resolve();
                      $rootScope.$broadcast(ORDER_EVENTS.ADDED_ITEM, { orderId: orderId});
                  } else {
                      notify.error('Order', response.error.message);
                      d.reject();
                  }
              });

              return d.promise;
          }
      }

      $scope.changeDeliveryDate = function (orderId, date) {
            var r = confirm("Are you sure, do you want to save changes?");
            if (r === true) {
                var d = $q.defer();

                var newDateString = $filter('date')(date, 'yyyy-MM-dd') || '';
                orderDataService.changeDeliveryDate({
                    order_id: orderId,
                    delivered_by: newDateString
                })
                .then(function (response) {
                    if (response.success) {
                        notify.success('Order', response.message);
                        d.resolve();
                    } else {
                        notify.error('Order', response.error.message);
                        d.reject();
                    }
                });

                return d.promise;
            }
        };

    }]);

});


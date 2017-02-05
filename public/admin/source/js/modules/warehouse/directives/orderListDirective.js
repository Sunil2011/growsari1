/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define(['./../module'], function (module) {

  module.registerDirective('gsOrderList', ['$parse', function () {
      return {
        restrict: 'A',
        scope: {
          confirmOrder: '&',
          cancelOrder: '&',
          readyToPack: '&',
          pack: '&',
          addItems: '&',
          addPoints: '&',
          addOrder: '&',
          taskFinish: '&',
          deleteOrderItem: '&',
          editOrderItem: '&',
          changeDeliveryDate: '&',
          orderActionTitle: '@',
          orderActionCallback: '&',
          orderActionCallbackExists: '@orderActionCallback',
        },
        replace: true,
        templateUrl: './js/modules/warehouse/views/gs-order-list.html?v='+cacheBuster,
        controller: function ($q, notify, ORDER_EVENTS, $rootScope, $attrs, $scope, $state, $stateParams, $filter, orderDataService, orderService, paginationSrvc, CONFIG) {
          $scope.CONFIG = CONFIG;
          $scope.srvc = orderService;
          $scope.fields = [];
          
          var vm = this;
          vm.boxDetails = {};
          vm.screenType = $scope.srvc.orderStatus;
          vm.userSession = $rootScope.userSession;
          vm.userType = vm.userSession.type.toUpperCase();
          
          switch (vm.screenType) {
            case 'pending' :
              vm.pendingStatus = true;
              vm.orderStatus = 'pending';
              vm.state = '';
              break;
            case 'confirmed' :
              vm.confirmStatus = true;
              vm.orderStatus = 'confirmed';
              break;
            case 'shipper_confirmed' :
              vm.confirmStatus = true;
              vm.orderStatus = 'confirmed,ready_to_pack';
              break;
            case 'ready_to_pack' :
              vm.packingStatus = true;
              vm.orderStatus = 'ready_to_pack';
              break;
            case 'allOrders' :
              vm.allStatus = true;
              vm.orderStatus = '';
              break;
            case 'adminOrders' :
              vm.allStatus = true;
              vm.orderStatus = '';
              vm.adminView = true;
              break;
            case 'packed' :
              vm.allStatus = true;
              vm.orderStatus = 'packed';
              break;
            case 'dispatched' :
              vm.allStatus = true;
              vm.orderStatus = 'dispatched';
              break;
            case 'delivered' :
              vm.allStatus = true;
              vm.orderStatus = 'delivered';
              break;
            case 'callCenterOrders' : 
              vm.allStatus = true;
              vm.orderStatus = 'pending,confirmed,ready_to_pack,packed,dispatched,delivered';
              break;
            case 'replacementOrders' :
              vm.orderStatus = 'pending';
              vm.replaceTask = 1;
              break;
            default :
              break;
          }

          var param = {
            status: vm.orderStatus,
            page: $scope.srvc.page,
            search: $scope.srvc.search,
            delivery_date: $stateParams.delivery_date || '',
            sort_by: $stateParams.sort_by || '',
            is_replace_task: vm.replaceTask || null,
          };
          
          if(vm.sortBy === undefined) {
              vm.sortBy = 'created_at';
          }
          
          if ($scope.orderActionTitle === 'Assign Shipper') {
            param['assign_shipper'] = 1;
          }

          orderDataService.getorderList(param)
          .then(function (orderData) {
            vm.list = orderData.list;
            if (orderData.totalCount == 0) {
              vm.noResults = true;
            } else {
              vm.noResults = false;
            }
            paginationSrvc.setPagination(orderData.totalCount, orderData.page, orderData.count_per_page);
          });
          
          // listen to order EVENTS
          $rootScope.$on(ORDER_EVENTS.ADDED_ITEM, function(event, args) {
            if (args.orderId) {
              vm.toggleOrderList(args.orderId, 1, 1);
            }
          });

          this.toggleOrderList = function (orderId, show, fetchList) {
            var field = "showList" + orderId;
            if ($scope[field] === true && !show) {
              $scope[field] = false;
              return;
            }
            
            if (vm["itemlist" + orderId] == undefined || fetchList) {
              orderDataService.getorderDetails(orderId)
              .then(function (orderData) {

                var items = orderData.order.items.list;
                vm["itemlist" + orderId] = items;
                vm["itemlistStatus" + orderId] = orderService.initializeItemList(orderId, items);

                //add date and checkbox New Orders
                $.each(items, function (index, value) {
                  if (value.is_available == '1') {
                    vm['isSelected' + value.id] = true;
                  }
                });
              });
            }
                
            $scope[field] = true;
          };

          //datepicker
          $scope.openDate = function (orderId, type) {
            $scope.srvc[type + orderId] = true;
          };
          $scope.formatDate = $rootScope.formatDate;
         
          //item select
          $scope.selectItems = function (orderId, itemId, status) {
            if (status) {
              vm['isSelected' + itemId] = true;
              vm['isSelectedQuantity' + itemId] = orderService.updateToOriginalItemQuantity(orderId, itemId);
            } else {
              vm['isSelected' + itemId] = false;
              vm['isSelectedQuantity' + itemId] = 0;              
              orderService.updateItemQuantity(orderId, itemId, 0);
            }
            
            var resp = orderService.updateItemStatus(orderId, itemId, status);
            if (resp == true) {
              vm['isAllSelected' + orderId] = true;
            } else {
              vm['isAllSelected' + orderId] = false;
            }
          };

          $scope.selectAllItems = function (orderId, items) {
            var status = false;
            if (vm['isAllSelected' + orderId]) {
              status = true;
            }
            
            $.each(items, function (index, value) {
              $scope.selectItems(orderId, value.id, status);
            });
          };
          
          $scope.updateQuantity = function (orderId, itemId, quantity) {
            if (!quantity) {
              vm['isSelected' + itemId] = false;
              vm['isAllSelected' + orderId] = false;
            } else {
              vm['isSelected' + itemId] = true;
            }
            
            orderService.updateItemQuantity(orderId, itemId, quantity);
          };          
          
          //apply search
          vm.getSearch = function () {
            $state.go($state.current, {search: $scope.srvc.search});
          };
          
          //apply search
          vm.filterByDelDate = function () {
              var newDateString = $filter('date')($scope.srvc.delivery_date, 'yyyy-MM-dd') || '';
              $state.go($state.current, {delivery_date: newDateString});
          };
          
          //sort
          vm.sortOrders = function () {
              $state.go($state.current, {sort_by: vm.sortBy});
          };
          
          $scope.printPicklist = function (orderId, type) {
                var url = CONFIG.ApiBaseUrl + '/order/' + orderId + '/picklist/' + type
                var printWindow = window.open( url, 'Print', 'left=200, top=200, width=950, height=500, toolbar=0, resizable=0');
                printWindow.addEventListener('load', function(){
                    printWindow.print();
                    setTimeout(function () { printWindow.close(); }, 100);
                }, true);
          }
          
          //get sku name
          $scope.getSku = function (orderId, itemId) {
            if ($scope.srvc.orderItemsArray[orderId] != undefined) {
              return $scope.srvc.orderItemsArray[orderId][itemId].sku;
            }
          };
          
          $scope.opened = {};
          $scope.open = function($event, elementOpened) {
              $event.preventDefault();
              $event.stopPropagation();
              $scope.opened[elementOpened] = !$scope.opened[elementOpened];
          };
          
          vm.openDelDate = function($event, elementOpened) {
              vm.isDateOpen = true;
          };
          
        },
        
        controllerAs: 'order'
      };
    }]);
});
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {

    "use strict";

    module.registerService('orderService', ['notify', '$q', '$log', '$filter',
        function (notify, $q, $log, $filter) {
            var vm = this;

            vm.orderStatus = '';
            vm.page = '';
            vm.search = '';
            vm.delivery_date = '';
            vm.setOrderStatus = function (status) {
                vm.orderStatus = status;
            };

            vm.boxCounts = [];
            vm.orderItemsArray = {};

            vm.initializeItemList = function (orderId, itemsArray) {
                vm.orderItemsArray[orderId] = {};
                $.each(itemsArray, function (index, value) {
                    var temp = {
                        item_id: value.id,
                        is_available: false,
                        total_quantity: value.quantity,
                        quantity: value.quantity,
                        packed_quantity: 0,
                        sku: value.sku
                    };

                    if (value.is_available == '1') {
                        temp.is_available = true;
                    }

                    vm.orderItemsArray[orderId][value.id] = temp;
                });
            };

            vm.updateItemStatus = function (orderId, itemId, status) {
                var allSelected = true;
                $.each(vm.orderItemsArray[orderId], function (index, value) {
                    if (value.item_id === itemId) {
                        vm.orderItemsArray[orderId][index].is_available = status;
                    }

                    if (value.is_available === false) {
                        allSelected = false;
                    }
                });

                return allSelected;
            };

            vm.updateItemQuantity = function (orderId, itemId, quantity) {
                $.each(vm.orderItemsArray[orderId], function (index, value) {
                    if (value.item_id === itemId) {
                        if (quantity === 0) {
                            vm.orderItemsArray[orderId][index].is_available = false;
                        }

                        vm.orderItemsArray[orderId][index].quantity = quantity;
                    }
                });

                return true;
            };
            
            vm.updateToOriginalItemQuantity  = function (orderId, itemId) {
                var quantity = 0;
                $.each(vm.orderItemsArray[orderId], function (index, value) {
                    if (value.item_id === itemId) {
                        quantity = vm.orderItemsArray[orderId][index].total_quantity;
                        vm.orderItemsArray[orderId][index].quantity = quantity;
                    }
                });

                return parseInt(quantity);
            };

            vm.getStockConfirmationParams = function (orderId) {
                var notAvailableCount = 0;
                var anyModification = 0;
                var total = 0;
                $.each(vm.orderItemsArray[orderId], function (index, value) {
                    if (value.is_available === false) {
                        notAvailableCount += 1;
                    }
                    
                    if (value.quantity != undefined && value.quantity != value.total_quantity) {
                        anyModification += 1;
                    }

                    total += 1;
                });

                if (notAvailableCount === total) {
                    notify.info('Order #' + orderId, 'Please select available items, none selected!');
                    return;
                }
                
                if ((vm.orderStatus == 'pending' || vm.orderStatus == 'replacementOrders') && anyModification > 0) {
                    if (!confirm("Items are out of stock, are you sure you want to send to call center?")) {
                        return false;
                    }
                } else if((vm.orderStatus == 'pending' || vm.orderStatus == 'replacementOrders') && !confirm("Are you sure you want to confirm the order?")) {
                    return false;
                }

                return {
                    order_id: orderId,
                    status: 'confirmed',
                    item_status: JSON.stringify(vm.orderItemsArray[orderId])
                };
            };

            vm.getPackBoxCount = function (orderId) {
                return vm.boxCounts[orderId] ? vm.boxCounts[orderId] : 0;
            };

            //get current status
            vm.getStatus = function (status) {
                var statusName = '';
                switch (status) {
                    case 'pending' :
                        statusName = 'Pending';
                        break;
                    case 'confirmed' :
                        statusName = 'Confirmed';
                        break;
                    case 'ready_to_pack' :
                        statusName = 'Confirmed';
                        break;
                    case 'packed' :
                        statusName = 'Packed';
                        break;
                    case 'dispatched' :
                        statusName = 'Dispached';
                        break;
                    case 'delivered' :
                        statusName = 'Delivered';
                        break;
                    case 'cancelled' :
                        statusName = 'Cancelled';
                        break;
                    default :
                        statusName = 'Unknown';
                        break;
                }
                return statusName;
            };

            //checking if any order update
            vm.isOrderCountUpdated = function (oldCount, newCount, userType) {
                if (oldCount != undefined) {
                    $.each(oldCount, function (index, value) {
                        if (value < newCount[index]) {
                            getOrderMesage(index, userType);
                        }
                    });
                }
            };
            
            function getOrderMesage(field, userType) {
                switch (field) {
                    case 'new_orders_count' :
                        if(userType === 'WAREHOUSE') {
                            notify.info('Orders', 'Hey, You got a new order.', false);
                        }
                        break;
                    case 'confirmed_orders_count' :
                        if(userType === 'SHIPPER') {
                            notify.info('Orders', 'Hey, You got a new confirmed order.', false);
                        }
                        break;
                    case 'pickup_orders_count' :
                        if(userType === 'SHIPPER') {
                            notify.info('Orders', 'Hey, You got a new order which is ready to pick up!', false);
                        }
                        break;
                    case 'replacement_confirmed_count' :
                        if(userType === 'WAREHOUSE') {
                            notify.info('Orders', 'Hey, You got a replacement confirmed from callcenter!', false);
                        }
                        break;
                    case 'task_count' :
                        notify.info('Orders', 'Hey, You got a new order task!', false);
                        break;
                    default :
                        break;
                }
            }

        }]);

});
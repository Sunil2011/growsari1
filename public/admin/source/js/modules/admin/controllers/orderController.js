/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {
    
  module.registerController('orderController', ['CONFIG', '$scope', '$location', '$timeout', '$state', '$stateParams', 'orderService',
        function (CONFIG, $scope, $location, $timeout, $state, $stateParams, orderService) {
            var vm = this;            
            orderService.page = $stateParams.page || 1;
            orderService.search = $stateParams.search;
            orderService.setOrderStatus('adminOrders');
            
            $scope.printInvoice = function(order) {
                var url = CONFIG.ApiBaseUrl + '/order/' + order.id + '/invoice'
                var printWindow = window.open( url, 'Print', 'left=200, top=200, width=950, height=500, toolbar=0, resizable=0');
                printWindow.addEventListener('load', function(){
                    printWindow.print();
                    setTimeout(function () { printWindow.close(); }, 100);
                }, true);
            };
    }]);

});
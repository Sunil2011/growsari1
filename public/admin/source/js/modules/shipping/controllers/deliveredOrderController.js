/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(['./../module'], function (module) {
    
  module.registerController('deliveredOrderController', ['$scope', '$stateParams', 'orderService','orderDataService', 'CONFIG',
        function ($scope, $stateParams, orderService, orderDataService, CONFIG) {
            var vm = this;
            orderService.page = $stateParams.page || 1;
            orderService.search = $stateParams.search;
            orderService.setOrderStatus('delivered');
            
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
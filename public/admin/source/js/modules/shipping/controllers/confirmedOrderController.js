/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    module.registerController('confirmedOrderController', ['notify', '$scope', '$state','$stateParams', 'orderService',
        function (notify, $scope, $state, $stateParams, orderService) {
            var vm = this;
            orderService.page = $stateParams.page || 1;
            orderService.search = $stateParams.search;
            orderService.setOrderStatus('shipper_confirmed');
    }]);

});

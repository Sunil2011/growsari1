/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    module.registerDirective('gsDatepicker', [ function () {
        return {
            restrict : 'E',
            replace: true,
            templateUrl : './js/modules/layout/views/gs-datepicker.html',
            controller : function($scope, $state) {
                
            },
        };
    }]);
});


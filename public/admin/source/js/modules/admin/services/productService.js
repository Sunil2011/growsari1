/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    "use strict";
    module.registerService('productService' , ['$q', '$log', '$filter', 
        function($q, $log, $filter) {
            var vm = this;
            
            vm.verifyProductField = function(product) {
                if(
                    product.brand_id === undefined  ||
                    product.category_id === undefined ||
                    product.format === undefined ||
                    product.price === undefined ||
                    product.srp === undefined ||
                    product.sku === undefined ||
                    product.volume === undefined ||
                    product.brand_id == '' ||
                    product.category_id == '' ||
                    product.format == '' ||
                    product.price == '' ||
                    product.sku == '' ||
                    product.volume == '' ||
                    product.srp == ''
                    ) {
                    return false
                }
                return true;
            }
            
        }]);
})

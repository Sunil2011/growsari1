
define(['./../module'], function (module) {

    module.registerController('productAddEditController', 
    ['notify', '$scope', '$state', 'productDataService', 'categoryDataService', 'brandDataService', 'productService', 'CONFIG', '$stateParams',
        function (notify, $scope, $state, productDataService, categoryDataService, brandDataService, productService, CONFIG, $stateParams) {
            var vm = this;

            vm.product = {};
            vm.product.id = $stateParams.product_id || '';

            if(vm.product.id) {
                productDataService.getproductDetails(vm.product.id)
                .then(function (response) {
                    if (response.success) {
                        vm.product = response.data.product;
                    } else {
                        alert(response.message);
                    }
                });
            }
            
            vm.updateSKU = function(brandId) {
                if(brandId != undefined) {
                    $.each(vm.brands, function(i, val){
                        if(val.brand_id == brandId) {
                            vm.product.brandName = val.brand_name;
                            return false;
                        }
                    });
                }
            };
            
            categoryDataService.getCategoryList()
                .then(function (response) {
                    if (response.success) {
                        vm.categories = response.data.category.list;
                    } else {
                        notify.error('Product',response.message);
                    }
                });

            brandDataService.getBrandList()
                .then(function (response) {
                    if (response.success) {
                        vm.brands = response.data.brand.list;
                    } else {
                        notify.error('Product',response.message);
                    }
                });

            vm.cancel = function () {
                $state.go('app.admin');
            };

            vm.submitProduct = function () {
                var verify = productService.verifyProductField(vm.product);
                if(!verify) {
                    notify.info('Product','Mandatory parameter required.');
                    return;
                }

                var data = {
                    'id': vm.product.id,
                    'category_id': vm.product.category_id,
                    'brand_id': vm.product.brand_id,
                    'volume': vm.product.volume,
                    'sku': vm.product.sku,
                    'super8_name': vm.product.super8_name,
                    'item_code': vm.product.item_code,
                    'barcode': vm.product.barcode,
                    'variant_color': vm.product.variant_color,
                    'format': vm.product.format,
                    'quantity': vm.product.quantity,
                    'price': vm.product.price,
                    'srp': vm.product.srp,
                    'is_promotional': vm.product.is_promotional,
                    'is_recommended': vm.product.is_recommended,
                    'is_new': vm.product.is_new,
                    'is_locked': vm.product.is_locked,
                    'is_available': vm.product.is_available
                };
                
                productDataService.addEditProduct(data)
                .then(function (response) {
                    if (response && !response.error) {
                        notify.success('Product',response.message);
                        $state.go('app.admin');
                    } else {
                        notify.error('Product',response.error.message);
                    }
                });
            }
        }]);
});





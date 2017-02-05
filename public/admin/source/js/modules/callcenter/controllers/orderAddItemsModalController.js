define(['./../module'], function (module) {

  module.registerController('orderAddItemsModalController', ['CONFIG', 'notify', '$uibModalInstance', '$scope', '$rootScope', '$state', 'orderId', 'orderAction', 'orderDataService', 'productDataService', 'brandDataService', 'categoryDataService', 'paginationSrvc',
    function (CONFIG, notify, $uibModalInstance, $scope, $rootScope, $state, orderId, orderAction, orderDataService, productDataService, brandDataService, categoryDataService, paginationSrvc) {
      var vm = this;

      $scope.orderId = orderId;
      $scope.categoryId = '';
      $scope.brandId = '';
      $scope.page = '';
      $scope.search = '';
      
      vm.close = function(boxId) {
          $uibModalInstance.close();
      };

      $scope.getParams = function() {
        return {
          category_id: $scope.categoryId,
          brand_id: $scope.brandId,
          page: $scope.page || 1,
          limit: 25,
          search: $scope.search || ''
        };
      };

      $scope.getProducts = function () {
        productDataService.getproductList($scope.getParams())
                .then(function (response) {
                  if (response.success) {
                    var productData = response.data.product;
                    vm.prd = productData.list;
                    paginationSrvc.setPagination(
                            productData.totalCount, 
                            productData.page, 
                            productData.count_per_page,
                            function(pageObj) {
                              $scope.page=pageObj.page;
                              $scope.getProducts();
                            });
                    if (productData.totalCount == 0) {
                      vm.noResults = true;
                    } else {
                      vm.noResults = false;
                    }
                  } else {
                    notify.error('Product List', response.message);
                  }
                });
      };

      $scope.getProducts();

      $scope.categories = categoryDataService.getCategoryList()
              .then(function (response) {
                if (response.success) {
                  $scope.cats = response.data.category.list;
                } else {
                  notify.error('Product List', response.message);
                }
              });


      $scope.brands = brandDataService.getBrandList()
              .then(function (response) {
                if (response.success) {
                  $scope.brds = response.data.brand.list;
                } else {
                  alert(response.message);
                }
              });

      vm.addItems = function(key, q) {
        if (key === undefined || vm.prd[key] === undefined || !q) {
          notify.error('Order #' + orderId, "Please select the product & enter quantity");
          return;
        }
        
        var product = vm.prd[key];
        product.quantity = q;
         
        if(orderAction == 'add') {
            $rootScope.$emit('itemDetails', product);
            vm.quantity = [];
            delete vm.productIndex;
        } else {
            orderDataService.addItemsToExistingOrder({
                order_id: $scope.orderId,
                products: JSON.stringify([{
                  product_id: product.id,
                  quantity: q
                }])
              }).then(function (response) {
                  vm.quantity = [];
                  delete vm.productIndex;

                  if (response.success) {
                      notify.success('Order',response.message);
                  } else {
                      notify.error('Order',response.error.message);
                  }
              });
        }
      };

    }]);
});



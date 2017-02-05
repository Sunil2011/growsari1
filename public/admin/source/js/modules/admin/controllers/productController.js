
define(['./../module'], function (module) {

  module.registerController('productController', ['notify', '$scope', '$state', '$stateParams', 'productDataService', 'categoryDataService', 'brandDataService', 'CONFIG', 'paginationSrvc',
    function (notify, $scope, $state, $stateParams, productDataService, categoryDataService, brandDataService, CONFIG, paginationSrvc) {
      var vm = this;

      vm.productParam = {
        category_id: $stateParams.category_id,
        brand_id: $stateParams.brand_id,
        page: $stateParams.page || 1,
        search: $stateParams.search || ''
      };

      $scope.getProducts = function() {
        productDataService.getproductList(vm.productParam)
        .then(function (response) {
          if (response.success) {
            var productData = response.data.product;
            vm.prd = productData.list;
            paginationSrvc.setPagination(productData.totalCount, productData.page, productData.count_per_page);
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

      $scope.prodCatAndOrBrand = function ($category_id, $brand_id, $search) {
        if ($category_id || $brand_id || $search) {
          $state.go('app.admin', {category_id: $category_id, brand_id: $brand_id, search: $search});
        } else {
          $state.go('app.admin', {});
        }
      };

      $scope.brands = brandDataService.getBrandList()
      .then(function (response) {
        if (response.success) {
          $scope.brds = response.data.brand.list;
        } else {
          alert(response.message);
        }
      });

      $scope.addProduct = function () {
        $state.go('app.productAddEdit', {});
      };
      
      $scope.export = function () {
        window.location.href = CONFIG.ApiBaseUrl + '/product/export';
      };

      $scope.editProduct = function ($id) {
        $state.go('app.productAddEdit', {product_id: $id});

      };

      $scope.deleteProduct = function (id) {
        var productId = vm.id || '';
        if (!productId) {
          notify.error('Product', 'Please select the product');
          return;
        }

        var r = confirm("Are you sure, do you want to delete?");
        if (r === true) {
          productDataService.deleteProduct({id: productId})
          .then(function (response) {
            if (response && response.success) {
              notify.success('Product', response.message);
              $state.reload();
            } else {
              notify.error('Product', response.error.message);
            }
          });
        }
      };

      $scope.BaseUrl = CONFIG.BaseUrl;
      $scope.selectedCat = $stateParams.category_id;
      $scope.selectedBrand = $stateParams.brand_id;
      $scope.selectedSearch = $stateParams.search;

    }]);

});


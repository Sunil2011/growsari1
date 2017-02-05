define(['./../module'], function (module) {

  module.registerController('assignShipperMapController', ['$filter', 'CONFIG', 'uiGmapIsReady', 'notify', '$scope', '$state', '$stateParams', '$uibModal', 'orderService', 'orderDataService', 'shipperTeamDataService',
    function ($filter, CONFIG, uiGmapIsReady, notify, $scope, $state, $stateParams, $uibModal, orderService, orderDataService, shipperTeamDataService) {
      var vm = this;
      $scope.lastLoadedPoint = 0;
      $scope.zoom = 0;
      
      //datepicker
      $scope.deliveryDate = '';
      $scope.formatDate = function(date) {
          return new Date(date);
      };
      $scope.openDate = function() {
          $scope.isOpen = true;
      };

      $scope.orders = [];
      $scope.getOrders = function() {
        $scope.deliveryDate = $filter('date')($scope.deliveryDate, "yyyy-MM-dd");
        
        orderDataService.getorderList({
          page: 1,
          limit: 1000,
          assign_shipper:1,
          delivery_date: $scope.deliveryDate
        })
        .then(function (orderData) {
          $scope.orders = $scope.formatStoresData(orderData.list);

          uiGmapIsReady.promise().then(function () {
            google.maps.event.addListenerOnce($scope.map.control.getGMap(), 'idle', function(){
              //loaded fully
              if ($scope.lastLoadedPoint){
                $scope.map.control.getGMap().setCenter(new google.maps.LatLng($scope.lastLoadedPoint.latitude, $scope.lastLoadedPoint.longitude));
                $scope.map.control.getGMap().setZoom($scope.zoom);
              }
            });
          });
        });
      }
      $scope.getOrders();

      $scope.formatStoresData = function (data) {
        var orders = [];
        var n = (data) ? data.length : 0;
        for (var i = 0; i < n; i++) {
          var temp = data[i];
          temp['point'] = {
            latitude:  temp['store_point_y'] + "" + Math.floor(Math.random() * 90 + 10),
            longitude: temp['store_point_x'] + "" + Math.floor(Math.random() * 90 + 10)
          };
          
          if (temp['shipper_team_id'] && temp['shipper_team_id'] !='0') {
            temp['icon'] = CONFIG.ImageUrl + '/markers/' + 'green-dot.png';
          } else {
            temp['icon'] = CONFIG.ImageUrl + '/markers/' + 'yellow-dot.png';
          }

          orders.push(temp);
        }

        return orders;
      };

      //init map
      $scope.map = {
        center: {
          latitude: 14,
          longitude: 120
        },
        zoom: 6,
        control: {}
      };

      $scope.window = {
        marker: {},
        show: false,
        closeClick: function () {
          this.show = false;
        },
        options: {}, // define when map is ready
        title: ''
      };

      $scope.markersEvents = {
        click: function (marker, eventName, model) {
          $scope.lastLoadedPoint = model.point;
          $scope.zoom = $scope.map.control.getGMap().getZoom();
          
          var modalInstance = $uibModal.open({
            animation: $scope.animationsEnabled,
            templateUrl: './js/modules/shipping/views/assign-shipper-box.html?v='+cacheBuster,
            controller: 'assignShipperBoxModalController',
            controllerAs: 'assignShipperBoxModal',
            size: 'small',
            backdrop: 'static',
            resolve: {
              order: function () {
                return model;
              }
            }
          });

          modalInstance.result.finally(function () {
            $scope.getOrders();
          });
        }
      };
      

      $scope.closeClick = function () {
        this.window = false;
      };
    }]);

});
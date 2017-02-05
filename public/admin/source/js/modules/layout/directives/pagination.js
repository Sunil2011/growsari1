/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {
    
    module.registerDirective('gsPagination', [ function () {
        return {
            restrict : 'E',
            replace: true,
            templateUrl : './js/modules/layout/views/gs-pagination.html?v='+cacheBuster,
            controller : function($scope, $state, paginationSrvc) {
                $scope.show = true;
                $scope.pagination = paginationSrvc;
                $scope.$watch('pagination.currentPage + pagination.numPerPage + pagination.totalItems', function(){
                    updatePagination();
                });
                
                $scope.setPage = function (pageNo) {
                    $scope.currentPage = pageNo;
                    if ($scope.pagination.callback) {
                      $scope.pagination.callback({page : $scope.pagination.currentPage});
                    } else {
                      $state.go($state.current.name, {page : $scope.pagination.currentPage});
                    }
                    updatePagination();
                };
                
                function updatePagination() {
                    $scope.pagination.begin = (($scope.pagination.currentPage - 1) * $scope.pagination.numPerPage) + 1
                    ,$scope.pagination.end = $scope.pagination.begin + $scope.pagination.numPerPage - 1;
                    if($scope.pagination.end > $scope.pagination.totalItems) {
                        $scope.pagination.end = $scope.pagination.totalItems;
                    }
                    if($scope.pagination.totalItems == 0) {
                        $scope.show = false;
                    }
                }
            },
        };
    }]);
});

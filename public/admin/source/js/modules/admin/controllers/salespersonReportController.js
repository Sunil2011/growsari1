/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {

    module.registerController('salespersonReportController', ['notify', '$scope', '$state', '$stateParams', '$filter', 'CONFIG', 'salesDataService', 'paginationSrvc',
        function (notify, $scope, $state, $stateParams, $filter, CONFIG, salesDataService, paginationSrvc) {
            var vm = this;
            
            vm.reportParam = {
                id : $stateParams.salesperson_id || '',
                page: $stateParams.page || 1,
                start_date: $stateParams.start_date || null,
                end_date: $stateParams.end_date || null,
            }
            
            vm.reportType = $stateParams.report_type;
            
            if($stateParams.report_type == 'briefRep') {
                vm.showBriefReport = true;
                salesDataService.getSalesReportList(vm.reportParam)
                .then(function (response) {
                    if (response.success) {
                        var data = response.data.report;
                        vm.reports = data.list;
                        paginationSrvc.setPagination(data.totalCount, data.page, data.count_per_page);
                        if (data.totalCount == 0) {
                            vm.noResults = true;
                        } else {
                          vm.noResults = false;
                        }
                    } else {
                        notify.error('Sales Person Report', response.message);
                    }
                });
            } else if($stateParams.report_type == 'detailRep') {
                vm.showDetailReport = true;
                salesDataService.getSalesDetReportList(vm.reportParam)
                .then(function (response) {
                    if (response.success) {
                        var data = response.data.report;
                        vm.reports = data.list;
                        paginationSrvc.setPagination(data.totalCount, data.page, data.count_per_page);
                        if (data.totalCount == 0) {
                            vm.noResults = true;
                        } else {
                          vm.noResults = false;
                        }
                    } else {
                        notify.error('Sales Person Report', response.message);
                    }
                });
            } else {
                vm.noResults = true;
            }
            
            
            vm.getFilteredList = function() {
                var startDate = vm.startDate || '';
                var endDate = vm.endDate || '';
                
                if(!(startDate || endDate)) {
                    return;
                }
                
                var stateName = '';
                if(vm.reportType == 'briefRep') {
                    stateName = 'app.salespersonReport';
                } else {
                    stateName = 'app.salespersonDetReport';
                }
                
                
                $state.go(stateName, {start_date: $filter('date')(startDate, 'yyyy-MM-dd'), end_date: $filter('date')(endDate, 'yyyy-MM-dd')});
            }
                
            vm.openStore = function(storeId) {
                $state.go('app.storeAddEdit', {store_id : storeId});
            }
            
            vm.openDate = function(type) {
                vm[type] = true;
            };
            
            vm.exportReport = function(type) {
                var startDate = $filter('date')(vm.startDate, 'yyyy-MM-dd') || '';
                var endDate = $filter('date')(vm.endDate, 'yyyy-MM-dd') || '';
                
                var stateName = '';
                if(vm.reportType == 'briefRep') {
                    stateName = 'export-brief-report';
                } else {
                    stateName = 'export-detail-report';
                }
                
                window.location.href = CONFIG.ApiBaseUrl + '/survey/' + stateName + '?id=' + vm.reportParam.id + '&start_date=' + startDate + '&end_date=' + endDate;
            }
            
            vm.exportDetailReport = function() {
                var startDate = $filter('date')(vm.startDate, 'yyyy-MM-dd') || '';
                var endDate = $filter('date')(vm.endDate, 'yyyy-MM-dd') || '';
                window.location.href = CONFIG.ApiBaseUrl + '/survey/export-detail-report?id=' + vm.reportParam.id + '&start_date=' + startDate + '&end_date=' + endDate;
            }
            
            vm.startDate = $stateParams.start_date;
            vm.endDate = $stateParams.end_date;
    }]);

});
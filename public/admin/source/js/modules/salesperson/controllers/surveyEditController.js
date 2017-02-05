define(['./../module'], function (module) {

    module.registerController('surveyEditController', 
    ['notify', '$scope', '$state', 'surveyDataService', 'CONFIG', '$stateParams', 'salesDataService',
        function (notify, $scope, $state, surveyDataService, CONFIG, $stateParams, salesDataService) {
            var vm = this;
            vm.survey = {};
            vm.survey.id = $stateParams.survey_id || '';
            vm.funnel_status = {
              'Owner not there' : 'Owner not there',
              'Rejected' : 'Rejected',
              'Left material' : 'Left material',
              'Download app' : 'Download app',
              'Sign-up' : 'Sign-up',
              '1st order done' : '1st order done'
            };
            vm.spend_per_week = {
              'less than 2k' : 'less than 2k',
              '2k-5k' : '2k-5k',
              '5k-10k' : '5k-10k',
              '10k-30k' : '10k-30k',
              'Sign-up' : 'Sign-up',
              '30k +' : '30k +'
            };

            if(vm.survey.id) {
                surveyDataService.getSurveyDetails(vm.survey.id)
                .then(function (response) {
                    if (response.success) {
                        vm.survey = response.data;
                        vm.survey.funnel_status = vm.survey.funnel_status.replace(/^\s+|\s+$/g, '');
                        
                        // get stores list if not linked
                        if(!parseInt(vm.survey.store_id)) {
                            vm.survey.is_store_linked = false;
                            getStores(vm.survey.account_id);
                        } else {
                            vm.survey.is_store_linked = true;
                        }
                    } else {
                        alert(response.message);
                    }
                });
            } else {
              $state.go('app.salesperson_survey');
              return;
            }
            
            function getStores(salespersonId) {
                var storeParam = {
                    salesperson_id : salespersonId
                };
                
                salesDataService.getUnassignedStore(storeParam)
                    .then(function (response) {
                        if (response.success) {
                            var storeData = response.data;
                            vm.store = storeData.list;
                        } else {
                            notify.error('Store List', response.message);
                        }
                    });
            }
           
            vm.cancel = function () {
                $state.go('app.salesperson_survey');
            };
        
            vm.editSurvey = function () {
                var verify = vm.verifyEditSurvey(vm.survey);
                if(!verify) {
                    notify.info('Survey', 'Mandatory parameter required.');
                    return;
                }
               
                var data = {
                    'id': vm.survey.id,
                    'name': vm.survey.name,
                    'customer_name': vm.survey.customer_name,
                    'contact_no': vm.survey.contact_no,
                    'address': vm.survey.address,
                    'point_x': vm.survey.point_x,
                    'point_y': vm.survey.point_y,
                    'photo': vm.survey.photo,
                    'is_covered': vm.survey.is_covered,
                    'is_storeowner': vm.survey.is_storeowner,
                    'has_smartphone': vm.survey.has_smartphone,
                    'spend_per_week': vm.survey.spend_per_week,
                    'funnel_status': vm.survey.funnel_status,
                    'revisit_date': vm.survey.revisit_date,
                    'revisit_time': vm.survey.revisit_time,
                    'remarks': vm.survey.remarks
                };
                
                if (vm.survey.is_store_linked === false) {
                    data.store_id = vm.survey.store_id;
                }
                
                if ($scope.file !== undefined) {
                    data.photo = $scope.file;
                }

                surveyDataService.editSurvey(data)
                .then(function (response) {
                    if (response && !response.error) {
                        notify.success('Survey', response.message);
                        $state.go('app.salesperson_survey');
                    } else {
                        notify.error('Survey', response.error.message);
                    }
                });
            };
            
            vm.verifyEditSurvey = function(survey) {
                if(
                    survey.id === '' ||
                    survey.name === '' ||
                    survey.contact_no === ''
                ) {
                    return false;
                }
                return true;
            };
            
            
            vm.submitSurvey = function() {
                vm.editSurvey();
            };
        }]);
});





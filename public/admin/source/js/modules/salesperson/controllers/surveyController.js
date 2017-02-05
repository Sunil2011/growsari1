define(['./../module'], function (module) {
    
  module.registerController('surveyController', ['notify', '$scope', '$state', '$stateParams', 'surveyDataService', 'CONFIG', 'paginationSrvc',
        function (notify, $scope, $state, $stateParams , surveyDataService , CONFIG, paginationSrvc) {
          
            var vm = this;
            vm.surveyParam = {
                page : $stateParams.page || 1,
                search: $stateParams.search || ''
            };
            
            surveyDataService.getSurveyList(vm.surveyParam)
                .then(function(response){
                    if(response.success) {
                        var surveyData = response.data;
                        vm.survey  =  surveyData.list;
                        paginationSrvc.setPagination(surveyData.totalCount,surveyData.page,surveyData.count_per_page);
                        if(surveyData.totalCount == 0) {
                            vm.noResults = true;
                        } else {
                          vm.noResults = false;
                        }
                    } else {
                        notify.error('Survey List',response.message);
                    }
            });
            
            $scope.editSurvey = function($id){
                $state.go('app.salesperson_surveyEdit', {survey_id : $id});
            };
            
            $scope.deleteSurvey = function(){
                var surveyId = vm.id;
                if (!surveyId) {
                  notify.error('Survey', 'Please select the survey');
                  return;
                }
                
                var r = confirm("Are you sure, do you want to delete?");
                if (r === true) {
                    surveyDataService.deleteSurvey({id: surveyId})
                      .then(function(response) {
                      if(response && response.success) {
                          notify.success('Survey', response.message);
                          $state.reload();
                      } else {
                          notify.error('Survey', response.error.message);
                      }
                  });
                } 
            };
            
            $scope.createStore  = function($id){
                $state.go('app.salesperson_storeAddEdit');
            };
            
            vm.searchSurvey = function() {
                $state.go($state.current, {search: vm.search});
            }
            
           $scope.BaseUrl = CONFIG.BaseUrl ;
    }]);

});


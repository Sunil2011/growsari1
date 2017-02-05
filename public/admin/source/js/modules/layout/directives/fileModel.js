
define(['./../module'], function (module) {
   
    module.registerDirective('fileModel', ['$parse',
        function($parse){
           return {
                restrict: 'A',
                link: function($scope, element, $attr) {
                    var model = $parse($attr.fileModel);
                    var modelSetter = model.assign;

                    element.bind('change', function(){
                        $scope.$apply(function(){
                            modelSetter($scope, element[0].files[0]);
                        });
                    });
                    
                    $scope.$watch($attr.fileModel, function(image) {
                        $($attr.fileModel).val(image);
                        if(image != undefined) {
                            $scope.fileName = image.name;
                        }
                    });
                }
           };
   }]);
    
});



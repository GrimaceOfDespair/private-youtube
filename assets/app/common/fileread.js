(function () {
  'use strict';
  
  angular
    .module('privtube.common')
    .directive("fileread", [function () {
      return {
        restrict: 'A',
        require: '?ngModel',
        link: function (scope, element, attributes, ngModel) {
          
          if (!ngModel) return;
          
          element.bind("change", function (changeEvent) {
            scope.$apply(function () {
              ngModel.$setViewValue(changeEvent.target.files[0]);
              // or all selected files:
              // scope.fileread = changeEvent.target.files;
            });
          });
        }
      };
    }]);    

})();
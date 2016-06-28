(function () {
  'use strict';
  
  angular
    .module('privtube.common')
    .directive("spinner", [function () {
      return {
        transclude: true,
        template:
            '<span ng-transclude/>'
          + '<div class="spinner" ng-class="{\'active\':busyLoad}">'
          +   '<div class="bounce1"></div>'
          +   '<div class="bounce2"></div>'
          +   '<div class="bounce3"></div>'
          + '</div>'
      };
    }]);    

})();
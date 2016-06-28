(function () {
  'use strict';
  
  angular
    .module('privtube.youtube')
    .provider('accessToken', [
    
      function accessTokenProvider() {
        
        this.$get = ['$window', function($window) {
          return {
            get: function() {
              return $window.access_token;
            }
          };
        }];
      }
    ]);
})();
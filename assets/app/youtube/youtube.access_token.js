(function () {
  'use strict';
  
  angular
    .module('privtube.youtube')
    .factory('accessToken', [
    
      '$window',
      function accessTokenFactory($window) {
        
        return $window.access_token;
      }
    ]);
})();
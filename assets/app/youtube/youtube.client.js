(function () {
  'use strict';
  
  angular
    .module('privtube.youtube')
    .factory('gapiClientFactory', ['configuration', '$window',
      function(configuration) {
        var gapiClient = $window.gapi.client;
        gapiClient.setApiKey(configuration.clientId);
        return gapiClient;
      }]);
})();
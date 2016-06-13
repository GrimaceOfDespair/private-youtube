(function () {
  'use strict';
  
  angular
    .module('privtube.common')
    
    // Make sure rest requests are authorized and CSRF safe
    .config(['$httpProvider', 'configuration',
      function($httpProvider, configuration) {
        $httpProvider.interceptors.push([function() {
          return {
            'request': function(config) {
              config.headers = config.headers || {};
              config.headers['X-WP-Nonce'] = configuration.nonce;
              return config;
            }
          };
        }]);
      }]);
})();
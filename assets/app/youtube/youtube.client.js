(function () {
  'use strict';
  
  angular
    .module('privtube.youtube')
    .factory('gapiClient', [
    
      '$q', 'configuration', '$window',
      function($q, configuration, $window) {
        
        var gapiClient = $window.gapi.client;
        
        return {
          loadChannel: function(channelId) {
            
            var deferred = $q.defer();
                
            gapiClient.load('youtube', 'v3', function() {
              gapiClient.youtube.search.list({
                part: 'snippet',
                channelId: configuration.channelId,
                maxResults: 50
              })
              .execute(function(response) {
                deferred.resolve(response.result);
              });
            });
            
            return deferred.promise;
          }
        }
      }]);
})();
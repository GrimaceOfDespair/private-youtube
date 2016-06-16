(function () {
  'use strict';
  
  angular
    .module('privtube.youtube')
    
    // Make sure rest requests are authorized and CSRF safe
    .service(['$http', '$q', 'configuration',
      function($http, $q, configuration) {
        var deferred = $q.defer();
        this.googleApiClientReady = function () {
            gapi.client.setApiKey(configuration.clientId);
            gapi.client.load('youtube', 'v3', function() {
                var request = gapi.client.youtube.playlistItems.list({
                    part: 'snippet',
                    playlistId: configuration.channelId,
                    maxResults: 8
                });
                request.execute(function(response) {
                    deferred.resolve(response.result);
                });
            });
            return deferred.promise;
        };
      }]);
})();
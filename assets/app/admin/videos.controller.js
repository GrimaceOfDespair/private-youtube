(function () {
  'use strict';
  
  // function init() {
    // window.initGapi(); // Calls the init function defined on the window
  // }
  
  angular
    .module('privtube.admin')
    .controller('VideosController', [
    
      '$scope', '$http', '$q', '$element', 'configuration',
      function($scope, $http, $q, $element, configuration) {
        
        $scope.roles = $element[0].attributes['data-roles'].value;
        $scope.role = null;
        
        $http({
          method: 'POST',
          url: configuration.ajaxurl,
          params: {
            action: 'listVideos',
            nonce: configuration.nonce
          },
        })
        .success(function(response) {
          $scope.videos = response.data.videos;
        });
        
        $scope.toggleStatus = function(video) {
          $http({
            method: 'POST',
            url: configuration.ajaxurl,
            params: {
              action: 'setVideoStatus',
              nonce: configuration.nonce
            },
            data: {
              id: video.id,
              status: (video.status == 'public' ? 'unlisted' : 'public'), 
            },
          })
          .success(function(response) {
            video.status = response.data.status;
          });
        }
      }
    ]);
})();
(function () {
  'use strict';
  
  // function init() {
    // window.initGapi(); // Calls the init function defined on the window
  // }
  
  angular
    .module('privtube.admin')
    .controller('VideosController', [
    
      '$scope', '$http', '$q', 'configuration',
      function($scope, $http, $q, configuration) {
        
        $http({
          method: 'POST',
          url: configuration.ajaxurl,
          params: {
            action: 'videolist',
            nonce: configuration.nonce
          },
        })
        .success(function(response) {
          $scope.videos = response.data.videos;
        });
        
        $scope.toggleStatus = function() {
          
        }
      }
    ]);
})();
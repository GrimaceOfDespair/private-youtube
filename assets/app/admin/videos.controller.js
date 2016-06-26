(function () {
  'use strict';
  
  angular
    .module('privtube.admin')
    .controller('VideosController', [
    
      '$scope', '$http', '$q', '$uibModal', 'configuration',
      function($scope, $http, $q, $uibModal, configuration) {
        
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
          
          var modalInstance = $uibModal.open({
            templateUrl: 'template/allowRoles.html',
            controller: 'RolesController',
            resolve: {
              video: function() {
                return video;
              }
            }
          });

          modalInstance.result.then(function (result) {
            video.title = result.video.title;
            video.description = result.video.description;
            video.status = result.video.status;
            video.tags = result.video.tags;
          });
        };
      }
    ]);
})();
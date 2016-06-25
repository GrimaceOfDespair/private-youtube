(function () {
  'use strict';
  
  // function init() {
    // window.initGapi(); // Calls the init function defined on the window
  // }
  
  angular
    .module('privtube.admin')
    .controller('VideosController', [
    
      '$scope', '$http', '$q', '$uibModal', 'configuration',
      function($scope, $http, $q, $uibModal, configuration) {
        
        $scope.$watch('role', function(scope) {
          $scope.role = null;
        });
        
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
              roles: function() {
                return video.tags;
              }
            }
          });

          modalInstance.result.then(function (result) {

            $http({
              method: 'POST',
              url: configuration.ajaxurl,
              params: {
                action: 'videoAllowRoles',
                nonce: configuration.nonce
              },
              data: {
                id: video.id,
                status: result.status,
                roles: result.roles,
              },
            })
            .success(function(response) {
              video.status = response.data.status;
              video.tags = response.data.tags;
            });
          });
          
          /*
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
          */
        }
      }
    ]);
})();
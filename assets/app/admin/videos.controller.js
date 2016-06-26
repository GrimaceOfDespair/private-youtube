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
              video: function() {
                return video;
              }
            }
          });

          modalInstance.result.then(function (result) {
            video.status = result.video.status;
            video.tags = result.video.tags;
          });
        };
      }
    ]);
})();
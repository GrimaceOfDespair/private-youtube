(function () {
  'use strict';
  
  angular
    .module('privtube.admin')
    .controller('RolesController', [
    
      '$scope', '$modalInstance', 'video', '$http', 'toastr',
      function($scope, $modalInstance, video, $http, toastr) {
        
        $scope.video = video;
        
        $scope.roles = {};
        
        $scope.loading = false;
        
        var roles = video.tags;
        if (roles) {
          for (var i = 0; i < roles.length; i++) {
            $scope.roles[roles[i]] = true;
          }
        }
        
        $scope.ok = function () {
          
          var roles = [];
          
          if ($scope.status != 'public') {
            for (var role in $scope.roles) {
              if ($scope.roles[role]) {
                roles.push(role);
              }
            }
          }
          
          var video = $scope.video;
          
          $scope.loading = true;
            
          $http({
            method: 'POST',
            url: configuration.ajaxurl,
            params: {
              action: 'updateVideo',
              nonce: configuration.nonce
            },
            data: {
              id: video.id,
              status: video.status,
              title: video.title,
              description: video.description,
              tags: roles
            }
          })
          .success(function(response) {
            $modalInstance.close({
              video: response.data
            });
          })
          .error(function(response) {
            toastr.error(response.data, 'Error');
            $scope.loading = false;
          });
        };

        $scope.cancel = function () {
          $modalInstance.dismiss('cancel');
        };
      }
    ]);
})();
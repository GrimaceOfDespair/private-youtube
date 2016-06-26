(function () {
  'use strict';
  
  angular
    .module('privtube.admin')
    .controller('RolesController', [
    
      '$scope', '$modalInstance', 'video', '$http',
      function($scope, $modalInstance, video, $http) {
        
        $scope.status = video.status;
        
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
          
          $scope.loading = true;
            
          $http({
            method: 'POST',
            url: configuration.ajaxurl,
            params: {
              action: 'videoAllowRoles',
              nonce: configuration.nonce
            },
            data: {
              id: video.id,
              status: $scope.status,
              roles: roles
            }
          })
          .success(function(response) {
            $modalInstance.close({
              video: response.data
            });
          })
          .error(function() {
          })
          .error(function() {
            $scope.loading = false;
          });
        };

        $scope.cancel = function () {
          $modalInstance.dismiss('cancel');
        };
      }
    ]);
})();
(function () {
  'use strict';
  
  angular
    .module('privtube.admin')
    .controller('RolesController', [
    
      '$scope', '$modalInstance', 'roles',
      function($scope, $modalInstance, roles) {
        
        $scope.roles = {};
        
        if (roles) {
          for (var i = 0; i < roles.length; i++) {
            $scope.roles[roles[i]] = true;
          }
        }
        
        $scope.ok = function () {
          
          var roles = [];
          for (var role in $scope.roles) {
            roles.push(role);
          }
          var status = roles.length == 0 ? 'public' : 'unlisted';
          
          $modalInstance.close({ roles: roles, status: status });
        };

        $scope.cancel = function () {
          $modalInstance.dismiss('cancel');
        };
      }
    ]);
})();
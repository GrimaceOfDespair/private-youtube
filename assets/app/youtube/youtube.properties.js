/*
 * Copyright Header - A WordPress plugin to list YouTube videos
 * Copyright (C) 2016-2017 Igor Kalders <igor@bithive.be>
 *
 * This file is part of Copyright Header.
 *
 * Copyright Header is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Copyright Header is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Copyright Header.  If not, see <http://www.gnu.org/licenses/>.
 */

(function () {
  'use strict';
  
  angular
    .module('privtube.admin')
    .controller('VideoPropertiesController', [
    
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
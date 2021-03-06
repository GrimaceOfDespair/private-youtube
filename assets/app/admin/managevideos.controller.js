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
    .controller('ManageVideosController', [

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
            templateUrl: /*!*/ 'template/video-properties.html',
            controller: 'VideoPropertiesController',
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

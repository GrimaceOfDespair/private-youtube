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
    .controller('UploadVideosController', [
    
      '$scope', '$http', '$q', '$uibModal', 'configuration', 'accessToken', 'toastr', '$location',
      function($scope, $http, $q, $uibModal, configuration, accessToken, toastr, $location) {
        
        $scope.video = {
          status: 'public'
        };
        
        $scope.roles = {};
        
        $scope.progress = {
          busy: false,
          progress: 0,
          description: '',
          remainingSeconds: 0,
        };
        
        $scope.$watch('video.file', function (file) {
          if (file) {
            if (!$scope.video.title) {
              $scope.video.title = file.name.replace(/^(.+?)(\.[^ .]+)?$/, '$1');
            }
            $scope.video.filename = file.name;
            $scope.video.filesize = file.size;
          } else {
            $scope.video.filename = '';
          }
        });
        
        $scope.uploadVideo = function() {
          
          $scope.progress.busy = true;
          $scope.progress.startTime = Date.now();
          
          var tags = [];
          
          if ($scope.status != 'public') {
            for (var role in $scope.roles) {
              if ($scope.roles[role]) {
                tags.push(role);
              }
            }
          }
          
          var metadata = {
            snippet: {
              title: $scope.video.title,
              description: $scope.video.description,
              tags: tags,
            },
            status: {
              privacyStatus: $scope.video.status,
            }
          };
          
          var uploader = new MediaUploader({
            baseUrl: 'https://www.googleapis.com/upload/youtube/v3/videos',
            file: $scope.video.file,
            token: accessToken.get(),
            metadata: metadata,
            params: { part: 'snippet,status' },
            onError: function (data) {
              var message = data;
              // Assuming the error is raised by the YouTube API, data will be
              // a JSON string with error.message set. That may not be the
              // only time onError will be raised, though.
              try {
                var errorResponse = JSON.parse(data);
                message = errorResponse.error.message;
              } finally {
                toastr.error(message, 'Error');
              }
            },
            
            onProgress: function (data) {
              
              $scope.$apply(function() {
                setProgress(data.loaded, data.total);
              });
              
            },
            
            onComplete: function (data) {
              
              document.getElementById('manage_videos').submit();
              
            }
          });
          
          setProgress(0, $scope.video.filesize);
          uploader.upload();
        };
        
        function setProgress(bytesUploaded, totalBytes) {
          
          var currentTime = Date.now();
          
          // The times are in millis, so we need to divide by 1000 to get seconds.
          var bytesPerSecond = bytesUploaded / ((currentTime - $scope.progress.startTime) / 1000);
          var percentageComplete = ((100 * bytesUploaded) / totalBytes).toFixed(0);

          var progress = $scope.progress;
          progress.progress = percentageComplete;
          progress.description = bytesUploaded + " / " + totalBytes + "  (" + percentageComplete + "%)";
          progress.remainingSeconds = ((totalBytes - bytesUploaded) / bytesPerSecond).toFixed(0);
        }
      }
    ]);
})();
(function () {
  'use strict';
  
  angular
    .module('privtube.youtube')
    .directive('youtubeUpload', ['$window', '$alert', function ($window, $alert) {
      'use strict';

      return {
        restrict: 'AE',
        templateUrl: "youtube/youtube.upload.html",
        scope: {
          videoTitle: "@",
          videoDesc: "@"
        },
        link: function ($scope, $element, $attrs) {

          var STATUS_POLLING_INTERVAL_MILLIS = 10 * 1000; // One minute.
          var ending = /\.apps\.googleusercontent\.com$/;
          var categoryId = 22;
          var tags = ['youtube-cors-upload'];

          $attrs.clientid += (ending.test($attrs.clientid) ? '' : '.apps.googleusercontent.com');

          $attrs.$set('data-clientid', $attrs.clientid);
          $attrs.$set('theme', $attrs.theme);

          var status = {
            init: 10,
            uploading: 20,
            processing: 30,
            final: 40,
            error: 50
          };

          var setStatus = function (newStatus, params) {
            $scope.status = newStatus;
            switch ($scope.status) {
              case status.init:
                break;
              case status.uploading:
                $scope.$emit('event:youtube-video-uploading');
                $scope.uploading = true;
                break;
              case status.processing:
                $scope.$emit('event:youtube-video-uploaded', params[0]);
                $scope.uploading = false;
                break;
              case status.final:
                $scope.$emit('event:youtube-video-processed', params[0]);
                $scope.uploading = false;
                break;
              case status.error:
                $scope.$emit('event:youtube-video-failed', params[0]);
                $scope.uploading = false;
                break;
            }
          };

          setStatus(status.init);

          // Some default values, based on prior versions of this directive
          var defaults = {
            callback: 'signinCallback',
            cookiepolicy: 'single_host_origin',
            requestvisibleactions: 'http://schemas.google.com/AddActivity',
            scope: [
              'https://www.googleapis.com/auth/plus.login',
              'https://www.googleapis.com/auth/userinfo.email',
              'https://www.googleapis.com/auth/youtube.upload',
              'https://www.googleapis.com/auth/youtube'
            ].join(' '),
            height: 'standard',
            width: 'wide',
            state: '',
            showprivacy: false,
            videoTitle: "Upload a Video",
            clientid: "YOUR CLIENT ID HERE"
          };

          defaults.clientid = $attrs.clientid;
          defaults.theme = $attrs.theme;

          // Overwrite default values if explicitly set
          angular.forEach(Object.getOwnPropertyNames(defaults), function (propName) {
            if ($attrs.hasOwnProperty(propName)) {
                defaults[propName] = $attrs[propName];
            }
          });

          $scope.loggedIn = false;
          $scope.uploading = false;
          $scope.uploadProgressText = '';
          
          $scope.video = {
            file: null,
            name: '',
            id: null,
            uploadProgress: 0,
          };

          $scope.$watch('videoFiles', function (files) {
            if (files && files.length > 0) {
              $scope.video.file = files[0];
            }
          });

          $scope.$watch('video.file', function (file) {
              if (file) {
                $scope.video.name = file.name;
              }
          });

          // Default language
          // Supported languages: https://developers.google.com/+/web/api/supported-languages
          $attrs.$observe('language', function (value) {
            $window.___gcfg = {
              lang: value ? value : 'en'
            };
          });

          $scope.$on('event:google-plus-signin-success', function (event, authResult) {
            $scope.$apply(function() {
              if (authResult.access_token) {
                $scope.loggedIn = true;
                $scope.accessToken = authResult.access_token;
                loadChannels();
              }
            });
          });

          $scope.upload = function () {

            if ($scope.videoTitle === "") {
              $alert({
                content: "Please enter a title for your video.",
                placement: 'top-right',
                type: 'warning',
                duration: 3
              });
            } else if ($scope.videoDesc === "") {
              $alert({
                content: "Please enter a description for your video.",
                placement: 'top-right',
                type: 'warning',
                duration: 3
              });
            } else if ($scope.status == status.uploading) {
              $alert({
                content: "Please wait until your video has finished uploading before uploading another one.",
                placement: 'top-right',
                type: 'warning',
                duration: 3
              });
            } else if ($scope.status == status.processing) {
              $alert({
                content: "Please wait until your video has finished processing before uploading another one.",
                placement: 'top-right',
                type: 'warning',
                duration: 3
              });
            } else {
              setStatus(status.uploading);
              uploadFile();
            }
          };
          
          function loadChannels() {
            $window.gapi.client.request({
              path: '/youtube/v3/channels',
              params: {
                part: 'snippet',
                mine: true
              },
              callback: function (response) {
                if (response.error) {
                  $alert(response.error.message);
                }
              }
            });
          }

          /**
           * Uploads a video file to YouTube.
           *
           * @method uploadFile
           * @param {object} file File object corresponding to the video to upload.
           */
          function uploadFile(file) {
            var metadata = {
              snippet: {
                title: $scope.videoTitle,
                description: $scope.videoDesc,
                tags: tags,
                categoryId: categoryId
              },
              status: {
                privacyStatus: "public",
                embeddable: true
              }
            };
            
            // This won't correspond to the *exact* start of the upload, but it should be close enough.
            var uploadStartTime = Date.now();
            
            var uploader = new MediaUploader({
              baseUrl: 'https://www.googleapis.com/upload/youtube/v3/videos',
              file: $scope.video.file,
              token: $scope.accessToken,
              metadata: metadata,
              params: {
                  part: Object.keys(metadata).join(',')
              },
              //access_type: 'offline',
              onError: function (data) {
                var message = data;
                // Assuming the error is raised by the YouTube API, data will be
                // a JSON string with error.message set. That may not be the
                // only time onError will be raised, though.
                try {
                  var errorResponse = JSON.parse(data);
                  message = errorResponse.error.message;
                } finally {
                  $alert(message);
                }
              },
              onProgress: function (data) {
                var currentTime = Date.now();
                var bytesUploaded = data.loaded;
                var totalBytes = data.total;
                // The times are in millis, so we need to divide by 1000 to get seconds.
                var bytesPerSecond = bytesUploaded / ((currentTime - uploadStartTime) / 1000);
                var estimatedSecondsRemaining = (totalBytes - bytesUploaded) / bytesPerSecond;
                var percentageComplete = ((bytesUploaded * 100) / totalBytes).toFixed(2);

                $scope.video.uploadProgress = percentageComplete;
                $scope.uploadProgressText = bytesUploaded + " / " + totalBytes + "  (" + percentageComplete + "%)";
              },
              onComplete: function (data) {
                var uploadResponse = JSON.parse(data);
                $scope.video.id = uploadResponse.id;
                pollForVideoStatus();
              }
            });
            
            uploader.upload();
          }

          function pollForVideoStatus() {
            $window.gapi.client.request({
              path: '/youtube/v3/videos',
              params: {
                part: 'status,player',
                id: $scope.video.id
              },
              callback: function (response) {
                if (response.error) {
                  // The status polling failed.
                  console.log(response.error.message);
                  $timeout(function() {
                    pollForVideoStatus();
                  }, STATUS_POLLING_INTERVAL_MILLIS);
                } else {
                  var uploadStatus = response.items[0].status.uploadStatus;
                  switch (uploadStatus) {
                    // This is a non-final status, so we need to poll again.
                    case 'uploaded':
                      setStatus(status.processing, [{
                          id: response.items[0].id,
                          type: response.items[0].kind
                      }]);
                      $timeout(function() {
                        pollForVideoStatus();
                      }, STATUS_POLLING_INTERVAL_MILLIS);
                      break;
                    // The video was successfully transcoded and is available.
                    case 'processed':
                      setStatus(status.final, [{
                          id: response.items[0].id,
                          type: response.items[0].kind
                      }]);
                      break;
                    // All other statuses indicate a permanent transcoding failure.
                    default:
                      setStatus(status.error, [{message: response.items[0].status.uploadStatus + ": " + response.items[0].status.rejectionReason}]);
                      break;
                  }
                }
              }
            });
          }
        }
      };
    }]);
})();
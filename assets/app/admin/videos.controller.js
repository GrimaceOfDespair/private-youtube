(function () {
  'use strict';
  
  // function init() {
    // window.initGapi(); // Calls the init function defined on the window
  // }
  
  angular
    .module('privtube.admin')
    .controller('VideosController', [
    
      '$scope', 'gapiClient', 'configuration',
      function($scope, gapiClient, configuration) {
        
        gapiClient
          .loadChannel(configuration.channelId)
          .then(function(result) {
          
            var videos = [];
            var items = result.items;
            
            for (var i = 0; i < items.length; i++) {
              
              var item = items[i].snippet;
              var id = items[i].id;
              switch (id.kind)
              {
                case 'youtube#channel':
                  $scope.title = item.title;
                  break;
                  
                case 'youtube#video':
                  videos.push({
                    id: id.videoId,
                    title: item.title,
                    publishedAd: item.publishedAt,
                    thumbnail: item.thumbnails.medium.url
                  });
                  break;
              }
            }
            
            $scope.videos = videos;
          });
      }
    ]);
})();
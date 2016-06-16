(function(){
  angular
    .module('privtube.admin')
    .config(['$stateProvider', '$urlRouterProvider',
      function ($stateProvider, $urlRouterProvider) {
        
        //$urlRouterProvider.otherwise('manage');

        $stateProvider
          .state('manage-videos', {
            url: '/manage-videos',
            views: {
              'main': {
                templateUrl: "admin/manage-videos.html",
                controller: "VideosController as videosCtrl"
              }
            } 
          })
          .state('upload-video', {
            url: '/upload-video',
            views: {
              'main': {
                templateUrl: "admin/upload-video.html",
                controller: "VideosController as videosCtrl"
              }
            } 
          });
    }]);
})();
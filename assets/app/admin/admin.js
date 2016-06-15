(function () {
  'use strict';
  
  angular
    .module('privtube.admin', [
      'ui.router.state',
      'ng-youtube-upload',
      'privtube.common',
      'privtube.configuration',
    ]);
})();
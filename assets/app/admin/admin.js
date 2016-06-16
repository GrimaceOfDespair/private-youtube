(function () {
  'use strict';
  
  angular
    .module('privtube.admin', [
      'ui.router.state',
      'privtube.common',
      'privtube.youtube',
      'privtube.configuration',
    ]);
})();
/*
function onSignInSuccess() { onSignIn(true); }
function onSignInFailure() { onSignIn(false); }
function onSignIn(success) {
  angular
    .element('#privtube-admin')
    .injector()
    .get('$rootScope')
    .$broadcast('event:google-plus-signin', { success: success });
}
*/

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
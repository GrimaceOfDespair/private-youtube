<?php
  $options = get_option('privtube_options');
  if ($options) {
    $yt_client_id = $options['client_id'];
  }
?>
<script>
function onSignInSuccess(authResult) {

  window.access_token = authResult.hg.access_token;
  
  // Warning: ugly hack ahead  
  var scope = angular
    .element('#privtube-admin')
    .injector()
    .get('$rootScope');
    
  scope.$apply(function() {
    scope.loggedIn = true;
  });
}
</script>
<div id="privtube-admin" ng-app="privtube.admin" ng-controller="VideosController">

  <toaster-container></toaster-container>
  
  <div id="login" class="container" ng-hide="loggedIn">
    <span class="g-signin2"
      data-onsuccess="onSignInSuccess"
      data-onfailure="onSignInFailure"
      data-callback="signinCallback"
      data-cookiepolicy="single_host_origin",
      data-requestvisibleactions="http://schemas.google.com/AddActivity",
      data-scope="<?= join([
        'https://www.googleapis.com/auth/plus.login',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/youtube'
      ], ' ');
      ?>">
    </span>
  </div>
  
  <div class="container" ng-if="loggedIn">

    <youtube-upload
      clientid="661237843986-6jbi54j2p62mip1e5q8gui4k0uboq4ka.apps.googleusercontent.com"
      data-video-title="'Test video'"
      data-video-desc="'Test video description'"
      />
      
  </div>
  
</div>

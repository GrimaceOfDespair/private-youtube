<?php
  $options = get_option('privtube_options');
  if ($options) {
    $yt_client_id = $options['client_id'];
  }
?>
<script>
function onSignInSuccess() {

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
<div id="privtube-admin" ng-app="privtube.admin">

  <toaster-container></toaster-container>
  
  <h2><?php echo __('YouTube Videos', 'privtube') ?>
  </h2>
  
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

    <ul class="nav nav-tabs">
      <li role="presentation" ui-sref-active="active" ><a ui-sref="manage-videos">Manage</a></li>
      <li role="presentation" ui-sref-active="active" ><a ui-sref="upload-video">Upload</a></li>
    </ul> 

    <div class="row">
        <div ui-view="main"></div>
    </div>
    
  </div>
  
</div>

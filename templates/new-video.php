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
<div
  id="privtube-admin"
  ng-cloak
  ng-app="privtube.admin"
  ng-controller="UploadVideosController">

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
  
  <form name="uploadForm" class="container"
    ng-if="loggedIn"
    >

    <div class="row">
      <div class ='col-md-12'>
        <div class="form-group drop-box""
             ngf-drop
             ng-model="videoFiles"
             ngf-accept="'video/*'"
             ngf-drag-over-class="dragover"
             ngf-multiple="false"
                >
            <p><?= __('Choose a video from your computer', 'privtube') ?>: .MOV, .MPEG4, MP4, .AVI, .WMV, .MPEGPS, .FLV, 3GPP, WebM</p>
        </div>
        <div ngf-no-file-drop><?= __('File Drag/drop is not supported', 'privtube') ?></div>
      </div>
    </div>
    
    <fieldset class="form-group">
      <label for="video-file"><?= __('File', 'privtube') ?></label>
      <div id="video-file" class="input-group">
        <span class="input-group-btn">
          <span class="btn btn-primary btn-file" >
            <span class="glyphicon glyphicon-folder-open"></span>
            <?= __('Browse', 'privtube') ?>
            <input type="file" id="file" class="file" accept="video/*"
              fileread ng-model="video.file" required />
          </span>
        </span>
        <input type="text" name="videoName" class="form-control" readonly value="{{video.filename}}" />
      </div>
    </fieldset>
    
    <?php include('video-edit.php') ?>

    <fieldset class="form-group">
      <button type="button" id="uploadButton" class="btn btn-success"
        ng-disabled="uploadForm.$invalid || progress.busy"
        ng-click="uploadVideo()">
        <span class="glyphicon glyphicon-upload"></span> <?= __('Upload video', 'privtube') ?>
      </button>
    </fieldset>

    <div ng-if="progress.busy">
      <div class="progress">
        <div id="transferred" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{progress.progress}}"
             aria-valuemin="0" aria-valuemax="100" style="width:{{progress.progress}}%">
            {{progress.description}}
        </div>
      </div>

      <div align="center" class="embed-responsive embed-responsive-16by9">
        <video controls ngf-src="video.file" ngf-accept="'video/*'"></video>
      </div>
    </div>

    <p>
      <small id="disclaimer">*
        <?= sprintf(__('By uploading a video, you certify that you own all rights to the content or that you are authorized by the owner to make the content publicly available on YouTube, and that it otherwise complies with the YouTube Terms of Service located at <a href="%s" target="_blank">%s</a>', 'privtube'), 'http://www.youtube.com/t/terms', 'http://www.youtube.com/t/terms') ?>
      </small>
    </p>
      
  </form>
  
</div>

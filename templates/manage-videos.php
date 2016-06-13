<div ng-app="privtube.admin">

  <h2><?php echo __('YouTube Videos', 'privtube') ?></h2>
  
  <ul class="nav nav-tabs">
    <li role="presentation" class="active"><a ui-sref="manage-videos">Manage</a></li>
    <li role="presentation"><a ui-sref="upload-video">Upload</a></li>
  </ul> 

  <div class="container-fluid">
      <div ui-view="main"></div>
      <toaster-container></toaster-container>
  </div>

</div>

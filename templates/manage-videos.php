<?php
  $role_names = array();
  foreach (get_editable_roles() as $role_name => $role_info) {
    $role_names []= $role_name;
  }
?>
<div class="row" ng-app="privtube.admin" ng-controller="VideosController"
  data-roles="<?= implode(',', $role_names) ?>">
  
  <div class="col-md-8">
    <form method="post" action="<?php menu_page_url('privtube-all-videos') ?>" role="form">
      <?php submit_button( __('Refresh', 'privtube'), 'primary', 'submit_clear', false ); ?>
    </form>
  </div>
  
  <div class="col-md-4">
    <select ng-model="role">
      <option value=""><?= __('All') ?></option>
      <?php wp_dropdown_roles( $selected ); ?>
    </select>
  </div>
  
  <div class='clearfix'></div>
  <br />
  
  <figure ng-repeat="video in videos | filterByTag:role" class="col-xs-6 col-sm-4 col-md-3">
    <a href="{{video.url}}" target="_blank">
      <img ng-src="{{video.thumbnail}}" />
    </a>
    <figcaption>
      <h4>
        <a ng-click="toggleStatus(video)" class="button"
          ng-attr-title="{{video.status == 'public' ? '<?= __('Make private', 'privtube') ?>' : '<?= __('Make public', 'privtube') ?>'}}"
          ><i
          ng-class="video.status == 'public' ? 'glyphicon-eye-open' : 'glyphicon-eye-close'"
          class="glyphicon"></i></a>
        <span ng-bind="video.title"></span>
      </h4>
      <h5 ng-bind="video.publishedAt"></h5>
    </figcaption>
  </figure>
  
  <script type="text/ng-template" id="template/allowRoles.html">
    <div class="modal-header">
      <h3 class="modal-title"><?= __('Allow video access') ?></h3>
    </div>
    <div class="modal-body">
      <ul>
        <?php foreach (get_editable_roles() as $role_name => $role_info) { ?>
          <li class="form-group">
            <label for="role_<?= $role_name ?>"><?= translate_user_role( $role_info['name'] ) ?></label>
            <input id="role_<?= $role_name ?>" class="form-control" type="checkbox" ng-model="roles.<?= $role_name ?>" />
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="modal-footer">
      <button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
      <button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
    </div>
  </script>
  
</div>


<?php 
/*
 * Copyright Header - A WordPress plugin to list YouTube videos
 * Copyright (C) 2016-2017 Igor Kalders <igor@bithive.be>
 *
 * This file is part of Copyright Header.
 *
 * Copyright Header is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Copyright Header is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Copyright Header.  If not, see <http://www.gnu.org/licenses/>.
 */ ?>
<?php
  $role_names = array();
  foreach (get_editable_roles() as $role_name => $role_info) {
    $role_names []= $role_name;
  }
?>
<div class="row" ng-app="privtube.admin" ng-controller="ManageVideosController">
  
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
  
  <div class="row" ng-repeat="row in videos | filterByTag:role | chunk:4">
    <figure ng-repeat="video in row" class="col-md-3">
      <a href="{{video.url}}" target="_blank">
        <img ng-src="{{video.thumbnail}}" />
      </a>
      <figcaption>
        <h4>
          <span ng-bind="video.title"></span>
        </h4>
        <h5>
          <a ng-click="toggleStatus(video)" class="button"
            ng-attr-title="{{video.status == 'public' ? '<?= esc_attr__('Make private', 'privtube') ?>' : '<?= esc_attr__('Make public', 'privtube') ?>'}}"
            ><i
            ng-class="video.status == 'public' ? 'glyphicon-eye-open' : 'glyphicon-eye-close'"
            class="glyphicon"></i></a>
          <span ng-bind="video.publishedAt"></span>
        </h5>
      </figcaption>
    </figure>
  </div>
  
  <script type="text/ng-template" id="template/video-properties.html">
    <div class="modal-header">
      <h3 class="modal-title"><?= __('Video data') ?></h3>
    </div>
    <div class="modal-body">
      <?php include('video-edit.php') ?>
    </div>
    <div class="modal-footer">
      <button spinner ng-class="{'active':loading}" class="btn btn-primary" type="button" ng-click="ok()" ng-disabled="loading">
        OK
      </button>
      <button class="btn btn-warning" type="button" ng-click="cancel()" ng-disabled="loading">
        Cancel
      </button>
    </div>
  </script>
</div>


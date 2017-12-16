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
<fieldset class="form-group">
  <label for="title"><?= __('Title', 'privtube') ?></label>
  <input ng-model="video.title" type="text" class="form-control" id="title" placeholder="<?= __('Video title', 'privtube') ?>" required>
</fieldset>

<fieldset class="form-group">
  <label for="description"><?= __('Description', 'privtube') ?></label>
  <textarea ng-model="video.description" class="form-control" id="description" rows="3"></textarea>
</fieldset>

<fieldset class="form-group">
  <label for="roles-selector"><?= __('Visible to', 'privtube') ?></label>
  <div class="btn-group" id="roles-selector" data-toggle="buttons">
    <label ng-click="video.status = 'public'" ng-class="{'active':video.status=='public'}" class="btn btn-primary">
      <input type="radio" name="access" id="public" autocomplete="off" ng-checked="video.status=='public'">
      <?= __('Everyone', 'privtube') ?>
    </label>
    <label ng-click="video.status = 'unlisted'" ng-class="{'active':video.status!='public'}" class="btn btn-primary">
      <input type="radio" name="access" id="unlisted" autocomplete="off" ng-checked="video.status!='public'">
      <?= __('Private', 'privtube') ?>
    </label>
  </div>
</fieldset>

<fieldset class="form-group" ng-if="video.status!='public'">
  <ul>
    <?php foreach (get_editable_roles() as $role_name => $role_info) { ?>
      <li class="form-group col-md-2">
        <label for="role_<?= $role_name ?>"><?= translate_user_role( $role_info['name'] ) ?></label>
        <input id="role_<?= $role_name ?>" class="form-control" type="checkbox" ng-model="roles.<?= $role_name ?>" />
      </li>
    <?php } ?>
  </ul>
</fieldset>

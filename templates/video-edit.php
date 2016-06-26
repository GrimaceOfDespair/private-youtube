<script type="text/ng-template" id="template/allowRoles.html">
  <form>
    <div class="modal-header">
      <h3 class="modal-title"><?= __('Video data') ?></h3>
    </div>
    <div class="modal-body">
      <fieldset class="form-group">
        <label for="title"><?= __('Title', 'privtube') ?></label>
        <input ng-model="video.title" type="text" class="form-control" id="title" placeholder="<?= __('Video title', 'privtube') ?>">
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
          <label ng-click="video.status = 'private'" ng-class="{'active':video.status!='public'}" class="btn btn-primary">
            <input type="radio" name="access" id="unlisted" autocomplete="off" ng-checked="video.status!='public'">
            <?= __('Private', 'privtube') ?>
          </label>
        </div>
      </fieldset>
      <fieldset class="form-group">
        <ul ng-if="video.status!='public'">
          <?php foreach (get_editable_roles() as $role_name => $role_info) { ?>
            <li class="form-group col-md-2">
              <label for="role_<?= $role_name ?>"><?= translate_user_role( $role_info['name'] ) ?></label>
              <input id="role_<?= $role_name ?>" class="form-control" type="checkbox" ng-model="roles.<?= $role_name ?>" />
            </li>
          <?php } ?>
        </ul>
      </fieldset>
    </div>
    <div class="modal-footer">
      <button class="btn btn-primary has-spinner" type="button" ng-click="ok()" ng-class="{'active':loading}">
        OK <span class="spinner"></span>
      </button>
      <button class="btn btn-warning" type="button" ng-click="cancel()" ng-disabled="loading">
        Cancel
      </button>
    </div>
  </form>
</script>

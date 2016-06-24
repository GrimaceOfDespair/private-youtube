<div ng-app="privtube.admin" ng-controller="VideosController">
  <ul>
    <li ng-repeat="video in videos" class="col-xs-6 col-sm-4 col-md-3">
      <figure>
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
    </li>
  </ul>
</div>
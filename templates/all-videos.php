<?php if ($videos): ?>
  <ul>
  <?php foreach ($videos as $video) { ?>
    <?php
      switch ($video['status']) {
        case 'unlisted':
        case 'private':
          $icon = 'eye-close';
          break;
          
        case 'public':
        default:
          $icon = 'eye-open';
          break;
      }
    ?>
    <li class="col-xs-6 col-sm-4 col-md-3">
      <figure>
        <a href="<?= $video['url'] ?>" target="_blank">
          <img src="<?= $video['thumbnail'] ?>" />
        </a>
        <figcaption>
          <h4>
            <a ng-click="toggle()"><i class="glyphicon glyphicon-<?= $icon ?>"></i></a>
            <?= $video['title'] ?>
          </h4>
          <h5><?= $video['publishedAt'] ?></h5>
        </figcaption>
      </figure>
    </li>
  <?php } ?>
  </ul>
<?php else:?>
  <div class="alert alert-warning" role="alert">
    <?= __('No videos found', 'privtube') ?>
  </div>
<?php endif;?>
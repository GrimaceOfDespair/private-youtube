<?php if ($videos): ?>
  <ul>
  <?php foreach ($videos as $video) { ?>
    <li class="col-xs-6 col-sm-4 col-md-3">
      <figure>
        <a href="<?= $video['url'] ?>" target="_blank">
          <img src="<?= $video['thumbnail'] ?>" />
        </a>
        <figcaption>
          <h4>
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
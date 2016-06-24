<?php if ($videos): ?>
  <?php foreach ($videos as $video) { ?>
    <article>
      <h3><?= $video['title'] ?></h3>
      <p>
        <iframe width="640" height="360" frameborder="0" allowfullscreen
          src="<?= $video['embed'] ?>"></iframe>
      </p>
      <?php if ($video['description']): ?>
        <p>
          <?= $video['description'] ?>
        </p>
      <?php endif; ?>
    </article>
  <?php } ?>
<?php else:?>
  <div class="alert alert-warning" role="alert">
    <?= __('No videos found', 'privtube') ?>
  </div>
<?php endif;?>
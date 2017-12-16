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
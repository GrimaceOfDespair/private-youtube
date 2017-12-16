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
 */

/*
(function($) {
  $(function() {
    $('.video').each(function(i, video) {
      $('.toggleVideo', video).click(function() {
        var videoId = $(video).data('video-id');
        var videoStatus = $(video).data('video-status');
        var newStatus = videoStatus == 'public' ? 'private' : 'public';
        if (confirm('Are you sure you want to make this video ' + newStatus)) {
          jQuery.ajax({
            url : toggle_video.ajax_url,
            type : 'post',
            data : {
              action : videoStatus == 'public' : 'make_private' : 'make_public',
              post_id : videoId
            },
            success : function( response ) {
              alert(response)
            }
          });
        }
      });
    });
  });
})(jQuery);
*/
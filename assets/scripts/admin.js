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
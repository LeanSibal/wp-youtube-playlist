jQuery(document).ready(function($){
  function setYoutube( youtube_id ) {
    $('div.youtube-container').html("<iframe width='551' height='346' src='https://www.youtube.com/embed/" + youtube_id + "?;ecver=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture;' allowfullscreen></iframe>");
  }
  var initial_youtube_id = $('.playlist-items li:first').data('video_id');
  $('.playlist-items li:first').addClass('active');
  setYoutube( initial_youtube_id );
  $('ul.playlist-items li.playlist-item').on('click', function(){
    var youtube_id = $( this ).data('video_id');
    setYoutube( youtube_id );
    $('ul.playlist-items li.active').removeClass('active');
    $( this ).addClass('active');
  });
});

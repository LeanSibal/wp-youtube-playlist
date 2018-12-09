jQuery(function($){
  $('#youtube_search').autocomplete({
    source: function( request, response ) {
      $.get({
        url: 'https://www.googleapis.com/youtube/v3/search',
        data: {
          part: 'snippet',
          key: 'AIzaSyAHWp6zyzOP7Bajp2W3omFJxNnmGhpJi_8',
          q: request.term
        },
        success: function( data ) {
          if( typeof data.items === 'undefined' ) response([]);
          var videos = [];
          for( var i in data.items ) {
            var video = data.items[i];
            if( typeof video.id.videoId === 'undefined' ) continue;
            videos.push({
              id: video.id.videoId,
              label: video.snippet.title,
              value: video.snippet.title
            });
          }
          response( videos );
        },
      });
		},
    minLength: 3,
    select: function( event, ui ) {
      $('#song_title').val( ui.item.label );
      $('#youtube_link').val('https://www.youtube.com/watch?v=' + ui.item.id );
    }
  });
});

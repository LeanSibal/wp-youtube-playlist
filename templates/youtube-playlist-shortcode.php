<?php
  $args = [
    'post_type' => 'youtube-playlist',
    'posts_per_page' => -1
  ];
  if( !empty( $atts['category'] ) ) {
    $args['tax_query'] = [[
      'taxonomy' => 'categories',
      'field' => 'slug',
      'terms' => 'aa'
    ]];
  }
  $songs = new WP_Query( $args );
?>
<div class="youtube-playlist-container">
  <div class="youtube-container">
   </div>
   <div class="playlist-container">
    <ul class="playlist-items">
      <?php while( $songs->have_posts() ): $songs->the_post(); ?>
      <li class="playlist-item" data-video_id="<?php echo get_post_meta( get_the_ID() , 'youtube_video_id', true ); ?>">
        <?php the_title(); ?>
      </li>
      <?php endwhile; ?>
    </ul>
   </div>
</div>

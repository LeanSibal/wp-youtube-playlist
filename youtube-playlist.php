<?php
/*
Plugin Name: Youtube Playlist
PluginURI: http://www.renesejling.dk
Description: Show youtube as playlist
Author: René Sejling
Version: 1.0.0
*/
require_once("plugin.php");

class YoutubePlaylist extends WordPressPlugin
{

  public $shortcodes = [
    'youtube-playlist' => 'youtube_playlist_shortcode'
  ];

  public $styles = [
    'youtube-playlist' => 'assets/css/youtube-playlist.css'
  ];

  public $scripts = [
    'youtube-playlist' => [
      'src' => 'assets/js/youtube-playlist.js',
      'deps' => [ 'jquery' ],
      'in_footer' => true
    ]
  ];

  public $actions = [
    'youtube_playlist_custom_post_type' => 'init',
    'youtube_playlist_custom_fields' => 'add_meta_boxes',
    'admin_scripts' => 'admin_enqueue_scripts',
    'youtube_playlist_custom_post_save_fields' => [
      'tag' => 'save_post',
      'accepted_args' => 3
    ]
  ];

  public function admin_scripts() {
    wp_register_script(
      'wp-youtube-admin-script',
      plugins_url() . '/wp-youtube-playlist/assets/js/admin.js',
      [ 'jquery', 'jquery-ui-autocomplete' ]
    );
  }

  public function youtube_playlist_custom_post_type() {
    $labels = [
      'name' => 'Youtube Playlists',
    ];
    $args = [
			'labels'             => $labels,
			'description'        => __( 'Description.', 'your-plugin-textdomain' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
      'exclude_from_search' => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'page-attributes' ),
      'menu_icon' => 'dashicons-video-alt3',
      'with_front' => false,
      'taxonomies' => [ 'categories' ]
    ];
    register_post_type( 'youtube-playlist', $args );
    register_taxonomy( 'categories', [ 'youtube-playlist' ], [
      'hierarchical' => false,
      'label' => 'Categories',
      'singular_label' => 'Category',
      'rewrite' => [
        'slug' => 'categories',
        'with_front' => false
      ]
    ]);
    register_taxonomy_for_object_type( 'categories', 'youtube-playlist' );
  }

  public function youtube_playlist_custom_fields() {
    add_meta_box(
      'artist_meta_box',
      'Artist & Youtube Link',
      [ $this, 'youtube_playlist_custom_fields_form' ],
      'youtube-playlist',
      'normal',
      'high'
    );
  }

  public function youtube_playlist_custom_fields_form() {
    global $post;
    wp_enqueue_script('wp-youtube-admin-script');
    $artist = get_post_meta( $post->ID, 'artist', true );
    $song_title = get_post_meta( $post->ID, 'song_title', true );
    $youtube_link = get_post_meta( $post->ID, 'youtube_link', true );
    ob_start();
    ?>
    <input type="hidden" name="youtube_playlist_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
    <p>
      <label for="artist">Search Youtube</label>
      <br/>
      <input type="text" name="search" id="youtube_search" class="regular-text" value=""/>
    </p>
    <p>
      <label for="artist">Artist</label>
      <br/>
      <input type="text" name="artist" id="artist" class="regular-text" value="<?php echo $artist; ?>"/>
    </p>
    <p>
      <label for="song_title">Title</label>
      <br/>
      <input type="text" name="song_title" id="song_title" class="regular-text" value="<?php echo $song_title; ?>"/>
    </p>
    <p>
      <label for="youtube_link">Youtube Link</label>
      <br/>
      <input type="text" name="youtube_link" id="youtube_link" class="regular-text" value="<?php echo $youtube_link; ?>"/>
    </p>
    <?php
    echo ob_get_clean();
  }
  
  public function youtube_playlist_custom_post_save_fields( $post_id, $post, $updated ) {
    if( !wp_verify_nonce( $_POST['youtube_playlist_nonce'], basename(__FILE__) ) ) {
      return $post_id;
    }
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return $post_id;
    }
    if( 'youtube-playlist' == $_POST['post_type'] ) {
      if( !current_user_can( 'edit_page', $post_id ) ) {
        return $post_id;
      } else if ( !current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
      }
    }
    $old_artist = get_post_meta( $post_id, 'artist', true );
    $new_artist = $_POST['artist'];
    $old_song_title = get_post_meta( $post_id, 'song_title', true );
    $new_song_title = $_POST['song_title'];
    $old_youtube_link = get_post_meta( $post_id, 'youtube_link', true );
    $new_youtube_link = $_POST['youtube_link'];
    preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $new_youtube_link, $matches);
    $youtube_video_id = !empty( $matches[0] ) ? $matches[0] : $new_youtube_link;

    if( $new_artist && $new_artist !== $old_artist ) {
      update_post_meta( $post_id, 'artist', $new_artist );
    } else if ( $new_artist == '' && $old_artist ) {
      delete_post_meta( $post_id, 'artist' );
    }
    if( $new_song_title && $new_song_title !== $old_song_title ) {
      update_post_meta( $post_id, 'song_title', $new_song_title );
    } else if ( $new_song_title == '' && $old_song_title ) {
      delete_post_meta( $post_id, 'song_title' );
    }
    if( $new_youtube_link && $new_youtube_link !== $old_youtube_link ) {
      update_post_meta( $post_id, 'youtube_link', $new_youtube_link );
      update_post_meta( $post_id, 'youtube_video_id', $youtube_video_id );
    } else if ( $new_youtube_link == '' && $old_youtube_link ) {
      delete_post_meta( $post_id, 'youtube_link' );
      delete_post_meta( $post_id, 'youtube_video_id' );
    }
    if( !empty( $new_artist ) ) {
      $title_arr[] = $new_artist;
    }
    if( !empty( $new_song_title ) ) {
      $title_arr[] = $new_song_title;
    }
    $new_title = implode( " - ", $title_arr );
    if( $new_title !== $post->post_title ) {
      $post->post_title = $new_title;
      wp_update_post( $post, true );
    }
  }

  public function youtube_playlist_autogenerated_title( $data, $postarr) {
    if( $data['post_type'] !== 'youtube-playlist' || empty( $postarr['post_ID'] ) ) return $data;
    $artist = get_post_meta( $postarr['post_ID'], 'artist', true );
    $song_title = get_post_meta( $postarr['post_ID'], 'song_title', true );
    if( !empty( $artist ) ) {
      $arr[] = $artist;
    }
    if( !empty( $song_title ) ) {
      $arr[] = $song_title;
    }
    $data['post_title'] = implode(" - ", $arr );
    return $data;
  }

  public function youtube_playlist_shortcode( $atts = null ) {
    wp_enqueue_style( 'youtube-playlist' );
    wp_enqueue_script( 'youtube-playlist' );
    ob_start();
    require_once("templates/youtube-playlist-shortcode.php");
    return ob_get_clean();
  }

}

new YoutubePlaylist;

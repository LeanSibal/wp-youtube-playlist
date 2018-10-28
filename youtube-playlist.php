<?php
/*
Plugin Name: Youtube Playlist
PluginURI: http://www.renesejling.dk
Description: Show youtube as playlist
Author: René Sejling
Version: 1.0.0
*/
require "plugin.php";

class YoutubePlaylist extends WordPressPlugin
{

  public $shortcodes = [
    'youtube-playlist' => 'youtube_playlist_shortcode'
  ];

  public $styles = [
    'youtube-playlist' => 'assets/css/youtube-playlist.css'
  ];

  public function youtube_playlist_shortcode() {
    wp_enqueue_style( 'youtube-playlist' );
    wp_enqueue_style( 'bootstrap' );
    ob_start();
    return ob_get_clean();
  }

}

new YoutubePlaylist;

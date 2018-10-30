<?php

if( !defined( 'ABSPATH' ) ) exit;

if( class_exists( 'WordPressPlugin' ) ) return;

class WordPressPlugin 
{

	public $version = "1.0.0";

  public $actions = [];

  public $filters = [];

  public $shortcodes = [];

  public $styles = [];

  public $scripts = [];

	public function __construct() {
		self::setup_actions();
		self::setup_filters();
	}

	protected function setup_actions() {
    if( !empty( $this->shortcodes ) ) $this->actions['setup_shortcodes'] = 'init';
    if( !empty( $this->styles ) || !empty( $this->scripts ) ) $this->actions['setup_scripts_styles'] = 'wp_enqueue_scripts';
		foreach( $this->actions as $function => $action ) {
      if( !method_exists( $this, $function ) ) continue;
			$tag = !empty( $action['tag'] ) ? $action['tag'] : $action;
			$priority = !empty( $action['priority'] ) ? $action['priority'] : 10;
			$accepted_args = !empty( $action['accepted_args'] ) ? $action['accepted_args'] : 1;
      add_filter( $tag, [ $this, $function ], $priority, $action );
		}
	}

	protected function setup_filters() {
		foreach( $this->filters as $function => $filter ) {
      if( !method_exists( $this, $function ) ) continue;
			$hook = !empty( $filter['tag'] ) ? $filter['tag'] : $filter;
			$priority = !empty( $filter['priority'] ) ? $filter['priority'] : 10;
			$accepted_args = !empty( $filter['accepted_args'] ) ? $filter['accepted_args'] : 1;
      add_filter( $hook, [ $this, $function ], $priority, $accepted_args );
		}
	}

  public function setup_shortcodes() {
    foreach( $this->shortcodes as $tag => $callback ) {
      if( !method_exists( $this, $callback ) ) continue;
      add_shortcode( $tag, [ $this, $callback ] );
    }
  }

  public function setup_scripts_styles() {
    foreach( $this->scripts as $handle => $src ) {
      $path = !empty( $src['src'] ) ? $src['src'] : $src;
      if( !file_exists( plugin_dir_path( __FILE__ ) . $path ) && !filter_var( $path, FILTER_VALIDATE_URL ) ) continue;
      $url = filter_var( $path, FILTER_VALIDATE_URL ) ? $path : plugins_url( $path, __FILE__ );
      $deps = !empty( $src['deps'] ) ? $src['deps'] : [];
      $ver = !empty( $src['ver'] ) ? $src['ver'] : $this->version;
      $in_footer = !empty( $src['in_footer'] ) ? $in_footer : false;
      wp_register_script( $handle, $url, $deps, $ver, $in_footer );
    }
    foreach( $this->styles as $handle => $src ) {
      $path = !empty( $src['src'] ) ? $src['src'] : $src;
      if( !file_exists( plugin_dir_path( __FILE__ ) . $path ) && !filter_var( $path, FILTER_VALIDATE_URL ) ) continue;
      $url = filter_var( $path, FILTER_VALIDATE_URL ) ? $path : plugins_url( $path, __FILE__ );
      $deps = !empty( $src['deps'] ) ? $src['deps'] : [];
      $ver = !empty( $src['ver'] ) ? $src['ver'] : $this->version;
      $media = !empty( $src['media'] ) ? $src['media'] : 'all';
      wp_register_style( $handle, $url, $deps, $ver, $media );
    }
  }

}

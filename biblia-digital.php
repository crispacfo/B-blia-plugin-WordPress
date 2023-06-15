<?php
/*
  Plugin Name: Bíblia Digital
  Plugin URI: estudobiblico.org
  Description: Tenha a Bíblia Sagrada em seu site para que seus visitantes acessem e conheçam mais sobre a palavra.
  Version: 1.0.3
  Author: Leonardo José Nunes
  Author URI: https://swaramadra.net
  URI: https://estudobiblico.org
  License: GNU General Public License
*/

if( ! defined('ABSPATH') ) exit;

require_once(plugin_dir_path( __FILE__ ) . 'includes/class-bible-plugin-config.php');
require_once(plugin_dir_path( __FILE__ ) . 'includes/class-bible-plugin-controller.php');
require_once(plugin_dir_path( __FILE__ ) . 'includes/class-bible-plugin-admin-register.php');

function biblia_digital_shortcode() {
  wp_enqueue_script( 'biblia_digital_plugin_js', plugin_dir_url( __FILE__  ) . "build/index.js", ["wp-element"], "1.0.0", true );
  wp_enqueue_style( 'biblia_digital_plugin_css', plugin_dir_url( __FILE__ ) . "build/index.css", null, "1.0.0" );
	wp_localize_script( 'biblia_digital_plugin_js', 'estudobiblico', array(
    'root_url' => get_site_url()
  ));
  return "<div id='biblia-digital-plugin' class='biblia-digital-plugin'></div>";
}

add_shortcode( 'biblia-digital', 'biblia_digital_shortcode' );

?>
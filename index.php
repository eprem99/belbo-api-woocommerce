<?php
/**
* Plugin Name: Belbo API Integration
* Plugin URI: https://vecto.digital/
* Description: Belbo API integration
* Version: 1.0.0
* Author: Yeprem Ghukasyan
* Author URI: https:/gegstyle.com/
**/
define( 'BELBO_PLUGIN_VERSION', '1.0.0' );
define( 'BELBO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BELBO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Activation or deactivation plugin hooks
register_activation_hook( __FILE__, array( 'Belbo', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Belbo', 'plugin_deactivation' ) );

require_once( BELBO_PLUGIN_DIR . 'inc/class-belbo.php' );
require_once( BELBO_PLUGIN_DIR . 'inc/class-belbo-settings.php' );
require_once( BELBO_PLUGIN_DIR . 'inc/class-belbo-conect.php' );

add_action( 'init', array( 'Belbo', 'init' ) );
add_action( 'wp_enqueue_scripts', array('Belbo', 'load_front_resources')); 

add_action( 'wp_ajax_belbo_conect_locations', array( 'Belbo_Conect', 'Belbo_Conect_Locations_Ajax' ));
add_action( 'wp_ajax_nopriv_belbo_conect_locations', array( 'Belbo_Conect', 'Belbo_Conect_Locations_Ajax'));
add_action( 'wp_ajax_belbo_conect_products', array( 'Belbo_Conect', 'Belbo_Conect_Products_Ajax' ));
add_action( 'wp_ajax_nopriv_belbo_conect_products', array( 'Belbo_Conect', 'Belbo_Conect_Products_Ajax'));
add_action( 'wp_ajax_belbo_add_cart', array( 'Belbo', 'belbo_add_cart' ));
add_action( 'wp_ajax_nopriv_belbo_add_cart', array( 'Belbo', 'belbo_add_cart'));
add_action( 'wp_ajax_belbo_add_dates', array( 'Belbo', 'belbo_add_dates' ));
add_action( 'wp_ajax_nopriv_belbo_add_dates', array( 'Belbo', 'belbo_add_dates'));
add_action( 'wp_ajax_belbo_add_time', array( 'Belbo', 'belbo_add_time' ));
add_action( 'wp_ajax_nopriv_belbo_add_time', array( 'Belbo', 'belbo_add_time'));



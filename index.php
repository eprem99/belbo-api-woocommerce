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


require_once( BELBO_PLUGIN_DIR . 'inc/class-belbo-settings.php' );
require_once( BELBO_PLUGIN_DIR . 'inc/class-belbo-conect.php' );


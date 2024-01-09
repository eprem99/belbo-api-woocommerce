<?php 

class Belbo_Settings {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'belbo_setting' ) );
		add_action('admin_menu', array($this,'belbo_setting_init'));
		add_action( 'admin_enqueue_scripts', array( $this, 'load_resources' ) );
	}
	public static function load_resources() {
		global $hook_suffix;

		if ( in_array( $hook_suffix, apply_filters( 'belbo-api-settings_hook_suffixes', array(
			'settings_page_belbo-api-settings',
		) ) ) ) {
			wp_register_style( 'belbo-admin', BELBO_PLUGIN_URL . 'assets/css/admin-styles.css', array());
			wp_enqueue_style( 'belbo-admin' );

			wp_register_script( 'belbo-admin.js', BELBO_PLUGIN_URL . 'assets/js/admin-js.js', array());
			wp_enqueue_script( 'belbo-admin.js' );
		}
	}
	public function belbo_setting () {
		$hook = add_options_page( 'Belbo API integration','Belbo settings','administrator','belbo-api-settings', array( $this, 'belbo_settings_page' ) );
		if ( $hook ) {
			add_action( "load-$hook", array( 'Belbo_Settings', 'load_resources' ) );
		}
	}

	function belbo_setting_init() {
		register_setting('belbo-api-settings', 'belbo_setting_section');
		add_settings_section(
			'belbo_setting_section',
			'Belbo Api Setting',
			array($this,'front_page_callback'),
			'belbo-api-settings'
		);

		register_setting( 'belbo-api-settings', 'belbo_accaunt' );
		add_settings_field( 
			'belbo_accaunt', 
			'Belbo Accaunt', 
			array($this,'belbo_cron_settings_checkbox'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_accaunt', 
				'option_name' => 'belbo_accaunt' 
			)
		);

		register_setting( 'belbo-api-settings', 'belbo_login' );
		add_settings_field( 
			'belbo_login', 
			'Belbo Api login', 
			array($this,'belbo_cron_settings_checkbox'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_login', 
				'option_name' => 'belbo_login' 
			)
		);

		register_setting( 'belbo-api-settings', 'belbo_password' );
		add_settings_field( 
			'belbo_password', 
			'Belbo Api Password', 
			array($this,'belbo_cron_settings_checkbox'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_password', 
				'option_name' => 'belbo_password' 
			)
		);

		register_setting( 'belbo-api-settings', 'belbo_cron_locations', [ 'sanitize_callback' => array($this,'cron_initialize_locations') ] );
		add_settings_field( 
			'belbo_cron_locations', 
			'Belbo Api Cron Locations', 
			array($this,'belbo_cron_settings_input'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_cron_locations', 
				'option_name' => 'belbo_cron_locations' 
			)
		);

		register_setting( 'belbo-api-settings', 'belbo_cron_products', [ 'sanitize_callback' => array($this,'cron_initialize_products') ] );
		add_settings_field( 
			'belbo_cron_products', 
			'Belbo Api Cron Products', 
			array($this,'belbo_cron_settings_input'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_cron_products', 
				'option_name' => 'belbo_cron_products' 
			)
		);
		register_setting( 'belbo-api-settings', 'belbo_cron_log' );
		add_settings_field( 
			'belbo_cron_log', 
			'Belbo Api Cron Log', 
			array($this,'belbo_cron_settings_input'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_cron_log', 
				'option_name' => 'belbo_cron_log' 
			)
		);
		register_setting( 'belbo-api-settings', 'belbo_cron_run' );
		add_settings_field( 
			'belbo_cron_run', 
			'Belbo Api Cron Run', 
			array($this,'belbo_cron_settings_button'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_cron_run', 
				'option_name' => 'belbo_cron_run' 
			)
		);

		register_setting( 'belbo-api-settings', 'belbo_success' );
		add_settings_field( 
			'belbo_success', 
			'Belbo Api success link', 
			array($this,'belbo_cron_settings_checkbox'), 
			'belbo-api-settings', 
			'belbo_setting_section', 
			array( 
				'id' => 'belbo_success', 
				'option_name' => 'belbo_success' 
			)
		);
	}
	public function front_page_callback() {
		esc_html_e( 'Belbo Api and Cron Settings', 'belbo' );
	}


	public function belbo_cron_settings_checkbox( $val ){
		$id = $val['id'];
		$option_name = $val['option_name'];
		$html = '<input type="text" id="'.$id.'" name="'.$option_name.'" value="'.esc_attr( get_option($option_name) ).'"/>';
		echo $html;
	}
	
	public function belbo_cron_settings_input($val) {
	
		$id = $val['id'];
		$option_name = $val['option_name'];
        $chicked = (get_option($option_name) == 1) ? 'checked' : '';
	
		$html = '<input type="checkbox" id="'.$id.'" name="'.$option_name.'" value="1"' .$chicked . '/>';
		$html .= '<label for="'.$id.'">'.esc_html('Enable or disable Cron Belbo Api','belbo').'</label>';
	
		echo $html;
	}

	public function belbo_cron_settings_button( $val ){
		$html = '<input class="button" style="margin-right: 15px" type="button" id="belbo_run_stores" name="belbo_run_stores" value="Run Stores"/>';
		$html .= '<input class="button" type="button" id="belbo_run_services" name="belbo_run_services" value="Run Services"/>';
		echo $html;
	}

	public function belbo_settings_page() {
		include(BELBO_PLUGIN_DIR . 'views/admin-config.php');
	}

	public function cron_initialize_locations($value){
	
		if($value == 1) {
           Belbo::Belbo_Cron_Locations();
		}else{
			Belbo::Belbo_Cron_Locations_Unschedule();
		}
	
		$value = sanitize_text_field( $value );
	
		return $value;
	}

	public function cron_initialize_products($value){
	
		if($value == 1) { 
			$belbo = new Belbo();
			$belbo->Belbo_Cron_Products();
		}else{
			Belbo::Belbo_Cron_Products_Unschedule();
		}
	
		$value = sanitize_text_field( $value );
	
		return $value;
	}

	public function belbo_messages($val){
		$html = '';
		// print_r($val);
		if($val != null){
			$html .= '<div class="notice notice-primary"> 
			<p><strong>'.$val['title'].'</strong></p></div>';
		}

		echo $html;
	}
}
new Belbo_Settings;
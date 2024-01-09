<?php

class Belbo {

    private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;
        add_action( 'Belbo_Locations', array( 'Belbo_Conect', 'Belbo_Conect_Locations'));
        add_action( 'Belbo_Products', array('Belbo_Conect', 'Belbo_Conect_Products')); 
    }
    
	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {

	}
	public static function load_front_resources() {
		wp_register_script( 'belbo-front', BELBO_PLUGIN_URL . 'assets/js/front-js.js', array(), '1.0.3', true );
		wp_enqueue_script( 'belbo-front' );
		wp_register_script( 'belbo-calendar-js', BELBO_PLUGIN_URL . 'assets/js/calendar.full.js', array(), '1.0', true );
		wp_enqueue_script( 'belbo-calendar-js' );
		wp_register_style( 'belbo-calendar', BELBO_PLUGIN_URL . 'assets/css/calendar.css', array());
		wp_enqueue_style( 'belbo-calendar' );
	}
    /**
	 * Removes all connection options
	 * @static
	*/
    public static function plugin_deactivation( ) {
		// Remove any scheduled cron jobs.
		$belbo_cron_events = array(
			'Belbo_Locations',
			'Belbo_Products',
		);
		
		foreach ( $belbo_cron_events as $belbo_cron_event ) {
			$timestamp = wp_next_scheduled( $belbo_cron_event );
			
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $belbo_cron_event );
			}
		}
	}

    public static function Belbo_Cron_Locations_Unschedule() {
        $timestamp = wp_next_scheduled( 'Belbo_Locations' );
			
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'Belbo_Locations' );
        } 
    }

    public static function Belbo_Cron_Products_Unschedule() {
        $timestamp = wp_next_scheduled( 'Belbo_Products' );
			
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'Belbo_Products' );
        }
    }
    
    public function Belbo_Cron_Locations() {
        if ( ! wp_next_scheduled( 'Belbo_Locations' ) ) {
			wp_schedule_event( time(), 'daily', 'Belbo_Locations');
		}
    }

    public function Belbo_Cron_Products() {
        if ( ! wp_next_scheduled( 'Belbo_Products' ) ) {
			wp_schedule_event( time(), 'daily', 'Belbo_Products');
		}
    }

    public function belbo_add_dates(){
		$login = new Belbo_Conect();
		$s = $login->Belbo_Login();
			$accaunt = get_option('belbo_accaunt');
			$termid = $_POST['store'];
			$store = get_post_meta($termid, 'store_location', true);
			if($_POST['lang'] == 'de'){
				$storeid = get_term_meta($store, 'belbo_location_id', true);
			}else{
				$storeid = get_term_meta($store, 'belbo_location_id_en', true);
			}
	
			$products = $_POST['products'];
			$sku = array();
			foreach($products as $product_id){
				$product = wc_get_product( $product_id );
				$id = $product->get_sku();
				$ids = explode('-', $id);
				$sku[] = $ids[0];
			}

			$service = implode(',', $sku);
	
			$args = array(
				'timeout' => 60,
				'headers' => array("Cookie" => "JSESSIONID={$s}"),
				'body' => array(
					'servicerId' => $storeid,
					'serviceIds' => "{$service}",
				)
			);
	
			$data = wp_remote_post("https://{$accaunt}.belbo.com/mobile/rest/1.0/appointment/dayCandidates", $args);
	
			$dat = json_decode($data['body'], true);
	
			$response = array(
				'store' => 6609,
				'product' => $sku[0],
				'data' => $dat,
				'success' => true
			);
			wp_send_json( $response );	
	}

    public function belbo_add_time(){
		$login = new Belbo_Conect();
		$s = $login->Belbo_Login();

		$accaunt = get_option('belbo_accaunt');
		$termid = $_POST['store'];
		$store = get_post_meta($termid, 'store_location', true);
		if($_POST['lang'] == 'de'){
			$storeid = get_term_meta($store, 'belbo_location_id', true);
		}else{
			$storeid = get_term_meta($store, 'belbo_location_id_en', true);
		}
		if(isset($_POST['products'])){
			$products = $_POST['products'];
			$sku = array();
			foreach($products as $product_id){
				$product = wc_get_product( $product_id );
				$id = $product->get_sku();
				$ids = explode('-', $id);
				$sku[] = $ids[0];
			}

			$product = implode(',', $sku);
		}elseif(isset($_POST['product'])){
			$product = $_POST['product'];
		}
		
		$date = date_create($_POST['data']);
		$day = date_format($date, "d.m.Y");

		$args = array(
			'timeout' => 60,
			'headers' => array("Cookie" => "JSESSIONID={$s}"),
			'body' => array(
				'servicerId' => $storeid,
				'serviceIds' => "{$product}",
				'date' => $day,
			)
		);

		$data = wp_remote_post("https://{$accaunt}.belbo.com/mobile/rest/1.0/appointment/timeCandidates", $args);

		$dat = json_decode($data['body'], true);

		$response = array(
			'data' => $dat,
			'success' => true
		);
		
		wp_send_json( $response );
	}

	public function belbo_add_cart() {
		$login = new Belbo_Conect();
		$s = $login->Belbo_Login();
        $termid = $_POST['store'];
		$store = get_post_meta($termid, 'store_location', true);
		if($_POST['lang'] == 'de'){
			$storeid = get_term_meta($store, 'belbo_location_id', true);
		}else{
			$storeid = get_term_meta($store, 'belbo_location_id_en', true);
		}
		


		if($_POST['date'] && $_POST['time'] && $storeid && $_POST['products']){
			$accaunt = get_option('belbo_accaunt');
			$back_url = get_option('belbo_success');

			$date = date_create($_POST['date']);
			$day = date_format($date, "d.m.Y");
			$time = $_POST['time'];
			$products = $_POST['products'];
			$sku = array();
			foreach($products as $product_id){
				$product = wc_get_product( $product_id );
				$id = $product->get_sku();
				$ids = explode('-', $id);
				$sku[] = $ids[0];
			}
			$service = implode(',', $sku);
			//print_r($service);
			$args = array(
			    'timeout'     => 60,
				'headers' => array('Cookie: JSESSIONID='.$s),
				'body' => array(
					'date' => $day,
					'time' => $time,
					'selectedProducts' => $service,
					'servicerGroupId' => $storeid,
					'successPage' => ($back_url != null || $back_url != '') ? $back_url : '',
				)
			);

			 $data = wp_remote_post("https://{$accaunt}.belbo.com/newAppointment/bookAppointment", $args);
			 $dat = json_decode($data['body'], true);
			// print_r($dat);
            if($dat['result'] == "OK"){
				$datas = array(
                        'result' => 'OK',
						'url' => "https://{$accaunt}.belbo.com".$dat['url']
				);
				$response = array(
					'data' => $datas,
					'success' => true
				);
				wp_send_json( $response );	
			}else{
				$response = array(
					'success' => false
				);
				wp_send_json( $response );
			}
		 }else{
			
			$response = array(
				'success' => false
			);
			wp_send_json( $response );
		}
		
	}

}

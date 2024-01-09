<?php

class Belbo_Conect
{
    public function __construct()
    {

    }

    public function Belbo_Conect_Products_Ajax(){
        $belbon = new Belbo_Conect();
        $belbon ->Belbo_Conect_Products();
        die();
    }

    public function Belbo_Conect_Locations_Ajax(){
        $belbon = new Belbo_Conect();
        $belbon ->Belbo_Conect_Locations();
        die();
    }

    public function Belbo_Login(){
        $accaunt = get_option('belbo_accaunt');
        $login = get_option('belbo_login');
        $password = get_option('belbo_password');

        if (wp_http_supports(["ssl"])) {
        $response = wp_remote_post(
            "https://{$accaunt}.belbo.com/mobile/rest/1.0/init/read"
        );
        } else {
            $response = wp_remote_post(
                "http://{$accaunt}.belbo.com/mobile/rest/1.0/init/read"
            );
        }

        $sesion = json_decode($response["body"], true);
        if($sesion['token'] == '' || $sesion['token'] == null)
        $args = [
            'timeout' => 60, 
            'headers' => array("Cookie" => "JSESSIONID={$sesion['sessionID']}"),
            'body' => array('username' => $login, 'password' => $password)
        ];

        if (wp_http_supports(["ssl"])) {
            $response = wp_remote_post(
                "https://{$accaunt}.belbo.com/mobile/rest/1.0/login/login",
                $args
            );
        } else {
            $response = wp_remote_post(
                "http://{$accaunt}.belbo.com/mobile/rest/1.0/login/login",
                $args
            );
        }

        return $sesion['sessionID'];
    }

    // public static function Belbo_Sesion(){
    //   $accaunt = get_option('belbo_accaunt');
      

    // }

    public static function Belbo_Conect_Locations()
    {
        global $wpdb;
        $sesion = Belbo_Conect::Belbo_Login();
        $belbon = new Belbo_Conect();
        $args = [
            'headers' => array('Cookie: JSESSIONID='.$sesion),
            "locale" => "de",
        ];

        if (wp_http_supports(["ssl"])) {
            $response = wp_remote_post(
                "https://kosmagic.belbo.com/mobile/rest/1.0/data/stores",
                $args
            );
        } else {
            $response = wp_remote_post(
                "http://kosmagic.belbo.com/mobile/rest/1.0/data/stores",
                $args
            );
        }

        $locations = json_decode($response["body"], true);
        $belbon->Belbo_Logger("Stores count " . count($locations["stores"]), true);
        $meta_key = "belbo_location_id";
        foreach ($locations["stores"] as $location) {
            $belbon->Belbo_Logger("Store Belbo " . $location["id"], true);
            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT term_id FROM $wpdb->termmeta WHERE meta_value = %d AND meta_key = %s",
                    [$location["id"], $meta_key]
                )
            );
            if((is_array($data) && count($data) <= 0) || $data == null) {
                $slug = Belbo_Conect::slugify($location["name"]);
                $insert_res = wp_insert_term(
                    $location["name"],
                    "store_locations",
                    [
                        "slug" => $slug,
                    ]
                );
                update_term_meta(
                    $insert_res["term_taxonomy_id"],
                    $meta_key,
                    $location["id"]
                );
                	
                pll_set_term_language($insert_res["term_taxonomy_id"], 'de');
            } else {
                $belbon->Belbo_Logger("Store id " . $data[0]->term_id, true);
                $post = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %d AND meta_key = %s",
                        [$data[0]->term_id, "belbo_location_id"]
                    )
                );
                $belbon->Belbo_Logger("Store post id " . $post[0]->post_id, true);
                if (is_array($post) && count($post) > 0) {
                    $post_id = $post[0]->post_id;
                    $belbon->Belbo_Logger(
                        "post_id " . json_encode($post_id),
                        true
                    );
                    $address = $location["address"];
                    $args = [
                        "address" => $address,
                        "lat" => $location["latitude"],
                        "lng" => $location["longitude"],
                        "zoom" => 14,
                    ];
                    update_post_meta($post_id, "store_address_map", $args);
                    $i = 0;
                    foreach ($location["currentBusinessHours"] as $date) {
                        $week = Belbo_Conect::Get_Week($date["day"]);
                        $meta_key = "opening_hours_" . $i . "_working_day";
                        $open = "opening_hours_" . $i . "_opening_hour";
                        $closed = "opening_hours_" . $i . "_closing_hour";
                        $status = "opening_hours_" . $i . "_closed_status";
                        $time = explode(" - ", $date["time"]);
                        update_post_meta($post_id, $meta_key, $week);
                        update_post_meta($post_id, $open, $time[0] . ":00");
                        update_post_meta($post_id, $closed, $time[1] . ":00");
                        update_post_meta($post_id, $status, 0);
                        $i++;
                    }
                }
            }
        }
        Belbo_Conect::Belbo_Conect_Locations_En();
        die();
    }


    public static function Belbo_Conect_Locations_En()
    {
        global $wpdb;
        $sesion = Belbo_Conect::Belbo_Login();
        $belbon = new Belbo_Conect();
        $args = [
            'headers' => array('Cookie: JSESSIONID='.$sesion),
            "locale" => "en",
        ];

        if (wp_http_supports(["ssl"])) {
            $response = wp_remote_post(
                "https://kosmagic.belbo.com/mobile/rest/1.0/data/stores",
                $args
            );
        } else {
            $response = wp_remote_post(
                "http://kosmagic.belbo.com/mobile/rest/1.0/data/stores",
                $args
            );
        }

        $locations = json_decode($response["body"], true);
        $belbon->Belbo_Logger("Stores count " . count($locations["stores"]), true);
        $meta_key = "belbo_location_id_en";
        foreach ($locations["stores"] as $location) {
            $belbon->Belbo_Logger("Store Belbo " . $location["id"], true);
            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT term_id FROM $wpdb->termmeta WHERE meta_value = %d AND meta_key = %s",
                    [$location["id"], $meta_key]
                )
            );
            $termde = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT term_id FROM $wpdb->termmeta WHERE meta_value = %d AND meta_key = %s",
                    [$location["id"], 'belbo_location_id']
                )
            );
            if((is_array($data) && count($data) <= 0) || $data == null) {
                $slug = Belbo_Conect::slugify($location["name"].'-en');
                $insert_res = wp_insert_term(
                    $location["name"],
                    "store_locations",
                    [
                        "slug" => $slug,
                    ]
                );
                update_term_meta(
                    $insert_res["term_taxonomy_id"],
                    $meta_key,
                    $location["id"].'-en'
                );
                	
                pll_set_term_language($insert_res["term_taxonomy_id"], 'en');
                pll_save_term_translations([
                    'en' => $insert_res["term_taxonomy_id"],
                    'de' => $termde[0]->term_id,
                    
                ]);
            } else {
                $belbon->Belbo_Logger("Store id " . $data[0]->term_id, true);
                $post = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %d AND meta_key = %s",
                        [$data[0]->term_id, "belbo_location_id_en"]
                    )
                );
                $belbon->Belbo_Logger("Store post id " . $post[0]->post_id, true);
                if (is_array($post) && count($post) > 0) {
                    $post_id = $post[0]->post_id;
                    $belbon->Belbo_Logger(
                        "post_id " . json_encode($post_id),
                        true
                    );
                    $address = $location["address"];
                    $args = [
                        "address" => $address,
                        "lat" => $location["latitude"],
                        "lng" => $location["longitude"],
                        "zoom" => 14,
                    ];
                    update_post_meta($post_id, "store_address_map", $args);
                    $i = 0;
                    foreach ($location["currentBusinessHours"] as $date) {
                        $week = Belbo_Conect::Get_Week($date["day"]);
                        $meta_key = "opening_hours_" . $i . "_working_day";
                        $open = "opening_hours_" . $i . "_opening_hour";
                        $closed = "opening_hours_" . $i . "_closing_hour";
                        $status = "opening_hours_" . $i . "_closed_status";
                        $time = explode(" - ", $date["time"]);
                        update_post_meta($post_id, $meta_key, $week);
                        update_post_meta($post_id, $open, $time[0] . ":00");
                        update_post_meta($post_id, $closed, $time[1] . ":00");
                        update_post_meta($post_id, $status, 0);
                        $i++;
                    }
                }
            }
        }
        die();
    }

    public static function Get_Week($dates)
    {
        if ($dates == "Montag") {
            return "Monday";
        } elseif ($dates == "Dienstag") {
            return "Tuesday";
        } elseif ($dates == "Mittwoch") {
            return "Wednesday";
        } elseif ($dates == "Donnerstag") {
            return "Thursday";
        } elseif ($dates == "Freitag") {
            return "Friday";
        } elseif ($dates == "Samstag") {
            return "Saturday";
        } elseif ($dates == "Sonntag") {
            return "Sunday";
        }
    }

    public static function Belbo_Conect_Products()
    {
        $belbon = new Belbo_Conect();
        $sesion = Belbo_Conect::Belbo_Login();
        global $wpdb;
        $meta_key = "belbo_location_id";
        $meta_cat = "belbo_service_cat";
        $stores = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->termmeta WHERE meta_key = %s",
                $meta_key
            ),
        );

        foreach ($stores as $strore) {
            $belbon->Belbo_Logger("Store " . json_encode($strore), true);
            $args = [
              "timeout" => 25,
                "headers" => [
                    'Cookie: JSESSIONID='.$sesion,
                    "locale" => "de",
                ],
                "body" => ["servicerGroupId" => $strore->meta_value],
            ];

            if (wp_http_supports(["ssl"])) {
                $response = wp_remote_post(
                    "https://kosmagic.belbo.com/externalBooking/calcGroups",
                    $args
                );
            } else {
                $response = wp_remote_post(
                    "http://kosmagic.belbo.com/externalBooking/calcGroups",
                    $args
                );
            }
           // $belbon->Belbo_Logger("id" . json_encode($response), true);

            $body = json_decode($response["body"], true);
            if (isset($body) && $body != null) {
                foreach ($body["groups"] as $service) {
                    $slug = Belbo_Conect::slugify($service["description"]);
                    $insert_res = wp_insert_term(
                        $service["description"],
                        "product_cat",
                        [
                            "description" => $service["description"],
                            "slug" => $slug,
                        ]
                    );

                    if (!is_wp_error($insert_res)) {
                        $term_id = $insert_res["term_taxonomy_id"];
                        update_term_meta(
                            $term_id,
                            $meta_cat,
                            $service["id"]
                        );
                        pll_set_term_language($term_id, 'de');
                    }else{
                     // $belbon->Belbo_Logger("id" . json_encode($insert_res->error_data['term_exists']), true);
                      $term_id = $insert_res->error_data['term_exists'];
                    } 

                    $products = Belbo_Conect::Belbo_Find_Products(
                        $service["elements"],
                        $body["services"]
                    );
                    if ($products) {
                    //  $belbon->Belbo_Logger("id" . json_encode($products), true);
                        Belbo_Conect::Belbo_Product_Create(
                            $term_id,
                            $products,
                            $strore->term_id
                        );
                    }
                }
            }
        }
        Belbo_Conect::Belbo_Conect_Products_En();
        die();
    }

    public static function Belbo_Product_Create($term_id, $products, $strore)
    {
        global $wpdb;
        $id = array();
        foreach ($products as $item) {
          $belbon = new Belbo_Conect();
        //  $belbon->Belbo_Logger("Location" . json_encode($item), true);
            $store = (int)$strore;
            $sku = $item["id"];
            $product_id = wc_get_product_id_by_sku($sku);
            if ($product_id) {
                $product = wc_get_product($product_id);
                $product->set_regular_price($item["fromPrice"]);
                $product->set_short_description($item["longDescription"]);
                $product->set_status('publish');
                $product->save();
                array_push($id, $product_id);
                $belbon->Belbo_Logger("Update Product De id " . json_encode($product_id), true);
                pll_set_post_language($product_id, 'de');
            } else {
                $slug = Belbo_Conect::slugify($item["description"]);
                $product = new WC_Product_Simple();
                $product->set_name(sanitize_text_field($item["description"]));
                $product->set_slug($slug);
                $product->set_sku($sku);
                $product->set_regular_price($item["fromPrice"]);
                $product->set_short_description($item["longDescription"]);
                $product->set_category_ids([$term_id]);
                $product->set_status('publish');
                $product->save();
                array_push($id, $product->id);
                $belbon->Belbo_Logger("Add Product De id " . json_encode($product->id), true);
                pll_set_post_language($product->id, 'de');
                wp_set_object_terms($product->id, array($store), 'store_locations', true);
            }
        }
        $storeid = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'store_location' AND meta_value = {$store}"
            ),
        );
        $post = get_post_meta($storeid[0]->post_id, 'store_products_rel', true);
        
        array_splice($id, array_search(0, $id), 1);
      if(is_array($post)){
        $result = array_merge($post, $id);
        update_post_meta($storeid[0]->post_id, 'store_products_rel', $result);
        
      }else{
        update_post_meta($storeid[0]->post_id, 'store_products_rel', $id);
      }
        
        
    }

    public static function Belbo_Find_Products($termproduct, $allproducts)
    {
        $belbon = new Belbo_Conect();

       // $belbon->Belbo_Logger("Groups" . json_encode($termproduct), true);
        $products = [];

        foreach ($allproducts as $product) {
            if (in_array($product["id"], $termproduct)) {
                $products[] = $product;
            }
        }
        return $products;
    }


    public static function Belbo_Conect_Products_En()
    {
        $belbon = new Belbo_Conect();
        $sesion = Belbo_Conect::Belbo_Login();
        global $wpdb;
        $meta_key = "belbo_location_id_en";
        $meta_cat = "belbo_service_cat_en";
        $meta_cat_de = "belbo_service_cat";
        $stores = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->termmeta WHERE meta_key = %s",
                $meta_key
            ),
        );

        foreach ($stores as $strore) {
            $belbon->Belbo_Logger("Store " . json_encode($strore), true);
            $loc = explode('-', $strore->meta_value);
            $args = [
              "timeout" => 25,
                "headers" => [
                    "Content-Type" => "application/x-www-form-urlencoded",
                    "Cookie" => "JSESSIONID=".$sesion,
                    "locale" => "en",
                ],
                "body" => ["servicerGroupId" => $loc[0]],
            ];

            if (wp_http_supports(["ssl"])) {
                $response = wp_remote_post(
                    "https://kosmagic.belbo.com/externalBooking/calcGroups",
                    $args
                );
            } else {
                $response = wp_remote_post(
                    "http://kosmagic.belbo.com/externalBooking/calcGroups",
                    $args
                );
            }
           // $belbon->Belbo_Logger("id" . json_encode($response), true);

            $body = json_decode($response["body"], true);
            
            if (isset($body) && $body != null) {

                foreach ($body["groups"] as $service) {
                    $data = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT term_id FROM $wpdb->termmeta WHERE meta_value = %d AND meta_key = %s",
                            [$service["id"], $meta_cat_de]
                        )
                    );
                    $belbon->Belbo_Logger("Ens term id " . json_encode($data[0]->term_id), true);
                    if ($data[0]->term_id != null) {
                        $slug = Belbo_Conect::slugify($service["description"].'-en');
                        $insert_res = wp_insert_term(
                            $service["description"],
                            "product_cat",
                            [
                                "description" => $service["description"],
                                "slug" => $slug,
                            ]
                        );

                        if (!is_wp_error($insert_res)) {
                            $term_id = $insert_res["term_taxonomy_id"];
                            update_term_meta(
                                $term_id,
                                $meta_cat,
                                $service["id"]
                            );
                            
                            $belbon->Belbo_Logger("En term id " . json_encode($term_id), true);
                            pll_set_term_language($term_id, 'en');
                            pll_save_term_translations([
                                'en' => $term_id,
                                'de' => $data[0]->term_id,
                            ]);
                        }else{
                        // $belbon->Belbo_Logger("id" . json_encode($insert_res->error_data['term_exists']), true);
                        $term_id = $insert_res->error_data['term_exists'];
                        } 

                        $products = Belbo_Conect::Belbo_Find_Products(
                            $service["elements"],
                            $body["services"]
                        );
                        if ($products) {
                          $belbon->Belbo_Logger("id Strore" . json_encode($strore->term_id), true);
                            Belbo_Conect::Belbo_Product_Create_En(
                                $term_id,
                                $products,
                                $strore->term_id
                            );
                        }
                    }
                }

            }
        }
    }

    public static function Belbo_Product_Create_En($term_id, $products, $store_id)
    {
        global $wpdb;
        $id = array();
        $store = (int)$store_id;
        $term_id = (int)$term_id;
        foreach ($products as $item) {
          $belbon = new Belbo_Conect();
            $sku = $item["id"];
            $skuen = $item["id"].'-en';
            $product_id = wc_get_product_id_by_sku($sku);
            if($product_id){
                $product_en = wc_get_product_id_by_sku($skuen);
                if ($product_en) {
                    $product = wc_get_product($product_en);
                    $product->set_regular_price($item["fromPrice"]);
                    $product->set_category_ids([$term_id]);
                    $product->save();
                    array_push($id, $product_en);
                    $belbon->Belbo_Logger("Update Product En id " . json_encode($product_en), true);
                } else {
                    $belbon->Belbo_Logger("Add Product En  Term " . json_encode($term_id), true);
                    $slug = Belbo_Conect::slugify($item["description"].'_en');
                    $product = new WC_Product_Simple();
                    $product->set_name(sanitize_text_field($item["description"]));
                    $product->set_slug($slug);
                    $product->set_sku($skuen);
                    $product->set_regular_price($item["fromPrice"]);
                    $product->set_short_description($item["longDescription"]);
                    $product->set_category_ids([$term_id]);
                    $product->save();
                    array_push($id, $product->id);
                    $belbon->Belbo_Logger("Add Product En id " . json_encode($product->id), true);
                    pll_set_post_language($product->id, 'en');
                    pll_save_post_translations([
                        'de' => $product_id,
                        'en' => $product->id,
                    ]);
                                       
                }
                wp_set_object_terms($product->id, array($store), 'store_locations', true);
            }
        }
        $storeid = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'store_location' AND meta_value = {$store_id}"
            ),
        );
        $post = get_post_meta($storeid[0]->post_id, 'store_products_rel', true);
        
        array_splice($id, array_search(0, $id), 1);
      if(is_array($post)){
        $result = array_merge($post, $id);
        update_post_meta($storeid[0]->post_id, 'store_products_rel', $result);
        
      }else{
        update_post_meta($storeid[0]->post_id, 'store_products_rel', $id);
      }
    }

    public static function slugify($text, string $divider = "_")
    {
        $text = preg_replace("~[^\pL\d]+~u", $divider, $text);
        $text = iconv("utf-8", "us-ascii//TRANSLIT", $text);
        $text = preg_replace("~[^-\w]+~", "", $text);
        $text = trim($text, $divider);
        $text = preg_replace("~-+~", $divider, $text);
        $text = strtolower($text);

        if (empty($text)) {
            return "n-a";
        }

        return $text;
    }

    public function Belbo_Logger($var, $info = false)
    {
        $information = "";
        $log = get_option('belbo_cron_log');
        if ($var && $log) {
            if ($info) {
                $information = "\n\n";
                $information .= str_repeat("-=", 64);
                $information .= "\nDate: " . date("Y-m-d H:i:s");
                $information .= "\nBelbo: \n";
            }
            $result = $var;
            if (is_array($var) || is_object($var)) {
                $result = "\n" . print_r($var, true);
            }
            $result .= "\n\n";
            $path = dirname(__FILE__) . "/belbo.log";
            error_log($information . $result, 3, $path);
            return true;
        }
        return false;
    }
}

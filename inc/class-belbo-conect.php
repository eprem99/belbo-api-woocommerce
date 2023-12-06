<?php

class Belbo_Conect {
    public function __construct()
    {
      // public $accaunt;
        
    }

    public function Conect_Belbo() {
        if ( wp_http_supports( array( 'ssl' ) ) ) {
          $response = wp_remote_get( 'https://belbo.com' );
        } else {
          $response = wp_remote_get( 'http://belbo.com' );
        }
    }

    public function Belbo_Logger($var, $info = false) {
        $information = "";
        if ($var) {
            if ($info) {
                $information = "\n\n";
                $information .= str_repeat("-=", 64);
                $information .= "\nDate: " . date('Y-m-d H:i:s');
                $information .= "\nBelbo: \n";
            }
            $result = $var;
            if (is_array($var) || is_object($var)) {
                $result = "\n" . print_r($var, true);
            }
            $result .= "\n\n";
            $path = dirname(__FILE__) . '/belbo.log';
            error_log($information . $result, 3, $path);
            return true;
        }
        return false;
    }
}
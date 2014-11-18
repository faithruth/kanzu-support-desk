<?php
/**
 * Retrieves new mail and logs a ticket 
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
   throw new Exception( $errstr );
}

function exceptionHandler($e) {
    echo $e;
    delete_transient('ksd_deamon_transient');
}

set_error_handler('errorHandler', E_ERROR & ~E_DEPRECATED );
set_exception_handler('exceptionHandler');


if( php_sapi_name() !== 'cli' ) {
     die( -e("Must be run from commandline.") ) ;
}

function find_wordpress_base_path() {
    $dir = dirname(__FILE__);
    do {
        //it is possible to check for other files here
        if( file_exists($dir."/wp-config.php") ) {
            return $dir;
        }
    } while( $dir = realpath("$dir/..") );
    return null;
}

if ( null === ( $wp_base  = find_wordpress_base_path()."/" ) ){
    die( 'This file should be located inside a wordpress installation.' );
}

define ( 'BASE_PATH', $wp_base );
define ('WP_USE_THEMES', false);
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require (BASE_PATH . 'wp-load.php');

if ( ! class_exists( 'KSD_Deamon' ) ) :

class KSD_Deamon {
    
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;   

        
    private $transient         = null;
    
    public function __construct(){
        $this->transient = 'ksd_deamon_transient';
    }
    

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

            // If the single instance hasn't been set, set it now.
            if ( null == self::$instance ) {
                    self::$instance = new self;
            }

            return self::$instance;
    }
    
    public function run (){
        
        $transient = $this->transient;
        $value     = $this->transient;
        $expiration= 60 * 60 * 1 ; //1 hr
        
        if (  false === get_transient( $transient ) ){
            set_transient( $transient, $value, $expiration );

            do_action('ksd_run_deamon');

            delete_transient($transient);            
        }else{
            _e( 'Script still running.' );
        }
        

    }
            

}

endif;
$ksd_deamon = KSD_Deamon::get_instance();

try{
    $ksd_deamon->run();
}  catch (Exception $e){
    delete_transient( 'ksd_deamon_transient' );
}
?>

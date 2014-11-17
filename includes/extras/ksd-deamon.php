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

error_reporting(-1);

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

        
    private $pid_file = null;
    
    public function __construct(){
        $this->pid_file = KSD_PLUGIN_DIR . '/includes/extras/pids/ksd_deamon.pid';
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
        
        $this->create_pid_file();
        
        do_action('ksd_run_deamon');
        
        $this->delete_pid_file();
    }
            
    /**
     * Delete the pid file.
     */
    private function delete_pid_file(){
        unlink( $this->pid_file   );
    }
    
    /**
     * create pid file.
     */
    private function create_pid_file(){
        
        //create pid file to ensure only one instance of this script runs at a time.;
        if ( file_exists( $this->pid_file ) ){
            $pid_arr = file( $this->pid_file  ); 
            die(  __('File ' .$this->pid_file  . ' already exists. The script is already running with pid '
                          . $pid_arr[0] . "\n") );
        }
        
        //Create pid file.
        $pid = getmypid();
        $fh = fopen( $this->pid_file  , "w") 
              or die( __("Unable to create pid file! Check permissions on " . KSD_PLUGIN_DIR . 
                      "/includes/extras/pids\n") );
        fwrite($fh, $pid);
        fclose($fh);
    }
    
}

endif;

$ksd_deamon = KSD_Deamon::get_instance();
$ksd_deamon->run();
?>

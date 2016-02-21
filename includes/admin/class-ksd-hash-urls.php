<?php
/**
 * Allow guests to create tickets by creating
 * hash URLs for them. 
 * Based heavily on http://wordpress.org/extend/plugins/post-password-plugin/
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Hash_Urls' ) ) :
    
class KSD_Hash_Urls {

        /**  
         * The cookie
         * @since 2.1.0
         * @var string 
         */
        protected $ksd_cookie = 'ksd-ticket-token_';
        
        /**
         * The validity of the secret URL in seconds
         * @var int 
         */
        private $cookie_lifetime = 315360000;//10 years. Be wary of 2038 & 2018 bugs. https://stackoverflow.com/questions/3290424/set-a-cookie-to-never-expire
        
     
        
	public function redirect_guest_tickets() {
            global $wp_query;
            
            if ( is_single() && isset( $wp_query->post->post_password ) && isset( $_GET['ksd'] ) && 'ksd_ticket' == $wp_query->post->post_type ) {
                if ( $this->do_cookies_match( $wp_query->post, sanitize_key( $_GET['ksd'] ) ) ) {
                    $this->set_cookie( $wp_query->post->post_password );
                }
            }
	}
        
        	
        
        /**
	 * Create a default salt
	 *
	 * @return string
	 */
	public function create_salt() {
		return substr( crypt( md5( time() ) ), 0, 32 );
	}
        

	/**
	 * Build our hash URL (custom permalink with token)
	 *
	 * @param int $post_ID
         * @param bool $force_short
	 * @return string
	 */
	public function create_hash_url( $post_ID, $force_short = false) {
		$permalinks     = get_option( 'permalink_structure' );
                $post           = get_post( $post_ID );
                
		if ( $force_short ) {
                    return wp_get_shortlink( $post_ID ).'&ksd='.$this->generate_token( $post->post_name, $post->post_password );
		} 
		return get_permalink( $post_ID ).( '' != $permalinks ? '?ksd=' : '&ksd=').$this->generate_token( $post->post_name, $post->post_password );
	 
	}
        
        
        
	/**
	 * Attempt a token match
	 *
	 * @param object $post 
	 * @param string $token 
	 * @return bool
	 */
	private function do_cookies_match( $post, $token ) {	
                $tmp_token = $this->generate_token( $post->post_name, $post->post_password );
		if( !isset( $_COOKIE[ $this->ksd_cookie.COOKIEHASH ] ) || $_COOKIE[ $this->ksd_cookie.COOKIEHASH ] != $tmp_token ) {
			return $tmp_token == $token;
		}

		return false;
	}
        
	/**
	 * Set the cookie 
	 * Functionality duplicated from WordPress' post password submit in wp-pass.php
	 *
	 * @param string $post_password 
	 * @return void
	 */
	private function set_cookie( $post_password ) {
		global $token, $wp_version;
		
		setcookie( $this->ksd_cookie.COOKIEHASH, $token, null, COOKIEPATH );
		$redirect_uri = 'http' . ( is_ssl() ? 's' : '') . '://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		
		if ( version_compare( $wp_version, '3.3', '<=') ) {// legacy cookie			
			setcookie( 'wp-postpass_' . COOKIEHASH, $post_password, time() + $this->cookie_lifetime, COOKIEPATH );
			wp_redirect( $redirect_uri ); 
		}
		else {			
                    global $wp_hasher;// hashed cookie
                    if ( empty( $wp_hasher ) ) {
			require_once( ABSPATH . 'wp-includes/class-phpass.php' );			
			$wp_hasher = new PasswordHash( 8, true );// By default, use the portable hash from phpass
                    }

                    setcookie( 'wp-postpass_' . COOKIEHASH, $wp_hasher->HashPassword( stripslashes( $post_password ) ), time() + $this->cookie_lifetime, COOKIEPATH );
                    wp_safe_redirect ($redirect_uri );
		}
		exit;
	}
        

	/**
	 * Make an access hash
	 * Currently as simple as md5( $post_name.$post_password )
	 *
	 * @param string $post_name 
         * @param string $post_password
	 * @return string
	 */
	private function generate_token( $post_name,$post_password ) {
            global $token;

            if ( is_null( $token ) ) {
		$settings  = Kanzu_Support_Desk::get_settings();

                if( empty( $settings['salt'] ) ) { 
                    $settings['salt'] = $this->create_salt();
                    update_option( KSD_OPTIONS_KEY, $settings );
                }

                $token = md5( $settings['salt'].$post_name.$post_password );
            }

            return $token;
	}        

}
endif;

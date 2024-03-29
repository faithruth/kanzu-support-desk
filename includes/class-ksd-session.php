<?php
/**
 * KSD Session
 * Adapted from EDD 2.5.13 
 *
 * This is a wrapper class for WP_Session and handles the storage of notices when tickets are created by post
 *
 * @package     KSD
 * @subpackage  Classes/Session
 * @author      Pippin Williamson
 * @since       2.2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KSD_Session Class
 *
 * @since 2.2.4
 */
class KSD_Session {

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 2.2.4
	 */
	private $session;

 

	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 2.2.4
	 */
	public function __construct() {
 

			// Use WP_Session (default)

			if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', 'ksd_wp_session' );
			}

			if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
				require_once KSD_PLUGIN_DIR . 'includes/libraries/wp_session/class-recursive-arrayaccess.php';
			}

			if ( ! class_exists( 'WP_Session' ) ) {
				require_once KSD_PLUGIN_DIR . 'includes/libraries/wp_session/class-wp-session.php';
				require_once KSD_PLUGIN_DIR . 'includes/libraries/wp_session/wp-session.php';
			}
                        if( ! $this->should_start_session() ) {
				return;
			}
 
                        $this->session = WP_Session::get_instance();
		}
  
 


	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 2.2.4
	 * @param string $key Session key
	 * @return string Session variable
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @since 2.2.4
	 *
	 * @param string $key Session key
	 * @param integer $value Session variable
	 * @return string Session variable
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}
 

		return $this->session[ $key ];
	}

  

 

	/**
	 * Determines if we should start sessions
	 *
	 * @since  2.2.4
	 * @return bool
	 */
	public function should_start_session() {

		$start_session = true;

		if( ! empty( $_SERVER[ 'REQUEST_URI' ] ) ) {

			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER[ 'REQUEST_URI' ], '/' );
			$uri       = untrailingslashit( $uri );

			if( in_array( $uri, $blacklist ) ) {
				$start_session = false;
			}

			if( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}

		}

		return apply_filters( 'ksd_start_session', $start_session );

	}
        
      

	/**
	 * Retrieve the URI blacklist
	 *
	 * These are the URIs where we never start sessions
	 *
	 * @since  2.2.4
	 * @return array
	 */
	public function get_blacklist() {

		$blacklist = apply_filters( 'KSD_Session_start_uri_blacklist', array(
			'feed',
			'feed/rss',
			'feed/rss2',
			'feed/rdf',
			'feed/atom',
			'comments/feed'
		) );

		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders
		$folder = str_replace( network_home_url(), '', get_site_url() );

		if( ! empty( $folder ) ) {
			foreach( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}

		return $blacklist;
	}
 

}
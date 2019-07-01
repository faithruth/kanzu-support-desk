<?php
/**
 * Admin side Kanzu Support Desk Notifications
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

namespace Kanzu\Ksd\KSD_Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email class functionality
 */
class Tinymce {
	/**
	 * Add cc button
	 *
	 * @since 2.0.3
	 */
	private function add_tinymce_cc_button() {
		if ( is_admin() ) {
			return;
		}
		add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_cc_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'register_tinymce_cc_button' ), 10, 2 );
	}

	/**
	 * Register the CC tinymce button
	 *
	 * @param array $plugin_array
	 * @return string
	 * @since 2.0.3
	 */
	public function add_tinymce_cc_plugin( $plugin_array ) {
		$plugin_array['KSDCC'] = KSD_PLUGIN_URL . '/assets/js/ksd-wp-editor-cc.js';
		return $plugin_array;
	}

	/**
	 * Register the CC button
	 *
	 * @param type $buttons
	 * @return type
	 * @since 2.0.3
	 */
	public function register_tinymce_cc_button( $buttons, $editor_id ) {
		global $current_screen;
		array_push( $buttons, 'ksd_cc_button' );
		return $buttons;
	}
}

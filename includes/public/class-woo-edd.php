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
class Woo_Edd {

	public function woo_support_tickets_tab( $tabs = array() ) {
		// Check WooCommerce show support tickets tab setting
		$settings = Kanzu_Support_Desk::get_settings();
		if ( 'yes' == $settings['show_woo_support_tickets_tab'] ) {
			// Add Support Tickets tab
			$tabs['support_tickets'] = array(
				'title'    => __( 'Support Tickets', 'kanzu-support-desk' ),
				'priority' => 50,
				'callback' => array( $this, 'woocommerce_support_tickets_tab' ),
			);
		}
		return $tabs;
	}

	public function woocommerce_support_tickets_tab() {
		include_once KSD_PLUGIN_DIR . 'includes/public/class-templates.php';
		$ksd_template = new KSD_Templates();
		$ksd_template->get_template_part( 'woocommerce', 'support-tickets-tab', true );
	}

	/**
	 * Append ticket list to WooCommerce 'My Account' page
	 *
	 * @since 2.2.0
	 */
	public function woo_edd_append_ticket_list() {
		printf( '<h2>%s</h2>', __( 'My Tickets', 'kanzu-support-desk' ) );
		$this->display_my_tickets();
	}

	public function edd_customers_admin_append_ticket_table() {
		printf( '<h3>%s</h3>', __( 'Tickets', 'kanzu-support-desk' ) );
		$this->get_ticket_table();
	}

	/**
	 * Add an extra column header to the WooCommerce Orders table
	 *
	 * @param array $table_headers
	 * @return string
	 */
	public function woo_orders_add_table_headers( $table_headers ) {
		$table_headers['order-tickets'] = '&nbsp;';
		return $table_headers;
	}

	/**
	 * Add a 'contact support' button to the WooCommerce orders table
	 *
	 * @param Array  $actions
	 * @param Object $order
	 * @return Array
	 * @TODO Receive and process woo_order_id
	 */
	public function woo_orders_add_ticket_button( $actions, $order ) {
		$ksd_settings                            = Kanzu_Support_Desk::get_settings();
		$url                                     = esc_url( add_query_arg( 'woo_order_id', $order->id, get_permalink( $ksd_settings['page_submit_ticket'] ) ) );
		$actions['ksd-woo-orders-create-ticket'] = array(
			'url'  => $url,
			'name' => __( 'Contact Support', 'kanzu-support-desk' ),
		);
		return $actions;
	}

	/**
	 * Get the current user's tickets displayed in a table.
	 * Used primarily to append the table to the EDD admin customer page
	 *
	 * @since 2.3.1
	 */
	public function get_ticket_table() {
		include_once KSD_PLUGIN_DIR . 'includes/public/class-templates.php';
		$ksd_template = new Templates();
		$ksd_template->get_template_part( 'list', 'my-tickets-table' );
	}

}

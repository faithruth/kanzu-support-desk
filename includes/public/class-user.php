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

namespace Kanzu\Ksd\Admin\Notification;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Email class functionality
 */
class User {

	/**
	 * Register a user
	 * @since 2.0.0
	 */
	public function register_user() {
		//Check the nonce
		if (!wp_verify_nonce($_POST['register-nonce'], 'ksd-register')) {
			die(__('Busted!', 'kanzu-support-desk'));
		}
		//@TODO Currently accepts defaults ( 'Last Name''First Name') Disable this
		//Perform server-side validation
		$first_name = sanitize_text_field($_POST['ksd_cust_firstname']);
		$last_name = sanitize_text_field($_POST['ksd_cust_lastname']);
		$username = sanitize_text_field($_POST['ksd_cust_username']);
		$email = sanitize_text_field($_POST['ksd_cust_email']);
		$password = sanitize_text_field($_POST['ksd_cust_password']);

		//@TODO Check if WP registrations are enabled

		//Check that we have all required fields
		if (empty($first_name) || empty($username) || empty($email) || empty($password)) {
			$response = __('Sorry, a required field is missing. Please fill in all fields.', 'kanzu-support-desk');
			echo (json_encode($response));
			die();
		}
		//Check that the fields are valid
		if ((strlen($first_name) || strlen($last_name) || strlen($username)) < 2) {
			$response = __('Sorry, the name provided should be at least 2 characters long.', 'kanzu-support-desk');
			echo (json_encode($response));
			die();
		}
		if (!is_email($email)) {
			$response = __('Sorry, the email you provided is not valid.', 'kanzu-support-desk');
			echo (json_encode($response));
			die();
		}
		//Check if the username is new
		if (username_exists($username)) {
			$response = __('Sorry, that username is already taken. Please choose another one', 'kanzu-support-desk');
			echo (json_encode($response));
			die();
		}
		//Yay! Register the user
		$userdata = array(
			'user_login' => $username,
			'user_pass' => $password,
			'user_email' => $email,
			'display_name' => $first_name . ' ' . $last_name,
			'first_name' => $first_name,
			'role' => 'ksd_customer',
		);
		if (!empty($last_name)) {
//Add the last name if it was provided
			$userdata['last_name'] = $last_name;
		}
		try {
			$user_id = wp_insert_user($userdata);
			if (!is_wp_error($user_id)) {
//Successfully created the user
				$login_url = sprintf('<a href="%1$s" title="%2$s">%3$s</a>', wp_login_url(), __('Login', 'kanzu-support-desk'), __('Click here to login', 'kanzu-support-desk'));
				$response = sprintf(__('Your account has been successfully created! If you are not automatically redirected in 5 seconds, %s', 'kanzu-support-desk'), $login_url);

				//Sign in the user
				$creds = array();
				$creds['user_login'] = $username;
				$creds['user_password'] = $password;
				$creds['remember'] = false;
				wp_signon($creds, false); //We don't check whether this happens

				echo (json_encode($response));
				die();
			} else {
//We had an error
				$error_message = __('Sorry, but something went wrong. Please retry or reload the page.', 'kanzu-support-desk');
				if (isset($user_id->errors['existing_user_email'])) {
//The email's already in use. Ask the user to reset their password
					$lost_password_url = sprintf('<a href="%1$s" title="%2$s">%3$s</a>', wp_lostpassword_url(), __('Lost Password', 'kanzu-support-desk'), __('Click here to reset your password', 'kanzu-support-desk'));
					$error_message = sprintf(__('Sorry, that email address is already used! %s', 'kanzu-support-desk'), $lost_password_url);
				}
				throw new Exception($error_message, -1);
			}
		} catch (Exception $e) {
			$response = array(
				'error' => array('message' => $e->getMessage(), 'code' => $e->getCode()),
			);
			echo json_encode($response);
			die(); // IMPORTANT: don't leave this out
		}

	}

	/**
	 * Filter attachments to only show those of the current user
	 * @return $query
	 */
	public function filter_media($query) {
		if (is_user_logged_in() && !current_user_can('manage_options')) {
			$query['author'] = get_current_user_id();
		}
		return $query;
	}

	/**
	 * Display a customer's tickets
	 * @since 2.0.0
	 */
	public function display_my_tickets() {
		//Include the templating and admin classes
		include_once KSD_PLUGIN_DIR . "includes/admin/class-ksd-admin.php";
		include_once KSD_PLUGIN_DIR . "includes/public/class-ksd-templates.php";
		$settings = Kanzu_Support_Desk::get_settings();
		if ("yes" == $settings['enable_customer_signup'] && !is_user_logged_in()) {
			$form_wrapper_classes = 'ksd-form-short-code';
			$form_classes = 'ksd-form-short-code-form';
			include KSD_PLUGIN_DIR . 'templates/default/html-public-register.php';
		} else {
			$ksd_template = new KSD_Templates();
			$ksd_template->get_template_part('list', 'my-tickets');
		}
	}

	/**
	 * Apply templates to a user's tickets prior to display
	 * Allow the tickets to be modified by actions before and after and for the ticket content itself
	 * to be modified using a filter
	 * @since 2.0.0
	 */
	public function apply_templates($content) {
		global $post;
		if ($post && $post->post_type == 'ksd_ticket' && is_singular('ksd_ticket') && is_main_query() && !post_password_required()) {
			$settings = Kanzu_Support_Desk::get_settings();
			if ("yes" == $settings['enable_customer_signup'] && !is_user_logged_in()) {
				//@TODO Send the current URL as the redirect URL for the 'login' and 'Register' action
				include_once KSD_PLUGIN_DIR . "includes/admin/class-ksd-admin.php";
				$form_wrapper_classes = 'ksd-form-short-code';
				$form_classes = 'ksd-form-short-code-form';
				include KSD_PLUGIN_DIR . 'templates/default/html-public-register.php';
				return;
			}

			global $current_user;
			if (in_array('ksd_customer', $current_user->roles) && $current_user->ID != $post->post_author) {
//This is a customer
				return __("Sorry, you do not have sufficient priviledges to view another customer's tickets", "kanzu-support-desk");
			}

			//Do actions before the ticket
			ob_start();
			do_action('ksd_before_ticket_content', $post->ID);
			$content = ob_get_clean() . $content;

			//Modify the ticket content
			$content = apply_filters('ksd_ticket_content', $content);

			//Do actions after the ticket
			ob_start();
			do_action('ksd_after_ticket_content', $post->ID);
			$content .= ob_get_clean();
		}
		return $content;
	}

	/**
	 * In the ticket archive, only show the current user's
	 * tickets. This prevents one user from seeing another's
	 * tickets
	 *
	 * @since 2.2.4
	 */
	public function hide_ticket_archive_content($query) {

		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		if (is_post_type_archive('ksd_ticket') && !empty($query->query['post_type']) && 'ksd_ticket' == $query->query['post_type']) {
			$query->set('author', get_current_user_id());
			return;
		}

	}

}
<?php

/**
 * Integrate KSD with WPCF7. Add an extra panel to the edit screen of contact form 7, save
 * the output and process submitted contact forms to create a ticket
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_WPCF7' ) ) :

    class KSD_WPCF7 {

    	public function __construct(){

	     	add_filter( 'wpcf7_editor_panels', array( $this, 'append_panel_to_wpcf7_admin' ) );

	       	add_action( 'wpcf7_save_contact_form', array( $this, 'save_wpcf7_admin_fields' ) );

	       	add_filter( 'wpcf7_form_class_attr', array( $this, 'append_class_to_cf7_form' ) );

	       	add_filter( 'wpcf7_skip_mail', array( $this, 'disable_sending_mail'), 10, 2 );

	       	add_action( 'wpcf7_before_send_mail', array( $this, 'create_ticket' ) );

	       	add_filter( 'ksd_channels', array( $this, 'add_cf7_channel' ) );
    	}

    	/**
    	 * Add a custom channel for the forms created using WPCF7
    	 *
    	 * @param array $ksd_channels KSD channels
    	 */
    	public function add_cf7_channel( $ksd_channels ){
    		$ksd_channels[] = 'wpcf7';
    		return $ksd_channels;
    	}

   		/**
   		 * Append a 'Kanzu Support Desk' panel to the contact form 7
   		 * panels in the admin side. When editing a cf7 form, there'll
   		 * be an additional 'Kanzu Support Desk' tab
   		 * Applies `wpcf7_editor_panels` filter
   		 * @since 2.3.7
   		 *
   		 * @param  array $panels CF7 panels
   		 * @return array $panels CF7 panels
   		 */
	    public function append_panel_to_wpcf7_admin( $panels ){
	        $panels['ksd-support-form'] = array(
	                                            'title' => __( 'Kanzu Support Desk', 'kanzu-support-desk' ),
	                                            'callback' => array( $this, 'render_contact_form_7_admin_panel' )
	                                            );
	        return $panels;
	    }

	    /**
	     * Callback to render the contact form 7 panel
   		 * @since 2.3.7
   		 *
	     * @param  Object $cf7_form Contact Form 7 form
	     */
	    public function render_contact_form_7_admin_panel( $cf7_form ){
	    	//Get the suggested mail tags
	    	ob_start();
	    	$cf7_form->suggest_mail_tags();
	    	$cf7_suggested_mail_tags = ob_get_clean();

	    	$this->auto_populate_ksd_cf7_fields( $cf7_form->id, $cf7_suggested_mail_tags );
	        ob_start();
	        include_once( KSD_PLUGIN_DIR .  "templates/admin/contact-form7-panel.php" );
	        echo ob_get_clean();
	    }

	    /**
	     * Attempt to autopopulate the KSD CF7 fields if they
	     * are not set. We check for the default CF7 fields
	     * - `[your-name][your-email][your-subject][your-message]` and
	     * we populate based on them.
	     *
	     * @param int $cf7_id The contact form 7 ID
	     * @param  string $cf7_tags List of the configured CF7 tags
	     *
	     */
	    private function auto_populate_ksd_cf7_fields( $cf7_id, $cf7_tags ){

	      $ksd_wpc7_default_fields = array(
	      	'_ksd_wpcf7_name' 		=> '[your-name]',
	      	'_ksd_wpcf7_subject' 	=> '[your-subject]',
	      	'_ksd_wpcf7_email' 		=> '[your-email]',
	      	'_ksd_wpcf7_message' 	=> '[your-message]'
	      );

	      foreach ( $ksd_wpc7_default_fields as $ksd_meta_key => $cf7_default_field ){
	      	 $current_ksd_cf7_field = get_post_meta( $cf7_id, $ksd_meta_key, true );

	      	 if( ! empty( $current_ksd_cf7_field ) ) {
	      	 	continue;
	      	 }

	      	 if( false !== strpos( $cf7_tags, $cf7_default_field ) ){
	      	 	update_post_meta( $cf7_id, $ksd_meta_key, $cf7_default_field );
	      	 }
	      }
	    }

	    /**
	     * In the admin side, after editing fields in the 'Kanzu Support Desk' panel,
	     * save the fields when the user clicks 'Save'
	     * @todo check that the fields exist
	     *
   		 * @since 2.3.7
   		 *
   		 * @param Object The contact form
   		 *
	     */
	    public function save_wpcf7_admin_fields( $contact_form ){

	      $ksd_cf7_enabled = ( isset( $_POST[ '_ksd_wpcf7_enabled' ] ) ? 'yes' : 'no' );
	      update_post_meta( $contact_form->id, '_ksd_wpcf7_enabled',  $ksd_cf7_enabled );

	      $ksd_wpc7_fields = array( '_ksd_wpcf7_name', '_ksd_wpcf7_subject', '_ksd_wpcf7_email', '_ksd_wpcf7_message' );

	      foreach( $ksd_wpc7_fields as $field ){
	      	if( isset( $_POST[ $field ] ) ){
	      		update_post_meta( $contact_form->id, $field,  sanitize_text_field( $_POST[ $field ] ) );
	      	}
	      }

	    }

	    /**
	     * To the displayed CF7 form, add a class `wpcf7-ksd-convert` indicating
	     * that the submission should be converted into a ticket
	     * Implements filter `wpcf7_form_class_attr`
	     *
	     * @param  string $classes Classes to be added to WPCF7 form
	     * @return string $classes Classes to be added to WPCF7 form
	     */
	    public function append_class_to_cf7_form( $classes ){
            if( ! function_exists('wpcf7_get_current_contact_form') ){
                return $classes;
            }
            
	    	$cf7 = wpcf7_get_current_contact_form();
	    	if( $cf7 ){
	    		if( 'yes' ==  get_post_meta( $cf7->id, '_ksd_wpcf7_enabled', true ) ){
	    			$classes.=' wpcf7-ksd-convert';
	    		}
	    	}
	    	return $classes;
	    }

	    /**
	     * If the CF7 form is activated to create tickets, disable sending of emails
	     * after a form is submitted
	     *
	     * @param  boolean $disable_sending_mail Whether to send mail or not after ticket submission
	     * @param  Object $contact_form         The current contact form
	     * @return boolean                       Whether to send mail or not after ticket submission
	     */
	    public function disable_sending_mail( $disable_sending_mail, $contact_form ){
	    	if( 'yes' ==  get_post_meta( $contact_form->id, '_ksd_wpcf7_enabled', true ) ){
	    		$disable_sending_mail = true;
	    	}
	    	return $disable_sending_mail;
	    }

	    /**
	     * When a Contact form is submitted, create a KSD ticket from
	     * the submitted data
	     *
	     * @param  Object $contact_form Contact form
	     */
	    public function create_ticket( $contact_form ){

	      $ksd_wpc7_fields = array(
	      	'_ksd_wpcf7_name' 		=> 'ksd_cust_fullname',
	      	'_ksd_wpcf7_subject' 	=> 'ksd_tkt_subject',
	      	'_ksd_wpcf7_email' 		=> 'ksd_cust_email',
	      	'_ksd_wpcf7_message' 	=> 'ksd_tkt_message'
	      );

	      $new_ksd_ticket = array();

	      foreach( $ksd_wpc7_fields as $wpcf7_field => $ksd_ticket_field ){
	      	$wpcf7_tag 			= get_post_meta( $contact_form->id, $wpcf7_field,  true );
	      	$wpcf7_post_field 	= preg_replace( '/[\[\]]/', '',  $wpcf7_tag );//Remove the []'s wrapping the CF7 field

	      	if( isset( $_POST[ $wpcf7_post_field ] ) ){
	 			$new_ksd_ticket[ $ksd_ticket_field ] = sanitize_text_field(  $_POST[ $wpcf7_post_field ] );
	      	}
	      }

	      //Add the channel
	      $new_ksd_ticket['ksd_tkt_channel'] = 'wpcf7';

           //Log the ticket
          do_action( 'ksd_log_new_ticket', $new_ksd_ticket );

	    }

    }

endif;

return new KSD_WPCF7();

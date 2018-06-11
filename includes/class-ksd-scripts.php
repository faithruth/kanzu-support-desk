<?php

namespace KSD;

/**
 * Enqueue scripts used in both the front and back end
 *
 * @author Kanzu Code
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // End if().

class KSD_Scripts {

    public function enqueue_general_scripts() {
        //For form validation
        wp_enqueue_script( KSD_SLUG . '-validate', KSD_PLUGIN_URL . 'assets/js/jquery.validate.min.js' , array( "jquery"), "1.13.0" );
        $validator_messages = $this->get_validator_localized_messages();
        wp_enqueue_script( KSD_SLUG . '-validate-messages', KSD_PLUGIN_URL . 'assets/js/jquery.validate.messages.js' , array( KSD_SLUG . '-validate' ) );
        wp_localize_script( KSD_SLUG . '-validate-messages', 'ksd_validate_messages', $validator_messages );
    }

        /**
     * Return localized messages used in the jQuery validation plugin
     * @return array Localized validation messages
     */
    private function get_validator_localized_messages(){
        return array(
            'required'      => __( 'This field is required.', 'kanzu-support-desk' ),
            'remote'        => __( 'Please fix this field.', 'kanzu-support-desk' ),
            'email'         => __( 'Please enter a valid email address.', 'kanzu-support-desk' ),
            'url'           => __( 'Please enter a valid URL.', 'kanzu-support-desk' ),
            'date'          => __( 'Please enter a valid date.', 'kanzu-support-desk' ),
            'dateISO'       => __( 'Please enter a valid date (ISO).', 'kanzu-support-desk' ),
            'number'        => __( 'Please enter a valid number.', 'kanzu-support-desk' ),
            'digits'        => __( 'Please enter only digits.', 'kanzu-support-desk' ),
            'equalTo'       => __( 'Please enter the same value again.', 'kanzu-support-desk' ),
            'creditcard'    => __( 'Please enter a valid credit card number.', 'kanzu-support-desk' ),
            'maxlength'     => sprintf( __( 'Please enter no more than %s characters.', 'kanzu-support-desk' ), '{0}' ),
            'minlength'     => sprintf( __( 'Please enter at least %s characters.', 'kanzu-support-desk' ), '{0}' ),
            'rangelength'   => sprintf( __( 'Please enter a value between %1$s and %2$s characters long.', 'kanzu-support-desk' ), '{0}','{1}' ),
            'range'         => sprintf( __( 'Please enter a value between %1$s and %2$s.', 'kanzu-support-desk' ), '{0}','{1}' ),
            'max'           => sprintf( __( 'Please enter a value less than or equal to %s.', 'kanzu-support-desk' ), '{0}' ),
            'min'           => sprintf( __( 'Please enter a value greater than or equal to %s.', 'kanzu-support-desk' ), '{0}' ),
            'step'          => sprintf(  __( 'Please enter a multiple of %s.', 'kanzu-support-desk' ), '{0}' ),
        );
    }

}

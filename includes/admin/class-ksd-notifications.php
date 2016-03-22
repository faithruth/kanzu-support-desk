<?php

/**
 * Notifications displayed throughout KSD's lifetime
 * on the site
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('KSD_Notifications')) :

    class KSD_Notifications {
    
        private $ksd_notifications_option = 'ksd_notifications';

        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @var      object
         */
        protected static $instance = null;

        public function __construct() {
            add_filter( 'ksd_notification_content', array( $this, 'modify_notification_content' ) );
            add_filter( 'ksd_notification_header', array( $this, 'notification_append_close_button' ) );            
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
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }
        
        /**
         * Get a notification to display
         * @return string
         * @since 2.2.0
         */
        public function get_new_notification() {
            if ( ! current_user_can( 'manage_options' ) ) {//If not an admin, end the party
                return;
            }

            //If notifications are disabled
            $ksd_settings = Kanzu_Support_Desk::get_settings();
            if( 'no' == $ksd_settings['notifications_enabled'] ){
                return;
            } 
            $notification_html = $notification_header = '';
            //Wait 5 days before attempting to display a notification
            if (  false === get_transient( '_ksd_notification_transient' ) ){ 
                $expiry = 60 * 60 * 24 * 5;//5 days
                set_transient( '_ksd_notification_transient', '_ksd_notification_transient', $expiry );        
            } else{
                return;
            }
            $notifications = get_option( $this->ksd_notifications_option );
            foreach ( $notifications as $id  => $current_nofification ){
                if( 1 == $current_nofification['displayed'] ){
                    continue;
                }
                //Have the minimum no. of days required before displaying this notification elapsed?
                if ( ! isset( $ksd_settings['ksd_activation_time'] ) ){
                    $ksd_settings['ksd_activation_time'] = date('U' );
                    Kanzu_Support_Desk::update_settings( $ksd_settings );
                }
                //If the plugin hasn't been active long enough, end the party
                $plugin_tenure_days =  ( date('U') - $ksd_settings['ksd_activation_time'] )/60/60/24;
                if ( $plugin_tenure_days < $current_nofification['threshold'] ){
                    continue;
                }
                
                $notification_html  .= '<div id="ksd-notifications" data-notification-id="'.$id.'" class="postbox">';
                $notification_html  .= apply_filters( 'ksd_notification_header',$notification_header );
                $notification_html  .= '<h3 class="hndle">'.$current_nofification['title'].'</h3>';
                $notification_html  .= '<div class="inside">';
                $notification_html  .= apply_filters( 'ksd_notification_content',$current_nofification['content'] );
                $notification_html  .= '</div></div>';  
                //Update the array to indicate that we've displayed this notification
                $notifications[$id]['displayed'] = 1;                
                update_option( $this->ksd_notifications_option, $notifications );
                break;
            }  
            return $notification_html;
        }
        
        
        /**
         * Set the default notifications
         * @since 2.2.0
         */
        public function set_defaults(){ 
            $defaults = $this->get_defaults();
            update_option( $this->ksd_notifications_option, $defaults );
        }
        
        public function modify_notification_content( $notification ){
            if( empty( $notification ) ){
                return;
            }
            $notification = $this->replace_place_holders( $notification );
            $notification.="<div class='ksd-notification-salutation'><span class='ksd-salut-text'>Cheers,<br />Kakoma<br />feedback@kanzucode.com</span><img src='".KSD_PLUGIN_URL."assets/images/logo.png'/></div>";
            $notification.="<div class='ksd-disable-notifications'>If you no longer want to receive any more messages from us, you can disable them <a href='#' class='ksd-notifications-disable'>here.</a></div>";           
            return $notification;
        }
        
        public function notification_append_close_button(){
            return '<div class="ksd-notification-close">
                        <img width="32" height="32" alt="" src="'.KSD_PLUGIN_URL.'assets/images/icons/close.png">             
                    </div>';
        }
        
        /**
         * When a user submits feedback, process it
         */
        public function process_notification_feedback(){
            $notifications      = get_option( $this->ksd_notifications_option );
            $user_response      = sanitize_text_field( $_POST['response'] );
            $notificationID     = sanitize_key( $_POST['notfxn_ID'] );
            $response   = __( 'Thanks for your time. If you ever have any feedback, please get in touch - feedback@kanzucode.com','kanzu-support-desk' );
            
            //Save the user response
            $notifications[ $notificationID ]['user_response'] = $user_response;
            update_option( $this->ksd_notifications_option, $notifications );

            //Said no? End the party. $notificationID is empty for IE
            if ( 'no' == $user_response || 'close' == $user_response || empty( $notificationID ) ){
                return $response;
            }
            $current_user   = wp_get_current_user();   
            //Take action for all the yeses...
            switch( $notificationID ){
                case 3501://quick call                    
                    $site_url           = get_site_url();
                    $quick_call_message = "{$current_user->user_email},{$current_user->user_firstname},{$current_user->user_lastname},{$current_user->display_name},{$site_url}";
                    $response           =  ( wp_mail( "feedback@kanzucode.com", "KSD Feedback - Quick Call",$quick_call_message ) ? __( 'Response sent successfully. We will be in touch shortly. Thank you!', 'kanzu-support-desk' ) : __( 'Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );                    
                    break;
                case 3502://ksd content                    
                    $ksd_content_message = "{$current_user->user_email},{$current_user->user_firstname},{$current_user->user_lastname},{$user_response}";
                    $response            =  ( wp_mail( "feedback@kanzucode.com", "KSD Feedback - Subscription",$ksd_content_message ) ? __( 'Thanks for your feedback! We will be in touch with the most popular content as soon as a substantial number of votes is in. Thank you!', 'kanzu-support-desk' ) : __( 'Error | Vote not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );    
                    break;
                case 3505://One feature...
                    $ksd_content_message = "{$current_user->user_email},{$current_user->user_firstname},{$current_user->user_lastname},{$user_response}";
                    $response            =  ( wp_mail( "feedback@kanzucode.com", "KSD Feedback - Feature Request",$ksd_content_message ) ? __( 'Thanks for your feedback! We will be in touch with the most popular content as soon as a substantial number of votes is in. Thank you!', 'kanzu-support-desk' ) : __( 'Error | Vote not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );                    
                    break;
                case 3506://NPS 
                    $ksd_nps_message    = "{$current_user->user_email},{$current_user->user_firstname},{$current_user->user_lastname},{$user_response}";
                    $response           =  ( wp_mail( "feedback@kanzucode.com", "KSD Feedback - NPS",$ksd_nps_message ) ? __( 'Thanks for your feedback! We look forward to improving KSD based on it.', 'kanzu-support-desk' ) : __( 'Error | Feedback not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );    
                    break;
            }
            return $response;
        }
                
        
        /**
         * Replace all placeholders
         * 
         */
        private function replace_place_holders( $notification ){
            $current_user = wp_get_current_user();            
            
            $placeholders   = array( "{{display_name}}" );
            $replacements   = array( $current_user->display_name );        
 
            return str_replace( $placeholders, $replacements, $notification );
        }
        
        /**
         * The default notifications
         * The items in the array are:
         *  id          - A unique identifier for the notification
         *  title       - The notification title
         *  threshold   - The minimum no. of days after which to show this notification
         *  displayed   - Has this notification been displayed?
         *  content     - The notification message
         * 
         * @TODO Internationalize this
         * @return array
         */
        public function get_defaults() {
            $defaults = array(
                3501 => array(
                            'title' => '[Kanzu Support Desk] Have time for a quick chat?',
                            'threshold' => 5,
                            'displayed' => 0,
                            'content' => "<p>Hi {{display_name}},<br />Kakoma here, lead developer of Kanzu Support Desk - your simple Help Desk plugin. We currently have several features we <strong>THINK</strong> you want. 
                            We, however, want to make sure that we devote our time to building features you <strong>ACTUALLY</strong> want. To do this, we would love to hear from you.</p>
                            <p><span class='ksd-blue'>If you’re willing to give me 10-15 minutes of your time</span>, it would go a long way to ensure that we serve you better.</p>
                            <p>If you’re interested, just click the button below and I’ll send you instructions for setting up our call. Also, <span class='ksd-blue'>I’m happy to take feature requests or offer help with any support issues you’re tackling</span><br />
                            <div class='ksd-buttons'><button type='button' id='ksd-notification-quick-call' class='button button-large button-primary ksd-notification-button ksd-notification-button-default'>I'll improve KSD</button><button type='button' class='button button-large ksd-notification-button ksd-notification-cancel'>Leave me alone!</button></div>
                            </p>",
                            'user_response' => ""
                    ),
                3502 => array(
                            'title' => '[Kanzu Support Desk] What would you like to read?',
                            'threshold' => 10,
                            'displayed' => 0,
                            'content' => "<p>Hi {{display_name}},<br />It's Kakoma again, lead developer of Kanzu Support Desk - your simple Help Desk plugin. So, over the course of development of KSD and building a business out of it, we've learnt a few things that you might find useful.</p>"
                            . "<p>We'd love to hear from you though - <span class='ksd-blue'>what would you like to read about?</span></p>"
                            . "<ul class='ksd-content-topics'>"
                            . "<li><input type='checkbox' name='ksd_content_topics[]' value='wp_plugin_dev' />Developing a premium WordPress plugin</li>"
                            . "<li><input type='checkbox' name='ksd_content_topics[]' value='startup' />Building a WordPress start-up</li>"
                            . "<li><input type='checkbox' name='ksd_content_topics[]' value='customer_care' />Customer Care</li>"                           
                            . "</ul>"
                            . "<div class='ksd-buttons'><button type='button' id='ksd-notification-content-topic' class='button button-large button-primary ksd-notification-button ksd-notification-button-default'>Send me tips</button><button type='button' class='button button-large ksd-notification-button ksd-notification-cancel'>Leave me alone!</button></div>",
                            'user_response' => ""
                    ),     
                3503 => array(
                            'title' => '[Kanzu Support Desk] Enable usage & error statistics',
                            'threshold' => 20,
                            'displayed' => 0,
                            'content' => "<p>Hi {{display_name}},<br />Kakoma here of Kanzu Support Desk - your simple Help Desk plugin. We use <em>Usage & Error statistics</em> to get the KSD’s performance, usage and customization data. These allow us make it more useful, stable and above all, more secure.</p>"
                            . "<p>Could you enable this feature by clicking the button below? KSD will be tonnes better because of you.</p>"
                            . "<div class='ksd-buttons'><button type='button' id='ksd-notification-enable-usage' class='button button-large button-primary ksd-notification-button ksd-notification-button-default'>I'll improve KSD</button><button type='button' class='button button-large ksd-notification-button ksd-notification-cancel'>Leave me alone!</button></div>",
                            'user_response' => ""
                        ),               
                3504 => array(
                            'title' => '[Kanzu Support Desk] WordPress rating',
                            'threshold' => 40,
                            'displayed' => 0,
                            'content' => "<p>Hi {{display_name}},<br />Most WordPress users evaluate a plugin based on its rating in the repository. Would you mind giving us a rating? It'll go a long way in making us more discoverable by other users. </p>"
                            . "<div class='ksd-buttons'><a href='https://wordpress.org/support/view/plugin-reviews/kanzu-support-desk?filter=5#postform' target='_blank' class='ksd-notification-review button button-large button-primary ksd-notification-button ksd-notification-button-default'>I'll improve KSD</a><button type='button' class='button button-large ksd-notification-button ksd-notification-cancel'>Leave me alone!</button></div>",
                            'user_response' => ""
                    ), 
                3505 => array(
                            'title' => '[Kanzu Support Desk] That one feature...',
                            'threshold' => 50,
                            'displayed' => 0,
                            'content' => "<p>Hi {{display_name}},<br />Is there any particular Help Desk feature that's been on your mind lately? Yeah? No? Let us know below."
                            . "<textarea class='ksd-notifications-one-feature' rows='4'> </textarea>"
                            . "<div class='ksd-buttons'><button type='button' id='ksd-notification-one-feature' class='button button-large button-primary ksd-notification-button ksd-notification-button-default'>Send Feature Request</button><button type='button' class='button button-large ksd-notification-button ksd-notification-cancel'>Leave me alone!</button></div>",
                            'user_response' => ""
                ),
                3506 => array(
                            'title' => '[Kanzu Support Desk] Would you recommend us?',
                            'threshold' => 90,
                            'displayed' => 0,
                            'content' => "<p>Hi {{display_name}},<br />To get a better idea of whether we are serving you well, we'd like to know, <strong>How likely is it that you would recommend KSD to a friend or colleague?</strong></p>"
                            . "<div class='ksd-notifications-nps'><div class='ksd-nps-labels'>"
                            . "<span class='ksd-nps-not-likely'>Not at all likely</span><span class='ksd-nps-ext-likely'>Extremely likely</span></div>"
                            . "<ul class='ksd-nps-score'><li>0</li><li>1</li><li>2</li><li>3</li><li>4</li><li>5</li><li>6</li><li>7</li><li>8</li><li>9</li><li>10</li></ul>"
                            . "<div class='ksd-notification-nps-error'></div>"
                            . "<div class='ksd-buttons'><button type='button' id='ksd-notification-nps' class='button button-large button-primary ksd-notification-button ksd-notification-button-default'>Rate KSD</button><button type='button' class='button button-large ksd-notification-button ksd-notification-cancel'>Leave me alone!</button></div>"     
                            . "</div>",
                            'user_response' => ""
                    )                  
            );

            return $defaults;
        }

    }

    
endif;
 

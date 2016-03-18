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
            add_filter( 'ksd_notification_content', array( $this, modify_notification_content ) );
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
         * @since 2.1.3
         */
        public function get_new_notification() {
            //If displaying settings is disabled
            if ( ! current_user_can( 'manage_options' ) ) {//If not an admin, end the party
                return;
            }
            $notification_html = '';
            //@TODO If time not elapsec (transient present), do nothing
            $notifications = get_option( $this->ksd_notifications_option );
            foreach ( $notifications as $current_notification ){
                if( $current_notification['displayed'] ){
                    continue;
                }
                //If threshold has elapsed
                
                $notification_html  .= '<div id="ksd-feedback" class="postbox ksd-notification-id-'.$current_notification['id'].'"><h3 class="hndle ui-sortable-handle">';
                $notification_html  .= $current_notification['title'].'</h3>';
                $notification_html  .= '<div class="inside">';
                $notification_html  .= $current_notification['message'];
                $notification_html  .= '</div></div>';    
                break;
            }
            
            return apply_filters( 'ksd_notification_content', $notification_html );
        }
        
        /**
         * Set the default notifications
         * @since 2.1.3
         */
        public function set_defaults(){ 
            $defaults = $this->get_defaults();
            update_option( $this->ksd_notifications_option, $defaults );
        }
        
        private function modify_notification_content( $notification ){
            if( empty( $notification ) ){
                return;
            }
            $notification = $this->replace_place_holders( $notification );
            $notification.="<span class='ksd-notification-salutation'>Kakoma<br />feedback@kanzucode.com</span>";
            $notification.="<div class='ksd-disable-notifications'>If you no longer want to receive any more messages from us, you can disable them <a href='#' class='ksd-disable'>here.</a></div>";           
            return $notification;
        }
        
        /**
         * Use a filter for this
         */
        private function replace_place_holders( $notification ){
            
        }
        
        /**
         * The default notifications
         * The items in the array are:
         *  id          - A unique identifier for the notification
         *  title       - The notification title
         *  threshold   - The minimum no. of days after which to show this notification
         *  displayed   - Has this notification been displayed?
         *  message     - The notification message
         * 
         * @TODO Internationalize this
         * @return array
         */
        public function get_defaults() {
            $defaults = array(
                array(
                    'id' => 0501,
                    'title' => '[Kanzu Support Desk] Have time for a quick chat?',
                    'threshold' => 5,
                    'displayed' => false,
                    'message' => "<p>Hi {{display_name}}. Kakoma here, lead developer of Kanzu Support Desk - your simple Help Desk plugin. You good? So, as we build the plugin, we have several features we <strong>THINK</strong> you want. 
                    We, however, are very sold on trying to make sure that we devote our time to creating features you <strong>ACTUALLY</strong> want. To do this, we would love to hear from you directly.</p>
                    <p>If you’re willing to give me 10-15 minutes of your time, it would mean a lot to me. You'll get to be a big part of helping us make KSD the best plugin it can possibly be.</p>
                    <p>If you’re interested, just click the button below and I’ll send you instructions for setting up our call. Also, I’m happy to offer help with any support issues you’re tackling<br />
                    <span class='ksd-buttons'><a href='#' class='ksd-notification-button ksd-notification-button-default'>Let's Improve KSD</a><a href='#' class='ksd-notification-button'>Leave me alone!</a></span>
                    </p>",
                    'user_response' => ""
                ),
                array(
                    'id' => 0502,
                    'title' => '[Kanzu Support Desk] What would you like to read?',
                    'threshold' => 10,
                    'displayed' => false,
                    'message' => "<p>Hi {{display_name}}. It's Kakoma again, lead developer of Kanzu Support Desk - your simple Help Desk plugin. You good? So, over the course of development of KSD and building a business out of it, we've learnt a few things that you might find useful.</p>"
                    . "<p>We'd love to hear from you though. What would you like to read about?</p>"
                    . "<span><select>"
                    . "<option>WordPress plugin development</option>"
                    . "<option>WordPress plugin business</option>"
                    . "</select></span>"
                    . "<input class='ksd-notifications-other' type='text'/>",
                    'user_response' => ""
                ),     
                array(
                    'id' => 0503,
                    'title' => '[Kanzu Support Desk] Enable usage & error statistics',
                    'threshold' => 20,
                    'displayed' => false,
                    'message' => "<p>Hi {{display_name}}. It's Kakoma again, lead developer of Kanzu Support Desk - your simple Help Desk plugin. You good? So, we use <em>Usage & Error statistics</em> to get the KSD’s performance, usage and customization data. These allow us make it more useful, stable and above all, more secure.</p>"
                    . "<p>Could you enable this feature by clicking the button below? KSD will be tonnes better because of you."
                    . "<span class='ksd-buttons'><a href='#' class='ksd-notification-button ksd-notification-button-default'>Let's Improve KSD</a><a href='#' class='ksd-notification-button'>Leave me alone!</a></span></p>",
                    'user_response' => ""
                ),                
                array(
                    'id' => 0503,
                    'title' => '[Kanzu Support Desk] WordPress rating',
                    'threshold' => 40,
                    'displayed' => false,
                    'message' => "<p>Hi {{display_name}}.You good? So, most WordPress users evaluate a plugin based on its rating in the repository. Would you mind giving us a rating? It'll go a long way in making us more discoverable by other users. </p>"
                    . "<span class='ksd-buttons'><a href='#' class='ksd-notification-button ksd-notification-button-default'>Leave a rating</a><a href='#' class='ksd-notification-button'>Leave me alone!</a></span>",
                    'user_response' => ""
                ),
                array(
                    'id' => 0504,
                    'title' => '[Kanzu Support Desk] That one feature...',
                    'threshold' => 50,
                    'displayed' => false,
                    'message' => "<p>Hi {{display_name}}. How's the going? So, is there any particular KSD feature that's been on your mind lately? Yeah? No? Let us know below."
                    . "<input class='ksd-notifications-other' type='textarea'/>"
                    . "<span class='ksd-buttons'><a href='#' class='ksd-notification-button ksd-notification-button-default'>Send</a><a href='#' class='ksd-notification-button'>Na,I'm good</a></span></p>",
                    'user_response' => ""
                ),
                array(
                    'id' => 0505,
                    'title' => '[Kanzu Support Desk] Would you recommend us?',
                    'threshold' => 90,
                    'displayed' => false,
                    'message' => "<p>Hi {{display_name}}. You good? So, to get a better idea of whether we are serving you well, we'd like to know, <strong>How likely is it that you would recommend KSD to a friend or colleague?</p>"
                    . "<div class='ksd-notifications-nps'><div class='ksd-nps-labels'>"
                    . "<span class='ksd-nps-not-likely'>Not at all likely</span><span class='ksd-nps-ext-likely'>Extremely likely</span></div>"
                    . "<ul class='ksd-nps-score'><li>1</li><li>2</li><li>3</li><li>4</li><li>5</li><li>6</li><li>7</li><li>8</li><li>9</li><li>10</li></ul></div>",
                    'user_response' => ""
                )                  
            );

            return $defaults;
        }

    }

    
endif;
 

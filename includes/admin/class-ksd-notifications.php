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

        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @var      object
         */
        protected static $instance = null;

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

        public function get_new_notification() {
            $notification_html = '';
            //@TODO If time not elapsec (transient present), do nothing
            $notifications = $this->default_notifications();
            $current_notification = $notifications[0];
            $notification_html  .= '<div id="ksd-feedback" class="postbox"><h3 class="hndle ui-sortable-handle">';
            $notification_html  .= $current_notification['title'].'</h3>';
            $notification_html  .= '<div class="inside">';
            $notification_html  .= $current_notification['message'];
            $notification_html  .= '</div></div>';
            
            return $notification_html;
        }
        
        /**
         * The default notifications
         * @return array
         */
        private function default_notifications() {
            $defaults = array(
                array(
                    'id' => 501,
                    'title' => '[Kanzu Support Desk] Have time for a quick chat?',
                    'threshold' => 1,
                    'displayed' => false,
                    'message' => "<p>Hi {name}. Kakoma, lead developer of Kanzu Support Desk - your Help Desk plugin, here. As we build the plugin, we have several features we THINK you want. 
                    We, however, are very sold on trying to make sure that we devote our time to creating features you ACTUALLY want. To do this, we would love to hear from you directly.</p>
                    <p>If you’re willing to give me 10-15 minutes of your time, it would mean a lot to me. You'll get to be a big part of helping us make KSD the best plugin it can possibly be.</p>
                    <p>If you’re interested, just reply to this message below, and I’ll send you instructions for setting up our call. Also, I’m happy to offer help with any support issues you’re tackling<br /><a href='#'>Let's Improve KSD</a><a href='#'>Na bro</a>Kakoma</p>",
                    'user_response' => ""
                ),
                array(
                    'id' => 502,
                    'title' => 'Usage and error ',
                    'threshold' => 2,
                    'displayed' => false,
                    'message' => 'Me again bro',
                    'user_response' => ""
                ),
                array(
                    'id' => 503,
                    'title' => 'Rate us in the WP repo',
                    'threshold' => 2,
                    'displayed' => false,
                    'message' => 'Me again bro',
                    'user_response' => ""
                ),
                array(
                    'id' => 504,
                    'title' => 'That one feature...',
                    'threshold' => 2,
                    'displayed' => false,
                    'message' => 'Me again bro',
                    'user_response' => ""
                ),
                array(
                    'id' => 505,
                    'title' => 'NPS survey',
                    'threshold' => 2,
                    'displayed' => false,
                    'message' => 'Me again bro',
                    'user_response' => ""
                )                  
            );

            return $defaults;
        }

    }

    
endif;
 

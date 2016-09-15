<?php
/**
 * Walks a new user through the plugin
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @since 2.2.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Onboarding' ) ) :

class KSD_Onboarding {
    
        /**
	 * Instance of this class.
	 *
	 * @since    1.7.0
	 *
	 * @var      object
	 */
	protected static $instance = null;   
        
        /**
         * Option key used to store the current onboarding stage
         * @var string
         */
        private $ksd_current_stage_option_key = 'ksd_onboarding_current_stage';    
        
        
        /**
         * Onboarding current stage
         * @var array
         */
        private $ksd_current_stage_key;         
        
        /**
         * Onboarding stage details
         * @var array
         */
        private $ksd_stage_details = array();          
        
        

        /**
	 * Return an instance of this class.
	 *
	 * @since     1.7.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}    
    
        public function __construct(){
            //Displays the onboarding progress navigation. Used for the front-end
            add_action( 'ksd_show_onboarding_progress', array( $this, 'show_onboarding_progress' ) );   
            
            add_action( 'admin_notices', array( $this, 'show_onboarding_progress' ) );
            
            //Jubilate when we are done
            add_action( 'ksd_onboarding_complete', array( $this, 'onboarding_complete' ) );
        }
        
        
        /**
         * Show the onboarding progress
         * @param int $this->ksd_current_stage_key String value of the stage to show. e.g. two
         * @since 2.2.0
         */
        public function show_onboarding_progress(){
            $this->ksd_settings = Kanzu_Support_Desk::get_settings();
            if ( 'no' === $this->ksd_settings['onboarding_enabled'] ){ 
                return;
            } 

            if( ! isset( $_GET[ 'ksd-onboarding' ] ) && ! get_option( $this->ksd_current_stage_option_key ) ){
                return;
            }
            
            if( isset( $_GET[ 'ksd-onboarding' ]  ) ){
                $this->ksd_current_stage_key    = sanitize_key( $_GET[ 'ksd-onboarding' ] );
            }else{
                $this->ksd_current_stage_key    = get_option( $this->ksd_current_stage_option_key );
            }
            
            $this->ksd_stage_details    = $this->get_stage_details();
            $is_last_stage              = $this->is_the_last_stage();
             
            echo $this->generate_onboarding_html( $is_last_stage );
            
            $this->save_current_stage();
            
            if ( $is_last_stage ){//If we are at the last stage, let's end the party
                do_action( 'ksd_onboarding_complete' );   
            }
        }
        
                        
        
        public function onboarding_complete(){
            //Turn off the onboarding setting
            $settings = Kanzu_Support_Desk::get_settings();
            $settings['onboarding_enabled'] = 'no';
            Kanzu_Support_Desk::update_settings( $settings );
        }
        
        /**
         * The information on the various stages. 
         * For instances where 'stage_notes' is an array, that stage has multiple
         * levels; in that case, the stage_notes values would have to be exhausted before
         * proceeding to the next stage
         * @TODO 2.2.0 Clean up the internationalization
         * @return Array
         */
        private function get_stage_details(){
            return array(
                'start-tour'   => array(
                    'title'         => __( 'Start tour', 'kanzu-support-desk' ),
                    'next_url'      =>  get_permalink( $this->ksd_settings['page_submit_ticket'] ),
                    'stage_notes'   => "Ready? Let's go! Click next to proceed..."
                ),
               'create-ticket'   => array(
                    'title'         => __( 'Create ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url('edit.php?post_type=ksd_ticket'),
                    'stage_notes'   => sprintf( '<p>%1$s </p><p><strong>%2$s</strong></p>',
                                                __( 'This is the support form the customer will use to create a ticket.','kanzu-support-desk'),
                                                __( "Create a ticket now by entering a subject, a message and clicking 'Send Message'. Then, click 'Next' to proceed", 'kanzu-support-desk') 
                                                )
                ),            
                'assign-ticket'   => array(
                    'title'         => __( 'Assign ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => array(
                                        sprintf( '<p>%1$s <strong>%2$s</strong></p>',
                                            __('You and your agents view all tickets here. Your customer\'s waiting; Go ahead and','kanzu-support-desk'),
                                            __( 'select the ticket you\'d like to make changes to', 'kanzu-support-desk') 
                                                ),
                                        sprintf( '<p>%1$s</p><p>%2$s</p><p><strong>%3$s</strong></p>',
                                            __('In this view, you or an agent can make changes to a ticket.','kanzu-support-desk'),
                                            __( 'From the ticket information box to the right, the ticket can be assigned to an agent, the status can be changed and the severity set appropriately.', 'kanzu-support-desk'),
                                            __('Assign the ticket to someone else and click Update to proceed','kanzu-support-desk')
                                                )    
                                        )
                    ),  
                'reply-ticket'   => array(
                    'title'         => __( 'Reply ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => sprintf( '<p>%1$s</p><p><strong>%2$s</strong></p>',
                                            __('Great! Now you need to respond to the customer. You can send a response or choose to type a private note for another agent; private notes are NOT sent to your customer.','kanzu-support-desk'),
                                            __( 'At the bottom of your screen is a textbox; type your response and click Send to proceed.', 'kanzu-support-desk')
                                              )
                ),                
                'resolve-ticket'   => array(
                    'title'         => __( 'Resolve ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => sprintf( '<p>%1$s</p><p><strong>%2$s</strong></p>',
                                        __('You are on a roll! One last thing...time to resolve the ticket since you have done such a fabulous job serving your customer.','kanzu-support-desk'),
                                        __( 'In the ticket information box, change ticket status from Open to Resolved and click Update to complete', 'kanzu-support-desk')
                                                ) 
                ),               
                'end-tour'   => array(
                    'title'         => __( 'Ready!', 'kanzu-support-desk' ) ,
                    'next_url'      => admin_url( 'edit.php?post_type=ksd_ticket' ),
                    'stage_notes'   => sprintf( '<p>%1$s<strong>%2$s<a target="blank" href="http://kanzucode.com/contact">kanzucode.com</a></strong></p>',
                                        __('That\'s it! Now that\'s how you handle customer care like a rockstar. To see full documentation & share feedback/feature requests, ','kanzu-support-desk'),
                                        __( 'get in touch at ', 'kanzu-support-desk')
                                        )
                )                
            );
        }
        
        /**
         * Details to return when an invalid stage is specified
         * @return Array
         */
        private function get_invalid_stage_details(){
            return array(
                'title'         =>  __("404 | Stage does not exist", "kanzu-support-desk" ),
               'stage_notes'    =>  __( "You have uncovered an onboarding stage that doesn't exist! Please click 'Next' to restart the tour", "kanzu-support-desk" ) 
            );
        }      
        
        /**
         * Generate the HTML to display
         * @param boolean $is_last_stage Whether this is the last stage
         * @return string HTML to display
         */
        private function generate_onboarding_html( $is_last_stage ){
            $this->ksd_stage_details     = $this->get_stage_details();
            
            if ( isset( $this->ksd_stage_details[$this->ksd_current_stage_key] ) ){
                $current_item   = $this->ksd_stage_details[$this->ksd_current_stage_key];
                if( $is_last_stage ){
                    $next_url_html = $this->get_last_stage_url();
                }else{
                    $next_item_key      = $this->get_next_onboarding_stage_key();  
                    $next_url_html      =  '<a href="' .add_query_arg( 'ksd-onboarding', $current_item['next_url'] , $next_item_key ). '" class="button-large button button-primary ksd-onboarding-next">'.__( 'Next', 'kanzu-support-desk' ).'</a>';
                }                
            }else{
                $current_item  = $this->get_invalid_stage_details();
                $next_url_html = $this->get_first_stage_url();
            }
            
            $onboarding_div             = '<div class="ksd-onboarding-progress">';
            $onboarding_div            .= '<ol class="ksd-onboarding-stages">';
            $notes                      = $current_item['stage_notes'];
            $mark_stages_incomplete     = false;
            foreach ( $this->ksd_stage_details as $stage_key => $stage_details ){
                if( $stage_key == $this->ksd_current_stage_key  ){
                    $stage_class            = 'class="active"';
                    $mark_stages_incomplete = true;
                }                
                elseif( $mark_stages_incomplete ){                    
                    $stage_class = '';
                }       
                else{
                    $stage_class = 'class="done"';
                }
                $onboarding_div .= "<li {$stage_class}>{$stage_details['title']}</li>";
            }
            $onboarding_div .= '</ol>';
            $onboarding_div .= '<div class="ksd-onboarding-notes">' . $notes . '<div class="ksd-onboarding-url">'.$next_url_html.'</div></div>';
            $onboarding_div .= '</div>';
            return $onboarding_div;
        }
        
      
        /**
         * Get the URL to show at the last stage
         * @return string URL to the last stage
         */
        private function get_last_stage_url(){
            return '<a href="' . admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-settings' ). '" class="button-large button button-primary ksd-onboarding-next">'.__( 'Customize your KSD', 'kanzu-support-desk' ).'</a>';
        }
        
        /**
         * Get the URL to show at the first stage. Used whenever an error
         * occurs to 'Reset' the process and go back to the first stage
         * 
         * @return string URL to the first stage
         */
        private function get_first_stage_url(){
            return '<a href="' .esc_url( add_query_arg( 'ksd-onboarding', admin_url('edit.php?post_type=ksd_ticket') , 'create-ticket' ) ). '" class="button-large button button-primary ksd-onboarding-next">'.__( 'Next', 'kanzu-support-desk' ).'</a>';
        }
 
 
        /**
         * Iterate through all the stages and return the key
         * for the stage AFTER the current stage
         * 
         * @param string $this->ksd_current_stage_key The key of the current stage
         * @return string The next stage's key or null if $this->ksd_current_stage_key doesn't exist or is the last stage
         */
        private function get_next_onboarding_stage_key() {
            $this->ksd_stage_details     = $this->get_stage_details();
            $current_key    = key( $this->ksd_stage_details );
            while ( $current_key !== null && $current_key != $this->ksd_current_stage_key ) {//Move to the next item till we have the current stage
                next( $this->ksd_stage_details );
                $current_key = key( $this->ksd_stage_details );
            }
            next( $this->ksd_stage_details );//Move to stage AFTER the current stage
            return key( $this->ksd_stage_details );//Get its key
        }

 

        /**
         * Check if this is the last onboarding stage
         * @param String $this->ksd_current_stage_key
         */
        private function is_the_last_stage(){
            $is_last_stage = false;
            end( $this->ksd_stage_details );
            $last_key = key( $this->ksd_stage_details );
            if( $this->ksd_current_stage_key == $last_key ){
                $is_last_stage = true;
            }
            reset( $this->ksd_stage_details );
            return $is_last_stage;
        }
        
        /**
         * Save the current onboarding stage
         * @param int $current_stage
         */
        private function save_current_stage(){
            update_option( $this->ksd_current_stage_option_key, $this->ksd_current_stage_key );
        }        

        
        /**
         * Mark a stage as complete. We do this by saving the next stage as the current stage
         */
        public function mark_stage_complete( $completed_stage ){
           $this->ksd_current_stage_key = $completed_stage;           
           $this->ksd_current_stage_key = $this->get_next_onboarding_stage_key();
           $this->save_current_stage();
        }
                              
 
}
endif;

return new KSD_Onboarding();

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
         * Onboarding stage options. Used to track progress
         * @var array
         */
        private $ksd_current_stage_details = array();        
        

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
         * @param int $current_stage String value of the stage to show. e.g. two
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
            
            $this->ksd_current_stage_details = get_option( $this->ksd_current_stage_option_key );
            if( isset( $_GET[ 'ksd-onboarding' ] ) ){
                $current_stage = sanitize_key( $_GET[ 'ksd-onboarding' ] );
                $this->ksd_current_stage_details['previous_stage']              = $current_stage;
                $this->ksd_current_stage_details['is_previous_stage_complete']  = "no";
            }   
            else{
                $current_stage  = $this->ksd_current_stage_details['previous_stage'];
                
                if( "yes" == $this->ksd_current_stage_details['is_previous_stage_complete'] ){
                    ++$current_stage;
                    $this->ksd_current_stage_details['previous_stage'] = $current_stage;
                }

                
            }   
            echo $this->generate_onboarding_html( $current_stage );
            $this->save_current_stage();
            if ( $current_stage == count( $this->get_stage_details() ) ){//If we are on the last stage, let's end the party
                do_action( 'ksd_onboarding_complete' );
            }
        }
        
                        
        
        public function onboarding_complete(){
            //Turn off the onboarding setting
            $settings = Kanzu_Support_Desk::get_settings();
            $settings['onboarding_enabled'] = 'no';
            Kanzu_Support_Desk::update_settings( $settings );
            
            //Remove the stage options
            delete_option( $this->ksd_current_stage_option_key );
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
                1   => array(
                    'title'         => __( 'Start tour', 'kanzu-support-desk' ),
                    'next_url'      =>  get_permalink( $this->ksd_settings['page_submit_ticket'] ),
                    'stage_notes'   => "Ready? Let's go! Click next to proceed..."
                ),
                2   => array(
                    'title'         => __( 'Create ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url('edit.php?post_type=ksd_ticket'),
                    'stage_notes'   => '<p>This is the support form the customer will use to create a ticket.</p><p><strong>Create a ticket now then click Next to proceed</strong></p>'    
                ),            
                3   => array(
                    'title'         => __( 'Assign ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => array(
                                        __( '<p>You and your agents view all tickets here. Your customer\'s waiting; Go ahead and
                                            <strong>select the ticket you\'d like to make changes to</strong></p>' ),
                                        __( '<p>In this view, you or an agent can make changes to a ticket.</p>
                                            <p>From the ticket information box to the right, the ticket can be 
                                            assigned to an agent, the status can be changed and the severity set appropriately.</p>
                                            <p><strong>Assign the ticket to someone else and click Update to proceed</strong></p>' )    
                                        )
                    ),  
                4   => array(
                    'title'         => __( 'Reply ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => __( '<p>Great! Now you need to respond to the customer. You can send a response or choose to type a private note for another agent; private notes are NOT sent to your customer.</p>'
                            . '             <p><strong>At the bottom of your screen is a textbox; type your response and hit send to proceed.</strong></p> ' )
                ),                
                5   => array(
                    'title'         => __( 'Resolve ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   =>__( '<p>You are on a roll! One last thing...time to resolve the ticket since you have done such a fabulous job serving your customer.</p>'
                            . '            <p><strong>In the ticket information box, change ticket status from Open to Resolved and click Update to complete</strong></p>' )   
                ),               
                6   => array(
                    'title'         => __( 'Ready!', 'kanzu-support-desk' ) ,
                    'next_url'      => admin_url( 'edit.php?post_type=ksd_ticket' ),
                    'stage_notes'   => __( '<p>That\'s it! Now that\'s how you handle customer care like a rockstar. To see full documentation & share feedback/feature requests, '
                            . '         <strong>get in touch at <a target="blank" href="http://kanzucode.com/contact">kanzucode.com</a></strong></p> ' )
                )                
            );
        }

        private function generate_onboarding_html( $current_stage ){
            $the_stages     = $this->get_stage_details();
            $next_url_html  = $this->get_stage_url($the_stages, $current_stage);
            
            $onboarding_div = '<div class="ksd-onboarding-progress">';
            $onboarding_div .= '<ol class="ksd-onboarding-stages">';
            $notes          = $this->get_stage_notes( $the_stages, $current_stage );
            for ( $i=1; $i <= count( $the_stages ); $i++ ){
                if( $i == $current_stage  ){
                    $stage_class = 'class="active"';
                }                
                elseif( $i < $current_stage ){
                    $stage_class = 'class="done"';
                }       
                else{
                    $stage_class = '';
                }
                $onboarding_div .= "<li {$stage_class}>{$the_stages[$i]['title']}</li>";
            }
            $onboarding_div .= '</ol>';
            $onboarding_div .= '<div class="ksd-onboarding-notes">' . $notes . '<div class="ksd-onboarding-url">'.$next_url_html.'</div></div>';
            $onboarding_div .= '</div>';
            return $onboarding_div;
        }
        

        private function get_stage_url( $the_stages, $current_stage ){
            $next_url_html = '';
            
            if ( ! empty ( $the_stages[$current_stage]['next_url'] ) ){
                $next_stage     = $current_stage + 1;//Increment the current stage and add the next as a query argument to the next url
                $next_url       = esc_url( add_query_arg( 'ksd-onboarding', $next_stage , $the_stages[$current_stage]['next_url'] ) );
                $next_url_html  = '<a href="' . $next_url. '" class="button-large button button-primary ksd-onboarding-next">'.__( 'Next', 'kanzu-support-desk' ).'</a>';
            }      
            //The last URL @TODO 2.2.0 On first access of settings, display link to 'quick-start guide' on our site
            if ( $current_stage == count( $the_stages ) ){
                 $next_url_html  = '<a href="' . admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-settings' ). '" class="button-large button button-primary ksd-onboarding-next">'.__( 'Customize your KSD', 'kanzu-support-desk' ).'</a>';
            }
            return $next_url_html;
        }        
        /**
         * Get the notes for the current stage or sub-stage.
         * Sub-stages are intermediary steps to completing a stage
         * @param Array $the_stages
         * @param int $current_stage  
         * @return string
         */
        private function get_stage_notes( $the_stages, $current_stage ){
            $notes = '';
            if ( is_array( $the_stages[$current_stage]['stage_notes'] ) ){                    
                if (   ! isset( $this->ksd_current_stage_details['previous_sub_stage'] ) ){//Sub-stage doesn't exist
                    $current_sub_stage = 0;                 
                }
                else{
                    $current_sub_stage = ++$this->ksd_current_stage_details['previous_sub_stage'];
                }
                if( isset( $the_stages[$current_stage]['stage_notes'][$current_sub_stage] ) ){
                    $this->ksd_current_stage_details['previous_sub_stage'] = $current_sub_stage;
                    $notes = $the_stages[$current_stage]['stage_notes'][$current_sub_stage];
                }
                else{
                    $notes = $the_stages[$current_stage]['stage_notes'][0];//@TODO Test extensively. This should never happen
                }
                //Important: The stage is only complete when we get to the last sub-stage
                if( $current_sub_stage == ( count( $the_stages[$current_stage]['stage_notes'] ) - 1 ) ){
                    $this->ksd_current_stage_details['is_previous_stage_complete'] = "yes";
                }
            }
            else{
                $notes = $the_stages[$current_stage]['stage_notes'];
                $this->ksd_current_stage_details['is_previous_stage_complete'] = "yes";
            }      
            return $notes;
        }
        
        /**
         * Save the current onboarding stage
         * @param int $current_stage
         */
        private function save_current_stage(){
            update_option( $this->ksd_current_stage_option_key, $this->ksd_current_stage_details );
        }
        
        /**
         * Mark a stage as complete
         */
        public function mark_stage_complete( $completed_stage ){
            $this->ksd_current_stage_details = get_option( $this->ksd_current_stage_option_key );
            $this->ksd_current_stage_details['previous_stage'] = $completed_stage;
            $this->ksd_current_stage_details['is_previous_stage_complete'] = "yes";
            $this->save_current_stage();
        }
                              
 
}
endif;

return new KSD_Onboarding();

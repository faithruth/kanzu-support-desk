<?php
/**
 * Walks a new user through the plugin
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @since 2.1.3
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
         * @since 2.1.3
         */
        public function show_onboarding_progress(){
            $this->ksd_settings = Kanzu_Support_Desk::get_settings();
          //  if ( 'no' === $this->ksd_settings['onboarding_enabled'] ){
         //       return;
         //   } 

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
            if ( $current_stage == ( count( $this->get_stage_details() ) - 1 ) ){//If we are on the last stage, let's end the party
                do_action( 'ksd_onboarding_complete' );
            }
        }
        
                        
        
        public function onboarding_complete(){
            Kanzu_Support_Desk::kanzu_support_log_me("complete");
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
         * @return Array
         */
        private function get_stage_details(){
            return array(
                1   => array(
                    'title'         => __( 'Start tour', 'kanzu-support-desk' ),
                    'next_url'      =>  get_permalink( $this->ksd_settings['page_submit_ticket'] ),
                    'stage_notes'   => ''
                ),
                2   => array(
                    'title'         => __( 'Create ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url('edit.php?post_type=ksd_ticket'),
                    'stage_notes'   => '<p>This is the support form the customer will use to create a ticket.</p><p><strong>Create a ticket now to proceed</strong></p>'    
                ),            
                3   => array(
                    'title'         => __( 'Assign ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => array(
                                        __( '<p>You and your agents view all tickets here. Your customer\'s waiting; Go ahead and
                                            <strong>Select the ticket you\'d like to make changes to</strong></p>' ),
                                        __( '<p>In this view, you or an agent can make changes to a ticket.</p>
                                            <p>From the ticket information box to the right, the ticket can be 
                                            assigned to an agent, the status can be changed and the severity set appropriately.</p>
                                            <p><strong>Assign the ticket to someone else to proceed</strong></p>' )    
                                        )
                    ),  
                4   => array(
                    'title'         => __( 'Reply ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => __( '<p>Great! Now you need to respond to the customer. You can send a response or choose to type a private note for another agent; private notes are NOT send to your customer.</p>'
                            . '             <p><strong>At the bottom of your screen is a textbox; type your response and hit send to proceed.</strong></p> ' )
                ),                
                5   => array(
                    'title'         => __( 'Resolve ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   =>__( '<p>You are on a roll! One last thing...time to resolve the ticket since you have done such a fabulous job serving your customer.</p>'
                            . '            <p><strong>In the ticket information box, update ticket status to Resolved and click Update to complete</strong></p>' )   
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
            $next_url_html  = '';
            //Increment the current stage and add the next as a query argument to the next url
            if ( ! empty ( $the_stages[$current_stage]['next_url'] ) ){
                $next_stage     = $current_stage + 1;
                $next_url       = esc_url( add_query_arg( 'ksd-onboarding', $next_stage , $the_stages[$current_stage]['next_url'] ) );
                $next_url_html  = '<a href="' . $next_url. '" class="button-small button button-primary">'.__( 'Next', 'kanzu-support-desk' ).'</a>';
            }
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
            $onboarding_div .= $next_url_html;
            $onboarding_div .= '<div class="ksd-onboarding-notes">' . $notes . '</div>';
            $onboarding_div .= '</div>';
            return $onboarding_div;
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
         * Show progress of onboarding progress
         */
        public function get_stage_info() {
            
            $settings = Kanzu_Support_Desk::get_settings();
            if ( 'no' === $settings['onboarding_enabled'] ) return;
            
            $referer     = $_SERVER['HTTP_REFERER'];
            $request_uri =  $_SERVER['REQUEST_URI'];
            $notes = ''; 

            $stage_3plus = 0;
            if ( strpos( $referer, '/submit-ticket/' ) > 0 && 
                    strpos($request_uri, 'edit.php?post_type=ksd_ticket&ksd-onboarding=3' )
                ) {
                $_GET['post_type']      = 'ksd_ticket';
                $_GET['ksd-onboarding'] = '3'; 
                $notes = __( 'Select ticket to respond to. ' );
                
            }
            
            if ( strpos( $referer, 'edit.php?post_type=ksd_ticket' ) > 0 
                    && strpos($request_uri, 'post.php?post=' ) > 0 ) {
                $_GET['post_type']      = 'ksd_ticket';
                $_GET['ksd-onboarding'] = '3'; 
                $stage_3plus = 1;
                $notes = __( 'In this view you can reply a ticket. Use the <b>Send</b> button to post the reply.  <br /><br />
                    Through the ticket information box to the right, the ticket can be 
                assigned to an agent, the status can be changed, and  the severity set appropriately. ' )   
                ;
            }
            
            if ( strpos( $referer, 'post.php?post=' ) > 0 
                    && strpos( $request_uri, 'post.php?post=' ) ) {
                $_GET['post_type']      = 'ksd_ticket';
                $_GET['ksd-onboarding'] = '7'; 
                $notes = __( 'Enjoy the plugin! See full documentation at <a target="blank" href="http://kanzucode.com">kanzucode.com</a> ' )    
                ;
                
                $settings['onboarding_enabled'] = 'no';
                Kanzu_Support_Desk::update_settings($settings);
            }
            
            if( ! isset( $_GET['post_type'] ) ||  $_GET['post_type']  !== 'ksd_ticket' ) return;
            
            $first_stage = 1;
            $last_stage  = 8;
            $stage = intval( $_GET['ksd-onboarding'] );
            $stage = ( $stage < 1 || $stage > 8 ) ? 1 : $stage;
            $next_url = admin_url( "user-new.php?post_type=ksd_ticket&ksd-onboarding=2" );
            
            $stage_class = array_fill( 1, 8, "" ) ; 
            
            foreach( $stage_class as $k => $v ) {
                if ( intval( $k ) < intval( $stage ) ){
                    $stage_class[ $k ] = 'done';
                    continue;
                }
                // $stage_class[$k] = "";
                
                if ( $k == $stage ){
                    $stage_class[ $k ] = 'active';
                    
                    switch( $k ){
                        case '1':
                            $next_url = get_permalink( $settings['page_submit_ticket'] ) ; 
                            $notes = '';
                        break;
                        case '2':
                            $next_url = admin_url( "post-new.php?post_type=ksd_ticket&ksd-onboarding=3" ) ;
                            $notes = ( strlen($notes) < 0 ) ? __( 'Select Role as KSD Customer to create agent' ) : $notes ;
                        break;
                        case '3':
                            $next_url = admin_url( "post-new.php?post_type=ksd_ticket&ksd-onboarding=4" ) ;
                            $notes = ( strlen($notes) < 0 ) ? __( 'Create ticket' ) : $notes ;
                            if( 1 == $stage_3plus ){
                            $stage_class['4'] = 'active';
                            $stage_class['5'] = 'active';
                            }
                        break;
                    }
                    
                    continue;
                }
                
            }
            
            echo     ' <div class="ksd-onboarding-progress">
                <ol class="ksd-onboarding-stages">
                                <li class="'. $stage_class[1] .'">' . __( 'Start tour', 'kanzu-support-desk' ) . '</li>
                                <li class="'. $stage_class[2] .'">' . __( 'Create ticket', 'kanzu-support-desk' ) . '</li>
                                <li class="'. $stage_class[3] .'">' . __( 'Reply ticket', 'kanzu-support-desk' ) . '</li>
                                <li class="'. $stage_class[4] .'">' . __( 'Resolve ticket', 'kanzu-support-desk' ) . '</li>
                                <li class="'. $stage_class[5] .'">' . __( 'Assign ticket', 'kanzu-support-desk' ) . '</li>
                                <li class="'. $stage_class[6] .'">' . __( 'Ready!', 'kanzu-support-desk' ) . '</li>
                </ol> 
                <a href="' . $next_url . '" class="button-small button button-primary ksd-mail-button">Next</a>
                    <div class="ksd-onboarding-notes">' . $notes. '</div>
                </div>
                    ';
        }    
}
endif;

return new KSD_Onboarding();

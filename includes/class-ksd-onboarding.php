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
        
        private $ksd_settings;
        

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
            
            add_action('admin_notices', array( $this, 'show_onboarding_progress' ) );
            
            //Jubilate when we are done
            add_action( 'ksd_onboarding_complete', array( $this, 'onboarding_complete' ) );
        }
        
        /**
         * Show the onboarding progress
         * @param int $current_stage String value of the stage to show. e.g. two
         * @since 2.1.3
         */
        public function show_onboarding_progress( $current_stage='' ){
            $this->ksd_settings = Kanzu_Support_Desk::get_settings();
          //  if ( 'no' === $this->ksd_settings['onboarding_enabled'] ){
         //       return;
         //   }

            if( ! isset( $_GET[ 'ksd-onboarding' ] ) && empty( $current_stage ) ){
                return;
            }
            if ( isset( $_GET[ 'ksd-onboarding' ] ) ){
                $current_stage = sanitize_key( $_GET[ 'ksd-onboarding' ] );
            }

            echo $this->generate_onboarding_html( $current_stage );
            if ( $current_stage == ( count( $this->current_stage ) - 1 ) ){//If we are on the last stage
                do_action( 'ksd_onboarding_complete' );
            }
        }
        
        /**
         * What stage are we at? Do the check here
         * @since 2.1.3
         */
        public function get_next_stage(){
            
            $settings = Kanzu_Support_Desk::get_settings();
            if ( 'no' === $settings['onboarding_enabled'] ){
                return;         
            }
            
            $referer     = $_SERVER['HTTP_REFERER'];
            $request_uri = $_SERVER['REQUEST_URI'];
            $notes = ''; 
            $onboarding_progress = 0;

            $stage_3plus = 0;
                        
            if ( strpos( $referer, get_permalink( $settings['page_submit_ticket'] ) ) > 0 && 
                    strpos($request_uri, 'edit.php?post_type=ksd_ticket&ksd-onboarding=3' )
                ) {

                return 4;
                
            }
            
            if ( strpos( $referer, 'edit.php?post_type=ksd_ticket' ) > 0 
                    && strpos($request_uri, 'post.php?post=' ) > 0 ) {
                return 5;
            }
            
            if ( strpos( $referer, 'post.php?post=' ) > 0 
                    && strpos( $request_uri, 'post.php?post=' ) ) {
                return -1;
            }
            
            if( ! isset( $_GET['post_type'] ) ||  $ksd_post_type  !== 'ksd_ticket' ){
                return;     
            }
 
        }
        
        public function onboarding_complete(){
            $settings = Kanzu_Support_Desk::get_settings();
            $settings['onboarding_enabled'] = 'no';
            Kanzu_Support_Desk::update_settings( $settings );
        }
        
        private function get_stage_details(){
            return array(
                1   => array(
                    'title'         => __( 'Start tour', 'kanzu-support-desk' ),
                    'next_url'      => get_permalink( $this->ksd_settings['page_submit_ticket'] ),
                    'stage_notes'   => ''
                ),
                2   => array(
                    'title'         => __( 'Create ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url('edit.php?post_type=ksd_ticket&ksd-onboarding=3'),
                    'stage_notes'   => ''    
                ),
                3   => array(
                    'title'         => __( 'Reply ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url( "user-new.php?post_type=ksd_ticket&ksd-onboarding=4" ),
                    'stage_notes'   => __( 'Select ticket to respond to. ' )
                ),
                4   => array(
                    'title'         => __( 'Resolve ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url( "user-new.php?post_type=ksd_ticket&ksd-onboarding=5" ),
                    'stage_notes'   =>__( 'In this view you can reply a ticket. Use the <b>Send</b> button to post the reply.  <br /><br />
                    Through the ticket information box to the right, the ticket can be 
                assigned to an agent, the status can be changed, and  the severity set appropriately. ' )   
                ),
                5   => array(
                    'title'         => __( 'Assign ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url( "user-new.php?post_type=ksd_ticket&ksd-onboarding=6" ),
                    'stage_notes'   => __( 'Enjoy the plugin! See full documentation at <a target="blank" href="http://kanzucode.com">kanzucode.com</a> ' )    
                ),
                6   => array(
                    'title'         => __( 'Ready!', 'kanzu-support-desk' ) ,
                    'next_url'      => admin_url( "user-new.php?post_type=ksd_ticket&ksd-onboarding=7" )
                )                
            );
        }

        private function generate_onboarding_html( $current_stage ){
            $the_stages = $this->get_stage_details();
            $onboarding_div = '<div class="ksd-onboarding-progress">';
            $onboarding_div .= '<ol class="ksd-onboarding-stages">';
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
            $onboarding_div .= '<a href="' . $the_stages[$current_stage]['next_url']. '" class="button-small button button-primary ksd-mail-button">'.__( 'Next', 'kanzu-support-desk' ).'</a>';
            $onboarding_div .= '<div class="ksd-onboarding-notes">' . $the_stages[$current_stage]['stage_notes']. '</div>';
            $onboarding_div .= '</div>';
            return $onboarding_div;
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

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

            if( isset( $_GET['ksd_getting_started'] ) ):
                add_action( 'admin_notices', array( $this, 'render_getting_started_banner' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_getting_started_scripts' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_getting_started_styles' ) );
            endif;    
            
            //Jubilate when we are done
            add_action( 'ksd_onboarding_complete', array( $this, 'onboarding_complete' ) );
        }

        /**
         * Render a getting started message
         * @return string
         */
        public function render_getting_started_banner(){?>
            <div id="ksd-gs-tabs">
              <ul>
                <li><a href="#ksd-gs-tabs-0">Welcome to KSD</a></li>
                <li><a href="#ksd-gs-tabs-1">Tickets - The Basics</a></li>
                <li><a href="#ksd-gs-tabs-2">Tickets - Management</a></li>
                <li><a href="#ksd-gs-tabs-3">Tickets - Organization</a></li>
                <li><a href="#ksd-gs-tabs-4">Customizing your set-up</a></li>
                <li><a href="#ksd-gs-tabs-5">More Features</a></li>
              </ul>
              <div id="ksd-gs-tabs-0">
                <h2>Firing up your customer service</h2>
                <p>Kanzu Support Desk, or KSD, simplifies the process of offering amazing customer service to everyone who looks to you for it.</p>
                <p>The plugin's built with your small business in mind; we know only too well how hard it is to manage multiple customer conversations while keeping all of them personal.</p>
                <p>You get centralized management, ease of use, reports, multiple integrations and a responsive support team to look to in case you have any challenges. Let's start the tour, shall we?</p>
                <a class="button button-primary ksd-gs-nav" href="#ksd-gs-tabs-1">Start Tour</a>
                <p>Team Kanzu Code</p>
              </div>
              <div id="ksd-gs-tabs-1">
                <h2>The basics...</h2>
                <p>Let's take it from the top</p>
                <p><strong>What is a ticket?</strong> Every conversation between you and your customer is called a ticket.</p>
                <p><strong>Who creates a ticket?</strong> Usually, your customer creates a ticket by using a form on your website, sending an email to your support email address or getting in touch with you on social media. Also, you or one of your team can create a ticket on behalf of a customer.</p>
 				       <p><strong>Who can view/manage/reply to a ticket?</strong>Management of tickets is restricted to certain WordPress roles. KSD comes with 3 custom roles: 
     					<ul class="ksd-gs-user-roles">
       						<li><strong>KSD Customer:</strong> This is the default role assigned to everyone who submits a ticket. It is the equivalent of the <a href="https://codex.wordpress.org/Roles_and_Capabilities#Subscriber" target="_blank">WordPress subscriber role</a></li>
       						<li><strong>KSD Agent</strong>This is a member of your team who can view, reply and make all changes to tickets apart from deleting them.</li>
       						<li><strong>KSD Supervisor</strong> This role has all the rights of a KSD Agent but also, they can delete tickets</li>
     					</ul>
 					    <p>Assign the right role to the members of your team and they'll be able to easily manage tickets.
 					      Note that anyone with the WordPress role of administrator has unrestricted access to all functions. <a href="https://kanzucode.com/knowledge_base/help-desk-user-roles/" target="_blank" class="button button-primary">More on roles here</a>
 				       </p>     
                <ul class="ksd-gs-navigation">
                  <li><a class="button button-secondary ksd-gs-nav ksd-gs-nav-prev" href="#ksd-gs-tabs-0">Previous</a> </li>
                  <li><a class="button button-primary ksd-gs-nav" href="#ksd-gs-tabs-2">Next</a> </li>
                </ul>                          
              </div>
              <div id="ksd-gs-tabs-2">
                <h2>Managing a ticket</h2>
                <p>All your tickets are listed under the <strong>Tickets</strong> menu. All tickets you have not yet read have a white background </p>
                <img src="<?php echo KSD_PLUGIN_URL . "/assets/images/ksd_tickets_read_unread.jpg"; ?>" class="ksd-gs-image" /> 
                <p>Click on a ticket to manage it. This presents you a screen where you can change <strong>ticket status</strong>, <strong>ticket severity</strong> or who it is assigned to</p>
                  <img src="<?php echo KSD_PLUGIN_URL . "/assets/images/ksd_ticket_info.jpg"; ?>" class="ksd-gs-image" />
                <p>Reply to your customer or to staff only</p>
                  <img src="<?php echo KSD_PLUGIN_URL . "/assets/images/ksd_reply_to_all.jpg"; ?>" class="ksd-gs-image" />
                <p>View ticket activity and what other tickets have been logged by the customer</p>  
                <img src="<?php echo KSD_PLUGIN_URL . "/assets/images/ksd_other_tickets.jpg"; ?>" class="ksd-gs-image" />       
                <ul class="ksd-gs-navigation">
                  <li><a class="button button-secondary ksd-gs-nav ksd-gs-nav-prev" href="#ksd-gs-tabs-1">Previous</a> </li>
                  <li><a class="button button-primary ksd-gs-nav" href="#ksd-gs-tabs-3">Next</a> </li>
                </ul>   
              </div>
              <div id="ksd-gs-tabs-3">
                <h2>Organization is key</h2>
                <p>For high efficiency, you'll need to organize your tickets. KSD allows two forms of categorization:</p>
                  <ol>
                    <li><strong>Categories</strong> Create multiple categories and subcategories to track tickets from particular clients (e.g. VIP), those from particular channels (e.g. email, facebook, website) or anything really.</li>
                    <li><strong>Products</strong> Create products to track tickets related to your products.</li>
                  </ol>
                <ul class="ksd-gs-navigation">
                  <li><a class="button button-secondary ksd-gs-nav ksd-gs-nav-prev" href="#ksd-gs-tabs-2">Previous</a> </li>
                  <li><a class="button button-primary ksd-gs-nav" href="#ksd-gs-tabs-4">Next</a> </li>
                </ul>                
              </div>              
              <div id="ksd-gs-tabs-4">
                <h2>Reports & Customization</h2>
                <p>Make changes to your set-up based on your needs. Set up replies to be sent automatically to your customer as soon as a ticket is created (auto-replies), select whom tickets should be automatically assigned to as soon as they are created and decide where your support page/form should be and what fields it should hold.</p>
                <img src="<?php echo KSD_PLUGIN_URL . "/assets/images/ksd_settings.jpg"; ?>" class="ksd-gs-image" /> 
               <p>Also, keep an eye on some key metrics   </p>  
                <img src="<?php echo KSD_PLUGIN_URL . "/assets/images/ksd_dashboard.jpg"; ?>" class="ksd-gs-image" /> 
                <p><a class="button button-seconday" href="https://kanzucode.com/knowledge_base/ksd-wordpress-helpdesk-plugin-settings/" target="_blank"> More on settings here</a></p>
                <ul class="ksd-gs-navigation">
                  <li><a class="button button-secondary ksd-gs-nav ksd-gs-nav-prev" href="#ksd-gs-tabs-3">Previous</a> </li>
                  <li><a class="button button-primary ksd-gs-nav" href="#ksd-gs-tabs-5">Next</a> </li>
                </ul>                   
              </div>
              <div id="ksd-gs-tabs-5">
                <h2>Taking this further...</h2>
                <p>It might seem that with all those features there surely cannot be more...but there are. Lots more actually.</p>
                <p>We have optional add-ons that allow you to:
                  <ul>
                    <li><strong>Mail:</strong> Create tickets automatically from emails sent to your support email address</li>
                    <li><strong>Chat:</strong> Set-up live chat and automatically create tickets from your conversations</li>
                    <li><strong>Knowledge Base:</strong> Create a rich set of articles that your customers can refer to even without contacting you</li>
                    <li><strong>Replies:</strong> Create message templates that you can use when replying a customer. </li>
                  </ul>
                  ...and a lot more....<a class="button button-seconday" href="<?php echo admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-addons' );?>" target="_blank"> More addons</a>
                </p>
                <p>That's it!! In case you'd like to take this tour again, go to ...and click 'Getting started Guide' and we'll show you around this neck of the woods again.</p>
                <a href="https://kanzucode.com/knowledge_base/simple-wordpress-helpdesk-plugin-quick-start/" target="_blank" class="button button-primary">Quick start guide</a>
              </div>              
            </div>
            <?php
        }        

        public function enqueue_getting_started_scripts(){
            wp_enqueue_script( KSD_SLUG . '-gs-js', KSD_PLUGIN_URL.'/assets/js/ksd-gs.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs' ), KSD_VERSION );

        }

        public function enqueue_getting_started_styles(){
            wp_enqueue_style( KSD_SLUG .'-gs-css', KSD_PLUGIN_URL.'/assets/css/ksd-gs.css' );            
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
                //For the 'view-ticket-list' stage, manually move it to the next stage since there's no 'Next' button
                if ( 'view-ticket-list' == $this->ksd_current_stage_key ){
                    $this->ksd_current_stage_key = 'reply-ticket';
                }
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
               'create-ticket'      => array(
                    'title'         => __( 'Create ticket', 'kanzu-support-desk' ),
                    'next_url'      => admin_url('edit.php?post_type=ksd_ticket'),
                    'stage_notes'   => sprintf( '<p>%1$s </p><p><strong>%2$s</strong></p>',
                                                __( 'This is the support form the customer will use to create a ticket.','kanzu-support-desk'),
                                                __( "Create a ticket now by entering a subject, a message and clicking 'Send Message'. Then, click 'Next' to proceed", 'kanzu-support-desk') 
                                                )
                ),            
                'view-ticket-list'  => array(
                    'title'         => __( 'View ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => sprintf( '<p>%1$s <strong>%2$s</strong></p>',
                                            __('You and your agents view all tickets here. Your customer\'s waiting; Go ahead and','kanzu-support-desk'),
                                            __( 'select the ticket you\'d like to make changes to', 'kanzu-support-desk') 
                                                ) 
                    ),            
                'reply-ticket'      => array(
                    'title'         => __( 'Reply ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   =>  sprintf( '<p>%1$s</p><p>%2$s</p><p>%3$s</p><p>%4$s</p>',
                                            __('In this view, you or an agent can make changes to a ticket.','kanzu-support-desk'),
                                            __( 'From the ticket information box to the right, the ticket can be assigned to an agent, the status can be changed and the severity set appropriately.', 'kanzu-support-desk'),
                                            __('This is also where you respond to your customer or type a private note for another agent. Private notes ARE NOT sent to customers.','kanzu-support-desk'),
                                            __( 'At the bottom of your screen is a textbox; type your response and click Send to proceed.', 'kanzu-support-desk')
                                                )    
                                         
                    ),                
                'resolve-ticket'    => array(
                    'title'         => __( 'Resolve ticket', 'kanzu-support-desk' ),
                    'next_url'      => '',
                    'stage_notes'   => sprintf( '<p>%1$s</p><p><strong>%2$s</strong></p>',
                                        __('You are on a roll! One last thing...time to resolve the ticket since you have done such a fabulous job serving your customer.','kanzu-support-desk'),
                                        __( 'In the ticket information box, change ticket status from Open to Resolved and click Update to complete', 'kanzu-support-desk')
                                                ) 
                ),               
                'end-tour'          => array(
                    'title'         => __( 'Ready!', 'kanzu-support-desk' ) ,
                    'next_url'      => admin_url( 'edit.php?post_type=ksd_ticket' ),
                    'stage_notes'   => __('That\'s it! Now that\'s how you handle customer care like a rockstar. To see full documentation or to customize your installation, click the buttons below','kanzu-support-desk'),
                                      
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
               'stage_notes'    =>  __( "404! You have uncovered an onboarding stage that doesn't exist! Please click 'Next' to restart the tour", "kanzu-support-desk" ) 
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
                }elseif( empty( $current_item['next_url'] ) ){
                    $next_url_html = '';
                }else{
                    $next_item_key      = $this->get_next_onboarding_stage_key();  
                    $next_url_html      =  '<a href="' .add_query_arg( 'ksd-onboarding', $next_item_key, $current_item['next_url'] ). '" class="button-large button button-primary ksd-onboarding-next">'.__( 'Next', 'kanzu-support-desk' ).'</a>';
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
            $cheatsheet_link        = '<a href="https://kanzucode.com/blog/7-things-to-look-out-for-in-a-wordpress-help-desk-plugin/" class="button-large button button-primary ksd-onboarding-last" target="_blank">'.__( 'Get Help Desk Tips', 'kanzu-support-desk' ).'</a>';
            $knowledge_base_link    = '<a href="https://kanzucode.com/knowledge_base/simple-wordpress-helpdesk-plugin-quick-start/" class="button-large button button-primary ksd-onboarding-last-right" target="_blank">'.__( 'KSD Documentation', 'kanzu-support-desk' ).'</a>';
            return $cheatsheet_link.$knowledge_base_link;
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

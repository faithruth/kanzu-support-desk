<?php
/**
 * Class KSDAPITests
 *
 * @package Kanzu_Support_Desk
 * 
 * @since 2.3.0    
 */

/**
 * Ticket tests
 */
class KSDAPITests extends WP_UnitTestCase {

	/*KSD Ticket details*/
	protected $_ksd_ticket_details;
	
	/*Reply */
	protected $_ksd_reply_details;
	
	/*Current user*/
	protected $_current_user;
	
	public function setUp() {
		parent::setUp();
		
		//Set the current user
		$this->_current_user = new WP_User( 1 );
		
		//Ticket details
		$this->_ksd_ticket_details = array(
			'ksd_tkt_subject' => 'Ticket Subject',
			'ksd_tkt_message' => 'Ticket Message',
			'ksd_tkt_status'  => 'open',
			'ksd_cust_email'  => $this->_current_user->user_email,
			'ksd_tkt_channel' => 'sample-ticket'
		);
		
		//Reply ticket details
		$this->_ksd_reply_details = array(
			'ksd_tkt_subject' => 'Ticket Reply Subject',
			'ksd_tkt_message' => 'Ticket Reply Message',
			'ksd_tkt_status'  => 'open',
			'ksd_cust_email'  => $this->_current_user->user_email,
			'ksd_tkt_channel' => 'sample-ticket',
			'ksd_tkt_time_logged' => current_time( 'mysql' ),
			'ksd_tkt_id'	  => 0 //Set this before use
		);
	}
	
	public function tearDown() {
		parent::tearDown();
	}
	
	/**
	 * ksd_log_new_ticket
         * 
         * @since 2.3.0  
	 */
	function test_ksd_log_new_ticket() {
		global $ticket_id;
		
		$ticket_id = 0;
		$new_ticket = $this->_ksd_ticket_details;
                $new_ticket['ksd_addon_tkt_id'] = '1';
                
		$ksd_admin = Admin::get_instance();
		
		add_action( 'save_post', function( $post_id ){
			global $ticket_id;
			$ticket_id = $post_id;
		} );

		do_action( 'ksd_log_new_ticket', $new_ticket ); 
		
		$this->assertGreaterThan( 0, $ticket_id );
	}
	
	/**
	 * Test ksd_reply_ticket
         *  
         * @since 2.3.0  
         * 
         *  ksd_reply_ticket
         * 
	 */
	function test_ksd_reply_ticket( ) {
		global $ticket_id, $reply_id;
		
		//First log a new ticket 
		$ticket_id = 0;
		$new_ticket = $this->_ksd_ticket_details;
		$new_ticket['ksd_addon_tkt_id'] = '1';
                $ksd_admin = Admin::get_instance();
		
		add_action( 'save_post', function( $post_id ){
			global $ticket_id;
			$ticket_id = $post_id;
		} );
		do_action( 'ksd_log_new_ticket', $new_ticket );
		
		$this->assertGreaterThan( 0, $ticket_id );
                
		//Create reply
		$reply_id = 0;
		$reply_data = $this->_ksd_reply_details;
		$reply_data['ksd_tkt_id'] = $ticket_id; //Set parent id 
		$reply_data['ksd_addon_tkt_id'] = 1;
		
		add_action( 'save_post', function( $post_id ){
			global $reply_id;
			$reply_id = $post_id;
		} );
                
		do_action( 'ksd_reply_ticket', $reply_data );
		
		$this->assertGreaterThan( 0, $reply_id );
	}
        
        
}

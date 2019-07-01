<?php
/**
 * Class KSDAdminTests
 * 
 * KSDAdmin class tests
 *
 * @package Kanzu_Support_Desk
 * 
 * @since 2.3.0
 */

/**
 * Ticket tests
 */
class KSDAdminTests extends WP_UnitTestCase {

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
                wp_set_current_user( 1, $this->_current_user->name );
		
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
         * Test  add settings action link to the plugins page.
         * 
         * @since 2.3.0
         */
        public function test_add_action_links(){
            $ksd_admin = Admin::get_instance();
            $links = $ksd_admin->add_action_links( array() );
            
            $this->assertGreaterThan( 0, count( $links ) );
        }
	
        /**
         * 
         * Test add the button used to add attachments to a ticket
         * 
         * @since 2.3.0
         */
        public function test_add_attachments_button(){
            $ksd_admin = Admin::get_instance();
            
            $editor_id = 'ksd_ticket';
            $_GET['page'] = 'ksd_ticket';
            $ksd_admin->add_attachments_button( $editor_id );
            
            $patten = '/^<a\s+href/';
            $this->expectOutputRegex( $patten );
            
            unset( $_GET['page'] );
            $this->assertNull( $ksd_admin->add_attachments_button( $editor_id ) );
        }
        
        /**
         * Test add_contextual_help
         * 
         * @since 2.3.0
         */
        public function test_add_contextual_help(){
            $ksd_admin = Admin::get_instance();
            
            $contextual_help = '';
            $screen_id_arr = array();
            
            $screen_arr = array();
            $screen = new stdClass();
            $screen->post_type = 'ksd_ticket';
            
            $screen_id_arr[] =  'ksd-ticket-list';
            $screen->id = 'edit-ksd_ticket';
            $screen_arr[] = $screen;
            
            $screen_id_arr[] =  'ksd-add-new-ticket';
            $screen->id = 'ksd_ticket';
            $screen->action = 'add';
            $screen_arr[] = $screen;
            
            $screen_id_arr[] =  'ksd-single-ticket-details';
            $screen->id = 'ksd_ticket';
            $screen->action = 'action';
            $screen_arr[] = $screen;
            
            $screen_id_arr[] =  'ksd-edit-categories';
            $screen->id = 'edit-ticket_category';
            $screen_arr[] = $screen;
            
            $screen_id_arr[] =  'ksd-edit-products';
            $screen->id = 'edit-product';
            $screen_arr[] = $screen;
            
            $screen_id_arr[] =  'ksd-dashboard';
            $screen->id = 'ksd_ticket_page_ksd-dashboard';
            $screen_arr[] = $screen;
            
            $screen_id_arr[] =  'ksd-settings';
            $screen->id = 'ksd_ticket_page_ksd-settings';
            $screen_arr[] = $screen;
            
            $screen_id_arr[] =  'ksd-addons';
            $screen->id = 'ksd_ticket_page_ksd-addons';
            $screen_arr[] = $screen;
            
            foreach ( $screen_id_arr as $key => $screen_id ){
                $screen = $screen_arr[$key];
                $contextual_help = $ksd_admin->add_contextual_help( '', $screen_id, $screen );
                
                $pattern = '/^<span>/';
                $this->assertRegExp( $pattern, $contextual_help );
            }
            
        }
        
        
        /**
         * Test add_importer_to_toolbox
         * 
         * @since 2.3.0
         */
        public function test_add_importer_to_toolbox(){
            $ksd_admin = Admin::get_instance();
            
            $ksd_admin->add_importer_to_toolbox();
            $pattern = '/.*tool-box.*>/';
            $this->expectOutputRegex( $pattern );
        }
        
        /**
         * Test new_ticket_imported
         * 
         * @since 2.3.0
         */
        public function test_new_ticket_imported(){
            $this->MarkTestIncomplete();
        }
        
        /**
         * Test ksd_importer_init
         * 
         * @since 2.3.0
         */
        public function test_ksd_importer_init(){
            global $wp_importers;
            
            $ksd_admin = Admin::get_instance();
            $ksd_admin->ksd_importer_init();

            $this->assertTrue( isset( $wp_importers['ksdimporter'] ) );
        }
        
        /**
         * Test add_menu_pages
         * 
         * @since 2.3.0
         */
        public function test_add_menu_pages(){
            global $submenu;
            $ksd_admin = Admin::get_instance();
            $ksd_admin->add_menu_pages();

            $menu_slug  = 'edit.php?post_type=ksd_ticket';

            //$this->assertTrue( isset( $submenu[ $menu_slug ] ) );
            $this->markTestIncomplete();
        }
        
        /**
         * Test add_my_tickets_link
         * 
         * @since 2.3.0
         */
        public function test_add_my_tickets_link(){
            global $current_user;
            $current_user = $this->_current_user ;
  
            $ksd_admin = Admin::get_instance();
            $ksd_admin->add_my_tickets_link();
            
            //$this->expectOutputRegex( '/^<a href/' );
            $this->markTestIncomplete();
        }
        
        
        /**
         * Test disable_notifications
         * 
         * @since 2.3.0
         */
        public function test_disable_notifications(){
            $ksd_admin = Admin::get_instance();
            $ksd_admin->disable_notifications();
            
            $this->expectOutputRegex( '/.*Thanks for your time.*/' );
            
        }
        
        public function create_ticket(){
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
                
                return $ticket_id;
        }
        
        /**
         * Test ticket_updated_messages
         * 
         * @since 2.3.0
         * 
         * @depends create_ticket
         */
        public function test_ticket_updated_messages( $post_id ){
            global $post, $post_ID;
            
            $post_ID = $post_id;
            $post = get_post( $post_id );
            
            $ksd_admin = Admin::get_instance();
            
            $arr = $ksd_admin->ticket_updated_messages( array() );
            
            $this->assertTrue( is_array( $arr ) );
        }
        
        /**
         * Test ticket_bulk_update_messages
         * 
         * @since 2.3.0
         */
        public function test_ticket_bulk_update_messages(){
            $ksd_admin = Admin::get_instance();
            $bulk_counts['updated'] = 0;
            $bulk_counts['locked'] = 0;
            $bulk_counts['deleted'] = 0;
            $bulk_counts['trashed'] = 0;
            $bulk_counts['untrashed'] = 0;
            $bulk_messages = $ksd_admin->ticket_bulk_update_messages(array(), $bulk_counts);
            
            $this->assertTrue( is_array( $bulk_messages ) );
        }
        

        
        /**
         * Test modify_license_status
         * 
         * @since 2.3.0
         */
        public function test_modify_license_status(){
            $this->markTestIncomplete();
        }
        
        /**
         * Test do_license_modifications
         * 
         * @since 2.3.0
         */
        public function test_do_license_modifications(){
            $this->markTestIncomplete();
        }
        
        /**
         * Test generate_debug_file
         * 
         * @since 2.3.0
         */
        public function test_generate_debug_file(){
            $this->markTestIncomplete();
        }
        
        /**
         * Test add_tinymce_cc_plugin
         * 
         * @since 2.3.0
         */
        public function test_add_tinymce_cc_plugin(){           
            $ksd_admin = Admin::get_instance();
            
            $plugin_array = $ksd_admin->add_tinymce_cc_plugin( array() );
            
            $this->assertTrue( isset( $plugin_array['KSDCC'] ) );
        }
        
        /**
         * Test register_tinymce_cc_button
         * 
         * @since 2.3.0
         */
        public function test_register_tinymce_cc_button(){
            
//            $ksd_admin = Admin::get_instance();
//            $editor_id = 'ksd_ticket';
//            $buttons = $ksd_admin->register_tinymce_cc_button( array(), $editor_id );
//            
//            $this->assertTrue( isset( $buttons['ksd_cc_button'] ) );
            
            $this->markTestIncomplete();
        }
        
        /**
         * Test add_tickets_headers
         * 
         * @since 2.3.0
         */
        public function test_add_tickets_headers(){
            $ksd_admin = Admin::get_instance();
            
            $defaults_arr = array( 'status' ,'assigned_to', 'severity','customer','replies' ); 
                
            $defaults = $ksd_admin->add_tickets_headers( array() );
            foreach( $defaults as $k => $v ){
                $this->assertTrue( in_array( $k, $defaults_arr ) );
            }
        }
        
        /**
         * Test ticket_table_sortable_columns
         * 
         * @since 2.3.0
         */
        public function test_ticket_table_sortable_columns(){
            $columns_arr = array( 'status', 'assigned_to', 'severity','customer' );
            $ksd_admin = Admin::get_instance();
            
            $columns = $ksd_admin->ticket_table_sortable_columns( array() ) ;
            foreach( $columns as $column ){
                $this->assertTrue( in_array( $column, $columns_arr ) );
            }
            
        }
        
        /**
         * Test ticket_table_remove_columns
         * 
         * @since 2.3.0
         */
        public function test_ticket_table_remove_columns(){
            $ksd_admin = Admin::get_instance();
            
            $columns = $ksd_admin->ticket_table_remove_columns( array() );
            
            $this->assertFalse( isset( $columns['tags'] ) );
        }
        
        /**
         * Test ticket_table_columns_orderby
         * 
         * @since 2.3.0
         */
        public function test_ticket_table_columns_orderby(){
            $orderby['severity'] = array(
                        'meta_key' => '_ksd_tkt_info_severity',
                        'orderby' => 'meta_value'
                        );
            $orderby['assigned_to'] = array(
                        'meta_key' => '_ksd_tkt_info_assigned_to',
                        'orderby' => 'meta_value_num'
                        );
            $orderby['status'] = array(
                        'orderby' => 'post_status'
                        );
            $orderby['customer'] = array(
                        'orderby' => 'post_author'
                        );
            
            $ksd_admin = Admin::get_instance();
            
            foreach( $orderby as $key => $value ){
                $vars['orderby'] = $key;
                $output = $ksd_admin->ticket_table_columns_orderby( $vars );
                $expected = array_merge( $vars, $value );
                
                $this->assertTrue( $output === $expected );
            }
            
        }
        
        /**
         * Test ticket_table_filter_headers
         */
        public function test_ticket_table_filter_headers(){           
            //$ksd_admin = Admin::get_instance();
            //$ksd_admin->ticket_table_filter_headers();
            
            $this->markTestIncomplete();
        }
        
        /**
         * Test ticket_table_apply_filters
         */
        public function test_ticket_table_apply_filters(){
            $ksd_admin = Admin::get_instance();
            $query = new WP_Query();
            
            $args = array( 'post_type' => 'ksd_ticket' );
            $ksd_admin->ticket_table_apply_filters( $args );
            
            
        }
        
        /**
         * Test populate_ticket_columns
         * 
         * @since 2.3.0
         * 
         * @depends create_ticket
         */
        public function test_populate_ticket_columns( $post_id ){
            $this->markTestIncomplete();
        }
        
        /**
         * Test get_ticket_status_label
         * 
         * @since 2.3.0
         */
        public function test_get_ticket_status_label(){
            $expected['Unknown']    = __( 'Unknown', 'kanzu-support-desk' );
            $expected['open']       = __( 'Open', 'kanzu-support-desk' );
            $expected['pending']    = __( 'Pending', 'kanzu-support-desk' );
            $expected['resolved']   = __( 'Resolved', 'kanzu-support-desk' );
            $expected['new']        = __( 'New', 'kanzu-support-desk' );
            $expected['draft']      = __( 'Draft', 'kanzu-support-desk' );
            
            $ksd_admin = Admin::get_instance();
            
            foreach( $expected as $post_status => $expect ){
                $output = $ksd_admin->get_ticket_status_label( $post_status );
                $this->assertEquals( $output, $expect );
            }
        }

        /**
         * Test get_ticket_severity_label
         * 
         * @since 2.3.0
         */
        public function test_get_ticket_severity_label(){
            $arr_expected['Unknown']    = __( 'Unknown', 'kanzu-support-desk' );
            $arr_expected['low']        = __( 'Low', 'kanzu-support-desk' );
            $arr_expected['medium']     = __( 'Medium', 'kanzu-support-desk' );
            $arr_expected['high']       = __( 'High', 'kanzu-support-desk' );
            $arr_expected['urgent']     = __( 'Urgent', 'kanzu-support-desk' );

            $ksd_admin = Admin::get_instance();
            
            foreach( $arr_expected as $ticket_severity => $expect ){
                $output = $ksd_admin->get_ticket_severity_label( $ticket_severity );
                $this->assertEquals( $output, $expect );
            }
        }
        
        /**
         * Test save_bulk_edit_ksd_ticket
         * 
         * @since 2.3.0
         * 
         * @depends create_ticket
         */
        public function test_save_bulk_edit_ksd_ticket( $post_id ){
            $ksd_admin = Admin::get_instance();
            
            $_POST[ 'post_ids' ] = array( $post_id );
            $_POST['_ksd_tkt_info_assigned_to'] = 1;
            $_POST['_ksd_tkt_info_severity'] = 'high';
            
            $ksd_admin->save_bulk_edit_ksd_ticket();
            
            $expected_assigned_to = get_post_meta( $post_id, '_ksd_tkt_info_assigned_to' );
            $this->assertEquals( 1, $expected_assigned_to );
            
            $expected_severity = get_post_meta( $post_id, '_ksd_tkt_info_severity' );
            $this->assertEquals( 'high', $expected_severity );
        }

        /**
         * Test ticket_views
         * 
         * @since 2.3.0
         */
        public function test_ticket_views(){
            $ksd_admin = Admin::get_instance();
            
            $output = $ksd_admin->ticket_views( array() );
            
            $this->assertTrue( isset( $output['mine'] ) );
            $this->assertTrue( isset( $output['unassigned'] ) );
        }
        
        /**
         * Test display_ticket_statuses_next_to_title
         * 
         * @since 2.3.0
         * 
         * @depends create_ticket
         */
        public function test_display_ticket_statuses_next_to_title( $post_id ){
            global $post;
            $post = get_post( $post_id );
            
            $ksd_admin = Admin::get_instance();
            $output = $ksd_admin->display_ticket_statuses_next_to_title( array( null) );
            
            $this->assertTrue( 0 === count( $output ) );
            
            $post->post_type = 'some_post_type';
            $output = $ksd_admin->display_ticket_statuses_next_to_title( array( null) );
            $this->assertGreaterThan( 0, count( $output ) );
            
        }
        
        /**
         * Test append_admin_feedback
         */
        public function test_append_admin_feedback(){
            $ksd_admin = Admin::get_instance();
            
            $ksd_settings = Kanzu_Support_Desk::get_settings();
            $ksd_settings['notifications_enabled'] = 'no';
            Kanzu_Support_Desk::update_settings( $ksd_settings );
            
            $output = @$ksd_admin->append_admin_feedback();
            
            $this->assertEquals('', $output );
            
            $this->markTestIncomplete();

        }
        
        /**
         * Test on_new_product
         * 
         * @since 2.3.0
         * 
         */
        public function test_on_new_product(){
            $ksd_admin = Admin::get_instance();
            $category_id = wp_create_category( 'product' );
            
            $postarr = array(
                'post_title' => 'product'
            );
            $postID = wp_insert_post( $postarr );
            $new_product = get_post( $postID );
            
            $ksd_admin->on_new_product( $postID, $new_product );
            
            //@TODO: 
            //$this->assertTrue( term_exists( 'product', 'product' ) );
        }
        
        /**
         * Test send_tracking_data
         * 
         * @since 2.3.0
         */
        public function test_send_tracking_data(){
            $this->markTestIncomplete();
        }
        
        /**
         * Test get_tracking_data
         */
        public function test_get_tracking_data(){
            $this->markTestIncomplete();
        }
        
        /**
         * Test quick_edit_custom_boxes
         * 
         * @since 2.3.0
         */
        public function test_quick_edit_custom_boxes(){
            $columns = array('assigned_to', 'severity' );
            $ksd_admin = Admin::get_instance();
            $post_type = 'ksd_ticket';
            
            foreach( $columns as $col ){
                $ksd_admin->quick_edit_custom_boxes($col, $post_type );
                
                $this->expectOutputRegex( '/.*fieldset.*/');
            }
            
        }
        
        /**
         * Test append_classes_to_ticket_grid
         * 
         * @since 2.3.0
         * 
         * @depends create_ticket
         */
        public function test_append_classes_to_ticket_grid( $post_id ){
            $ksd_admin = Admin::get_instance();
            $class = 'test-class';
            
            $output = $ksd_admin->append_classes_to_ticket_grid( array(), $class, $post_ID );
            
            $this->assertGreaterThan( 0, count( $classes ) );
        }
        
        /**
         * Test change_publish_button
         * 
         * @since 2.3.0
         */
        public function test_change_publish_button(){
            $ksd_admin = Admin::get_instance();
            $text = 'Publish';
            $translation = 'Translation';
            
            $output = $ksd_admin->change_publish_button( $translation, $text );
            
            $this->assertEquals( $output, $translation );

        }
        
        /**
         * Test display the main Kanzu Support Desk admin dashboard
         * 
         * @since 2.3.0
         */
        public function test_output_admin_menu_dashboard(){
            $this->markTestIncomplete( 'Test included files later' );
        }
        
        /**
         * Test do_admin_includes
         */
        public function test_do_admin_includes(){
            
            $ksd_admin = Admin::get_instance();
            $ksd_admin->do_admin_includes();
            
            $this->assertTrue(class_exists( 'Controller' ) );
            $this->assertTrue(class_exists( 'Tickets_Controller' ) );
            $this->assertTrue(class_exists( 'Users_Controller' ) );
        }
        
        /**
         * Test filter_totals
         * 
         * @since 2.3.0
         */
        public function test_filter_totals(){
            $ksd_admin = Admin::get_instance();
            
            $_POST['ksd_admin_nonce'] = wp_create_nonce( 'ksd-admin-nonce' );
            
            $ksd_admin->filter_totals();
            
            $pattern = '/^\[{"mine":"\d+","unassigned":"\d+"}\]/';
            $this->expectOutputRegex( $pattern );
        }
        
        
	/**
	 * Log a ticket
	 */
	function test_log_ticket_as_addon() {
		global $ticket_id;
		
		$ticket_id = 0;
		$new_ticket = $this->_ksd_ticket_details;
                $new_ticket['ksd_addon_tkt_id'] = '1';
                
		$ksd_admin = Admin::get_instance();
		
		add_action( 'save_post', function( $post_id ){
			global $ticket_id;
			$ticket_id = $post_id;
		} );
		//$ksd_admin->log_new_ticket( $this->_ksd_ticket_details , true );
		do_action( 'ksd_log_new_ticket', $new_ticket ); 
		
		$this->assertGreaterThan( 0, $ticket_id );
	}
	
	/**
	 * Reply ticket
         *  
         *  Action hooks use //@TODO: Move to separate API test file s
         *  ksd_log_new_ticket
         *  ksd_reply_ticket
	 */
	function test_reply_ticket_as_addon() {
		global $ticket_id, $reply_id;
		
		//First log a new ticket 
		$ticket_id = 0;
		$new_ticket = $this->_ksd_ticket_details;
		$ksd_admin = Admin::get_instance();
		
		add_action( 'save_post', function( $post_id ){
			global $ticket_id;
			$ticket_id = $post_id;
		} );
		//$ksd_admin->log_new_ticket( $this->_ksd_ticket_details , true );
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
		//$ksd_admin->reply_ticket( $this->_ksd_ticket_details , true );
		do_action( 'ksd_reply_ticket', $reply_data );
		
		$this->assertGreaterThan( 0, $reply_id );
	}
        
        
}

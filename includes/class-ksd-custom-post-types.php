<?php
/**
 * KSD's custom post types
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Custom_Post_Types' ) ) : 
    
class KSD_Custom_Post_Types {
    
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
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
        
        /***
         * Create 'ksd_ticket' Custom post type
         * @since 2.0.0
         */
        public static function create_custom_post_types() { 
            
            $ksd_cpt = self::get_instance();
            $ksd_cpt->create_ksd_ticket();
            $ksd_cpt->create_ksd_replies();
            $ksd_cpt->create_ksd_ticket_activity();
            $ksd_cpt->create_private_notes();
            //@TODO Use custom fields for tkt_cc,tkt_is_read and rep_cc
             flush_rewrite_rules();//Because of the rewrites, this is necessary
        }        
        
        /**
         * Add custom KSD ticket statuses
         * @since 2.0.0
         */
        public static function custom_ticket_statuses() {
            register_post_status( 'open', array(
                'label'                     => _x( 'Open', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'exclude_from_search'       => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Open <span class="count">(%s)</span>', 'Open <span class="count">(%s)</span>', 'kanzu-support-desk' )
                ) );
            register_post_status( 'pending', array(
                'label'                     => _x( 'Pending', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'exclude_from_search'       => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'kanzu-support-desk' )
                ) );
            register_post_status( 'resolved', array(
                'label'                     => _x( 'Resolved', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'exclude_from_search'       => true,
                'label_count'               => _n_noop( 'Resolved <span class="count">(%s)</span>', 'Resolved <span class="count">(%s)</span>', 'kanzu-support-desk' )
                ) );
            register_post_status( 'new', array(  
                'label'                     => _x( 'New', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'exclude_from_search'       => true,
                'label_count'               => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>', 'kanzu-support-desk' )
                ) );
        }                
        
        
        public function create_ksd_ticket(){
            /*----Tickets -----*/
            $labels = array(
                'name'              => _x( 'Tickets', 'post type general name', 'kanzu-support-desk' ),
                'singular_name'     => _x( 'Ticket', 'post type singular name', 'kanzu-support-desk' ),
                'add_new'           => _x( 'Add New', 'singular item', 'kanzu-support-desk' ),
                'add_new_item'      => __( 'Add New Ticket', 'kanzu-support-desk' ),
                'edit_item'         => __( 'Reply Ticket', 'kanzu-support-desk' ),
                'new_item'          => __( 'New Ticket', 'kanzu-support-desk' ),
                'all_items'         => __( 'All Tickets', 'kanzu-support-desk' ),
                'view_item'         => __( 'View Ticket', 'kanzu-support-desk' ),
                'view_items'        => __( 'View Tickets', 'kanzu-support-desk' ),
                'search_items'      => __( 'Search Tickets', 'kanzu-support-desk' ),
                'not_found'         => __( 'No Tickets found', 'kanzu-support-desk' ),
                'not_found_in_trash'=> __( 'No tickets found in the Trash', 'kanzu-support-desk' ),
                'parent_item_colon' => '',
                'menu_name'         => __( 'Tickets', 'kanzu-support-desk' )
            );
            $ticket_supports = array( 'title', 'custom-fields' );
            if ( !isset( $_GET['post'] ) ) {
                $ticket_supports[] = 'editor';
            }
            
            $args = array(
                'labels'                => $labels,
                'description'           => __( 'All your customer service tickets', 'kanzu-support-desk' ),
                'public'                => true,
                'exclude_from_search'   => true, 
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'show_in_nav_menus'     => false, 
                'query_var'             => true,
                'rewrite'               => array( 'slug' => 'ksd_ticket', 'with_front' => false ),
                'menu_position'         => 25,
                'has_archive'           => true,
                'capability_type'       => 'ksd_ticket',
		'map_meta_cap'          => true,
                'menu_icon'             => 'dashicons-tickets-alt',
                'supports'              => $ticket_supports,
                'taxonomies'            => array( 'post_tag' )
            );
            if ( ! post_type_exists( 'ksd_ticket' ) ) {
                 register_post_type( 'ksd_ticket', $args ); 
            }
           
            //@Change type of 'tags' to 'products'. Tags are categories that aren't heirarchical
            //Add the 'Product' tag to the ticket post type
           // Add new taxonomy, NOT hierarchical (like tags)
            $product_labels = array(
                    'name'                       => _x( 'Products', 'taxonomy general name' , 'kanzu-support-desk' ),
                    'singular_name'              => _x( 'Product', 'taxonomy singular name' , 'kanzu-support-desk' ),
                    'search_items'               => __( 'Search Products' , 'kanzu-support-desk' ),
                    'popular_items'              => __( 'Popular Products' , 'kanzu-support-desk' ),
                    'all_items'                  => __( 'All Products' , 'kanzu-support-desk' ),
                    'parent_item'                => null,
                    'parent_item_colon'          => null,
                    'edit_item'                  => __( 'Edit Product' , 'kanzu-support-desk' ),
                    'update_item'                => __( 'Update Product' , 'kanzu-support-desk' ),
                    'add_new_item'               => __( 'Add New Product' , 'kanzu-support-desk' ),
                    'new_item_name'              => __( 'New Product Name' , 'kanzu-support-desk' ),
                    'separate_items_with_commas' => __( 'Separate products with commas' , 'kanzu-support-desk' ),
                    'add_or_remove_items'        => __( 'Add or remove products' , 'kanzu-support-desk' ),
                    'choose_from_most_used'      => __( 'Choose from the most used products' , 'kanzu-support-desk' ),
                    'not_found'                  => __( 'No products found.' , 'kanzu-support-desk' ),
                    'menu_name'                  => __( 'Products' , 'kanzu-support-desk' )
            );

            $product_args = array(
                    'hierarchical'          => false,
                    'labels'                => $product_labels,
                    'show_ui'               => true,
                    'show_admin_column'     => true,
                    'update_count_callback' => '_update_post_term_count',
                    'query_var'             => true,
                    'rewrite'               => array( 'slug' => 'product' ),
            );

            register_taxonomy( 'product', 'ksd_ticket', $product_args ); 
                        
            //Ticket Categories
            $tkt_category_args = array(
                'hierarchical'  => true,
            );
            register_taxonomy( 'ticket_category', 'ksd_ticket', $tkt_category_args );             
            
        }
        
        public function create_ksd_replies(){
            /*----Replies -----*/
            $reply_labels = array(
                'name'                  => _x( 'Replies', 'post type general name', 'kanzu-support-desk'),
                'singular_name'         => _x( 'Reply', 'post type singular name', 'kanzu-support-desk'),
                'add_new'               => __( 'Add New', 'kanzu-support-desk'),
                'add_new_item'          => __( 'Add New Reply', 'kanzu-support-desk'),
                'edit_item'             => __( 'Edit Reply', 'kanzu-support-desk'),
                'new_item'              => __( 'New Reply', 'kanzu-support-desk'),
                'all_items'             => __( 'All Replies', 'kanzu-support-desk'),
                'view_item'             => __( 'View Reply', 'kanzu-support-desk'),
                'search_items'          => __( 'Search Replies', 'kanzu-support-desk'),
                'not_found'             => __( 'No Replies found', 'kanzu-support-desk'),
                'not_found_in_trash'    => __( 'No Replies found in Trash', 'kanzu-support-desk'),
                'parent_item_colon'     => '',
                'menu_name'             => __( 'Replies', 'kanzu-support-desk')
            );

            $reply_args = array(
                'labels'                => $reply_labels,
                'public'                => false,                
                'query_var'             => false,
                'rewrite'               => false,
                'show_ui'               => false,
                'map_meta_cap'          => true,
                'capability_type'       => 'ksd_reply',          
                'supports'              => array( 'editor', 'custom-fields' ),
                'can_export'            => true
            );
            if ( ! post_type_exists( 'ksd_reply' ) ) {
                register_post_type( 'ksd_reply', $reply_args );        
            }
        }
        
        public function create_private_notes(){
            /*----Private Notes -----*/
            $private_note_labels = array(
                'name'                  => _x( 'Private Notes', 'post type general name', 'kanzu-support-desk'),
                'singular_name'         => _x( 'Private Note', 'post type singular name', 'kanzu-support-desk'),
                'add_new'               => __( 'Add New', 'kanzu-support-desk'),
                'add_new_item'          => __( 'Add New Private Note', 'kanzu-support-desk'),
                'edit_item'             => __( 'Edit Private Note', 'kanzu-support-desk'),
                'new_item'              => __( 'New Private Note', 'kanzu-support-desk'),
                'all_items'             => __( 'All Private Notes', 'kanzu-support-desk'),
                'view_item'             => __( 'View Private Note', 'kanzu-support-desk'),
                'search_items'          => __( 'Search Private Notes', 'kanzu-support-desk'),
                'not_found'             => __( 'No Private Notes found', 'kanzu-support-desk'),
                'not_found_in_trash'    => __( 'No Private Notes found in Trash', 'kanzu-support-desk'),
                'parent_item_colon'     => '',
                'menu_name'             => __( 'Private Notes', 'kanzu-support-desk')
            );

            $private_note_args = array(
                'labels'                => $private_note_labels,
                'public'                => false,                
                'query_var'             => false,
                'rewrite'               => false,
                'show_ui'               => false,
                'map_meta_cap'          => true,
                'capability_type'       => 'ksd_private_note',             
                'supports'              => array( 'editor', 'custom-fields' ),//@TODO Change this. None of these are needed
                'can_export'            => true
            );
            if ( ! post_type_exists( 'ksd_private_note' ) ) {
                register_post_type( 'ksd_private_note', $private_note_args );         
            }
        }
        
        public function create_ksd_ticket_activity(){
            /*----Ticket Activity -----*/
            //Holds changes to ticket info as events
            $ticket_activity_labels = array(
                'name'                  => _x( 'Ticket Activity', 'post type general name', 'kanzu-support-desk'),
                'singular_name'         => _x( 'Ticket Activity', 'post type singular name', 'kanzu-support-desk'),
                'add_new'               => __( 'Add New', 'kanzu-support-desk'),
                'add_new_item'          => __( 'Add New Ticket Activity', 'kanzu-support-desk'),
                'edit_item'             => __( 'Edit Ticket Activity', 'kanzu-support-desk'),
                'new_item'              => __( 'New Ticket Activity', 'kanzu-support-desk'),
                'all_items'             => __( 'All Ticket Activities', 'kanzu-support-desk'),
                'view_item'             => __( 'View Ticket Activity', 'kanzu-support-desk'),
                'search_items'          => __( 'Search Ticket Activities', 'kanzu-support-desk'),
                'not_found'             => __( 'No Ticket Activity found', 'kanzu-support-desk'),
                'not_found_in_trash'    => __( 'No Ticket Activity found in Trash', 'kanzu-support-desk'),
                'parent_item_colon'     => '',
                'menu_name'             => __( 'Ticket Activity', 'kanzu-support-desk')
            );

            $ticket_activity_args = array(
                'labels'                => $ticket_activity_labels,
                'public'                => false,                
                'query_var'             => false,
                'rewrite'               => false,
                'show_ui'               => false,
                'map_meta_cap'          => true,
                'capability_type'       => 'ksd_ticket_activity',
                'supports'              => array( 'editor', 'custom-fields' ),//@TODO Change this. None of these are needed
                'can_export'            => true
            );
            if ( ! post_type_exists( 'ksd_ticket_activity' ) ) {
                register_post_type( 'ksd_ticket_activity', $ticket_activity_args );        
            }
        }

}

endif;

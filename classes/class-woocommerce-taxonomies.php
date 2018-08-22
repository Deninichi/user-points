<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
/**
 * User Points plugin Woocommerce custom taxonomies Class
 *
 *
 * @package WordPress
 * @subpackage User_Points
 * @category Plugin
 * @author Denis Nichik
 * @since 1.0.0
 */
class UP_Woo_Taxonomies {

	/**
	 * Constructor function.
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'up_woo_custom_taxonomies' ), 0 );

	} // End __construct()


	/**
	 * Add new woocommerce taxonomies
	 * @access public
	 * @since 1.0.0
	 */
	public function up_woo_custom_taxonomies(){

		// Organization tax
		$labels = array(
            'name'                       => _x( 'Organizations', 'Taxonomy General Name', 'user-point' ),
            'singular_name'              => _x( 'Organization', 'Taxonomy Singular Name', 'user-point' ),
            'menu_name'                  => __( 'Organizations', 'user-point' ),
            'all_items'                  => __( 'All Items', 'user-point' ),
            'parent_item'                => __( 'Parent Item', 'user-point' ),
            'parent_item_colon'          => __( 'Parent Item:', 'user-point' ),
            'new_item_name'              => __( 'New Item Name', 'user-point' ),
            'add_new_item'               => __( 'Add New Item', 'user-point' ),
            'edit_item'                  => __( 'Edit Item', 'user-point' ),
            'update_item'                => __( 'Update Item', 'user-point' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'user-point' ),
            'search_items'               => __( 'Search Items', 'user-point' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'user-point' ),
            'choose_from_most_used'      => __( 'Choose from the most used items', 'user-point' ),
            'not_found'                  => __( 'Not Found', 'user-point' ),
        );
        $rewrite = array(
            'slug'                       => 'organization',
            'with_front'                 => true,
            'hierarchical'               => true,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'organization', array( 'product' ), $args );


        // Category tax
        $labels = array(
            'name'                       => _x( 'Charity Categories', 'Taxonomy General Name', 'user-point' ),
            'singular_name'              => _x( 'Charity Category', 'Taxonomy Singular Name', 'user-point' ),
            'menu_name'                  => __( 'Charity Categories', 'user-point' ),
            'all_items'                  => __( 'All Items', 'user-point' ),
            'parent_item'                => __( 'Parent Item', 'user-point' ),
            'parent_item_colon'          => __( 'Parent Item:', 'user-point' ),
            'new_item_name'              => __( 'New Item Name', 'user-point' ),
            'add_new_item'               => __( 'Add New Item', 'user-point' ),
            'edit_item'                  => __( 'Edit Item', 'user-point' ),
            'update_item'                => __( 'Update Item', 'user-point' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'user-point' ),
            'search_items'               => __( 'Search Items', 'user-point' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'user-point' ),
            'choose_from_most_used'      => __( 'Choose from the most used items', 'user-point' ),
            'not_found'                  => __( 'Not Found', 'user-point' ),
        );
        $rewrite = array(
            'slug'                       => 'charity_category',
            'with_front'                 => true,
            'hierarchical'               => true,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'charity_category', array( 'product' ), $args );

	}

}

new UP_Woo_Taxonomies();
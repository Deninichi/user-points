<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
/**
 * User Points plugin Woocommerce donation product Class
 *
 *
 * @package WordPress
 * @subpackage User_Points
 * @category Plugin
 * @author Denis Nichik
 * @since 1.0.0
 */
class Woo_Donation_Product {

	/**
	 * Constructor function.
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		// Remove Woocommerce hooks
		add_action( 'wp', array( $this, 'up_remove_woo_hooks' ), 15 );
		
		// Donation field HTML
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'up_donation_field_html' ), 15 );

		// Hide donation cat from shop page
		add_action( 'pre_get_posts', array( $this, 'up_pre_get_posts_query' ) );

		//Send custom data in cart item
		add_filter('woocommerce_add_cart_item_data', array( $this, 'up_add_donation_item_data' ), 1, 10);
		add_filter('woocommerce_get_cart_item_from_session', array( $this, 'up_get_donation_cart_items_from_session' ), 1, 3 );

		//Add new calculated price to product in cart
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'up_update_donation_custom_price' ), 1, 1 );

		// Disable payment gatewsays for donation product
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'up_unset_gateway_by_category' ) );

		// Add donation payment to total 
		add_action( 'woocommerce_order_status_completed', array( $this, 'up_add_donation_to_total' ), 10, 1 );

		// Show total donation field on the "general" settings page
		add_filter('admin_init', array( $this, 'up_register_donation_settings_field' ) );

		// Shortcodes
		add_shortcode( 'total_donation_value', array( $this, 'render_total_donation_value' ) );

	} // End __construct()


	/**
	 * HTML code of donation field 
	 * @access public
	 * @since 1.0.0
	 */
	public function up_donation_field_html(){
		
		global $product;

		if ( 2060 === $product->get_id() ) {
			$html = '<div class="donation-field">Donation: $<input type="number" id="donation" step="10" min="10" name="donation" value="10" onkeydown="return false"></div>';
		}
		

		echo $html;
	}


	/**
	 * Hide donation category from shop page
	 * @access public
	 * @since 1.0.0
	 */
	public function up_pre_get_posts_query( $q ){
		
		if (! $q->is_main_query() || ! is_shop() ) return;
		
		if ( ! is_admin( $q ) )
		{
			$q->set( 'tax_query', array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'id',
					'terms' => array( 116 ),
					'operator' => 'NOT IN'
				)
			));
		}
	}

	/**
	 * Add custom donation item data to cart
	 * @access public
	 * @since 1.0.0
	 */
	public function up_add_donation_item_data( $cart_item_data, $product_id ){

		global $woocommerce;

	    $new_value = array();
	    $new_value['_custom_options'] = array( 
	    			'new_price' => $_POST['donation']
	    );
	    if(empty($cart_item_data)) {
	        return $new_value;
	    } else {
	        return array_merge($cart_item_data, $new_value);
	    }
	}


	/**
	 * Get donation cart items from session
	 * @access public
	 * @since 1.0.0
	 */
	public function up_get_donation_cart_items_from_session( $item, $values, $key ){

		if (array_key_exists( '_custom_options', $values ) ) {
	        $item['_custom_options'] = $values['_custom_options'];
	    }
	    return $item;
	}


	/**
	 * Update donation price in the cart
	 * @access public
	 * @since 1.0.0
	 */
	public function up_update_donation_custom_price( $cart_object ) {

	    foreach ( $cart_object->cart_contents as $cart_item_key => $value ) {   

	    	if ( isset( $value['_custom_options']['new_price'] ) ) {
    	    	$value['data']->set_price( $value['_custom_options']['new_price'] );
    	    }    
	    }
	}


	/**
	 * Disable payment gateways for donation category 
	 * @access public
	 * @since 1.0.0
	 */
	public function up_unset_gateway_by_category( $available_gateways ) {

	    global $woocommerce;

		$unset = false;
		$category_ids = array( 116 );
		foreach ( $woocommerce->cart->cart_contents as $key => $values ) {
		    $terms = get_the_terms( $values['product_id'], 'product_cat' );    
		    foreach ( $terms as $term ) {        
		        if ( in_array( $term->term_id, $category_ids ) ) {
		            $unset = true;
		            break;
		        }
		    }
		}
	    
	    if ( $unset == true ) {
	    	unset( $available_gateways['sb_test'] );
	    } else {
	    	unset( $available_gateways['mycred'] );
	    }
	    //var_dump($available_gateways);
	    return $available_gateways;

	}


	/**
	 * Remove Woo hooks for denation
	 * @access public
	 * @since 1.0.0
	 */
	public function up_remove_woo_hooks(){

		global $product;

		if ( 'donation' === $product ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}

	}


	/**
	 * Register new donation settings field
	 * @access public
	 * @since 1.0.0
	 */
	public function up_register_donation_settings_field(){

		register_setting( 'general', 'total_donations', 'esc_attr' );
    	add_settings_field( 'total_donations', '<label for="total_donations">'.__('Total Donations' , 'total_donations' ).'</label>' , array( $this, 'up_donation_settings_field_html'), 'general');

	}


	/**
	 * Show new donation settings field
	 * @access public
	 * @since 1.0.0
	 */
	public function up_donation_settings_field_html(){

		$value = get_option( 'total_donations', '' );
    	echo '<input type="text" id="total_donations" name="total_donations" value="' . $value . '" />';

	}


	/**
	 * Add new donation to total value
	 * @access public
	 * @since 1.0.0
	 */
	public function up_add_donation_to_total( $order_id ){

		// Get Order
		$order   = wc_get_order( $order_id );

		$items_number = 0;
		foreach ( $order->get_items() as $item_id => $item ) {

			if ( 2060 === $item->get_product_id() ) {
				$total_donations = get_option( 'total_donations' );
	
				update_option( 'total_donations', $total_donations + $order->get_total() );
			}

		}

	}


	/**
	 * Render box with total donations value
	 * @access public
	 * @since 1.0.0
	 */
	public function render_total_donation_value(){

		

		ob_start();
		$total_donations = str_split( number_format( get_option( 'total_donations' ) ) );
		?>

		<div class="counting">
			<p>Total Donated as of <?php echo date( 'm/d/Y' ) ?></p>
			<div class="numbers">
				<?php foreach ( $total_donations as $number ){
					
					if ( is_numeric( $number ) ){
						echo '<strong>' . $number . '</strong>';
					}
					else{
						echo $number;
					}

				} ?>
			</div>
			<p>and counting</p>
		</div>

		<?php

		$html = ob_get_contents();
		ob_clean();

		return $html;
	}


}

new Woo_Donation_Product();
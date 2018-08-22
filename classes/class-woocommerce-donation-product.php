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
		add_action( 'init', array( $this, 'up_remove_woo_hooks' ), 15 );
		
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

		// Add donation payment to product total 
		add_action( 'woocommerce_order_status_completed', array( $this, 'up_add_donation_to_product' ), 10, 1 );

		// Show total donation field on the "general" settings page
		add_filter('admin_init', array( $this, 'up_register_donation_settings_field' ) );

		// Shortcodes
		add_shortcode( 'total_donation_value', array( $this, 'render_total_donation_value' ) );

		//Add product donation field
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_product_custom_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_custom_field' ) );

	} // End __construct()


	/**
	 * HTML code of donation field 
	 * @access public
	 * @since 1.0.0
	 */
	public function up_donation_field_html(){
		
		global $product;

		$product_cats = array();
		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		foreach ( $terms as $term ) {
		    $product_cats[] = $term->term_id;
		}

		$html = '';
		if ( in_array( 116, $product_cats ) ) {
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
	    	unset( $available_gateways['braintree_credit_card'] );
	    	unset( $available_gateways['braintree_paypal'] );
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

		if ( is_product() ) {
		
			global $product;

			$product_cats = array();
			$terms = get_the_terms( $product->get_id(), 'product_cat' );
			foreach ( $terms as $term ) {
			    $product_cats[] = $term->term_id;
			}

			if ( in_array( 116, $product_cats ) ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			}

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

		// Exclude categories
		$category_ids = array( 116 );

		// Get Order
		$order   = wc_get_order( $order_id );

		$items_number = 0;
		foreach ( $order->get_items() as $item_id => $item ) {

			$terms = get_the_terms( $item->get_product_id(), 'product_cat' );    

		    foreach ( $terms as $term ) {        
		        if ( in_array( $term->term_id, $category_ids ) ) {
		            $total_donations = get_option( 'total_donations' );
	
					update_option( 'total_donations', $total_donations + $order->get_total() );
		        }
		    }

		}

	}


	/**
	 * Add new donation to total product value.
	 * Each product has separate total donations by this one product
	 * @access public
	 * @since 1.0.0
	 */
	public function up_add_donation_to_product( $order_id ){

		// Exclude categories
		$category_ids = array( 116 );

		// Get Order
		$order   = wc_get_order( $order_id );

		$items_number = 0;
		foreach ( $order->get_items() as $item_id => $item ) {

			$terms = get_the_terms( $item->get_product_id(), 'product_cat' );    

		    foreach ( $terms as $term ) {        
		        if ( in_array( $term->term_id, $category_ids ) ) {

		        	$product = wc_get_product( $item->get_product_id() );
 					$product_total_donations = $product->get_meta( 'up_product_donations' );

					$product->update_meta_data( 'up_product_donations', sanitize_text_field( $product_total_donations + $item->get_total() ) );
					$product->save();
		        }
		    }

		}

	}


	/**
	 * Render box with total donations value
	 * @access public
	 * @since 1.0.0
	 */
	public function render_total_donation_value( $atts ){

		global $post;
		$product = wc_get_product( $post->ID );
 		$product_total_donations = $product->get_meta( 'up_product_donations' );

		$product_sold = get_post_meta( $post->ID, 'total_sales', true );

		if ( ! $product_sold ) {
			$product_sold = 0;
		}

		if ( 'single-left' === $atts['type'] || 'single-top' === $atts['type'] ){
			$total_donations = str_split( number_format( $product_total_donations ) );
		} else {
			$total_donations = str_split( number_format( get_option( 'total_donations' ) ) );
		}
		

		ob_start();
		?>

		<div class="counting <?php echo $atts['type']; ?>">

			<?php if ( 'single-left' === $atts['type'] ): ?>
				<p>Total Donations Raised</p>
			<?php elseif( 'single-top' === $atts['type'] ) : ?>
				<span class="total">
					<img src="<?php echo get_template_directory_uri();?>/images/counter.png"> Total Donated as of <?php echo date( 'm/d/Y' ) ?>
				</span>
			<?php else: ?>
				<p>Total Donated as of <?php echo date( 'm/d/Y' ) ?></p>
			<?php endif; ?>

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

			<?php if ( 'single-left' === $atts['type'] ): ?>
				<p><?php echo $product_sold; ?> Total donations and counting</p>
			<?php elseif( 'single-top' === $atts['type'] ) : ?>
				
			<?php else: ?>
				<p>and counting</p>
			<?php endif; ?>

		</div>

		<?php

		$html = ob_get_contents();
		ob_clean();

		return $html;
	}



	/**
	 * Add new field to woocommerce product
	 * @access public
	 * @since 1.0.0
	 */
	public function add_product_custom_field(){
		global $product;

		$description = sanitize_text_field( 'Current product total donations' );
        $placeholder = sanitize_text_field( '0' );

        $args = array(
            'id'            => 'up_product_donations',
            'label'         => sanitize_text_field( 'Product total donations' ),
            'placeholder'   => $placeholder,
            'desc_tip'      => true,
            'description'   => $description,
        );
        woocommerce_wp_text_input( $args );

	}


	/**
	 * Save new field to woocommerce product
	 * @access public
	 * @since 1.0.0
	 */
	public function save_product_custom_field( $post_id ){
		
		$product = wc_get_product( $post_id );
		$product_donations = isset( $_POST['up_product_donations'] ) ? $_POST['up_product_donations'] : '';
		$product->update_meta_data( 'up_product_donations', sanitize_text_field( $product_donations ) );
		$product->save();

	}


}

new Woo_Donation_Product();
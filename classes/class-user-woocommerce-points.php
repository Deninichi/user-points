<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
/**
 * User Points plugin points calculation Class
 *
 *
 * @package WordPress
 * @subpackage User_Points
 * @category Plugin
 * @author Denis Nichik
 * @since 1.0.0
 */
class Woo_Points {

	/**
	 * Constructor function.
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {
		
		add_action( 'woocommerce_payment_complete', array( $this, 'mycred_pro_reward_order_points' ), 20 );

		//add_action( 'after_setup_theme', array( $this, 'mycred_pro_adjust_woo_rewards'), 110 );

	} // End __construct()

	/**
	 * Set points value after each payment
	 * @access public
	 * @since 1.0.0
	 */
	public function mycred_pro_reward_order_points( $order_id ){

		if ( ! function_exists( 'mycred' ) ) return;

		// Get Order
		$order   = wc_get_order( $order_id );

		$items_number = 0;
		foreach ( $order->get_items() as $item_id => $item ) {
			$quantity = $item->get_quantity();
			$items_number += $quantity;
		}

		// Load myCRED
		$mycred  = mycred();

		// Do not payout if order was paid using points
		if ( $order->get_payment_method() == 'mycred' ) return;

		if ( ! $mycred ) return;

		// Make sure user only gets points once per order
		if ( $mycred->has_entry( 'reward', $order_id, $order->get_user_id() ) ) return;

		// Reward example 10 * order items in points.
		$reward  = 10 * $items_number;

		// Add reward
		$mycred->add_creds(
			'reward',
			$order->get_user_id(),
			$reward,
			'Reward for store purchase',
			$order_id,
			array( 'ref_type' => 'post' )
		);
	}


	/**
	 * Set points value after each payment
	 * @access public
	 * @since 1.0.0
	 */
	// public function mycred_pro_adjust_woo_rewards() {

	// 	remove_action( 'woocommerce_payment_complete',    'mycred_woo_payout_rewards' );
	// 	add_action( 'woocommerce_order_status_completed', 'mycred_woo_payout_rewards' );

	// }

}

new Woo_Points();
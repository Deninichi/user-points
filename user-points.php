<?php 

/**
 * Plugin Name: User Points
 * Plugin URI: http://deninichi.com/user-points/
 * Description: MyCred user points add-on
 * Version: 1.0.0
 * Author: Denis Nichik
 * Author URI: http://deninichi.com/
 * Requires at least: 4.0.0
 * Tested up to: 4.0.0
 *
 * Text Domain: user-points
 * Domain Path: /languages/
 *
 * @package User_Points
 * @category Core
 * @author Denis Nichik
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Returns the main instance of User_Points to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object User_Points
 */
function User_Points() {
	return User_Points::instance();
} // End User_Points()
add_action( 'plugins_loaded', 'User_Points' );


/**
 * Main User_Points Class
 *
 * @class User_Points
 * @version	1.0.0
 * @since 1.0.0
 * @package	User_Points
 * @author Denis Nichik
 */
final class User_Points {

	/**
	 * User_Points The single instance of User_Points.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	
	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The plugin directory URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $plugin_url;

	/**
	 * The plugin directory path.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $plugin_path;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct () {
		$this->token 			= 'user-point';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.0';

		// Woocommerce points
		require_once( 'classes/class-user-woocommerce-points.php' );

		// Woocommerce donation product
		require_once( 'classes/class-woocommerce-donation-product.php' );

	} // End __construct()


	/**
	 * Main User_Points Instance
	 *
	 * Ensures only one instance of User_Points is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see User_Points()
	 * @return Main User_Points instance
	 */
	public static function instance () {
		
		if ( is_null( self::$_instance ) ){
			self::$_instance = new self();
		}

		return self::$_instance;
	} // End instance()


} // End Class

$userPoints = new User_Points();


/**
 * Plugin activation hook
 * @access public
 * @since 1.0.0
 */
function up_activate() {

	if ( ! get_option( "total_donations" ) ) {
		add_option( "total_donations", 0, '', 'yes' );
	}

}
register_activation_hook( __FILE__, 'up_activate' );
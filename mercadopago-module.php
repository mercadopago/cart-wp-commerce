<?php

/**
 * Plugin Name: WPeComm Mercado Pago Module
 * Plugin URI: https://github.com/mercadopago/cart-wp-commerce
 * Description: This is the <strong>oficial</strong> module of Mercado Pago for WP-eCommerce plugin. This module enables WP-eCommerce to use Mercado Pago as a payment Gateway for purchases made in your e-commerce store.
 * Author: Mercado Pago
 * Author URI: https://www.mercadopago.com.br/developers/
 * Developer: Marcelo T. Hama (marcelo.hama@mercadolibre.com)
 * Copyright: Copyright(c) MercadoPago [https://www.mercadopago.com]
 * Version: 4.2.5
 * License: https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * Text Domain: wpecomm-mercadopago-module
 * Domain Path: /languages/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if class is already loaded
if ( !class_exists( 'WPeComm_MercadoPago_Module' ) ) :

// WPeCommerce MercadoPago Module main class
class WPeComm_MercadoPago_Module {

	const VERSION = '4.2.5';

	// Singleton design pattern
	protected static $instance = null;
	public static function initMercadoPagoGatewayClass() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	// Class constructor
	private function __construct() {
		// load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain_wpecomm' ) );
		// verify if WPeCommerce is already installed
		if ( class_exists( 'WP_eCommerce' ) ) {
			$this->recurse_copy(
				plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-languages',
				dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-languages'
			);
			$this->recurse_copy(
				plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-images',
				dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-images'
			);
			$this->recurse_copy(
				plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-lib',
				dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-lib'
			);
			copy(
				plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-basic.php',
				dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-basic.php'
			);
			copy(
				plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-custom.php',
				dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-custom.php'
			);
			copy(
				plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-ticket.php',
				dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-ticket.php'
			);
			register_deactivation_hook( __FILE__, array( $this, "on_deactivation" ) );
		} else {
			add_action( 'admin_notices', array( $this, 'notifyWPeCommerceMiss' ) );
		}
	}
	// Custom deactivation hook
	public function on_deactivation() {
		if ( !current_user_can( 'activate_plugins' ) )
			return;
		if ( file_exists( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-ticket.php' ) ) {
			unlink( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-ticket.php' );
		}
		if ( file_exists( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-custom.php' ) ) {
			unlink( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-custom.php' );
		}
		if ( file_exists( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-basic.php' ) ) {
			unlink( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-basic.php' );
		}
		if ( file_exists( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-lib' ) ) {
			$this->deleteDir( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-lib' );
		}
		if ( file_exists( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-images' ) ) {
			$this->deleteDir( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-images' );
		}
		if ( file_exists( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-languages' ) ) {
			$this->deleteDir( dirname(plugin_dir_path( __FILE__ )) . '/wp-e-commerce/wpsc-merchants/mercadopago-languages' );
		}
		flush_rewrite_rules();
	}

	public function deleteDir($path) {
		if ( is_dir( $path ) === true ) {
			$files = array_diff( scandir( $path ), array( '.', '..' ) );
			foreach ( $files as $file ) {
				$this->deleteDir( realpath( $path ) . '/' . $file );
			}
			return rmdir($path);
		} else if ( is_file( $path ) === true ) {
			return unlink( $path );
		}
		return false;
	}

	// Places a warning error to notify user that WP-eCommerce is missing
	public function notifyWPeCommerceMiss() {
		echo
			'<div class="error"><p>' . sprintf(
				__( 'WPeComm Mercado Pago Module depends on the last version of %s to execute!', 'wpecomm-mercadopago-module' ),
				'<a href="https://wordpress.org/extend/plugins/wp-e-commerce/">' . 'WP-eCommerce' . '</a>'
			) .
			'</p></div>';
	}

	// Multi-language plugin
	public function load_plugin_textdomain_wpecomm() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpecomm-mercadopago-module' );
		load_textdomain(
			'wpecomm-mercadopago-module',
			trailingslashit(WP_LANG_DIR ) . 'wpecomm-mercadopago-module/wpecomm-mercadopago-module-' . $locale . '.mo'
		);
		load_plugin_textdomain( 'wpecomm-mercadopago-module', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public static function getTemplatesPath() {
		return plugin_dir_path( __FILE__ ) . 'templates/';
	}

	public function recurse_copy($src, $dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
		 		if ( is_dir($src . '/' . $file) ) {
					$this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
		 		} else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	/**
	 * Summary: Builds up the array for the mp_install table, with info related with checkout.
	 * Description: Builds up the array for the mp_install table, with info related with checkout.
	 * @return an array with the module informations.
	 */
	public static function get_common_settings() {

		$infra_data = array(
			'module_version' => WPeComm_MercadoPago_Module::VERSION,
			'platform' => 'WPeCommerce',
			'platform_version' => get_option( 'wpsc_version', '0' ),
			'code_version' => phpversion(),
			'so_server' => PHP_OS
		);

		return $infra_data;

	}

	/**
	 * Summary: Get client id from access token.
	 * Description: Get client id from access token.
	 * @return the client id.
	 */
	public static function get_client_id( $at ) {
		$t = explode ( '-', $at );
		if ( count( $t ) > 0 ) {
			return $t[1];
		}
		return '';
	}

}

add_action( 'plugins_loaded', array( 'WPeComm_MercadoPago_Module', 'initMercadoPagoGatewayClass' ), 0 );

// Add settings link on plugin page.
function wpecomm_mercadopago_settings_link( $links ) {

	$option_page = 'options-general.php?page=wpsc-settings&tab=gateway&payment_gateway_id=';
	$plugin_links = array();

	$plugin_links[] = '<a href="' . esc_url( admin_url(
		$option_page . 'WPSC_Merchant_MercadoPago_Basic' ) ) . '">' .
		__( 'Basic Checkout', 'wpecomm-mercadopago-module' ) . '</a>';
	$plugin_links[] = '<a href="' . esc_url( admin_url(
		$option_page . 'WPSC_Merchant_MercadoPago_Custom' ) ) . '">' .
		__( 'Custom Checkout', 'wpecomm-mercadopago-module' ) . '</a>';
	$plugin_links[] = '<a href="' . esc_url( admin_url(
		$option_page . 'WPSC_Merchant_MercadoPago_Ticket' ) ) . '">' .
		__( 'Ticket', 'wpecomm-mercadopago-module' ) . '</a>';

	$plugin_links[] = '<br><a target="_blank" href="' .
		'https://wordpress.org/support/view/plugin-reviews/wpecomm-mercado-pago-module?filter=5#postform">' .
		sprintf(
			__( 'Rate Us', 'wpecomm-mercadopago-module' ) . ' %s', '&#9733;&#9733;&#9733;&#9733;&#9733;'
		) . '</a>';
	$plugin_links[] = '<a target="_blank" href="' .
		'https://wordpress.org/support/plugin/wpecomm-mercado-pago-module#postform">' .
		__( 'Report Issue', 'wpecomm-mercadopago-module' ) . '</a>';

	return array_merge( $plugin_links, $links );

}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wpecomm_mercadopago_settings_link' );

endif;

?>

<?php

/**
* Plugin Name: WPeComm Mercado Pago Module
 * Plugin URI: https://github.com/mercadopago/cart-wp-commerce
 * Description: This is the <strong>oficial</strong> module of Mercado Pago for WP-eCommerce plugin. This module enables WP-eCommerce to use Mercado Pago as a payment Gateway for purchases made in your e-commerce store.
 * Author: Mercado Pago
 * Author URI: https://www.mercadopago.com.br/developers/
 * Developer: Marcelo T. Hama (marcelo.hama@mercadolibre.com)
 * Copyright: Copyright(c) MercadoPago [http://www.mercadopago.com]
 * Version: 4.1.0
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * Text Domain: wpecomm-mercadopago-module
 * Domain Path: /languages/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// Check if class is already loaded
if ( !class_exists( 'WPeComm_MercadoPago_Module' ) ) :

// WPeCommerce MercadoPago Module main class
class WPeComm_MercadoPago_Module {

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
				$this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-languages'
			);
      $this->recurse_copy(
        plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-images',
        $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-images'
      );
      $this->recurse_copy(
        plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-lib',
        $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-lib'
      );
      copy(
        plugin_dir_path( __FILE__ ) . 'wpecomm-mercado-pago-module/mercadopago-basic.php',
        $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-basic.php'
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
	  if ( file_exists( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-basic.php' ) ) {
			unlink( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-basic.php' );
	  }
    if ( file_exists( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-lib' ) ) {
      $this->deleteDir( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-lib' );
    }
    if ( file_exists( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-images' ) ) {
      $this->deleteDir( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-images' );
    }
    if ( file_exists( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-languages' ) ) {
      $this->deleteDir( $this->fs_get_wp_config_path() . '/plugins/wp-e-commerce/wpsc-merchants/mercadopago-languages' );
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
				'<a href="http://wordpress.org/extend/plugins/wp-e-commerce/">' . 'WP-eCommerce' . '</a>'
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

	public function fs_get_wp_config_path() {
    $base = dirname(__FILE__);
    $path = false;

    if (@file_exists(dirname(dirname($base))."/wp-content")) {
        $path = dirname(dirname($base))."/wp-content";
    } else
    if (@file_exists(dirname(dirname(dirname($base)))."/wp-content")) {
        $path = dirname(dirname(dirname($base)))."/wp-content";
    } else
    $path = false;
    if ($path != false) {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
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

}

add_action( 'plugins_loaded', array( 'WPeComm_MercadoPago_Module', 'initMercadoPagoGatewayClass' ), 0 );

// Add settings link on plugin page
function wpecomm_mercadopago_settings_link( $links ) {
	$plugin_links = array();
	$plugin_links[] = '<a href="' . esc_url( admin_url(
		'options-general.php?page=wpsc-settings&tab=gateway&payment_gateway_id=WPSC_Merchant_MercadoPago_Basic' ) ) . '">' .
		__( 'Basic Checkout', 'wpecomm-mercadopago-module' ) .
	'</a>';
	/*$plugin_links[] = '<a href="' . esc_url( admin_url(
		'admin.php?page=wc-settings&tab=checkout&section=WPeComm_MercadoPagoCustom_Gateway' ) ) . '">' .
		__( 'Custom Checkout', 'wpecomm-mercadopago-module' ) .
	'</a>';
	$plugin_links[] = '<a href="' . esc_url( admin_url(
		'admin.php?page=wc-settings&tab=checkout&section=WPeComm_MercadoPagoTicket_Gateway' ) ) . '">' .
		__( 'Ticket', 'wpecomm-mercadopago-module' ) .
	'</a>';*/
	return array_merge( $plugin_links, $links );
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wpecomm_mercadopago_settings_link' );

endif;

?>

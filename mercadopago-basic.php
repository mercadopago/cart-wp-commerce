<?php

/**
 * Plugin Name: WPeComm Mercado Pago Module
 * Plugin URI: https://github.com/mercadopago/cart-wp-commerce
 * Description: This is the <strong>oficial</strong> module of Mercado Pago for WP-eCommerce plugin. This module enables WP-eCommerce to use Mercado Pago as a payment Gateway for purchases made in your e-commerce store.
 * Author: Mercado Pago
 * Author URI: https://www.mercadopago.com.br/developers/
 * Developer: Andre Fuhrman (andrefuhrman@gmail.com) | Edited: Matias Gordon (matias.gordon@mercadolibre.com), Marcelo T. Hama (marcelo.hama@mercadolibre.com)
 * Copyright: Copyright(c) MercadoPago [http://www.mercadopago.com]
 * Version: 4.0.0
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * Text Domain: woocommerce-mercadopago-module
 * Domain Path: /languages/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$nzshpcrt_gateways[$num] = array(
	'name' =>  __( 'Mercado Pago Basic Checkout', 'wpecomm-mercadopago-module' ),
	'api_version' => 2.0,
	//'image' => WPSC_URL . '/images/mercadopago.gif',
	'class_name' => 'WPSC_Merchant_MercadoPago_Basic',
	'has_recurring_billing' => false,
	'wp_admin_cannot_cancel' => true,
	'display_name' => __( 'Mercado Pago Basic Checkout', 'wpecomm-mercadopago-module' ),
	'requirements' => array(
		/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		'php_version' => 5.6,
		 /// for modules that may not be present, like curl
		'extra_modules' => array()
	),
	'internalname' => 'WPSC_Merchant_MercadoPago_Basic',

	// All array members below here are legacy, and use the code in mercadopago_multiple.php
	'form' => 'form_mercadopago_basic',
	'submit_function' => 'submit_mercadopago_basic',
	'payment_type' => 'mercadopago',
	'supported_currencies' => array(
		'currency_list' =>  array( 'AUD', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'VEF', 'USD' ),
		'option_name' => 'mercadopago_curcode'
	)
);

class WPSC_Merchant_MercadoPago_Basic extends wpsc_merchant {

	public function __construct() {
		// load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain_mp' ) );
	}

	// Multi-language plugin
	function load_plugin_textdomain_mp() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpecomm-mercadopago-module' );
		load_textdomain(
			'wpecomm-mercadopago-module',
			trailingslashit(WP_LANG_DIR ) . 'languages/wpecomm-mercadopago-module-' . $locale . '.mo'
		);
		load_plugin_textdomain( 'wpecomm-mercadopago-module', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

}

/**
 * Saving of Mercado Pago Basic Checkout Settings
 * @access public
 *
 * @since 4.0
 */
function submit_mercadopago_basic() {
	/*if ( isset ( $_POST['mercadopago_certified_apiuser'] ) ) {
		update_option( 'mercadopago_certified_apiuser', $_POST['mercadopago_certified_apiuser'] );
	}
	if ( isset ( $_POST['mercadopago_certified_apipass'] ) ) {
		update_option( 'mercadopago_certified_apipass', $_POST['mercadopago_certified_apipass'] );
	}
	if ( isset ( $_POST['mercadopago_curcode'] ) ) {
		update_option( 'mercadopago_curcode', $_POST['mercadopago_curcode'] );
	}
	if ( isset ( $_POST['mercadopago_certified_apisign'] ) ) {
		update_option( 'mercadopago_certified_apisign', $_POST['mercadopago_certified_apisign'] );
	}
	if ( isset ( $_POST['mercadopago_certified_server_type'] ) ) {
		update_option( 'mercadopago_certified_server_type', $_POST['mercadopago_certified_server_type'] );
	}
	if ( isset ( $_POST['mercadopago_ipn'])) {
		update_option( 'mercadopago_ipn', (int)$_POST['mercadopago_ipn'] );
	}*/

	return true;
}

/**
 * Form Basic Checkout Returns the Settings Form Fields
 * @access public
 *
 * @since 4.0
 * @return $output string containing Form Fields
 */
function form_mercadopago_basic() {
	global $wpdb, $wpsc_gateways;

	$serverType1 = '';
	$serverType2 = '';
	$select_currency[ get_option( 'mercadopago_curcode' ) ] = "selected='selected'";

	if ( get_option( 'mercadopago_certified_server_type' ) == 'sandbox' ) {
		$serverType1 = "checked='checked'";
	} elseif ( get_option( 'mercadopago_certified_server_type' ) == 'production' ) {
		$serverType2 = "checked='checked'";
	}

	$mercadopago_ipn = get_option( 'mercadopago_ipn' );
	$output = "
	<tr>
		<td>" . __('Mercado Pago Client_id', 'wpecomm-mercadopago-module' ) . "
		</td>
		<td>
			<input type='text' size='40' value='" . get_option( 'mercadopago_certified_apiuser') . "' name='mercadopago_certified_apiuser' />
		</td>
	</tr>
	<tr>
		<td>" . __('Mercado Pago Client_secret', 'wpecomm-mercadopago-module' ) . "
		</td>
		<td>
			<input type='text' size='40' value='" . get_option( 'mercadopago_certified_apipass') . "' name='mercadopago_certified_apipass' />
		</td>
	</tr>
	<tr>
		<td>" . __('Server Type', 'wpecomm-mercadopago-module' ) . "
		</td>
		<td>
			<input $serverType1 type='radio' name='mercadopago_certified_server_type' value='sandbox' id='mercadopago_certified_server_type_sandbox' /> <label for='mercadopago_certified_server_type_sandbox'>" . __('Sandbox (For testing)', 'wpecomm-mercadopago-module' ) . "</label> &nbsp;
			<input $serverType2 type='radio' name='mercadopago_certified_server_type' value='production' id='mercadopago_certified_server_type_production' /> <label for='mercadopago_certified_server_type_production'>" . __('Production', 'wpecomm-mercadopago-module' ) . "</label>
			<p class='description'>
				" . sprintf( __( "Only use the sandbox server if you have a sandbox account with mercadopago. You can find out more about this <a href='%s'>here</a>.", 'wpecomm-mercadopago-module' ), esc_url( 'https://cms.mercadopago.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/howto_testing_sandbox' ) ) . "
			</p>
		</td>
	</tr>

	<tr>
		<td>
		" . __( 'IPN', 'wpecomm-mercadopago-module' ) . "
		</td>
		<td>
			<input type='radio' value='1' name='mercadopago_ipn' id='mercadopago_ipn1' " . checked( $mercadopago_ipn, 1, false ) . " /> <label for='mercadopago_ipn1'>".__('Yes', 'wpecomm-mercadopago-module')."</label> &nbsp;
			<input type='radio' value='0' name='mercadopago_ipn' id='mercadopago_ipn2' " . checked( $mercadopago_ipn, 0, false ) . " /> <label for='mercadopago_ipn2'>".__('No', 'wpecomm-mercadopago-module')."</label>
			<p class='description'>
			" . __( "IPN (instant payment notification) will automatically update your sales logs to 'Accepted payment' when a customer's payment is successful. For IPN to work you also need to have IPN turned on in your mercadopago settings. If it is not turned on, the sales will remain as 'Order Pending' status until manually changed. It is highly recommended using IPN, especially if you are selling digital products.", 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
  	</tr>\n";

	$mercadopago_ipn = get_option( 'mercadopago_ipn' );
	$store_currency_code = WPSC_Countries::get_currency_code( absint( get_option( 'currency_type' ) ) );

	$current_currency = get_option( 'mercadopago_curcode' );

	if ( ( $current_currency == '' ) && in_array( $store_currency_code, $wpsc_gateways['WPSC_Merchant_MercadoPago_Basic']['supported_currencies']['currency_list'] ) ) {
		update_option( 'mercadopago_curcode', $store_currency_code );
		$current_currency = $store_currency_code;
	}
	if ( $current_currency != $store_currency_code ) {
		$output .= "<tr> <td colspan='2'><strong class='form_group'>" . __( 'Currency Converter', 'wpecomm-mercadopago-module' ) . "</td> </tr>
		<tr>
			<td colspan='2'>
			" . __( 'Your website is using a currency not accepted by mercadopago. Please select an accepted currency using the drop down menu below. Buyers on your site will still pay in your local currency. However, we will convert the currency and send the order through to mercadopago using the currency you choose below.', 'wpecomm-mercadopago-module' ) . "
			</td>
		</tr>

		<tr>
			<td>
			" . __('Convert to', 'wpecomm-mercadopago-module' ) . "
			</td>
			<td>
				<select name='mercadopago_curcode'>\n";

		if ( ! isset( $wpsc_gateways['WPSC_Merchant_MercadoPago_Basic']['supported_currencies']['currency_list'] ) ) {
			$wpsc_gateways['WPSC_Merchant_MercadoPago_Basic']['supported_currencies']['currency_list'] = array();
		}


		// TODO verify that this query is correct, the WPSC_Countries call that repalced it was coded to duplicate the results, but
		// why are currecies of inactive countries being returned??
		//$old_currency_list = $wpdb->get_results( "SELECT DISTINCT `code`, `currency` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `code` IN ('" . implode( "','", $mercadopago_currency_list ) . "')", ARRAY_A );
		$mercadopago_currency_list = array_map( 'esc_sql', $wpsc_gateways['WPSC_Merchant_MercadoPago_Basic']['supported_currencies']['currency_list'] );
		$currency_list = WPSC_Countries::get_currencies( true );
		$currency_codes_in_commmon = array_intersect( array_keys( $currency_list ), $mercadopago_currency_list );

		foreach ( $currency_codes_in_commmon as $currency_code ) {

			$currency_item = $currency_list[$currency_code];

			if ( in_array( $currency_code, $mercadopago_currency_list ) ) {
				$selected_currency = '';

				if ( $current_currency == $currency_item['code'] ) {
					$selected_currency = "selected='selected'";
				}

				$output .= "<option ".$selected_currency." value='{$currency_item['code']}'>{$currency_item['currency']}</option>";
			}
		}

		$output .= "
				</select>
			</td>
		</tr>\n";
	}

	$output .="
	<tr>
		<td colspan='2'>
			<p class='description'>
	 		" . sprintf( __( "For more help configuring mercadopago Basic Checkout, please read our documentation <a href='%s'>here</a>", 'wpecomm-mercadopago-module' ), esc_url( 'http://docs.wpecommerce.org/documentation/mercadopago-basic-checkout/' ) ) . "
	 		</p>
		</td>
   	</tr>\n";

	return $output;
}

new WPSC_Merchant_MercadoPago_Basic();

?>

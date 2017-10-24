<?php

/**
 * Part of WPeComm Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Andre Fuhrman (andrefuhrman@gmail.com) | Edited: Matias Gordon (matias.gordon@mercadolibre.com), Marcelo T. Hama (marcelo.hama@mercadolibre.com)
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License: https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * Text Domain: wpecomm-mercadopago-module
 * Domain Path: /mercadopago-languages/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once "mercadopago-lib/mercadopago.php";

$nzshpcrt_gateways[$num] = array(
	'name' =>  __( 'Mercado Pago - Basic Checkout', 'wpecomm-mercadopago-module' ),
	'class_name' => 'WPSC_Merchant_MercadoPago_Basic',
	'display_name' => __( 'Mercado Pago - Basic Checkout', 'wpecomm-mercadopago-module' ),
	'requirements' => array(
		/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		'php_version' => 5.6,
		 /// for modules that may not be present, like curl
		'extra_modules' => array()
	),
	'internalname' => 'WPSC_Merchant_MercadoPago_Basic',
	// All array members below here are legacy, and use the code in mercadopago_multiple.php
	'form' => 'form_mercadopago_basic',
	'function' => 'function_mercadopago_basic',
	'submit_function' => 'submit_mercadopago_basic',
	'payment_type' => 'mercadopago'
);

class WPSC_Merchant_MercadoPago_Basic extends wpsc_merchant {

	function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain_wpecomm' ) );
	}

	// Multi-language plugin
	function load_plugin_textdomain_wpecomm() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpecomm-mercadopago-module' );
		load_textdomain(
			'wpecomm-mercadopago-module',
			trailingslashit( WP_LANG_DIR ) . 'mercadopago-languages/wpecomm-mercadopago-module-' . $locale . '.mo'
		);
		load_plugin_textdomain( 'wpecomm-mercadopago-module', false, dirname( plugin_basename( __FILE__ ) ) . '/mercadopago-languages/' );
	}

	public static function callback_submit_options_basic() {
		$client_id =  get_option( 'mercadopago_certified_clientid' );
		$client_secret = get_option( 'mercadopago_certified_clientsecret' );
		if ( ! empty( $client_id ) && ! empty( $client_secret ) ) {
			$mp = new MP( $client_id, $client_secret );
			$access_token = $mp->get_access_token();
			// analytics
			if ( $access_token != null ) {
				$checkout_basic = in_array( 'WPSC_Merchant_MercadoPago_Basic', get_option( 'custom_gateway_options' ) );
				$infra_data = WPeComm_MercadoPago_Module::get_common_settings();
				$infra_data['checkout_basic'] = ( $checkout_basic ? 'true' : 'false' );
				$response = $mp->analytics_save_settings( $infra_data );
			}
		}
	}

	public static function add_checkout_script_basic() {
		if ( in_array( 'WPSC_Merchant_MercadoPago_Basic', (array)get_option( 'custom_gateway_options' ) ) ) {
			$payments = array();
			$gateways = (array)get_option( 'custom_gateway_options' );
			foreach ( $gateways as $g ) {
				$payments[] = $g;
			}
			$payments = str_replace( '-', '_', implode( ', ', $payments ) );
			$email = wp_get_current_user()->user_email;

			echo '<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
				<script type="text/javascript">
					window.onload = function() {
						if (document.getElementById("wpsc_shopping_cart_container")) {
							var MA = ModuleAnalytics;
							MA.setToken("' . get_option('mercadopago_certified_clientid') . '");
							MA.setPlatform("WPeCommerce");
							MA.setPlatformVersion("' . get_option( 'wpsc_version', '0' ) . '");
							MA.setModuleVersion("' . WPeComm_MercadoPago_Module::VERSION . '");
							MA.setPayerEmail("' . ( $email != null ? $email : "" ) . '");
							MA.setUserLogged( ' . ( empty( $email ) ? 0 : 1 ) . ' );
							MA.setInstalledModules("' . $payments . '");
							MA.post();
						}
					};
				</script>';
		}
	}

	public static function update_checkout_status_basic( $purchase_log_id ) {
		if ( get_post_meta( $purchase_log_id, '_used_gateway', true ) != 'WPSC_Merchant_MercadoPago_Basic' )
			return;
		if ( in_array( 'WPSC_Merchant_MercadoPago_Basic', (array)get_option( 'custom_gateway_options' ) ) ) {
			echo '<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				var MA = ModuleAnalytics;
				MA.setToken("' . get_option('mercadopago_certified_clientid') . '");
				MA.setPaymentType("basic");
				MA.setCheckoutType("basic");
				MA.put();
			</script>';
		}
	}

}

add_action(
	'wpsc_submit_gateway_options',
	array( 'WPSC_Merchant_MercadoPago_Basic', 'callback_submit_options_basic' )
);

add_action(
	'wpsc_bottom_of_shopping_cart',
	array( 'WPSC_Merchant_MercadoPago_Basic', 'add_checkout_script_basic' )
);

add_action(
	'wpsc_confirm_checkout',
	array( 'WPSC_Merchant_MercadoPago_Basic', 'update_checkout_status_basic' )
);

/**
 * Saving of Mercado Pago Basic Checkout Settings
 * @access public
 *
 * @since 4.0
 */
function submit_mercadopago_basic() {
	if (isset($_POST['mercadopago_certified_clientid'])) {
		update_option('mercadopago_certified_clientid', trim($_POST['mercadopago_certified_clientid']));
	}
	if (isset($_POST['mercadopago_certified_clientsecret'])) {
		update_option('mercadopago_certified_clientsecret', trim($_POST['mercadopago_certified_clientsecret']));
	}
	if (isset($_POST['mercadopago_certified_siteid'])) {
		update_option('mercadopago_certified_siteid', trim($_POST['mercadopago_certified_siteid']));
	}
	// TODO: find a better way to pass translated fields to customer view
	if (isset($_POST['mercadopago_certified_checkoutmessage1'])) {
		update_option('mercadopago_certified_checkoutmessage1', trim($_POST['mercadopago_certified_checkoutmessage1']));
	}
	if (isset($_POST['mercadopago_certified_checkoutmessage2'])) {
		update_option('mercadopago_certified_checkoutmessage2', trim($_POST['mercadopago_certified_checkoutmessage2']));
	}
	if (isset($_POST['mercadopago_certified_checkoutmessage3'])) {
		update_option('mercadopago_certified_checkoutmessage3', trim($_POST['mercadopago_certified_checkoutmessage3']));
	}
	if (isset($_POST['mercadopago_certified_checkoutmessage4'])) {
		update_option('mercadopago_certified_checkoutmessage4', trim($_POST['mercadopago_certified_checkoutmessage4']));
	}
	if (isset($_POST['mercadopago_certified_checkoutmessage5'])) {
		update_option('mercadopago_certified_checkoutmessage5', trim($_POST['mercadopago_certified_checkoutmessage5']));
	}
	if (isset($_POST['mercadopago_certified_checkoutmessage6'])) {
		update_option('mercadopago_certified_checkoutmessage6', trim($_POST['mercadopago_certified_checkoutmessage6']));
	}
	if (isset($_POST['mercadopago_certified_url_sucess'])) {
		update_option('mercadopago_certified_url_sucess', trim($_POST['mercadopago_certified_url_sucess']));
	}
	if (isset($_POST['mercadopago_certified_url_pending'])) {
		update_option('mercadopago_certified_url_pending', trim($_POST['mercadopago_certified_url_pending']));
	}
	if (isset($_POST['mercadopago_certified_istestuser'])) {
		update_option('mercadopago_certified_istestuser', trim($_POST['mercadopago_certified_istestuser']));
	}
	if (isset($_POST['mercadopago_certified_currencyratio'])) {
		update_option('mercadopago_certified_currencyratio', trim($_POST['mercadopago_certified_currencyratio']));
	}
	if (isset($_POST['mercadopago_certified_description'])) {
		update_option('mercadopago_certified_description', trim($_POST['mercadopago_certified_description']));
	}
	if (isset($_POST['mercadopago_certified_category'])) {
		update_option('mercadopago_certified_category', trim($_POST['mercadopago_certified_category']));
	}
	if (isset($_POST['mercadopago_certified_invoiceprefix'])) {
		update_option('mercadopago_certified_invoiceprefix', trim($_POST['mercadopago_certified_invoiceprefix']));
	}
	if (isset($_POST['mercadopago_certified_typecheckout'])) {
		update_option('mercadopago_certified_typecheckout', trim($_POST['mercadopago_certified_typecheckout']));
	}
	if (isset($_POST['mercadopago_certified_iframewidth'])) {
		update_option('mercadopago_certified_iframewidth', trim($_POST['mercadopago_certified_iframewidth']));
	}
	if (isset($_POST['mercadopago_certified_iframeheight'])) {
		update_option('mercadopago_certified_iframeheight', trim($_POST['mercadopago_certified_iframeheight']));
	}
	if (isset($_POST['mercadopago_certified_autoreturn'])) {
		update_option('mercadopago_certified_autoreturn', trim($_POST['mercadopago_certified_autoreturn']));
	}
	if (isset($_POST['mercadopago_certified_currencyconversion'])) {
		update_option('mercadopago_certified_currencyconversion', trim($_POST['mercadopago_certified_currencyconversion']));
	}
	if (isset($_POST['mercadopago_certified_maxinstallments'])) {
		update_option('mercadopago_certified_maxinstallments', trim($_POST['mercadopago_certified_maxinstallments']));
	}
	if (isset($_POST['mercadopago_certified_exmethods']) && isset($_POST['mercadopago_certified_paymentmethods'])) {
		$paymentmethods = explode(",", $_POST['mercadopago_certified_paymentmethods']);
		$methods = $_POST['mercadopago_certified_exmethods'];
		if (!in_array('account_money', $methods)) {
			array_push($methods, 'account_money');
		}
		$result = array_diff($paymentmethods, $methods);
		update_option('mercadopago_certified_exmethods', implode(",", $result));
		update_option('mercadopago_certified_paymentmethods', $_POST['mercadopago_certified_paymentmethods']);
	} else {
		update_option('mercadopago_certified_exmethods', '');
		update_option('mercadopago_certified_paymentmethods', '');
	}
	/*if (isset($_POST['mercadopago_certified_exmethods'])) {
		$methods = implode(",", $_POST['mercadopago_certified_exmethods']);
		update_option('mercadopago_certified_exmethods', $methods);
	} else {
		update_option('mercadopago_certified_exmethods', '');
	}*/
	if ( isset( $_POST['mercadopago_certified_siteid'] ) ) {
		$mp = new MP(
			get_option( 'mercadopago_certified_clientid' ),
			get_option( 'mercadopago_certified_clientsecret' )
		);
		$access_token = $mp->get_access_token();
		// analytics
		if ( $access_token != null ) {
			$infra_data = WPeComm_MercadoPago_Module::get_common_settings();
			$infra_data['mercado_envios'] = 'false';
			$infra_data['two_cards'] = ( $_POST['mercadopago_certified_twocards'] == 'active' ? 'true' : 'false' );
			$response = $mp->analytics_save_settings( $infra_data );
		}
		if ( $access_token != null && ! empty( $_POST['mercadopago_certified_twocards'] ) ) {
			$payment_split_mode = trim( $_POST['mercadopago_certified_twocards'] );
			$response = $mp->set_two_cards_mode( $payment_split_mode );
		}
	}
	/*if (isset($_POST['mercadopago_certified_sandbox'])) {
		update_option('mercadopago_certified_sandbox', trim($_POST['mercadopago_certified_sandbox']));
	}*/
	if (isset($_POST['mercadopago_certified_debug'])) {
		update_option('mercadopago_certified_debug', trim($_POST['mercadopago_certified_debug']));
	}
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

	$result = validateCredentials(
		get_option('mercadopago_certified_clientid'),
		get_option('mercadopago_certified_clientsecret')
	);
	$store_currency = WPSC_Countries::get_currency_code(absint(get_option('currency_type')));

	// Trigger API to get payment methods and site_id, also validates Client_id/Client_secret.
	$currency_message = "";
	if ($result['is_valid'] == true) {
		try {
			// checking the currency
			if (!isSupportedCurrency($result['site_id'])) {
				if (get_option('mercadopago_certified_currencyconversion') == 'inactive') {
					$result['currency_ratio'] = -1;
					$currency_message .= '<img width="12" height="12" src="' .
						plugins_url( 'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ ) ) . '">' .
						' ' . __( 'ATTENTION: The currency', 'wpecomm-mercadopago-module' ) . ' ' . $store_currency .
						' ' . __( 'defined in WPeCommerce is different from the one used in your credentials country.<br>The currency for transactions in this payment method will be', 'wpecomm-mercadopago-module' ) .
						' ' . getCurrencyId( $result['site_id'] ) . ' (' . getCountryName( $result['site_id'] ) . ').' .
						' ' . __( 'Currency conversions should be made outside this module.', 'wpecomm-mercadopago-module' );
				} else if (get_option('mercadopago_certified_currencyconversion') == 'active' && $result['currency_ratio'] != -1 ) {
					$currency_message .= '<img width="12" height="12" src="' .
						plugins_url( 'wpsc-merchants/mercadopago-images/check.png', plugin_dir_path( __FILE__ ) ) . '">' .
						' ' . __( 'CURRENCY CONVERTED: The currency conversion ratio from', 'wpecomm-mercadopago-module' )  . ' ' . $store_currency .
						' ' . __( 'to', 'wpecomm-mercadopago-module' ) . ' ' . getCurrencyId( $result['site_id'] ) . __( ' is: ', 'wpecomm-mercadopago-module' ) . $result['currency_ratio'] . ".";
				} else {
					$result['currency_ratio'] = -1;
					$currency_message .= '<img width="12" height="12" src="' .
						plugins_url( 'wpsc-merchants/mercadopago-images/error.png', plugin_dir_path( __FILE__ ) ) . '">' .
						' ' . __( 'ERROR: It was not possible to convert the unsupported currency', 'wpecomm-mercadopago-module' ) . ' ' . $store_currency .
						' '	. __( 'to', 'wpecomm-mercadopago-module' ) . ' ' . getCurrencyId( $result['site_id'] ) . '.' .
						' ' . __( 'Currency conversions should be made outside this module.', 'wpecomm-mercadopago-module' );
				}
			} else {
				$result['currency_ratio'] = -1;
			}
			$credentials_message = '<img width="12" height="12" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/check.png', plugin_dir_path( __FILE__ ) ) . '">' .
				' ' . __( 'Your credentials are <strong>valid</strong> for', 'wpecomm-mercadopago-module' ) .
				': ' . getCountryName( $result['site_id'] ) . ' <img width="18.6" height="12" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/' . $result['site_id'] . '/' . $result['site_id'] . '.png', plugin_dir_path( __FILE__ ) ) . '"> ';
		} catch ( MercadoPagoException $e ) {
			$credentials_message = '<img width="12" height="12" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/error.png', plugin_dir_path( __FILE__ ) ) . '">' .
				' ' . __( 'Your credentials are <strong>not valid</strong>!', 'wpecomm-mercadopago-module' );
		}
	} else {
		$credentials_message = '<img width="12" height="12" src="' .
			plugins_url( 'wpsc-merchants/mercadopago-images/error.png', plugin_dir_path( __FILE__ ) ) . '">' .
			' ' . __( 'Your credentials are <strong>not valid</strong>!', 'wpecomm-mercadopago-module' );
	}

	$api_secret_locale = sprintf(
		'<a href="https://www.mercadopago.com/mla/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlb/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlc/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mco/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlm/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mpe/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlv/account/credentials?type=basic" target="_blank">%s</a> %s ' .
		'<a href="https://www.mercadopago.com/mlu/account/credentials?type=basic" target="_blank">%s</a>',
		__( 'Argentine', 'wpecomm-mercadopago-module' ),
		__( 'Brazil', 'wpecomm-mercadopago-module' ),
		__( 'Chile', 'wpecomm-mercadopago-module' ),
		__( 'Colombia', 'wpecomm-mercadopago-module' ),
		__( 'Mexico', 'wpecomm-mercadopago-module' ),
		__( 'Peru', 'wpecomm-mercadopago-module' ),
		__( 'Venezuela', 'wpecomm-mercadopago-module' ),
		__( 'or', 'wpecomm-mercadopago-module' ),
		__( 'Uruguay', 'wpecomm-mercadopago-module' )
	);
	// check validity of iFrame width/height fields.
	if ( get_option( 'mercadopago_certified_iframewidth') != null &&
	!is_numeric( get_option( 'mercadopago_certified_iframewidth') ) &&
	get_option('mercadopago_certified_description') == "IFrame" ) {
		$iframe_width_desc = '<img width="12" height="12" src="' .
			plugins_url( 'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ ) ) . '">' .
			' ' . __( 'This field should be an integer.', 'wpecomm-mercadopago-module' );
	} else {
		$iframe_width_desc =
			__( 'If your integration method is iFrame, please inform the payment iFrame width.', 'wpecomm-mercadopago-module' );
	}
	if ( get_option( 'mercadopago_certified_iframeheight') != null &&
	!is_numeric( get_option( 'mercadopago_certified_iframeheight') ) &&
	get_option('mercadopago_certified_description') == "IFrame" ) {
		$iframe_height_desc = '<img width="12" height="12" src="' .
			plugins_url( 'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ ) ) . '">' .
			' ' . __( 'This field should be an integer.', 'wpecomm-mercadopago-module' );
	} else {
		$iframe_height_desc =
			__( 'If your integration method is iFrame, please inform the payment iFrame height.', 'wpecomm-mercadopago-module' );
	}
	// Checks if max installments is a number.
	if ( get_option( 'mercadopago_certified_maxinstallments') != null &&
		!is_numeric( get_option( 'mercadopago_certified_maxinstallments') ) ) {
		$installments_desc = '<img width="12" height="12" src="' .
			plugins_url( 'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ ) ) . '">' .
			' ' . __( 'This field should be an integer.', 'wpecomm-mercadopago-module' );
	} else {
		$installments_desc =
			__( 'Select the max number of installments for your customers.', 'wpecomm-mercadopago-module' );
	}
	// Get callbacks
	if (get_option('mercadopago_certified_url_sucess') != '') {
		$url_sucess = get_option('mercadopago_certified_url_sucess');
	} else {
		$url_sucess = get_site_url();
	}
	if (get_option('mercadopago_certified_url_pending') != '') {
		$url_pending = get_option('mercadopago_certified_url_pending');
	} else {
		$url_pending = get_site_url();
	}

	// send output to generate settings page
	$output = "
	<tr>
		<td>
			<img width='200' height='52' src='" .
			plugins_url(
				'wpsc-merchants/mercadopago-images/mplogo.png',
				plugin_dir_path( __FILE__ )
			) . "'>
		</td>
		<td>
			<input type='hidden' size='60' value='" . $result['site_id'] . "' name='mercadopago_certified_siteid' />
			<input type='hidden' size='60' value='" . $result['all_payment_methods'] . "' name='mercadopago_certified_paymentmethods' />
			<input type='hidden' size='60' value='" .
				__( 'Tax fees applicable in store', 'wpecomm-mercadopago-module' ) .
				"' name='mercadopago_certified_checkoutmessage1' />
			<input type='hidden' size='60' value='" .
				__( 'Shipping service used by store', 'wpecomm-mercadopago-module' ) .
				"' name='mercadopago_certified_checkoutmessage2' />
			<input type='hidden' size='60' value='" .
				__( 'Keep paying wih Mercado Pago', 'wpecomm-mercadopago-module' ) .
				"' name='mercadopago_certified_checkoutmessage3' />
			<input type='hidden' size='60' value='" .
				__( 'Thank you for your order. Proceed with your payment completing the following information.', 'wpecomm-mercadopago-module' ) .
				"' name='mercadopago_certified_checkoutmessage4' />
			<input type='hidden' size='60' value='" .
				__( 'Pay with Mercado Pago', 'wpecomm-mercadopago-module' ) .
				"' name='mercadopago_certified_checkoutmessage5' />
			<input type='hidden' size='60' value='" .
				__( 'An error occurred when proccessing your payment. Please try again or contact us for assistence.', 'wpecomm-mercadopago-module' ) .
				"' name='mercadopago_certified_checkoutmessage6' />
			<input type='hidden' size='60' value='" . $result['is_test_user'] . "' name='mercadopago_certified_istestuser' />
			<input type='hidden' size='60' value='" . $result['currency_ratio'] . "' name='mercadopago_certified_currencyratio' />
			<strong>" . __( 'Mercado Pago Credentials', 'wpecomm-mercadopago-module' ) . "</strong>
			<p class='description'>" .
				sprintf( '%s', $credentials_message ) . "<br>" .
				sprintf(
					__( 'You can obtain your credentials for', 'wpecomm-mercadopago-module' ) . " %s.",
					$api_secret_locale
				) .
			"</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Client_id', 'wpecomm-mercadopago-module' ) . "</td>
		<td>
			<input type='text' size='60' value='" . get_option('mercadopago_certified_clientid') . "' name='mercadopago_certified_clientid' />
			<p class='description'>
				" . __( "Insert your Mercado Pago Client_id.", 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Client_secret', 'wpecomm-mercadopago-module' ) . "</td>
		<td>
			<input type='text' size='60' value='" . get_option('mercadopago_certified_clientsecret') . "' name='mercadopago_certified_clientsecret' />
			<p class='description'>
				" . __( "Insert your Mercado Pago Client_secret.", 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>" . __('Checkout Options', 'wpecomm-mercadopago-module' ) . "</strong></h3></td>
	</tr>
	<tr>
		<td>" . __('Description', 'wpecomm-mercadopago-module' ) . "</td>
		<td>
			<input type='textarea' size='60' value='" . get_option( 'mercadopago_certified_description') . "' name='mercadopago_certified_description' />
			<p class='description'>" .
				__( "Description shown to the client in the checkout.", 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Store Category', 'wpecomm-mercadopago-module' ) . "</td>
		<td>" .
			category() . "
			<p class='description'>" .
				__( "Define which type of products your store sells.", 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Store Identificator', 'wpecomm-mercadopago-module' ) . "</td>
		<td>
			<input type='text' size='60' value='" . (get_option( 'mercadopago_certified_invoiceprefix') == "" ? "WPeComm-" : get_option( 'mercadopago_certified_invoiceprefix')) . "' name='mercadopago_certified_invoiceprefix' />
			<p class='description'>" .
				__( "Please, inform a prefix to your store.", "wpecomm-mercadopago-module" ) . ' ' .
				__( "If you use your Mercado Pago account on multiple stores you should make sure that this prefix is unique as Mercado Pago will not allow orders with same identificators.", "wpecomm-mercadopago-module" ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Integration Method', 'wpecomm-mercadopago-module' ) . "</td>
		<td>" .
			type_checkout() . "
			<p class='description'>" .
				__( "Select how your clients should interact with Mercado Pago. Modal Window (inside your store), Redirect (Client is redirected to Mercado Pago), or iFrame (an internal window is embedded to the page layout).", 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('iFrame Width', 'wpecomm-mercadopago-module' ) . "</td>
		<td>
			<input type='number' id='iframewidth' name='mercadopago_certified_iframewidth' min='640' value='" .
			(get_option( 'mercadopago_certified_iframewidth') == null ? 640 : get_option( 'mercadopago_certified_iframewidth')) . "' />
			<p class='description'>" . $iframe_width_desc . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('iFrame Height', 'wpecomm-mercadopago-module' ) . "</td>
		<td>
			<input type='number' id='iframeheight' name='mercadopago_certified_iframeheight' min='800' value='" .
			(get_option('mercadopago_certified_iframeheight') == null ? 800 : get_option( 'mercadopago_certified_iframeheight')) . "' />
			<p class='description'>" . $iframe_height_desc . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Auto Return', 'wpecomm-mercadopago-module') . "</td>
		<td>" .
			auto_return() . "
			<p class='description'>" .
				__( 'After the payment, client is automatically redirected.', 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('URL Approved Payment', 'wpecomm-mercadopago-module') . "</td>
		<td>
			<input name='mercadopago_certified_url_sucess' type='text' value='" . $url_sucess . "'/>
			<p class='description'>" .
				__( 'This is the URL where the customer is redirected if his payment is approved.',
					'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('URL Pending Payment', 'wpecomm-mercadopago-module') . "</td>
		<td>
			<input name='mercadopago_certified_url_pending' type='text' value='" . $url_pending . "'/>
			<p class='description'>" .
				__( 'This is the URL where the customer is redirected if his payment is in process.',
					'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>" . __('Payment Options', 'wpecomm-mercadopago-module') . "</strong></h3></td>
	</tr>
	<tr>
		<td>" . __('Max installments', 'wpecomm-mercadopago-module') . "</td>
		<td>" .
			installments() . "
			<p class='description'>" .
				$installments_desc . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Currency Conversion', 'wpecomm-mercadopago-module') . "</td>
		<td>" .
			currency_conversion() . "
			<p class='description'>" .
				__('If the used currency in WPeCommerce is different or not supported by Mercado Pago, convert values of your transactions using Mercado Pago currency ratio', 'wpecomm-mercadopago-module') . "<br >" .
				__(sprintf('%s', $currency_message)) . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Payment Methods', 'wpecomm-mercadopago-module') . "</td>
		<td>" .
			methods($result['payment_methods']) . "
		</td>
	</tr>
	<tr>
		<td>" . __( 'Two Cards Mode', 'wpecomm-mercadopago-module' ) . "</td>
		<td>" .
			"<select name='mercadopago_certified_twocards' id='auto_return'>" .
				payment_split_mode( $result['payment_split_mode'] ) .
			"</select>
			<p class='description'>" .
				__( 'Your customer will be able to use two different cards to pay the order.', 'wpecomm-mercadopago-module' ) .
			"</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>" . __('Test and Debug Options', 'wpecomm-mercadopago-module') . "</strong></h3></td>
	</tr>" .
	/*<tr>
		<td>" . __('Enable Sandbox', 'wpecomm-mercadopago-module') . "
		</td>
		<td>" .
			sandbox() . "
			<p class='description'>" . __(
				"This option allows you to test payments inside a sandbox environment.",
				'wpecomm-mercadopago-module'
			) . "</p>
		</td>
	</tr>*/
	"<tr>
		<td>" . __('Debug mode', 'wpecomm-mercadopago-module') . "</td>
		<td>" .
			debugs() . "
			<p class='description'>" .
			__('Enable to display log messages in browser console (not recommended in production environment)', 'wpecomm-mercadopago-module') . "
			</p>
		</td>
	</tr>\n";
	return $output;
}

/*===============================================================================
	FUNCTIONS TO PROCESS PAYMENT
================================================================================*/

function function_mercadopago_basic($seperator, $sessionid) {

	global $wpdb, $wpsc_cart;

	// this grabs the purchase log id from the database that refers to the $sessionid
	$purchase_log = $wpdb->get_row(
		"SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS .
		"` WHERE `sessionid`= " . $sessionid . " LIMIT 1"
		, ARRAY_A);

	// indicates that the used method is WPSC_Merchant_MercadoPago_Basic
	update_post_meta( $purchase_log['id'], '_used_gateway', 'WPSC_Merchant_MercadoPago_Basic' );

	// this grabs the customer info using the $purchase_log from the previous SQL query
	$usersql = "SELECT `" . WPSC_TABLE_SUBMITED_FORM_DATA . "`.value,
		`" . WPSC_TABLE_CHECKOUT_FORMS . "`.`name`,
		`" . WPSC_TABLE_CHECKOUT_FORMS . "`.`unique_name` FROM
		`" . WPSC_TABLE_CHECKOUT_FORMS . "` LEFT JOIN
		`" . WPSC_TABLE_SUBMITED_FORM_DATA . "` ON
		`" . WPSC_TABLE_CHECKOUT_FORMS . "`.id =
		`" . WPSC_TABLE_SUBMITED_FORM_DATA . "`.`form_id` WHERE
		`" . WPSC_TABLE_SUBMITED_FORM_DATA . "`.`log_id`=" . $purchase_log['id'];
	$userinfo = $wpdb->get_results($usersql, ARRAY_A);
	$arr_info = array();
	foreach ((array)$userinfo as $key => $value){
		$arr_info[$value['unique_name']] = $value['value'];
	}
	// site id
	$site_id = get_option('mercadopago_certified_siteid', 'MLA');
	if ( empty($site_id) || $site_id == null )
		$site_id = 'MLA';

	// Using a string to register each item (this is a workaround to deal with API problem that shows only first item)
	$list_of_items = array();
	// products
	$items = array();
	if (sizeof($wpsc_cart->cart_items) > 0) {
		foreach ($wpsc_cart->cart_items as $i => $item) {
			if ($item->quantity > 0) {
				$product = get_post($item->product_id);
				$picture_url = 'https://www.mercadopago.com/org-img/MP3/home/logomp3.gif';
				if ($item->thumbnail_image) {
					foreach ($item->thumbnail_image as $key => $image) {
						if ($key == 'guid') {
							$picture_url = $image;
							break;
						}
					}
				}
				$unit_price = (
					((float)($item->unit_price)) *
					(float)($item->quantity)
				) * (
					(float)get_option('mercadopago_certified_currencyratio') > 0 ?
					(float)get_option('mercadopago_certified_currencyratio') : 1
				);
				if ( $site_id == 'MCO' || $site_id == 'MLC' ) {
					$unit_price = floor( $unit_price );
				}
				array_push($list_of_items, ($item->product_name . ' x ' . $item->quantity));
				array_push($items, array(
					'id' => $item->product_id,
					'title' => ( $item->product_name . ' x ' . $item->quantity ),
					'description' => sanitize_file_name( (
						// This handles description width limit of Mercado Pago.
						strlen( $product->post_content ) > 230 ?
						substr( $product->post_content, 0, 230 ) . "..." :
						$product->post_content
					)),
					'picture_url' => $picture_url,
					'category_id' => get_option('mercadopago_certified_category'),
					'quantity' => 1,
					'unit_price' => $unit_price,
					'currency_id' => getCurrencyId($site_id)
				));
			}
		}
		// tax fees cost as an item
		$fee_price = (
 			((float)($wpsc_cart->total_tax))
 		) * (
 			(float)get_option('mercadopago_certified_currencyratio') > 0 ?
 			(float)get_option('mercadopago_certified_currencyratio') : 1
 		);
		if ( $site_id == 'MCO' || $site_id == 'MLC' ) {
			$fee_price = floor( $fee_price );
		}
		array_push($items, array(
			'title' => get_option('mercadopago_certified_checkoutmessage1'),
			'description' => get_option('mercadopago_certified_checkoutmessage1'),
			'category_id' => get_option('mercadopago_certified_category'),
			'quantity' => 1,
			'unit_price' => $fee_price,
			'currency_id' => getCurrencyId($site_id)
		));
		$ship_cost = (
			((float)($wpsc_cart->base_shipping)+(float)($wpsc_cart->total_item_shipping))
		) * (
			(float)get_option('mercadopago_certified_currencyratio') > 0 ?
			(float)get_option('mercadopago_certified_currencyratio') : 1
		);
		if ( $site_id == 'MCO' || $site_id == 'MLC' ) {
			$ship_cost = floor( $ship_cost );
		}
		if ($ship_cost > 0) {
			array_push($list_of_items, get_option('mercadopago_certified_checkoutmessage2'));
			// shipment cost as an item
			array_push($items, array(
				'title' => $wpsc_cart->selected_shipping_option,
				'description' => get_option('mercadopago_certified_checkoutmessage2'),
				'category_id' => get_option('mercadopago_certified_category'),
				'quantity' => 1,
				'unit_price' => $ship_cost,
				'currency_id' => getCurrencyId($site_id)
			));
		}
		// Using a string to register each item (this is a workaround to deal with API problem that shows only first item)
		$items[0]['title'] = implode( ', ', $list_of_items );
	}

	// Find excluded payment methods
	$excluded_payment_string = get_option('mercadopago_certified_exmethods');
	if ($excluded_payment_string != '') {
		$excluded_payment_methods = array();
		$excluded_payment_string = explode(',', $excluded_payment_string);
		foreach ($excluded_payment_string as $exclude ) {
			if ($exclude != "") {
				array_push( $excluded_payment_methods, array(
					"id" => $exclude
				));
			}
		}
		$payment_methods = array(
			"installments" => (int)get_option('mercadopago_certified_maxinstallments'),
			"excluded_payment_methods" => $excluded_payment_methods,
			'default_installments' => 1
		);
	} else {
		$payment_methods = array(
			"installments" => (int)get_option('mercadopago_certified_maxinstallments'),
			'default_installments' => 1
		);
	}

	// create Mercado Pago preference
	$billing_details = wpsc_get_customer_meta();
	$order_id = ( isset( $billing_details['_wpsc_cart.log_id'] ) ? $billing_details['_wpsc_cart.log_id'][0] : 0 );
	$preferences = array(
		'items' => $items,
		// payer should be filled with billing info because orders can be made with non-logged customers.
		'payer' => array(
			'name' => $arr_info['billingfirstname'],
			'surname' => $arr_info['billinglastname'],
			'email' => $arr_info['billingemail'],
			'phone'	=> array(
				'number' => $arr_info['billingphone']
			),
			'address' => array(
				'street_name' => $arr_info['billingaddress'] . ' / ' .
					$arr_info['billingcity'] . ' ' .
					$arr_info['billingstate'] . ' ' .
					$arr_info['billingcountry'],
				'zip_code' => $arr_info['billingpostcode']
			)
		),
		'back_urls' => array(
			'success' => workaroundAmperSandBug( esc_url( add_query_arg(
				'sessionid', $sessionid, get_option( 'transact_url' )
			) ) ),
			'failure' => workaroundAmperSandBug( esc_url( add_query_arg(
				'sessionid', $sessionid, get_option( 'transact_url' )
			) ) ),
			'pending' => workaroundAmperSandBug( esc_url( add_query_arg(
				'sessionid', $sessionid, get_option( 'transact_url' )
			) ) )
		),
		//'marketplace' =>
		//'marketplace_fee' =>
		'shipments' => array(
			//'cost' => (float) $order->get_total_shipping(),
			//'mode' =>
			'receiver_address' => array(
				'zip_code' => $arr_info['shippingpostcode'],
				//'street_number' =>
				'street_name' => $arr_info['shippingaddress'] . ' ' .
					$arr_info['shippingcity'] . ' ' .
					$arr_info['shippingstate'] . ' ' .
					$arr_info['shippingcountry'],
				//'floor' =>
				'apartment' => $arr_info['shippingfirstname']
			)
		),
		'payment_methods' => $payment_methods,
		'external_reference' => get_option('mercadopago_certified_invoiceprefix') . $order_id
		//'additional_info' => $order->customer_message
		//'expires' =>
		//'expiration_date_from' =>
		//'expiration_date_to' =>
	);

	// Do not set IPN url if it is a localhost!
	$notification_url = get_site_url() . '/wpecomm-mercadopago-module/?wc-api=WC_WPeCommMercadoPago_Gateway';
	if ( !strrpos( $notification_url, "localhost" ) ) {
		$preferences['notification_url'] = workaroundAmperSandBug( $notification_url );
	}
	// Set sponsor ID
	if ( get_option('mercadopago_certified_istestuser') == "no" ) {
		switch ($site_id) {
			case 'MLA':
				$sponsor_id = 219693774;
				break;
			case 'MLB':
				$sponsor_id = 219691508;
				break;
			case 'MLC':
				$sponsor_id = 219691655;
				break;
			case 'MCO':
				$sponsor_id = 219695429;
				break;
			case 'MLM':
				$sponsor_id = 219696864;
				break;
			case 'MPE':
				$sponsor_id = 219692012;
				break;
			case 'MLV':
				$sponsor_id = 219696139;
				break;
			case 'MLU':
				$sponsor_id = 246377137;
				break;
			default:
				$sponsor_id = null;
		}
		if ($sponsor_id != null)
			$preferences[ 'sponsor_id' ] = $sponsor_id;
	}

	// auto return options
	if ( get_option('mercadopago_certified_autoreturn') == "active" ) {
		$preferences[ 'auto_return' ] = "approved";
	}

	// log created preferences
	if ( get_option('mercadopago_certified_debug') == "Yes" ) {
		debug_to_console_basic(
			"@" . __FUNCTION__ . " - " .
			"Preferences created, now processing it: " .
			json_encode($preferences, JSON_PRETTY_PRINT)
		);
	}

	process_payment_wpecomm_mp_basic($preferences, $wpsc_cart);

}

function process_payment_wpecomm_mp_basic($preferences, $wpsc_cart) {

	$mp = new MP(
		get_option('mercadopago_certified_clientid'),
		get_option('mercadopago_certified_clientsecret')
	);
	$access_token = $mp->get_access_token();
	$isTestUser = get_option('mercadopago_certified_istestuser');

	// trigger API to create payment
	$preferenceResult = $mp->create_preference($preferences);
	if ($preferenceResult['status'] == 201) {
		/*if (get_option('mercadopago_certified_sandbox') == "active") {
			$link = $preferenceResult['response']['sandbox_init_point'];
		} else {*/
			$link = $preferenceResult['response']['init_point'];
		/*}*/
		$site_id = get_option('mercadopago_certified_siteid', 'MLA');
		if ( empty($site_id) || $site_id == null )
		$site_id = 'MLA';
		// build payment banner url
		$banners_mercadopago_standard = array(
			"MLA" => 'MLA/standard.jpg',
			"MLB" => 'MLB/standard.jpg',
			"MCO" => 'MCO/standard.jpg',
			"MLC" => 'MLC/standard.gif',
			"MPE" => 'MPE/standard.png',
			"MLV" => 'MLV/standard.jpg',
			"MLM" => 'MLM/standard.jpg',
			"MLU" => 'MLU/standard.jpg'
		);
		$html =
			'<img alt="Mercado Pago" title="Mercado Pago" width="468" height="60" src="' .
			plugins_url( 'wpsc-merchants/mercadopago-images/' . $banners_mercadopago_standard[$site_id],
				plugin_dir_path( __FILE__ ) ) . '">';
		if ($link) {
			// build payment button html code
			switch (get_option('mercadopago_certified_typecheckout')) {
				case "Redirect":
					// we don't need to build the payment page, as it is a redirection to Mercado Pago
					header("location: " . $link);
					break;
				case "Iframe":
					$html .= '<p></p><p>' . wordwrap(
						get_option('mercadopago_certified_checkoutmessage4'),
						60, '<br>' ) . '</p>';
					$html .=
						'<iframe src="' . $link . '" name="MP-Checkout" ' .
						'width="' . ( is_numeric( (int)
							get_option('mercadopago_certified_iframewidth') ) ?
							get_option('mercadopago_certified_iframewidth') : 640 ) . '" ' .
						'height="' . ( is_numeric( (int)
							get_option('mercadopago_certified_iframeheight') ) ?
							get_option('mercadopago_certified_iframeheight') : 800 ) . '" ' .
						'frameborder="0" scrolling="no" id="checkout_mercadopago"></iframe>';
					break;
				case "Lightbox": default:
						$html .= '<p></p>';
						$html .=
							'<a id="mp-btn" href="' . $link . '" name="MP-Checkout" class="button alt" mp-mode="modal">' .
							get_option('mercadopago_certified_checkoutmessage5') .
							'</a> ';
						$html .=
							'<script type="text/javascript">(function(){function $MPBR_load(){window.$MPBR_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;s.src = ("https:"==document.location.protocol?"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/":"https://mp-tools.mlstatic.com/buttons/")+"render.js";var x = document.getElementsByTagName("script")[0];x.parentNode.insertBefore(s, x);window.$MPBR_loaded = true;})();}window.$MPBR_loaded !== true ? (window.attachEvent ? window.attachEvent("onload", $MPBR_load) : window.addEventListener("load", $MPBR_load, false)) : null;})();</script>';
						$html .=
							'<style>#mp-btn {background-color: #009ee3; border: 1px solid #009ee3; border-radius: 4px;
								color: #fff; display: inline-block; font-family: Arial,sans-serif; font-size: 18px;
								font-weight: normal; margin: 0; padding: 10px; text-align: center; width: 50%;}
							</style>';
					break;
			}
		} else {
			$html = '<p>' . get_option('mercadopago_certified_checkoutmessage6') . '</p>';
		}
		// show page
		get_header();
		$page = '<div style="position: relative; margin: 20px 0;" >';
			$page .= '<div style="margin: 0 auto; width: 1080px; ">';
				$page .= '<h3>' . get_option('mercadopago_certified_checkoutmessage3') . '</h3>';
				$page .= $html;
			$page .= '</div>';
		$page .= '</div>';
		echo $page;
		get_footer();
		exit;
	} else {
		// TODO: create a better user feedback...
		get_header();
		echo "Error: " . $preferenceResult['status'];
		get_footer();
		exit();
	}
}

/*===============================================================================
	FUNCTIONS TO GENERATE VIEWS
================================================================================*/

function methods($methods = null) {
	$activemethods = explode(",", get_option('mercadopago_certified_exmethods'));
	if ($methods != '' || $methods != null) {
		$showmethods = '';
		foreach ($methods as $method) :
			if ($method['id'] != 'account_money') {
				$icon = '<img height="12" src="' . $method['secure_thumbnail'] . '">';
				if ($activemethods != null && in_array($method['id'], $activemethods)) {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox" value="' .
						$method['id'] . '"> ' . $icon . " (" . $method['name'] . ')<br /><br />';
				} else {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox" checked="yes" value="' .
						$method['id'] . '"> ' . $icon . " (" . $method['name'] . ')<br /><br />';
				}
				/*if ($activemethods != null && in_array($method['id'], $activemethods)) {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox" checked="yes" value="' .
						$method['id'] . '"> ' . $icon . " (" . $method['name'] . ')<br /><br />';
				} else {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox" value="' .
						$method['id'] . '"> ' . $icon . " (" . $method['name'] . ')<br /><br />';
				}*/
			}
		endforeach;
		$showmethods .=
			'<p class="description">' .
				__( 'Select the payment methods that you want to receive with Mercado Pago.', 'wpecomm-mercadopago-module' ) .
			'</p>';
		return $showmethods;
	} else {
		$showmethods = '<img width="12" height="12" src="' .
			plugins_url( 'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ ) ) . '">' . ' ' .
			__( 'Configure your Client_id and Client_secret to have access to more options.', 'wpecomm-mercadopago-module' );
		return $showmethods;
	}
}

function installments() {
		if (get_option('mercadopago_certified_maxinstallments') == null ||
		get_option('mercadopago_certified_maxinstallments') == '') {
			$mercadopago_certified_maxinstallments = 24;
		} else {
			$mercadopago_certified_maxinstallments = get_option('mercadopago_certified_maxinstallments');
		}
		$times = array('1','3','6','9','12','15','18','24','36');
		$showinstallment = '<select name="mercadopago_certified_maxinstallments">';
		foreach ($times as $installment) :
			if ($installment == $mercadopago_certified_maxinstallments) {
				$showinstallment .=
					'<option value="' . $installment .
					'" selected="selected">' . $installment .
					'</option>';
			} else {
				$showinstallment .=
					'<option value="' . $installment . '">' . $installment . '</option>';
			}
		endforeach;
		$showinstallment .= '</select>';
		return $showinstallment;
	}

function auto_return() {
		$auto_return = get_option('mercadopago_certified_autoreturn');
		$auto_return = $auto_return === false || is_null($auto_return) ? "inactive" : $auto_return;
		$auto_return_options = array(
			array("value" => "active", "text" => __( "Active", "wpecomm-mercadopago-module")),
			array("value" => "inactive", "text" => __( "Inactive", "wpecomm-mercadopago-module"))
		);
		$select_auto_return = '<select name="mercadopago_certified_autoreturn" id="auto_return">';
		foreach ($auto_return_options as $op_auto_return) :
			$selected = "";
			if ($op_auto_return['value'] == $auto_return) :
			$selected = 'selected="selected"';
			endif;
			$select_auto_return .=
				'<option value="' . $op_auto_return['value'] .
				'" id="auto_return-' . $op_auto_return['value'] .
				'" ' . $selected . '>' . $op_auto_return['text'] .
				'</option>';
		endforeach;
		$select_auto_return .= "</select>";
		return $select_auto_return;
	}

function type_checkout() {
	$type_checkout = get_option('mercadopago_certified_typecheckout');
	$type_checkout = $type_checkout === false || is_null($type_checkout) ? "Redirect" : $type_checkout;
	// type Checkout
	$type_checkout_options = array(
		'Iframe',
		'Modal Window',
		'Redirect'
	);
	$select_type_checkout = '<select name="mercadopago_certified_typecheckout" id="type_checkout">';
	foreach ($type_checkout_options as $select_type) :
		$selected = "";
		if ($select_type == $type_checkout) :
			$selected = 'selected="selected"';
		endif;
		$select_type_checkout .=
			'<option value="' . $select_type .
			'" id="type-checkout-' . $select_type .
			'" ' . $selected . ' >' . __($select_type, "wpecomm-mercadopago-module") .
			'</option>';
	endforeach;
	$select_type_checkout .= "</select>";
	return $select_type_checkout;
}

function category() {
	$category = get_option('mercadopago_certified_category');
	$category = $category === false || is_null($category) ? "others" : $category;
	// category marketplace
	$list_category = MPRestClient::get( array( "uri" => "/item_categories" ) );
	$list_category = $list_category["response"];
	$select_category = '<select name="mercadopago_certified_category" id="category" style="max-width:600px;>';
	foreach ($list_category as $category_arr) :
		$selected = "";
		if ($category_arr['id'] == $category) :
			$selected = 'selected="selected"';
		endif;
		$select_category .=
			'<option value="' . $category_arr['id'] .
			'" id="type-checkout-' . $category_arr['description'] .
			'" ' . $selected . ' >' . $category_arr['description'] .
			'</option>';
	endforeach;
	$select_category .= "</select>";
	return $select_category;
}

/*function sandbox() {
	$sandbox = get_option('mercadopago_certified_sandbox');
	$sandbox = $sandbox === false || is_null($sandbox) ? "inactive" : $sandbox;
	$sandbox_options = array(
		array("value" => "active", "text" => "Active"),
		array("value" => "inactive", "text" => "Inactive")
	);
	$select_sandbox = '<select name="mercadopago_certified_sandbox" id="sandbox">';
	foreach ($sandbox_options as $op_sandbox) :
		$selected = "";
		if ($op_sandbox['value'] == $sandbox) :
		$selected = 'selected="selected"';
		endif;
		$select_sandbox .=
			'<option value="' . $op_sandbox['value'] .
			'" id="sandbox-' . $op_sandbox['value'] .
			'" ' . $selected . '>' . __($op_sandbox['text'], "wpecomm-mercadopago-module") .
			'</option>';
	endforeach;
	$select_sandbox .= "</select>";
	return $select_sandbox;
}*/

function currency_conversion() {
	$currencyconversion = get_option('mercadopago_certified_currencyconversion');
	$currencyconversion = $currencyconversion === false || is_null($currencyconversion) ? "inactive" : $currencyconversion;
	$currencyconversion_options = array(
		array("value" => "active", "text" => "Active"),
		array("value" => "inactive", "text" => "Inactive")
	);
	$select_currencyconversion = '<select name="mercadopago_certified_currencyconversion" id="currencyconversion">';
	foreach ($currencyconversion_options as $op_currencyconversion) :
		$selected = "";
		if ($op_currencyconversion['value'] == $currencyconversion) :
		$selected = 'selected="selected"';
		endif;
		$select_currencyconversion .=
			'<option value="' . $op_currencyconversion['value'] .
			'" id="currencyconversion-' . $op_currencyconversion['value'] .
			'" ' . $selected . '>' . __($op_currencyconversion['text'], "wpecomm-mercadopago-module") .
			'</option>';
	endforeach;
	$select_currencyconversion .= "</select>";
	return $select_currencyconversion;
}

function payment_split_mode( $twocards ) {

	$twocards = $twocards === false || is_null( $twocards ) ? 'inactive' : $twocards;
	$twocards_options = array(
		array('value' => 'active', 'text' => __( 'Active', 'wpecomm-mercadopago-module' ) ),
		array('value' => 'inactive', 'text' => __( 'Inactive', 'wpecomm-mercadopago-module' ) )
	);

	$select_twocards = '';
	foreach ( $twocards_options as $op_twocards ) :
		$selected = '';
		if ( $op_twocards['value'] == $twocards ) :
			$selected = 'selected="selected"';
		endif;
		$select_twocards .=
			'<option value="' . $op_twocards['value'] .
			'" id="twocards-' . $op_twocards['value'] .
			'" ' . $selected . '>' . $op_twocards['text'] .
			'</option>';
	endforeach;

	return $select_twocards;

}

function debugs() {
	if (get_option('mercadopago_certified_debug') == null || get_option('mercadopago_certified_debug') == '') {
		$mercadopago_certified_debug = 'No';
	} else {
		$mercadopago_certified_debug = get_option('mercadopago_certified_debug');
	}
	$debugs = array('No','Yes');
	$showdebugs = '<select name="mercadopago_certified_debug">';
	foreach ($debugs as  $debug ) :
		if ($debug == $mercadopago_certified_debug) {
			$showdebugs .= '<option value="' . $debug . '" selected="selected">' . __($debug, "wpecomm-mercadopago-module") . '</option>';
		} else {
			$showdebugs .= '<option value="' . $debug . '">' . __($debug, "wpecomm-mercadopago-module") . '</option>';
		}
	endforeach;
	$showdebugs .= '</select>';
	return $showdebugs;
}

/*===============================================================================
	AUXILIARY FUNCTIONS
================================================================================*/

// check if we have valid credentials.
function validateCredentials($client_id, $client_secret) {
	$result = array();
	if (empty($client_id) || empty($client_secret)) {
		$result['site_id'] = null;
		$result['collector_id'] = null;
		$result['is_valid'] = false;
		$result['is_test_user'] = true;
		$result['currency_ratio'] = -1;
		$result['payment_split_mode'] = 'inactive';
		$result['payment_methods'] = null;
		$result['all_payment_methods'] = '';
		return $result;
	}
	if (strlen($client_id) > 0 && strlen($client_secret) > 0 ) {
		try {
			$mp = new MP($client_id, $client_secret);
			$result['access_token'] = $mp->get_access_token();
			$get_request = $mp->get( "/users/me?access_token=" . $result['access_token'] );
			if (isset($get_request['response']['site_id'])) {

				$result['is_test_user'] = in_array('test_user', $get_request['response']['tags']) ? "yes" : "no";
				$result['site_id'] = $get_request['response']['site_id'];
				$result['collector_id'] = $get_request['response']['id'];
				$result['payment_split_mode'] = $mp->check_two_cards();

				$payment_methods = $mp->get( "/v1/payment_methods/?access_token=" . $result['access_token'] );
				$payment_methods = $payment_methods["response"];
				$arr = array();
				foreach ($payment_methods as $payment) {
					$arr[] = $payment['id'];
				}
				$result['payment_methods'] = $payment_methods;
				$result['all_payment_methods'] = implode(",", $arr);
				// check for auto converstion of currency
				$result['currency_ratio'] = -1;
				if ( get_option('mercadopago_certified_currencyconversion') == "active" ) {
					$currency_obj = MPRestClient::get_ml( array( "uri" =>
						"/currency_conversions/search?from=" .
						WPSC_Countries::get_currency_code(absint(get_option('currency_type'))) .
						"&to=" .
						getCurrencyId( $result['site_id'] )
					) );
					if ( isset( $currency_obj[ 'response' ] ) ) {
						$currency_obj = $currency_obj[ 'response' ];
						if ( isset( $currency_obj['ratio'] ) ) {
							$result['currency_ratio'] = (float) $currency_obj['ratio'];
						} else {
							$result['currency_ratio'] = -1;
						}
					} else {
						$result['currency_ratio'] = -1;
					}
				}
				$result['is_valid'] = true;
				return $result;
			} else {
				$result['site_id'] = null;
				$result['collector_id'] = null;
				$result['is_valid'] = false;
				$result['is_test_user'] = true;
				$result['currency_ratio'] = -1;
				$result['payment_split_mode'] = 'inactive';
				$result['payment_methods'] = null;
				$result['all_payment_methods'] = '';
				return $result;
			}
		} catch ( MercadoPagoException $e ) {
			$result['site_id'] = null;
			$result['collector_id'] = null;
			$result['is_valid'] = false;
			$result['is_test_user'] = true;
			$result['currency_ratio'] = -1;
			$result['payment_split_mode'] = 'inactive';
			$result['payment_methods'] = null;
			$result['all_payment_methods'] = '';
			return $result;
		}
	}
	$result['site_id'] = null;
	$result['collector_id'] = null;
	$result['is_valid'] = false;
	$result['is_test_user'] = true;
	$result['currency_ratio'] = -1;
	$result['payment_split_mode'] = 'inactive';
	$result['payment_methods'] = null;
	$result['all_payment_methods'] = '';
	return $result;
}

function getCurrencyId($site_id) {
	switch ($site_id) {
		case 'MLA': return 'ARS';
		case 'MLB': return 'BRL';
		case 'MCO': return 'COP';
		case 'MLC': return 'CLP';
		case 'MLM': return 'MXN';
		case 'MLV': return 'VEF';
		case 'MPE': return 'PEN';
		case 'MLU': return 'UYU';
		default: return '';
	}
}

function getCountryName($site_id) {
	$country = $site_id;
	switch ($site_id) {
		case 'MLA': return __( 'Argentine', 'wpecomm-mercadopago-module' );
		case 'MLB': return __( 'Brazil', 'wpecomm-mercadopago-module' );
		case 'MCO': return __( 'Colombia', 'wpecomm-mercadopago-module' );
		case 'MLC': return __( 'Chile', 'wpecomm-mercadopago-module' );
		case 'MLM': return __( 'Mexico', 'wpecomm-mercadopago-module' );
		case 'MLV': return __( 'Venezuela', 'wpecomm-mercadopago-module' );
		case 'MPE': return __( 'Peru', 'wpecomm-mercadopago-module' );
		case 'MLU': return __( 'Uruguay', 'wpecomm-mercadopago-module' );
	}

}

// Return boolean indicating if currency is supported.
function isSupportedCurrency($site_id) {
	$store_currency_code = WPSC_Countries::get_currency_code(absint(get_option('currency_type')));
	return $store_currency_code == getCurrencyId($site_id);
}

// Fix to URL Problem : #038; replaces & and breaks the navigation
function workaroundAmperSandBug( $link ) {
	return str_replace('&#038;', '&', $link);
}

function debug_to_console_basic($data) {
	// TODO: review debug function as it causes header to be sent
	/*$output  = "<script>console.log( '[WPeComm-Mercado-Pago-Module Logger] => ";
	$output .= json_encode(print_r($data, true), JSON_PRETTY_PRINT);
	$output .= "' );</script>";
	echo $output;*/
}

/*===============================================================================
	INSTANTIATIONS
================================================================================*/

new WPSC_Merchant_MercadoPago_Basic();

?>

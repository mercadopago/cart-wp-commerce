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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once 'mercadopago-lib/mercadopago.php';

$nzshpcrt_gateways[$num] = array(
	'name' => __( 'Mercado Pago - Basic Checkout', 'wpecomm-mercadopago-module' ),
	//'api_version' => 2.0,
	'class_name' => 'WPSC_Merchant_MercadoPago_Basic',
	'display_name' => __( 'Mercado Pago - Basic Checkout', 'wpecomm-mercadopago-module' ),
	'requirements' => array(
		// So that you can restrict merchant modules to PHP 5, if you use PHP 5 features.
		'php_version' => 5.6,
		// For modules that may not be present, like curl.
		'extra_modules' => array()
	),
	'internalname' => 'WPSC_Merchant_MercadoPago_Basic',
	// All array members below here are legacy, and use the code in mercadopago_multiple.php.
	'form' => 'form_mercadopago_basic',
	'function' => 'function_mercadopago_basic',
	'submit_function' => 'submit_mercadopago_basic',
	'payment_type' => 'mercadopago'
);

class WPSC_Merchant_MercadoPago_Basic extends wpsc_merchant {

	function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain_wpecomm' ) );
	}

	// Multi-language plugin.
	function load_plugin_textdomain_wpecomm() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpecomm-mercadopago-module' );
		load_textdomain(
			'wpecomm-mercadopago-module',
			trailingslashit( WP_LANG_DIR ) . 'mercadopago-languages/wpecomm-mercadopago-module-' .
				$locale . '.mo'
		);
		load_plugin_textdomain(
			'wpecomm-mercadopago-module',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/mercadopago-languages/'
		);
	}

}

/**
 * Summary: Saving of Mercado Pago Basic Checkout Settings.
 * Description: Saving of Mercado Pago Basic Checkout Settings.
 * @return a string that identifies the path.
 */
function submit_mercadopago_basic() {

	if ( isset( $_POST['mercadopago_certified_clientid'] ) ) {
		update_option(
			'mercadopago_certified_clientid',
			trim( $_POST['mercadopago_certified_clientid'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_clientsecret'] ) ) {
		update_option(
			'mercadopago_certified_clientsecret',
			trim( $_POST['mercadopago_certified_clientsecret'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_siteid'] ) ) {
		update_option( 'mercadopago_certified_siteid',
			trim( $_POST['mercadopago_certified_siteid'] )
		);
	}

	// TODO: find a better way to pass translated fields to customer view...
	if ( isset( $_POST['mercadopago_certified_checkoutmessage1'] ) ) {
		update_option(
			'mercadopago_certified_checkoutmessage1',
			trim( $_POST['mercadopago_certified_checkoutmessage1'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_checkoutmessage2'] ) ) {
		update_option(
			'mercadopago_certified_checkoutmessage2',
			trim( $_POST['mercadopago_certified_checkoutmessage2'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_checkoutmessage3'] ) ) {
		update_option(
			'mercadopago_certified_checkoutmessage3',
			trim( $_POST['mercadopago_certified_checkoutmessage3'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_checkoutmessage4'] ) ) {
		update_option(
			'mercadopago_certified_checkoutmessage4',
			trim( $_POST['mercadopago_certified_checkoutmessage4'] )
		);
	}

	if ( isset( $_POST['mercadopago_certified_checkoutmessage5'] ) ) {
		update_option(
			'mercadopago_certified_checkoutmessage5',
			trim( $_POST['mercadopago_certified_checkoutmessage5'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_checkoutmessage6'] ) ) {
		update_option(
			'mercadopago_certified_checkoutmessage6',
			trim( $_POST['mercadopago_certified_checkoutmessage6'] )
		);
	}

	if ( isset( $_POST['mercadopago_certified_url_sucess'] ) ) {
		update_option(
			'mercadopago_certified_url_sucess',
			trim( $_POST['mercadopago_certified_url_sucess'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_url_pending'] ) ) {
		update_option( 'mercadopago_certified_url_pending',
			trim( $_POST['mercadopago_certified_url_pending'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_istestuser'] ) ) {
		update_option(
			'mercadopago_certified_istestuser',
			trim( $_POST['mercadopago_certified_istestuser'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_currencyratio'] ) ) {
		update_option(
			'mercadopago_certified_currencyratio',
			trim( $_POST['mercadopago_certified_currencyratio'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_description'] ) ) {
		update_option(
			'mercadopago_certified_description',
			trim( $_POST['mercadopago_certified_description'] ) );
	}
	if ( isset( $_POST['mercadopago_certified_category'] ) ) {
		update_option(
			'mercadopago_certified_category',
			trim( $_POST['mercadopago_certified_category'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_invoiceprefix'] ) ) {
		update_option(
			'mercadopago_certified_invoiceprefix',
			trim( $_POST['mercadopago_certified_invoiceprefix'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_typecheckout'] ) ) {
		update_option(
			'mercadopago_certified_typecheckout',
			trim( $_POST['mercadopago_certified_typecheckout'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_iframewidth'] ) ) {
		update_option(
			'mercadopago_certified_iframewidth',
			trim( $_POST['mercadopago_certified_iframewidth'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_iframeheight'] ) ) {
		update_option(
			'mercadopago_certified_iframeheight',
			trim( $_POST['mercadopago_certified_iframeheight'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_autoreturn'] ) ) {
		update_option(
			'mercadopago_certified_autoreturn',
			trim( $_POST['mercadopago_certified_autoreturn'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_currencyconversion'] ) ) {
		update_option(
			'mercadopago_certified_currencyconversion',
			trim( $_POST['mercadopago_certified_currencyconversion'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_maxinstallments'] ) ) {
		update_option(
			'mercadopago_certified_maxinstallments',
			trim( $_POST['mercadopago_certified_maxinstallments'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_exmethods'] ) &&
	isset( $_POST['mercadopago_certified_paymentmethods'] ) ) {
		$paymentmethods = explode( ',', $_POST['mercadopago_certified_paymentmethods'] );
		$methods = $_POST['mercadopago_certified_exmethods'];
		if ( ! in_array( 'account_money', $methods ) ) {
			array_push( $methods, 'account_money' );
		}
		$result = array_diff( $paymentmethods, $methods );
		update_option(
			'mercadopago_certified_exmethods',
			implode( ',', $result )
		);
		update_option(
			'mercadopago_certified_paymentmethods',
			$_POST['mercadopago_certified_paymentmethods']
		);
	} else {
		update_option( 'mercadopago_certified_exmethods', '' );
		update_option( 'mercadopago_certified_paymentmethods', '' );
	}
	/*if ( isset( $_POST['mercadopago_certified_exmethods'] ) ) {
		$methods = implode( ',', $_POST['mercadopago_certified_exmethods'] );
		update_option( 'mercadopago_certified_exmethods', $methods );
	} else {
		update_option( 'mercadopago_certified_exmethods', '' );
	}*/
	if ( ! empty( $_POST['mercadopago_certified_twocards'] ) ) {
		$mp = new MP(
			WPeComm_MercadoPago_Module::get_module_version(),
			get_option( 'mercadopago_certified_clientid' ),
			get_option( 'mercadopago_certified_clientsecret' )
		);
		$payment_split_mode = trim( $_POST['mercadopago_certified_twocards'] );
		$response = $mp->set_two_cards_mode( $payment_split_mode );
	}
	if ( isset( $_POST['mercadopago_certified_sandbox'] ) ) {
		update_option(
			'mercadopago_certified_sandbox',
			trim( $_POST['mercadopago_certified_sandbox'] )
		);
	}
	if ( isset( $_POST['mercadopago_certified_debug'] ) ) {
		update_option(
			'mercadopago_certified_debug',
			trim( $_POST['mercadopago_certified_debug'] )
		);
	}

	return true;

}

/**
 * Summary: Form Basic Checkout Returns the Settings Form Fields.
 * Description: Form Basic Checkout Returns the Settings Form Fields.
 * @return $output string containing Form Fields.
 */
function form_mercadopago_basic() {

	global $wpdb, $wpsc_gateways;

	$currency_message = '';
	$result = validate_credentials(
		get_option( 'mercadopago_certified_clientid' ),
		get_option( 'mercadopago_certified_clientsecret' )
	);
	$store_currency = WPSC_Countries::get_currency_code( absint( get_option( 'currency_type' ) ) );

	$api_secret_locale = sprintf(
		'<a href="https://www.mercadopago.com/mla/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlb/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlc/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mco/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlm/account/credentials?type=basic" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mpe/account/credentials?type=basic" target="_blank">%s</a> %s ' .
		'<a href="https://www.mercadopago.com/mlv/account/credentials?type=basic" target="_blank">%s</a>',
		__( 'Argentine', 'wpecomm-mercadopago-module' ),
		__( 'Brazil', 'wpecomm-mercadopago-module' ),
		__( 'Chile', 'wpecomm-mercadopago-module' ),
		__( 'Colombia', 'wpecomm-mercadopago-module' ),
		__( 'Mexico', 'wpecomm-mercadopago-module' ),
		__( 'Peru', 'wpecomm-mercadopago-module' ),
		__( 'or', 'wpecomm-mercadopago-module' ),
		__( 'Venezuela', 'wpecomm-mercadopago-module' )
	);

	// Trigger API to get payment methods and site_id, also validates Client_id/Client_secret.
	if ( $result['is_valid'] == true ) {
		// Checking the currency.
		if ( ! WPeComm_MercadoPago_Module::is_supported_currency(
		$result['country_configs']['currency'] ) ) {
			if ( get_option( 'mercadopago_certified_currencyconversion' ) == 'inactive' ) {
				$result['currency_ratio'] = -1;
				$currency_message .= WPeComm_MercadoPago_Module::build_currency_not_converted_msg(
					$result['country_configs']['currency'],
					$result['country_configs']['country_name']
				);
			} elseif ( get_option( 'mercadopago_certified_currencyconversion' ) == 'active' &&
			$result['currency_ratio'] != -1 ) {
				$currency_message .= WPeComm_MercadoPago_Module::build_currency_converted_msg(
					$result['country_configs']['currency'],
					$result['currency_ratio']
				);
			} else {
				$result['currency_ratio'] = -1;
				$currency_message .= WPeComm_MercadoPago_Module::build_currency_conversion_err_msg(
					$result['country_configs']['currency']
				);
			}
		} else {
			$result['currency_ratio'] = -1;
		}
		$credentials_message = WPeComm_MercadoPago_Module::build_valid_credentials_msg(
			$result['country_configs']['country_name'],
			$result['site_id']
		);
	} else {
		$credentials_message = WPeComm_MercadoPago_Module::build_invalid_credentials_msg();
	}

	// Check validity of iFrame width/height fields.
	if ( get_option( 'mercadopago_certified_iframewidth' ) != null &&
	! is_numeric( get_option( 'mercadopago_certified_iframewidth' ) ) &&
	get_option( 'mercadopago_certified_description' ) == 'IFrame' ) {
		$iframe_width_desc = '<img width="12" height="12" src="' . plugins_url(
			'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ )
		) . '"> ' . __( 'This field should be an integer.', 'wpecomm-mercadopago-module' );
	} else {
		$iframe_width_desc =
			__( 'If your integration method is iFrame, please inform the payment iFrame width.', 'wpecomm-mercadopago-module' );
	}
	if ( get_option( 'mercadopago_certified_iframeheight' ) != null &&
	! is_numeric( get_option( 'mercadopago_certified_iframeheight' ) ) &&
	get_option( 'mercadopago_certified_description' ) == "IFrame" ) {
		$iframe_height_desc = '<img width="12" height="12" src="' . plugins_url(
			'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ )
		) . '"> ' . __( 'This field should be an integer.', 'wpecomm-mercadopago-module' );
	} else {
		$iframe_height_desc =
			__( 'If your integration method is iFrame, please inform the payment iFrame height.', 'wpecomm-mercadopago-module' );
	}

	// Checks if max installments is a number.
	if ( get_option( 'mercadopago_certified_maxinstallments' ) != null &&
		! is_numeric( get_option( 'mercadopago_certified_maxinstallments' ) ) ) {
		$installments_desc = '<img width="12" height="12" src="' . plugins_url(
			'wpsc-merchants/mercadopago-images/warning.png',
			plugin_dir_path( __FILE__ )
		) . '"> ' . __( 'This field should be an integer.', 'wpecomm-mercadopago-module' );
	} else {
		$installments_desc =
			__( 'Select the max number of installments for your customers.', 'wpecomm-mercadopago-module' );
	}

	// Get callbacks.
	if ( get_option( 'mercadopago_certified_url_sucess' ) != '' ) {
		$url_sucess = get_option( 'mercadopago_certified_url_sucess' );
	} else {
		$url_sucess = get_site_url();
	}
	if ( get_option( 'mercadopago_certified_url_pending' ) != '' ) {
		$url_pending = get_option( 'mercadopago_certified_url_pending' );
	} else {
		$url_pending = get_site_url();
	}

	// Send output to generate settings page.
	$output = '
	<tr>
		<td>
			<img width="200" height="52" src="' .
			plugins_url(
				'wpsc-merchants/mercadopago-images/mplogo.png',
				plugin_dir_path( __FILE__ )
			) . '">
		</td>
		<td>
			<input type="hidden" size="60" value="' .
			$result['site_id'] .
			'" name="mercadopago_certified_siteid" />' .
			'<input type="hidden" size="60" value="' .
			$result['all_payment_methods'] .
			'" name="mercadopago_certified_paymentmethods" />' .
			'<input type="hidden" size="60" value="' .
			__( 'Tax fees applicable in store', 'wpecomm-mercadopago-module' ) .
			'" name="mercadopago_certified_checkoutmessage1" />
			<input type="hidden" size="60" value="' .
			__( 'Shipping service used by store', 'wpecomm-mercadopago-module' ) .
			'" name="mercadopago_certified_checkoutmessage2" />
			<input type="hidden" size="60" value="' .
			__( 'Keep paying wih Mercado Pago', 'wpecomm-mercadopago-module' ) .
			'" name="mercadopago_certified_checkoutmessage3" />
			<input type="hidden" size="60" value="' .
			__( 'Thank you for your order. Proceed with your payment completing the following information.', 'wpecomm-mercadopago-module' ) .
			'" name="mercadopago_certified_checkoutmessage4" />
			<input type="hidden" size="60" value="' .
			__( 'Pay with Mercado Pago', 'wpecomm-mercadopago-module' ) .
			'" name="mercadopago_certified_checkoutmessage5" />
			<input type="hidden" size="60" value="' .
			__( 'An error occurred when proccessing your payment. Please try again or contact us for assistence.', 'wpecomm-mercadopago-module' ) .
			'" name="mercadopago_certified_checkoutmessage6" />
			<input type="hidden" size="60" value="' .
			$result['is_test_user'] .
			'" name="mercadopago_certified_istestuser" />
			<input type="hidden" size="60" value="' .
			$result['currency_ratio'] .
			'" name="mercadopago_certified_currencyratio" />
			<strong>' . __( 'Mercado Pago Credentials', 'wpecomm-mercadopago-module' ) . '</strong>
			<p class="description">' .
				sprintf( '%s', $credentials_message ) . '<br>' .
				sprintf(
					__( 'You can obtain your credentials for', 'wpecomm-mercadopago-module' ) . ' %s.',
					$api_secret_locale
				) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Client_id', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' .
			get_option( 'mercadopago_certified_clientid' ) .
			'" name="mercadopago_certified_clientid" />
			<p class="description">' .
				__( 'Insert your Mercado Pago Client_id.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Client_secret', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' .
			get_option( 'mercadopago_certified_clientsecret' ) .
			'" name="mercadopago_certified_clientsecret" />
			<p class="description">' .
				__( 'Insert your Mercado Pago Client_secret.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>' .
			__( 'Checkout Options', 'wpecomm-mercadopago-module' ) .
		'</strong></h3></td>
	</tr>
	<tr>
		<td>' . __( 'Description', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="textarea" size="60" value="' .
			get_option( 'mercadopago_certified_description' ) .
			'" name="mercadopago_certified_description" />
			<p class="description">' .
				__( 'Description shown to the client in the checkout.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Store Category', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<select name="mercadopago_certified_category" id="category" style="max-width:600px;>' .
				WPeComm_MercadoPago_Module::get_categories( 'mercadopago_certified_category' ) .
			'</select>
			<p class="description">' .
				__( 'Define which type of products your store sells.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Store Identificator', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' .
			( get_option( 'mercadopago_certified_invoiceprefix' ) == '' ?
				'WPeComm-' : get_option( 'mercadopago_certified_invoiceprefix' ) ) .
			'" name="mercadopago_certified_invoiceprefix" />
			<p class="description">' .
				__( 'Please, inform a prefix to your store.', 'wpecomm-mercadopago-module' ) . ' ' .
				__( 'If you use your Mercado Pago account on multiple stores you should make sure that this prefix is unique as Mercado Pago will not allow orders with same identificators.', 'wpecomm-mercadopago-module' ) . '
			</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Integration Method', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_certified_typecheckout" id="type_checkout">' .
				type_checkout() .
			'</select>
			<p class="description">' .
				__( 'Select how your clients should interact with Mercado Pago. Modal Window (inside your store), Redirect (Client is redirected to Mercado Pago), or iFrame (an internal window is embedded to the page layout).', 'wpecomm-mercadopago-module' ) . '
			</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'iFrame Width', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="number" id="iframewidth" name="mercadopago_certified_iframewidth"' .
			' min="640" value="' . ( get_option( 'mercadopago_certified_iframewidth' ) == null ?
			640 : get_option( 'mercadopago_certified_iframewidth' ) ) . '" />
			<p class="description">' .
				$iframe_width_desc .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'iFrame Height', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="number" id="iframeheight" name="mercadopago_certified_iframeheight"' .
			' min="800" value="' . ( get_option( 'mercadopago_certified_iframeheight' ) == null ?
			800 : get_option( 'mercadopago_certified_iframeheight' ) ) . '" />
			<p class="description">' .
				$iframe_height_desc .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Auto Return', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_certified_autoreturn" id="auto_return">' .
				auto_return() .
			'</select>
			<p class="description">' .
				__( 'After the payment, client is automatically redirected.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'URL Approved Payment', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input name="mercadopago_certified_url_sucess" type="text" value="' .
			$url_sucess . '"/>
			<p class="description">' .
				__( 'This is the URL where the customer is redirected if his payment is approved.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'URL Pending Payment', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input name="mercadopago_certified_url_pending" type="text" value="' .
			$url_pending . '"/>
			<p class="description">' .
				__( 'This is the URL where the customer is redirected if his payment is in process.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>' .
			__( 'Payment Options', 'wpecomm-mercadopago-module' ) .
		'</strong></h3></td>
	</tr>
	<tr>
		<td>' . __( 'Max installments', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_certified_maxinstallments">' .
				installments() .
			'</select>
			<p class="description">' .
				$installments_desc .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Currency Conversion', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_certified_currencyconversion" id="currencyconversion">' .
				WPeComm_MercadoPago_Module::currency_conversion(
					'mercadopago_certified_currencyconversion'
				) .
			'</select>
			<p class="description">' .
				__( 'If the used currency in WPeCommerce is different or not supported by Mercado Pago, convert values of your transactions using Mercado Pago currency ratio', 'wpecomm-mercadopago-module' ) .
				'<br >' . __( sprintf( '%s', $currency_message ) ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Payment Methods', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' . methods( $result['payment_methods'] ) .
		'</td>
	</tr>
	<tr>
		<td>' . __( 'Two Cards Mode', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_certified_twocards" id="auto_return">' .
				payment_split_mode( $result['payment_split_mode'] ) .
			'</select>
			<p class="description">' .
				__( 'Your customer will be able to use two different cards to pay the order.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>' .
			__( 'Test and Debug Options', 'wpecomm-mercadopago-module' ) .
		'</strong></h3></td>
	</tr>
	<tr>
		<td>' . __( 'Enable Sandbox', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_certified_sandbox" id="sandbox">' .
				WPeComm_MercadoPago_Module::sandbox( 'mercadopago_certified_sandbox' ) .
			'</select>
			<p class="description">' .
				__( 'This option allows you to test payments inside a sandbox environment.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Debug mode', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_certified_debug">' .
				WPeComm_MercadoPago_Module::debugs( 'mercadopago_certified_debug' ) .
			'</select>
			<p class="description">' .
				__( 'Enable to display log messages in browser console (not recommended in production environment)', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>';

	return $output;

}

/*
 * ========================================================================
 * CHECKOUT BUSINESS RULES (CLIENT SIDE)
 * ========================================================================
 */

/**
 * Summary: Build Mercado Pago preference.
 * Description: Create Mercado Pago preference and get init_point URL based in the order options
 * from the cart.
 * @return the preference object.
 */
function function_mercadopago_basic( $seperator, $sessionid ) {

	global $wpdb, $wpsc_cart;

	// This grabs the purchase log id from the database that refers to the $sessionid.
	$purchase_log = $wpdb->get_row(
		'SELECT * FROM `' . WPSC_TABLE_PURCHASE_LOGS .
		'` WHERE `sessionid`= ' . $sessionid . ' LIMIT 1'
		, ARRAY_A);

	// This grabs the customer info using the $purchase_log from the previous SQL query.
	$usersql = 'SELECT `' . WPSC_TABLE_SUBMITED_FORM_DATA . '`.value,
		`' . WPSC_TABLE_CHECKOUT_FORMS . '`.`name`,
		`' . WPSC_TABLE_CHECKOUT_FORMS . '`.`unique_name` FROM
		`' . WPSC_TABLE_CHECKOUT_FORMS . '` LEFT JOIN
		`' . WPSC_TABLE_SUBMITED_FORM_DATA . '` ON
		`' . WPSC_TABLE_CHECKOUT_FORMS . '`.id =
		`' . WPSC_TABLE_SUBMITED_FORM_DATA . '`.`form_id` WHERE
		`' . WPSC_TABLE_SUBMITED_FORM_DATA . '`.`log_id`=' . $purchase_log['id'];
	$userinfo = $wpdb->get_results( $usersql, ARRAY_A );
	$arr_info = array();
	foreach ( (array) $userinfo as $key => $value ) {
		$arr_info[$value['unique_name']] = $value['value'];
	}

	// Site id.
	$site_id = get_option( 'mercadopago_certified_siteid', 'MLA' );
	if ( empty( $site_id ) || $site_id == null )
		$site_id = 'MLA';
	$country_configs = WPeComm_MercadoPago_Module::get_country_config( $site_id );

	// Get currency ratio.
	$currency_ratio = 1;
	if ( get_option( 'mercadopago_certified_currencyconversion' ) == 'active' ) {
		$currency_ratio = (float) WPeComm_MercadoPago_Module::get_conversion_rate(
			$country_configs['currency']
		);
	}

	// String to register each item (workaround to deal with API that shows only first item).
	$list_of_items = array();
	// Products.
	$items = array();
	if ( sizeof( $wpsc_cart->cart_items ) > 0 ) {
		// Tax fees cost as an item.
		array_push( $items, array(
			'id' => 2147483647,
			'title' => get_option( 'mercadopago_certified_checkoutmessage1' ),
			'description' => get_option( 'mercadopago_certified_checkoutmessage1' ),
			'category_id' => get_option( 'mercadopago_certified_category' ),
			'quantity' => 1,
			'unit_price' => ( (float) ( $wpsc_cart->total_tax ) ) * $currency_ratio,
			'currency_id' => $country_configs['currency']
		) );
		// Shipment cost as an item.
		$ship_cost = $currency_ratio * (
			(float) $wpsc_cart->base_shipping + (float) $wpsc_cart->total_item_shipping
		);
		if ( $ship_cost > 0 ) {
			array_push( $list_of_items, get_option( 'mercadopago_certified_checkoutmessage2' ) );
			array_push( $items, array(
				'id' => 2147483647,
				'title' => $wpsc_cart->selected_shipping_option,
				'description' => get_option( 'mercadopago_certified_checkoutmessage2' ),
				'category_id' => get_option( 'mercadopago_certified_category' ),
				'quantity' => 1,
				'unit_price' => $ship_cost,
				'currency_id' => $country_configs['currency']
			) );
		}
		foreach ( $wpsc_cart->cart_items as $i => $item ) {
			if ( $item->quantity > 0 ) {
				$product = get_post( $item->product_id );
				$picture_url = 'https://www.mercadopago.com/org-img/MP3/home/logomp3.gif';
				if ( $item->thumbnail_image ) {
					foreach ( $item->thumbnail_image as $key => $image ) {
						if ( $key == 'guid' ) {
							$picture_url = $image;
							break;
						}
					}
				}
				array_push( $list_of_items, ( $item->product_name . ' x ' . $item->quantity ) );
				array_push( $items, array(
					'id' => $item->product_id,
					'title' => ( $item->product_name . ' x ' . $item->quantity ),
					'description' => sanitize_file_name( (
						// This handles description width limit of Mercado Pago.
						strlen( $product->post_content ) > 230 ?
						substr( $product->post_content, 0, 230 ) . '...' :
						$product->post_content
					) ),
					'picture_url' => $picture_url,
					'category_id' => get_option( 'mercadopago_certified_category' ),
					'quantity' => 1,
					'unit_price' => $currency_ratio *
						( ( (float) ( $item->unit_price ) ) * ( (float) ( $item->quantity ) ) ),
					'currency_id' => $country_configs['currency']
				) );
			}
		}
		// String to register each item (workaround to deal with API that shows only first item).
		$items[0]['title'] = implode( ', ', $list_of_items );
	}

	// Find excluded payment methods.
	$excluded_payment_string = get_option( 'mercadopago_certified_exmethods' );
	if ( $excluded_payment_string != '' ) {
		$excluded_payment_methods = array();
			$excluded_payment_string = explode( ',', $excluded_payment_string );
			foreach ( $excluded_payment_string as $exclude ) {
			if ($exclude != '') {
				array_push( $excluded_payment_methods, array(
					'id' => $exclude
				) );
			}
		}
		$payment_methods = array(
			'installments' => (int) get_option( 'mercadopago_certified_maxinstallments' ),
			'excluded_payment_methods' => $excluded_payment_methods,
			'default_installments' => 1
		);
	} else {
		$payment_methods = array(
			'installments' => (int) get_option( 'mercadopago_certified_maxinstallments' ),
			'default_installments' => 1
		);
	}

	// Create Mercado Pago preference.
	$billing_details = wpsc_get_customer_meta();
	$order_id = $billing_details['_wpsc_cart.log_id'][0];
	$preferences = array(
		'items' => $items,
		// Payer should be filled with billing info because orders can be made with non-logged customers.
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
			'success' => WPeComm_MercadoPago_Module::workaround_ampersand_bug(
				esc_url( add_query_arg( 'sessionid', $sessionid, get_option( 'transact_url' ) ) )
			),
			'failure' => WPeComm_MercadoPago_Module::workaround_ampersand_bug(
				esc_url( add_query_arg( 'sessionid', $sessionid, get_option( 'transact_url' ) ) )
			),
			'pending' => WPeComm_MercadoPago_Module::workaround_ampersand_bug(
				esc_url( add_query_arg( 'sessionid', $sessionid, get_option( 'transact_url' ) ) )
			)
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
		'external_reference' => get_option( 'mercadopago_certified_invoiceprefix' ) . $order_id
		//'additional_info' => $order->customer_message
		//'expires' =>
		//'expiration_date_from' =>
		//'expiration_date_to' =>
	);

	// Do not set IPN url if it is a localhost.
	if ( ! strrpos( get_site_url(), 'localhost' ) ) {
		$preferences['notification_url'] = WPeComm_MercadoPago_Module::workaround_ampersand_bug(
			// TODO: check if this is really working...
			get_site_url() . '/wpecomm-mercadopago-module'
		);
	}

	// Set sponsor ID.
	if ( get_option( 'mercadopago_certified_istestuser' ) == 'no' ) {
		$preferences['sponsor_id'] = $country_configs['sponsor_id'];
	}

	// Auto return options.
	if ( get_option( 'mercadopago_certified_autoreturn' ) == 'active' ) {
		$preferences[ 'auto_return' ] = 'approved';
	}

	// Log created preferences.
	if ( get_option( 'mercadopago_certified_debug' ) == 'active' ) {
		debug_to_console_basic(
			'@' . __FUNCTION__ . ' - ' .
			'Preferences created, now processing it: ' .
			json_encode( $preferences, JSON_PRETTY_PRINT )
		);
	}

	process_payment_wpecomm_mp_basic( $preferences, $wpsc_cart );

}

function process_payment_wpecomm_mp_basic( $preferences, $wpsc_cart ) {

	$mp = new MP(
		WPeComm_MercadoPago_Module::get_module_version(),
		get_option( 'mercadopago_certified_clientid' ),
		get_option( 'mercadopago_certified_clientsecret' )
	);

	// Trigger API to create payment.
	$preferenceResult = $mp->create_preference( $preferences );

	if ( $preferenceResult['status'] == 201 ) {

		if ( get_option( 'mercadopago_certified_sandbox' ) == 'active' ) {
			$link = $preferenceResult['response']['sandbox_init_point'];
		} else {
			$link = $preferenceResult['response']['init_point'];
		}

		$site_id = get_option( 'mercadopago_certified_siteid', 'MLA' );
		if ( empty( $site_id) || $site_id == null ) {
			$site_id = 'MLA';
		}
		$country_configs = WPeComm_MercadoPago_Module::get_country_config( $site_id );

		$html = '<img alt="Mercado Pago" title="Mercado Pago" width="468" height="60" src="' .
			$country_configs['checkout_banner'] . '">';

		if ( $link ) {
			// Build payment button html code.
			switch ( get_option( 'mercadopago_certified_typecheckout' ) ) {
				case 'Redirect':
					// We don't need to build the payment page, as it is a redirection to Mercado Pago.
					header( 'location: ' . $link);
					break;
				case 'Iframe':
					$html .= '<p></p><p>' . wordwrap(
						get_option( 'mercadopago_certified_checkoutmessage4' ),
						60, '<br>' ) . '</p>';
					$html .=
						'<iframe src="' . $link . '" name="MP-Checkout" ' .
						'width="' . ( is_numeric( (int)
							get_option( 'mercadopago_certified_iframewidth' ) ) ?
							get_option( 'mercadopago_certified_iframewidth' ) : 640 ) . '" ' .
						'height="' . ( is_numeric( (int)
							get_option( 'mercadopago_certified_iframeheight' ) ) ?
							get_option( 'mercadopago_certified_iframeheight' ) : 800 ) . '" ' .
						'frameborder="0" scrolling="no" id="checkout_mercadopago"></iframe>';
					break;
				case 'Lightbox': default:
						$html .= '<p></p>';
						$html .=
							'<a id="mp-btn" href="' . $link .
							'" name="MP-Checkout" class="button alt" mp-mode="modal">' .
							get_option( 'mercadopago_certified_checkoutmessage5' ) .
							'</a> ';
						$html .=
							'<script type="text/javascript">(function(){function $MPBR_load(){window.$MPBR_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;s.src = ("https:"==document.location.protocol?"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/":"https://mp-tools.mlstatic.com/buttons/")+"render.js";var x = document.getElementsByTagName("script")[0];x.parentNode.insertBefore(s, x);window.$MPBR_loaded = true;})();}window.$MPBR_loaded !== true ? (window.attachEvent ? window.attachEvent("onload", $MPBR_load) : window.addEventListener("load", $MPBR_load, false) ) : null;})();</script>';
						$html .=
							'<style>#mp-btn {background-color: #009ee3; border: 1px solid #009ee3; border-radius: 4px;
								color: #fff; display: inline-block; font-family: Arial,sans-serif; font-size: 18px;
								font-weight: normal; margin: 0; padding: 10px; text-align: center; width: 50%;}
							</style>';
					break;
			}
		} else {
			$html = '<p>' . get_option( 'mercadopago_certified_checkoutmessage6' ) . '</p>';
		}

		// Show page.
		get_header();
		$page = '<div style="position: relative; margin: 20px 0;" >';
			$page .= '<div style="margin: 0 auto; width: 1080px; ">';
				$page .= '<h3>' . get_option( 'mercadopago_certified_checkoutmessage3' ) . '</h3>';
				$page .= $html;
			$page .= '</div>';
		$page .= '</div>';
		echo str_replace( 'http:', 'https:', $page );
		get_footer();
		exit;

	} else {

		// TODO: create a better user feedback...
		get_header();
		echo 'Error: ' . $preferenceResult['status'];
		get_footer();
		exit();

	}

}

/*
 * ========================================================================
 * FUNCTIONS TO GENERATE VIEWS (SERVER SIDE)
 * ========================================================================
 */

function methods( $methods = null ) {

	$activemethods = explode( ',', get_option( 'mercadopago_certified_exmethods' ) );

	if ( $methods != '' || $methods != null ) {
		$showmethods = '';
		foreach ( $methods as $method ) :
			if ( $method['id'] != 'account_money' ) {
				$icon = '<img height="12" src="' . $method['secure_thumbnail'] . '">';
				if ( $activemethods != null && in_array( $method['id'], $activemethods ) ) {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox"' .
						' value="' . $method['id'] . '"> ' . $icon . ' (' .
						$method['name'] . ' )<br /><br />';
				} else {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox"' .
						' checked="yes" value="' . $method['id'] . '"> ' . $icon . ' (' .
						$method['name'] . ')<br /><br />';
				}
				/*if ( $activemethods != null && in_array($method['id'], $activemethods ) ) {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox"' .
						' checked="yes" value="' . $method['id'] . '"> ' . $icon . ' (' .
						$method['name'] . ')<br /><br />';
				} else {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox"' .
						' value="' . $method['id'] . '"> ' . $icon . ' (' .
						$method['name'] . ')<br /><br />';
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

function payment_split_mode( $twocards ) {

	$twocards = $twocards === false || is_null( $twocards ) ? 'inactive' : $twocards;
	$twocards_options = array(
		array('value' => 'active', 'text' => __( 'Active', 'wpecomm-mercadopago-module' ) ),
		array('value' => 'inactive', 'text' => __( 'Inactive', 'wpecomm-mercadopago-module' ) )
	);

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

function installments() {

	if ( get_option( 'mercadopago_certified_maxinstallments' ) == null ||
	get_option( 'mercadopago_certified_maxinstallments' ) == '' ) {
		$mercadopago_certified_maxinstallments = 24;
	} else {
		$mercadopago_certified_maxinstallments =
			get_option( 'mercadopago_certified_maxinstallments' );
	}
	$times = array( '1','3','6','9','12','15','18','24','36' );

	foreach ( $times as $installment ) :
		if ( $installment == $mercadopago_certified_maxinstallments ) {
			$showinstallment .=
				'<option value="' . $installment .
				'" selected="selected">' . $installment .
				'</option>';
		} else {
			$showinstallment .=
				'<option value="' . $installment . '">' . $installment . '</option>';
		}
	endforeach;

	return $showinstallment;

}

function auto_return() {

	$auto_return = get_option( 'mercadopago_certified_autoreturn' );
	$auto_return = $auto_return === false || is_null( $auto_return ) ? 'inactive' : $auto_return;
	$auto_return_options = array(
		array('value' => 'active', 'text' => __( 'Active', 'wpecomm-mercadopago-module' ) ),
		array('value' => 'inactive', 'text' => __( 'Inactive', 'wpecomm-mercadopago-module' ) )
	);

	foreach ( $auto_return_options as $op_auto_return ) :
		$selected = '';
		if ( $op_auto_return['value'] == $auto_return ) :
			$selected = 'selected="selected"';
		endif;
		$select_auto_return .=
			'<option value="' . $op_auto_return['value'] .
			'" id="auto_return-' . $op_auto_return['value'] .
			'" ' . $selected . '>' . $op_auto_return['text'] .
			'</option>';
	endforeach;

	return $select_auto_return;

}

function type_checkout() {

	$type_checkout = get_option( 'mercadopago_certified_typecheckout' );
	$type_checkout =
		$type_checkout === false || is_null( $type_checkout ) ? 'Redirect' : $type_checkout;
	$type_checkout_options = array( 'Iframe', 'Modal Window', 'Redirect' );

	foreach ( $type_checkout_options as $select_type ) :
		$selected = '';
		if ( $select_type == $type_checkout ) :
			$selected = 'selected="selected"';
		endif;
		$select_type_checkout .=
			'<option value="' . $select_type .
			'" id="type-checkout-' . $select_type .
			'" ' . $selected . ' >' . __( $select_type, 'wpecomm-mercadopago-module' ) .
			'</option>';
	endforeach;

	return $select_type_checkout;

}

/*
 * ========================================================================
 * AUXILIARY AND FEEDBACK METHODS (SERVER SIDE)
 * ========================================================================
 */

/**
 * Summary: Check if we have valid credentials.
 * Description: Check if we have valid credentials.
 * @return an array with the results of the evaluation.
 */
function validate_credentials( $client_id, $client_secret ) {

	$result = array(
		'is_valid' => false,
		'is_test_user' => false,
		'site_id' => null,
		'collector_id' => false,
		'country_configs' => null,
		'payment_split_mode' => 'inactive',
		'payment_methods' => null,
		'currency_ratio' => -1,
		'all_payment_methods' => null
	);

	if ( empty( $client_id ) || empty( $client_secret ) ) {
		return $result;
	}

	$mp = new MP(
		WPeComm_MercadoPago_Module::get_module_version(),
		$client_id,
		$client_secret
	);
	$access_token = $mp->get_access_token();
	$get_request = $mp->get( '/users/me?access_token=' . $access_token );

	if ( isset( $get_request['response']['site_id'] ) ) {

		$result['is_test_user'] = in_array( 'test_user', $get_request['response']['tags'] ) ?
			'yes' : 'no';
		$result['site_id'] = $get_request['response']['site_id'];
		$result['collector_id'] = $get_request['response']['id'];
		$result['country_configs'] =
			WPeComm_MercadoPago_Module::get_country_config( $result['site_id'] );
		$result['payment_split_mode'] = $mp->check_two_cards();

		$payment_methods = $mp->get( '/v1/payment_methods/?access_token=' . $access_token );
		$payment_methods = $payment_methods['response'];
		$arr = array();
		foreach ( $payment_methods as $payment ) {
			$arr[] = $payment['id'];
		}
		$result['payment_methods'] = $payment_methods;
		$result['all_payment_methods'] = implode( ',', $arr );

		// Check for auto converstion of currency (only if it is enabled).
		$result['currency_ratio'] = -1;
		if ( get_option( 'mercadopago_certified_currencyconversion' ) == 'active' ) {
			$result['currency_ratio'] = WPeComm_MercadoPago_Module::get_conversion_rate(
				$result['country_configs']['currency']
			);
		}

		$result['is_valid'] = true;

	}

	return $result;

}

function debug_to_console_basic( $data ) {
	// TODO: review debug function as it causes header to be sent
	/*$output	= '<script>console.log( "[WPeComm-Mercado-Pago-Module Logger] => ';
	$output .= json_encode( print_r( $data, true ), JSON_PRETTY_PRINT );
	$output .= '" );</script>';
	echo $output;*/
}

/*
 * ========================================================================
 * IPN MECHANICS (SERVER SIDE)
 * ========================================================================
 */

/**
 * Summary: This call checks any incoming notifications from Mercado Pago server.
 * Description: This call checks any incoming notifications from Mercado Pago server.
 */
function check_ipn_response() {
	@ob_clean();
	//$data = $check_ipn_request_is_valid( $_GET );
	if ( $data ) {
		header( 'HTTP/1.1 200 OK' );
		//do_action( 'valid_mercadopago_ipn_request', $data );
	}
}

/*
 * ========================================================================
 * INSTANTIATIONS
 * ========================================================================
 */

new WPSC_Merchant_MercadoPago_Basic();

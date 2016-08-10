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
 * Text Domain: wpecomm-mercadopago-module
 * Domain Path: /mercadopago-languages/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

include_once "mercadopago-lib/mercadopago.php";
include_once "mercadopago-lib/MPApi.php";

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
		'currency_list' =>  array( 'ARS', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'VEF' ),
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
			trailingslashit(WP_LANG_DIR ) . 'mercadopago-languages/wpecomm-mercadopago-module-' . $locale . '.mo'
		);
		load_plugin_textdomain( 'wpecomm-mercadopago-module', false, dirname( plugin_basename( __FILE__ ) ) . '/mercadopago-languages/' );
	}

}
new WPSC_Merchant_MercadoPago_Basic();

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
	if ($_POST['mercadopago_certified_clientsecret'] != null) {
		update_option('mercadopago_certified_clientsecret', trim($_POST['mercadopago_certified_clientsecret']));
	}
	if ($_POST['mercadopago_certified_description'] != null) {
		update_option('mercadopago_certified_description', trim($_POST['mercadopago_certified_description']));
	}
	if ($_POST['mercadopago_certified_category'] != null) {
		update_option('mercadopago_certified_category', trim($_POST['mercadopago_certified_category']));
	}
	if ($_POST['mercadopago_certified_invoiceprefix'] != null) {
		update_option('mercadopago_certified_invoiceprefix', trim($_POST['mercadopago_certified_invoiceprefix']));
	}
	if ($_POST['mercadopago_certified_typecheckout'] != null) {
		update_option('mercadopago_certified_typecheckout', trim($_POST['mercadopago_certified_typecheckout']));
	}
	if (isset($_POST['mercadopago_certified_iframewidth'])) {
		update_option('mercadopago_certified_iframewidth', trim($_POST['mercadopago_certified_iframewidth']));
	}
	if (isset($_POST['mercadopago_certified_iframeheight'])) {
		update_option('mercadopago_certified_iframeheight', trim($_POST['mercadopago_certified_iframeheight']));
	}
	if ($_POST['mercadopago_certified_autoreturn'] != null) {
		update_option('mercadopago_certified_autoreturn', trim($_POST['mercadopago_certified_autoreturn']));
	}
	if ($_POST['mercadopago_certified_currencyconversion'] != null) {
		update_option('mercadopago_certified_currencyconversion', trim($_POST['mercadopago_certified_currencyconversion']));
	}
	if ($_POST['mercadopago_certified_maxinstallments'] != null) {
		update_option('mercadopago_certified_maxinstallments', trim($_POST['mercadopago_certified_maxinstallments']));
	}
	if (isset($_POST['mercadopago_certified_exmethods'])) {
		$methods = implode(",", $_POST['mercadopago_certified_exmethods']);
		update_option('mercadopago_certified_exmethods', $methods);
	} else {
		update_option('mercadopago_certified_exmethods', '');
	}
	if ($_POST['mercadopago_certified_sandbox'] != null) {
		update_option('mercadopago_certified_sandbox', trim($_POST['mercadopago_certified_sandbox']));
	}
	if ($_POST['mercadopago_certified_debug'] != null) {
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
	if ($result['is_valid'] == true) {
		try {
			// checking the currency
			$currency_message = "";
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

	/*if ($result['is_valid'] == true) {
		$credentials_message = '<img width="12" height="12" src="' .
		plugins_url(
			'wpsc-merchants/mercadopago-images/check.png',
			plugin_dir_path( __FILE__ ) ) . '">' .
		' ' . __( 'Your credentials are <strong>valid</strong> for', 'wpecomm-mercadopago-module' ) .
		': ' . getCountryName( $result['site_id'] ) . ' <img width="18.6" height="12" src="' .
		plugins_url(
			'wpsc-merchants/mercadopago-images/' . $result['site_id'] . '/' . $result['site_id'] . '.png',
			plugin_dir_path( __FILE__ ) ) . '"> ';
	} else {
		$credentials_message = '<img width="12" height="12" src="' .
			plugins_url( 'wpsc-merchants/mercadopago-images/error.png', plugin_dir_path( __FILE__ ) ) . '">' .
			' ' . __( 'Your credentials are <strong>not valid</strong>!', 'wpecomm-mercadopago-module' );
	}*/

	$api_secret_locale = sprintf(
		'<a href="https://www.mercadopago.com/mla/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlb/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlc/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mco/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlm/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mpe/account/credentials?type=custom" target="_blank">%s</a> %s ' .
		'<a href="https://www.mercadopago.com/mlv/account/credentials?type=custom" target="_blank">%s</a>',
		__( 'Argentine', 'wpecomm-mercadopago-module' ),
		__( 'Brazil', 'wpecomm-mercadopago-module' ),
		__( 'Chile', 'wpecomm-mercadopago-module' ),
		__( 'Colombia', 'wpecomm-mercadopago-module' ),
		__( 'Mexico', 'wpecomm-mercadopago-module' ),
		__( 'Peru', 'wpecomm-mercadopago-module' ),
		__( 'or', 'wpecomm-mercadopago-module' ),
		__( 'Venezuela', 'wpecomm-mercadopago-module' )
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

	// send output to generate settings page
	$output = "
	<tr>
		<td></td>
		<td><h3><strong>" . __('Mercado Pago Credentials', 'wpecomm-mercadopago-module' ) . "</strong></h3></td>
	</tr>
	<tr>
		<td>
			<img width='200' height='52' src='" .
				plugins_url( 'wpsc-merchants/mercadopago-images/mplogo.png', plugin_dir_path( __FILE__ ) ) .
			"'>
		</td>
		<td>
			<p><a href='https://wordpress.org/support/view/plugin-reviews/wpecomm-mercado-pago-module?filter=5#postform' target='_blank' class='button button-primary'>" . sprintf(
					__( 'Please, rate us %s on WordPress.org and give your feedback to help improve this module!', 'wpecomm-mercadopago-module' ),
					'&#9733;&#9733;&#9733;&#9733;&#9733;'
					) . "
			</a></p><br>
			<p class='description'>" .
				sprintf( '%s', $credentials_message ) . '<br>' . sprintf(
					__( 'You can obtain your credentials for', 'wpecomm-mercadopago-module' ) . ' %s.',
					$api_secret_locale ) . "
			</p>
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
			(get_option( 'mercadopago_certified_iframeheight') == null ? 800 : get_option( 'mercadopago_certified_iframeheight')) . "' />
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
		<td>" . __('Exclude Payment Methods', 'wpecomm-mercadopago-module') . "</td>
		<td>" .
			methods($result['site_id']) . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>" . __('Test and Debug Options', 'wpecomm-mercadopago-module') . "</strong></h3></td>
	</tr>
	<tr>
		<td>" . __('Enable Sandbox', 'wpecomm-mercadopago-module') . "
		</td>
		<td>" .
			sandbox() . "
			<p class='description'>" . __(
				"This option allows you to test payments inside a sandbox environment.",
				'wpecomm-mercadopago-module'
			) . "</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Debug mode', 'wpecomm-mercadopago-module') . "</td>
		<td>" .
			debugs() . "
			<p class='description'>" .
			__('Enable to display error messages to frontend (not recommended in production environment)', 'wpecomm-mercadopago-module') . "
			</p>
		</td>
	</tr>\n";
	return $output;
}

/*===============================================================================
	FUNCTIONS TO GENERATE VIEWS
================================================================================*/

function methods($country = null) {
	$activemethods = explode(",", get_option('mercadopago_certified_exmethods'));
	if ($country != '' || $country != null) {
		$mp = new MPApi();
		$methods = $mp->getPaymentMethods($country);
		$showmethods = '';
		foreach ($methods as $method) :
			if ($method['id'] != 'account_money') {
				$icon = '<img height="12" src="' .
				$method['secure_thumbnail'] . '">';
				if ($activemethods != null && in_array($method['id'], $activemethods)) {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox" checked="yes" value="' .
						$method['id'] . '"> ' . $icon . " (" . $method['name'] . ')<br /><br />';
				} else {
					$showmethods .=
						'<input name="mercadopago_certified_exmethods[]" type="checkbox" value="' .
						$method['id'] . '"> ' . $icon . " (" . $method['name'] . ')<br /><br />';
				}
			}
		endforeach;
		$showmethods .=
			'<p class="description">' .
				__( 'Select the payment methods that you <strong>don\'t</strong> want to receive with Mercado Pago.', 'wpecomm-mercadopago-module' ) .
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
	$mp = new MPApi();
	$list_category = $mp->getCategories();
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

function sandbox() {
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
}

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
	if (empty($client_id)) {
		$result['site_id'] = null;
		$result['is_valid'] = false;
		return $result;
	}
	if (empty($client_secret)) {
		$result['site_id'] = null;
		$result['is_valid'] = false;
		return $result;
	}
	if (strlen($client_id) > 0 && strlen($client_secret) > 0 ) {
		try {
			$mp = new MP($client_id, $client_secret);
			$result['access_token'] = $mp->get_access_token();
			$mpApi = new MPApi();
			$get_request = $mpApi->getMe($result['access_token']);
			if (isset($get_request['response']['site_id'])) {
				$result['is_test_user'] = isset($get_request['response']['tags']['test_user']);
				$result['site_id'] = $get_request['response']['site_id'];
				// check for auto converstion of currency
				$result['currency_ratio'] = $mpApi->getCurrencyRatio(
					WPSC_Countries::get_currency_code(absint(get_option('currency_type'))),
					getCurrencyId($result['site_id'])
				);
				$result['is_valid'] = true;
				return $result;
			} else {
				$result['site_id'] = null;
				$result['is_valid'] = false;
				return $result;
			}
		} catch ( MercadoPagoException $e ) {
			$result['site_id'] = null;
			$result['is_valid'] = false;
			return $result;
		}
	}
	$result['site_id'] = null;
	$result['is_valid'] = false;
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
	}

}

// Return boolean indicating if currency is supported.
function isSupportedCurrency($site_id) {
	$store_currency_code = WPSC_Countries::get_currency_code(absint(get_option('currency_type')));
	return $store_currency_code == getCurrencyId($site_id);
}

function debug_to_console($data) {
  $output  = "<script>console.log( 'PHP debugger: ";
  $output .= json_encode(print_r($data, true), JSON_PRETTY_PRINT);
  $output .= "' );</script>";
  echo $output;
}

?>

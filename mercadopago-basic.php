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
	//$select_currency[ get_option( 'mercadopago_curcode' ) ] = "selected='selected'";

	$result = validateCredentials(
		get_option('mercadopago_certified_clientid'),
		get_option('mercadopago_certified_clientsecret')
	);

	if ($result['is_valid'] == true) {
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
	}

	if ( get_option( 'mercadopago_certified_server_type' ) == 'sandbox' ) {
		$serverType1 = "checked='checked'";
	} elseif ( get_option( 'mercadopago_certified_server_type' ) == 'production' ) {
		$serverType2 = "checked='checked'";
	}
	$mercadopago_certified_ipn = get_option( 'mercadopago_certified_ipn' );

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
		<td>" . __('Auto Return', 'wpecomm-mercadopago-module' ) . "</td>
		<td>" .
			auto_return() . "
			<p class='description'>" .
				__( 'After the payment, client is automatically redirected.', 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>" . __('Payment Options', 'wpecomm-mercadopago-module' ) . "</strong></h3></td>
	</tr>
	<tr>
		<td>" . __('Max installments', 'wpecomm-mercadopago-module' ) . "</td>
		<td>" .
			installments() . "
			<p class='description'>" .
				$installments_desc . "
			</p>
		</td>
	</tr>
	<tr>
		<td>" . __('Exclude Payment Methods', 'wpecomm-mercadopago-module' ) . "</td>
		<td>" .
			methods($result['site_id']) . "
		</td>
	</tr>
	<tr>
		<td>
		" . __( 'IPN', 'wpecomm-mercadopago-module' ) . "
		</td>
		<td>
			<input type='radio' value='1' name='mercadopago_certified_ipn' id='mercadopago_ipn1' " . checked( $mercadopago_certified_ipn, 1, false ) . " /> <label for='mercadopago_ipn1'>".__('Yes', 'wpecomm-mercadopago-module') . "</label> &nbsp;
			<input type='radio' value='0' name='mercadopago_certified_ipn' id='mercadopago_ipn2' " . checked( $mercadopago_certified_ipn, 0, false ) . " /> <label for='mercadopago_ipn2'>".__('No', 'wpecomm-mercadopago-module') . "</label>
			<p class='description'>
			" . __( "IPN (instant payment notification) will automatically update your sales logs to 'Accepted payment' when a customer's payment is successful. It is highly recommended using IPN, especially if you are selling digital products.", 'wpecomm-mercadopago-module' ) . "
			</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>" . __('Test and Debug Options', 'wpecomm-mercadopago-module' ) . "</strong></h3></td>
	</tr>
	<tr>
		<td>" . __('Server Type', 'wpecomm-mercadopago-module' ) . "
		</td>
		<td>
			<input $serverType1 type='radio' name='mercadopago_certified_server_type' value='sandbox' id='mercadopago_certified_server_type_sandbox' /> <label for='mercadopago_certified_server_type_sandbox'>" .
				__('Sandbox (For testing)', 'wpecomm-mercadopago-module' ) . "</label> &nbsp;
			<input $serverType2 type='radio' name='mercadopago_certified_server_type' value='production' id='mercadopago_certified_server_type_production' /> <label for='mercadopago_certified_server_type_production'>" .
				__('Production', 'wpecomm-mercadopago-module' ) . "</label>
			<p class='description'>" . __(
				"Only use the sandbox server if you have a sandbox account with mercadopago.",
				'wpecomm-mercadopago-module'
			) . "</p>
		</td>
	</tr>\n";

	/*$mercadopago_certified_ipn = get_option( 'mercadopago_certified_ipn' );
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
	}*/

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

/*===============================================================================
	FUNCTIONS TO GENERATE VIEWS
================================================================================*/

function methods($country = null) {
		$activemethods = preg_split("/[\s,]+/",get_option('mercadopago_certified_exmethods'));
		if ($country != '' || $country != null) {
			$mp = new MPApi();
			$methods = $mp->getPaymentMethods($country);
			$showmethods = '';
			foreach ($methods as $method) :
				if ($method['id'] != 'account_money') {
					if ($activemethods != null && in_array($method['id'], $activemethods)) {
						$showmethods .=
							'<input name="mercadopago_certified_exmethods[]" type="checkbox" checked="yes" value="' .
							$method['id'] . '">' . $method['name'].'<br />';
					} else {
						$showmethods .=
							'<input name="mercadopago_certified_exmethods[]" type="checkbox" value="' .
							$method['id'] . '"> '.$method['name'].'<br />';
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
			array("value" => "active", "text" => "Active"),
			array("value" => "inactive", "text" => "Inactive")
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
	$type_checkout = get_option('mercadopago_certified_description');
	$type_checkout = $type_checkout === false || is_null($type_checkout) ? "Redirect" : $type_checkout;
	// type Checkout
	$type_checkout_options = array(
		'Iframe',
		'Lightbox',
		'Redirect'
	);
	$select_type_checkout = '<select name="mercadopago_certified_description" id="type_checkout">';
	foreach ($type_checkout_options as $select_type) :
		$selected = "";
		if ($select_type == $type_checkout) :
			$selected = 'selected="selected"';
		endif;
		$select_type_checkout .=
			'<option value="' . $select_type .
			'" id="type-checkout-' . $select_type .
			'" ' . $selected . ' >' . $select_type .
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

/*===============================================================================
	AUXILIARY FUNCTIONS
================================================================================*/

// check if we have valid credentials.
function validateCredentials($client_id, $client_secret) {
	$result = array();
	if ( empty( $client_id ) ) {
		$result['site_id'] = null;
		$result['is_valid'] = false;
		return $result;
	}
	if ( empty( $client_secret ) ) {
		$result['site_id'] = null;
		$result['is_valid'] = false;
		return $result;
	}
	if ( strlen( $client_id ) > 0 && strlen( $client_secret ) > 0 ) {
		try {
			$mp = new MP( $client_id, $client_secret );
			$result['access_token'] = $mp->get_access_token();
			$get_request = $mp->get( "/users/me?access_token=" . $result['access_token'] );
			if ( isset( $get_request[ 'response' ][ 'site_id' ] ) ) {
				$result['is_test_user'] = in_array( 'test_user', $get_request[ 'response' ][ 'tags' ] );
				$result['site_id'] = $get_request[ 'response' ][ 'site_id' ];
				$result['payments'] = methods($result['site_id']);
				//$payments = $mp->get( "/v1/payment_methods/?access_token=" . $access_token );
				//array_push( $payment_methods, "n/d" );
				//foreach ( $payments[ "response" ] as $payment ) {
				//	array_push( $payment_methods, str_replace( "_", " ", $payment[ 'id' ] ) );
				//}
				// check for auto converstion of currency
				$result['currency_ratio'] = 1;
				$currency_obj = MPRestClient::get_ml( array( "uri" =>
					"/currency_conversions/search?from=" .
					get_woocommerce_currency() .
					"&to=" .
					$getCurrencyId( $site_id )
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

function getCurrencyId( $site_id ) {
	switch ( $site_id ) {
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

function getCountryName( $site_id ) {
	$country = $site_id;
	switch ( $site_id ) {
		case 'MLA': return __( 'Argentine', 'wpecomm-mercadopago-module' );
		case 'MLB': return __( 'Brazil', 'wpecomm-mercadopago-module' );
		case 'MCO': return __( 'Colombia', 'wpecomm-mercadopago-module' );
		case 'MLC': return __( 'Chile', 'wpecomm-mercadopago-module' );
		case 'MLM': return __( 'Mexico', 'wpecomm-mercadopago-module' );
		case 'MLV': return __( 'Venezuela', 'wpecomm-mercadopago-module' );
		case 'MPE': return __( 'Peru', 'wpecomm-mercadopago-module' );
	}

}

?>

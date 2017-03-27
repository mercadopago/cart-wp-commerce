<?php

/**
 * Part of WPeComm Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Marcelo T. Hama (marcelo.hama@mercadolibre.com)
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License: https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * Text Domain: wpecomm-mercadopago-module
 * Domain Path: /mercadopago-languages/
 */

include_once "mercadopago-lib/mercadopago.php";

$nzshpcrt_gateways[$num] = array(
	'name' =>  __( 'Mercado Pago - Ticket', 'wpecomm-mercadopago-module' ),
	'api_version' => 2.0,
	//'image' => WPSC_URL . '/wpsc-merchants/mercadopago-images/mercadopago.png',
	'class_name' => 'WPSC_Merchant_MercadoPago_Ticket',
	'has_recurring_billing' => true,
	'display_name' => __( 'Mercado Pago - Ticket', 'wpecomm-mercadopago-module' ),
	'wp_admin_cannot_cancel' => false,
	'requirements' => array(
		'php_version' => 5.6,
			'extra_modules' => array()
	),
	'form' => 'form_mercadopago_ticket',
	'submit_function' => 'submit_mercadopago_ticket',
	'internalname' => 'WPSC_Merchant_MercadoPago_Ticket'
);

class WPSC_Merchant_MercadoPago_Ticket extends wpsc_merchant {

	var $name = '';
	var $purchase_id = null;

	function __construct( $purchase_id = null, $is_receiving = false ) {
		add_action( 'init', array( $this, 'load_plugin_textdomain_wpecomm' ) );
		add_action( 'wpsc_submit_gateway_options', array( $this, 'callback_submit_options_ticket' ) );
		$this->purchase_id = $purchase_id;
		$this->name = __( 'Mercado Pago - Ticket', 'wpecomm-mercadopago-module' );
		parent::__construct( $purchase_id, $is_receiving );
	}

	public static function callback_submit_options_ticket() {
		$accesstoken = get_option( 'mercadopago_ticket_accesstoken' );
		if ( ! empty( $accesstoken ) ) {
			$mp = new MP( $accesstoken );
			$get_request = $mp->get( "/users/me?access_token=" . get_option( 'mercadopago_ticket_accesstoken' ) );
			// analytics
			if ( isset( $get_request['response']['site_id'] ) ) {
				$checkout_custom_ticket = in_array( 'WPSC_Merchant_MercadoPago_Ticket', get_option( 'custom_gateway_options' ) );
				$infra_data = WPeComm_MercadoPago_Module::get_common_settings();
				$infra_data['checkout_custom_ticket'] = ( $checkout_custom_ticket ? 'true' : 'false' );
				$response = $mp->analytics_save_settings( $infra_data );
			}
		}
	}

	function submit() {
		global $wpdb, $wpsc_cart;

		// labels
		$form_labels = json_decode(stripslashes(
			preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', stripslashes(
				get_option('mercadopago_ticket_checkoutmessage1', '')
			))
		), true);
		if ( $form_labels == '' ) {
			$form_labels = array(
				"form" => array(
					"issuer_selection" => __( 'Please, select the ticket issuer of your preference.', 'wpecomm-mercadopago-module' ),
					"payment_instructions" => __( 'Click [Place order] button. The ticket will be generated and you will be redirected to print it.', 'wpecomm-mercadopago-module' ),
					"ticket_note" => __( 'Important - The order will be confirmed only after the payment approval.', 'wpecomm-mercadopago-module' ),
					"ticket_resume" => __( 'Please, pay the ticket to get your order approved.', 'wpecomm-mercadopago-module'),
					"print_ticket" => __( 'Print the Ticket', 'wpecomm-mercadopago-module' ),
					"payment_approved" => __( "Payment <strong>approved</strong>.", "wpecomm-mercadopago-module" ),
					"payment_in_process" => __( "Your payment under <strong>review</strong>.", "wpecomm-mercadopago-module" ),
					"payment_rejected" => __( "Your payment was <strong>refused</strong>.", "wpecomm-mercadopago-module" ),
					"payment_pending" => __( "Your payment is <strong>pending</strong>.", "wpecomm-mercadopago-module" ),
					"payment_cancelled" => __( "Your payment has been <strong>canceled</strong>.", "wpecomm-mercadopago-module" ),
					"payment_in_mediation" => __( "Your payment is in <strong>mediation</strong>.", "wpecomm-mercadopago-module" ),
					"payment_charged_back" => __( "Your payment has been <strong>refunded</strong>.", "wpecomm-mercadopago-module" ),
					"return_and_try" => __( "Return and Try Again", "wpecomm-mercadopago-module" ),
					"tax_fees" => __( "Tax fees applicable in store", "wpecomm-mercadopago-module" ),
					"shipment" => __( "Shipping service used by store", "wpecomm-mercadopago-module" ),
					"payment_converted" => __( "Payment with converted currency", "wpecomm-mercadopago-module" ),
					"to" => __( "to", "wpecomm-mercadopago-module" ),
					"label_other_bank" => __( "Other Bank", "wpecomm-mercadopago-module" ),
					"label_choose" => __( "Choose", "wpecomm-mercadopago-module" )
				),
				"error" => array(
					"missing_data_checkout" => __( "Your payment failed to be processed.<br/>Are you sure you have set all information?", "wpecomm-mercadopago-module" ),
					"server_error_checkout" => __( "Your payment could not be completed. Please, try again.", "wpecomm-mercadopago-module" )
				)
			);
		}

		if ( isset( $_REQUEST[ 'mercadopago_ticket' ][ 'amount' ] ) &&
			!empty( $_REQUEST[ 'mercadopago_ticket' ][ 'amount' ] ) &&
			isset( $_REQUEST[ 'mercadopago_ticket' ][ 'paymentMethodId' ] ) &&
			!empty( $_REQUEST[ 'mercadopago_ticket' ][ 'paymentMethodId' ] ) ) {

			// Creates the order parameters by checking the cart configuration
			$preference = $this->create_preference($wpdb, $wpsc_cart, $form_labels);
			$preference['payment_method_id'] = $_REQUEST[ 'mercadopago_ticket' ][ 'paymentMethodId' ];

			// Create order preferences with Mercado Pago API request
			try {
				$mp = new MP(
					get_option('mercadopago_ticket_accesstoken')
				);
				$mp->sandbox_mode( false );
				$ticket_info = $mp->create_payment( json_encode( $preference ) );
				if ( is_wp_error( $ticket_info ) ||
					$ticket_info[ 'status' ] < 200 || $ticket_info[ 'status' ] >= 300 ) {
					get_header();
					echo '<h3>' .
						$form_labels['error']['server_error_checkout'] .
						'<br/></h3>' . $ticket_info['response']['message'] .
						'<br/><br/><h3><a href="' . add_query_arg('sessionid',
							$this->cart_data['session_id'], get_option( 'shopping_cart_url' )) .
							'">' . $form_labels['form']['return_and_try'] . '</a></h3><br/><br/>';
					get_footer();
				} else {
					$response = $ticket_info[ 'response' ];
					if ( array_key_exists( 'status', $response ) ) {
						if ( $response[ 'status' ] == "pending" && $response[ 'status_detail' ] == "pending_waiting_payment" ) {
							$message = '<p>' . $form_labels['form']['ticket_resume'] . ' ' .
								'<a id="submit-payment" target="_blank" href="' .
								$response[ 'transaction_details' ][ 'external_resource_url' ] . '" class="button alt">' .
								$form_labels['form']['print_ticket'] . '</a></p><br/>';
							update_option('mercadopago_ticket_order_result', $message);
						}
						$transaction_url_with_sessionid = add_query_arg(
							'sessionid', $this->cart_data['session_id'], get_option( 'transact_url' )
						);
						wp_redirect( $transaction_url_with_sessionid );
					}
				}
			} catch ( MercadoPagoException $e ) {
				get_header();
				echo '<h3>' .
					$form_labels['error']['server_error_checkout'] .
					'<br/><br/>' . '<a href="' . add_query_arg('sessionid',
						$this->cart_data['session_id'], get_option( 'shopping_cart_url' )) .
						'">' . $form_labels['form']['return_and_try'] . '</a></h3><br/><br/>';
				get_footer();
			}
		} else {
			get_header();
			echo '<h3>' .
				$form_labels['error']['missing_data_checkout'] .
				'<br/><br/>' . '<a href="' . add_query_arg('sessionid',
					$this->cart_data['session_id'], get_option( 'shopping_cart_url' )) .
					'">' . $form_labels['form']['return_and_try'] . '</a></h3><br/><br/>';
			get_footer();
		}

		exit();
	}

	// Process the payment of Mercado Pago
	function create_preference($wpdb, $wpsc_cart, $form_labels) {

		// this grabs the purchase log id from the database that refers to the $sessionid
		$purchase_log = $wpdb->get_row(
			"SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS .
			"` WHERE `sessionid`= " . $this->cart_data['session_id'] . " LIMIT 1"
			, ARRAY_A);

		// indicates that the used method is WPSC_Merchant_MercadoPago_Custom
		update_post_meta( $purchase_log['id'], '_used_gateway', 'WPSC_Merchant_MercadoPago_Ticket' );

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
		$site_id = get_option('mercadopago_ticket_siteid', 'MLA');
		if ( empty($site_id) || $site_id == null )
			$site_id = 'MLA';

		// Here we build the array that contains ordered itens, from customer cart
		$items = array();
		$purchase_description = "";
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
					$purchase_description =
						$purchase_description . ' ' .
						( $item->product_name . ' x ' . $item->quantity );
					$unit_price = (
						((float)($item->unit_price)) *
						(float)($item->quantity)
					) * (
						(float)get_option('mercadopago_ticket_currencyratio') > 0 ?
						(float)get_option('mercadopago_ticket_currencyratio') : 1
					);
					if ( $site_id == 'MCO' || $site_id == 'MLC' ) {
						$unit_price = floor( $unit_price );
					}
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
						'category_id' => get_option('mercadopago_ticket_category'),
						'quantity' => 1,
						'unit_price' => $unit_price
					));
				}
			}

			// tax fees cost as an item
			$fee_price = (
				((float)($wpsc_cart->total_tax))
			) * (
				(float)get_option('mercadopago_ticket_currencyratio') > 0 ?
				(float)get_option('mercadopago_ticket_currencyratio') : 1
			);
			if ( $site_id == 'MCO' || $site_id == 'MLC' ) {
				$fee_price = floor( $fee_price );
			}
			array_push($items, array(
				'title' => $form_labels['form']['tax_fees'],
				'description' => $form_labels['form']['tax_fees'],
				'category_id' => get_option('mercadopago_ticket_category'),
				'quantity' => 1,
				'unit_price' => $fee_price
			));

			// shipment cost as an item
			$ship_price = (
				((float)($wpsc_cart->base_shipping)+(float)($wpsc_cart->total_item_shipping))
			) * (
				(float)get_option('mercadopago_ticket_currencyratio') > 0 ?
				(float)get_option('mercadopago_ticket_currencyratio') : 1
			);
			if ( $site_id == 'MCO' || $site_id == 'MLC' ) {
				$ship_price = floor( $ship_price );
			}
			array_push($items, array(
				'title' => $wpsc_cart->selected_shipping_option,
				'description' => $form_labels['form']['shipment'],
				'category_id' => get_option('mercadopago_ticket_category'),
				'quantity' => 1,
				'unit_price' => $ship_price
			));
		}

		// Build additional information from the customer data
		$billing_details = wpsc_get_customer_meta();
		$order_id = ( isset( $billing_details['_wpsc_cart.log_id'] ) ? $billing_details['_wpsc_cart.log_id'][0] : 0 );
		$payer_additional_info = array(
			'first_name' => $arr_info['billingfirstname'],
			'last_name' => $arr_info['billinglastname'],
			//'registration_date' =>
			'phone' => array(
			//'area_code' =>
			'number' => $arr_info['billingphone']
		),
		'address' => array(
			'zip_code' => $arr_info['billingpostcode'],
			//'street_number' =>
			'street_name' => $arr_info['billingaddress'] . ' / ' .
				$arr_info['billingcity'] . ' ' .
				$arr_info['billingstate'] . ' ' .
				$arr_info['billingcountry']
			)
		);

		// Create the shipment address information set
		$shipments = array(
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
		);

		// The payment preference
		$payment_preference = array (
			'transaction_amount' => (float) number_format( $wpsc_cart->calculate_total_price() * (
				(float)get_option('mercadopago_ticket_currencyratio') > 0 ?
				(float)get_option('mercadopago_ticket_currencyratio') : 1
			), 2 ),
			'description' => $purchase_description,
			'payer' => array(
				'email' => $arr_info['billingemail']
			),
			'external_reference' => get_option('mercadopago_ticket_invoiceprefix') . $order_id,
			'additional_info' => array(
				'items' => $items,
				'payer' => $payer_additional_info,
				'shipments' => $shipments
			)
		);

		// Do not set IPN url if it is a localhost!
		$notification_url = get_site_url() . '/wpecomm-mercadopago-module/?wc-api=WC_WPeCommMercadoPago_Gateway';
		if ( !strrpos( $notification_url, "localhost" ) ) {
			$preferences['notification_url'] = workaroundAmperSandBug( $notification_url );
		}

		// Set sponsor ID
		if ( get_option('mercadopago_ticket_istestuser') == "no" ) {
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
				$payment_preference[ 'sponsor_id' ] = $sponsor_id;
		}

		return $payment_preference;
	}

	// Multi-language plugin
	function load_plugin_textdomain_wpecomm() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpecomm-mercadopago-module' );
		load_textdomain(
			'wpecomm-mercadopago-module',
			trailingslashit(WP_LANG_DIR ) . 'mercadopago-languages/wpecomm-mercadopago-module-' . $locale . '.mo'
		);
		load_plugin_textdomain( 'wpecomm-mercadopago-module', false, dirname( plugin_basename( __FILE__ ) ) . '/mercadopago-languages/' );
	}

	public static function update_checkout_status_ticket( $purchase_log_id ) {
		if ( get_post_meta( $purchase_log_id, '_used_gateway', true ) != 'WPSC_Merchant_MercadoPago_Ticket' )
			return;
		$client_id = WPeComm_MercadoPago_Module::get_client_id( get_option('mercadopago_ticket_accesstoken') );
		if ( in_array( 'WPSC_Merchant_MercadoPago_Ticket', (array)get_option( 'custom_gateway_options' ) ) ) {
			echo '<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				var MA = ModuleAnalytics;
				MA.setToken("' . $client_id . '");
				MA.setPaymentType("ticket");
				MA.setCheckoutType("custom");
				MA.put();
			</script>';
		}
	}

	/**
	 * parse_gateway_notification method, receives data from the payment gateway
	 * @access private
	 */
	function parse_gateway_notification() {
		// TODO: implement
	}

	/**
	 * process_gateway_notification method, receives data from the payment gateway
	 * @access public
	 */
	function process_gateway_notification() {
		// TODO: implement
	}

}

function _wpsc_filter_mercadopago_ticket_merchant_customer_notification_raw_message( $message, $notification ) {
	$purchase_log = $notification->get_purchase_log();

	if ( $purchase_log->get( 'gateway' ) == 'WPSC_Merchant_MercadoPago_Ticket' )
		$message = get_option( 'mercadopago_ticket_order_result', 'Your order is <strong>pending</strong>.' ) . "\r\n" . $message;

	return $message;
}

add_filter(
	'wpsc_purchase_log_customer_notification_raw_message',
	'_wpsc_filter_mercadopago_ticket_merchant_customer_notification_raw_message',
	10, 2
);
add_filter(
	'wpsc_purchase_log_customer_html_notification_raw_message',
	'_wpsc_filter_mercadopago_ticket_merchant_customer_notification_raw_message',
	10, 2
);

add_action(
	'wpsc_confirm_checkout',
	array( 'WPSC_Merchant_MercadoPago_Ticket', 'update_checkout_status_ticket' )
);

/*===============================================================================
	AUXILIARY FUNCTIONS
================================================================================*/

// check if we have valid credentials.
function validateCredentials_ticket($access_token) {
	$result = array();
	if (empty($access_token)) {
		$result['site_id'] = null;
		$result['is_valid'] = false;
		$result['is_test_user'] = true;
		$result['currency_ratio'] = -1;
		$result['payment_methods'] = array();
		return $result;
	}
	if ( strlen($access_token) > 0 ) {
		try {
			$mp = new MP($access_token);
			$result['access_token'] = $access_token;
			$get_request = $mp->get( "/users/me?access_token=" . $access_token );
			if (isset($get_request['response']['site_id'])) {
				$result['is_test_user'] = in_array('test_user', $get_request['response']['tags']) ? "yes" : "no";
				$result['site_id'] = $get_request['response']['site_id'];
				// get ticket payments
				$payment_methods = array();
				$payments = $mp->get( "/v1/payment_methods/?access_token=" . $result['access_token'] );
				foreach ( $payments[ "response" ] as $payment ) {
					if ( $payment[ 'payment_type_id' ] != 'account_money' && $payment[ 'payment_type_id' ] != 'credit_card' &&
 						 $payment[ 'payment_type_id' ] != 'debit_card' && $payment[ 'payment_type_id' ] != 'prepaid_card' ) {
						array_push( $payment_methods, $payment );
					}
				}
				$result['payment_methods'] = $payment_methods;
				// check for auto converstion of currency
				$result['currency_ratio'] = -1;
				if ( get_option('mercadopago_ticket_currencyconversion') == "active" ) {
					$currency_obj = MPRestClient::get_ml( array( "uri" =>
						"/currency_conversions/search?from=" .
						WPSC_Countries::get_currency_code(absint(get_option('currency_type'))) .
						"&to=" .
						getCurrencyId_ticket( $result['site_id'] )
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
				$result['is_valid'] = false;
				$result['is_test_user'] = true;
				$result['currency_ratio'] = -1;
				$result['payment_methods'] = array();
				return $result;
			}
		} catch ( MercadoPagoException $e ) {
			$result['site_id'] = null;
			$result['is_valid'] = false;
			$result['is_test_user'] = true;
			$result['currency_ratio'] = -1;
			$result['payment_methods'] = array();
			return $result;
		}
	}
	$result['site_id'] = null;
	$result['is_valid'] = false;
	$result['is_test_user'] = true;
	$result['currency_ratio'] = -1;
	$result['payment_methods'] = array();
	return $result;
}

function getCurrencyId_ticket($site_id) {
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

function getCountryName_ticket($site_id) {
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
function isSupportedCurrency_ticket( $site_id ) {
	$store_currency_code = WPSC_Countries::get_currency_code(absint(get_option('currency_type')));
	return $store_currency_code == getCurrencyId_ticket($site_id);
}

// Fix to URL Problem : #038; replaces & and breaks the navigation
function workaroundAmperSandBug_ticket( $link ) {
	return str_replace('&#038;', '&', $link);
}

function getImagePath_ticket( $image_name ) {
	return plugins_url( 'wpsc-merchants/mercadopago-images/' . $image_name, plugin_dir_path( __FILE__ ) );
}

// Called when saving the settings, to send analytics data
add_action(
	'wpsc_submit_gateway_options',
	array( 'WPSC_Merchant_MercadoPago_Ticket', 'callback_submit_options_ticket' )
);

/*===============================================================================
	CHECKOUT FORM AND SETTINGS PAGE
================================================================================*/

function add_checkout_script_ticket() {
	if ( in_array( 'WPSC_Merchant_MercadoPago_Ticket', (array)get_option( 'custom_gateway_options' ) ) ) {
		$payments = array();
		$gateways = (array)get_option( 'custom_gateway_options' );
		foreach ( $gateways as $g ) {
			$payments[] = $g;
		}
		$payments = str_replace( '-', '_', implode( ', ', $payments ) );
		$email = wp_get_current_user()->user_email;

		$client_id = WPeComm_MercadoPago_Module::get_client_id( get_option('mercadopago_ticket_accesstoken') );

		return '<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				var MA = ModuleAnalytics;
				MA.setToken("' . $client_id . '");
				MA.setPlatform("WPeCommerce");
				MA.setPlatformVersion("' . get_option( 'wpsc_version', '0' ) . '");
				MA.setModuleVersion("' . WPeComm_MercadoPago_Module::VERSION . '");
				MA.setPayerEmail("' . ( $email != null ? $email : "" ) . '");
				MA.setUserLogged( ' . ( empty( $email ) ? 0 : 1 ) . ' );
				MA.setInstalledModules("' . $payments . '");
				MA.post();
			</script>';
	}
}

/**
 * Form Ticket Checkout Returns the Settings Form Fields
 * @access public
 *
 * @since 4.0
 * @return $output string containing Form Fields
 */
function form_mercadopago_ticket() {
	global $wpdb, $wpsc_gateways;

	// labels
	$form_labels = array(
		"form" => array(
			"issuer_selection" => __( 'Please, select the ticket issuer of your preference.', 'wpecomm-mercadopago-module' ),
			"payment_instructions" => __( 'Click [Place order] button. The ticket will be generated and you will be redirected to print it.', 'wpecomm-mercadopago-module' ),
			"ticket_note" => __( 'Important - The order will be confirmed only after the payment approval.', 'wpecomm-mercadopago-module' ),
			"ticket_resume" => __( 'Please, pay the ticket to get your order approved.', 'wpecomm-mercadopago-module'),
			"print_ticket" => __( 'Print the Ticket', 'wpecomm-mercadopago-module' ),
			"payment_approved" => __( "Payment <strong>approved</strong>.", "wpecomm-mercadopago-module" ),
			"payment_in_process" => __( "Your payment under <strong>review</strong>.", "wpecomm-mercadopago-module" ),
			"payment_rejected" => __( "Your payment was <strong>refused</strong>.", "wpecomm-mercadopago-module" ),
			"payment_pending" => __( "Your payment is <strong>pending</strong>.", "wpecomm-mercadopago-module" ),
			"payment_cancelled" => __( "Your payment has been <strong>canceled</strong>.", "wpecomm-mercadopago-module" ),
			"payment_in_mediation" => __( "Your payment is in <strong>mediation</strong>.", "wpecomm-mercadopago-module" ),
			"payment_charged_back" => __( "Your payment has been <strong>refunded</strong>.", "wpecomm-mercadopago-module" ),
			"return_and_try" => __( "Return and Try Again", "wpecomm-mercadopago-module" ),
			"tax_fees" => __( "Tax fees applicable in store", "wpecomm-mercadopago-module" ),
			"shipment" => __( "Shipping service used by store", "wpecomm-mercadopago-module" ),
			"payment_converted" => __( "Payment with converted currency", "wpecomm-mercadopago-module" ),
			"to" => __( "to", "wpecomm-mercadopago-module" ),
			"label_other_bank" => __( "Other Bank", "wpecomm-mercadopago-module" ),
			"label_choose" => __( "Choose", "wpecomm-mercadopago-module" )
		),
		"error" => array(
			"missing_data_checkout" => __( "Your payment failed to be processed.<br/>Are you sure you have set all information?", "wpecomm-mercadopago-module" ),
			"server_error_checkout" => __( "Your payment could not be completed. Please, try again.", "wpecomm-mercadopago-module" )
		)
	);

	$result = validateCredentials_ticket(
		get_option('mercadopago_ticket_accesstoken')
	);
	$store_currency = WPSC_Countries::get_currency_code(absint(get_option('currency_type')));

	// Trigger API to get payment methods and site_id, also validates Access_token.
	$currency_message = "";
	if ($result['is_valid'] == true) {
		try {
			// checking the currency
			if (!isSupportedCurrency_ticket($result['site_id'])) {
				if (get_option('mercadopago_ticket_currencyconversion') == 'inactive') {
					$result['currency_ratio'] = -1;
					$currency_message .= '<img width="12" height="12" src="' .
						plugins_url( 'wpsc-merchants/mercadopago-images/warning.png', plugin_dir_path( __FILE__ ) ) . '">' .
						' ' . __( 'ATTENTION: The currency', 'wpecomm-mercadopago-module' ) . ' ' . $store_currency .
						' ' . __( 'defined in WPeCommerce is different from the one used in your credentials country.<br>The currency for transactions in this payment method will be', 'wpecomm-mercadopago-module' ) .
						' ' . getCurrencyId_ticket( $result['site_id'] ) . ' (' . getCountryName_ticket( $result['site_id'] ) . ').' .
						' ' . __( 'Currency conversions should be made outside this module.', 'wpecomm-mercadopago-module' );
				} else if (get_option('mercadopago_ticket_currencyconversion') == 'active' && $result['currency_ratio'] != -1 ) {
					$currency_message .= '<img width="12" height="12" src="' .
						plugins_url( 'wpsc-merchants/mercadopago-images/check.png', plugin_dir_path( __FILE__ ) ) . '">' .
						' ' . __( 'CURRENCY CONVERTED: The currency conversion ratio from', 'wpecomm-mercadopago-module' )  . ' ' . $store_currency .
						' ' . __( 'to', 'wpecomm-mercadopago-module' ) . ' ' . getCurrencyId_ticket( $result['site_id'] ) .
						__( ' is: ', 'wpecomm-mercadopago-module' ) . $result['currency_ratio'] . ".";
				} else {
					$result['currency_ratio'] = -1;
					$currency_message .= '<img width="12" height="12" src="' .
						plugins_url( 'wpsc-merchants/mercadopago-images/error.png', plugin_dir_path( __FILE__ ) ) . '">' .
						' ' . __( 'ERROR: It was not possible to convert the unsupported currency', 'wpecomm-mercadopago-module' ) . ' ' . $store_currency .
						' '	. __( 'to', 'wpecomm-mercadopago-module' ) . ' ' . getCurrencyId_ticket( $result['site_id'] ) . '.' .
						' ' . __( 'Currency conversions should be made outside this module.', 'wpecomm-mercadopago-module' );
				}
			} else {
				$result['currency_ratio'] = -1;
			}
			$credentials_message = '<img width="12" height="12" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/check.png', plugin_dir_path( __FILE__ ) ) . '">' .
				' ' . __( 'Your credentials are <strong>valid</strong> for', 'wpecomm-mercadopago-module' ) .
				': ' . getCountryName_ticket( $result['site_id'] ) . ' <img width="18.6" height="12" src="' .
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
		'<a href="https://www.mercadopago.com/mla/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlb/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlc/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mco/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlm/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mpe/account/credentials?type=custom" target="_blank">%s</a>, ' .
		'<a href="https://www.mercadopago.com/mlv/account/credentials?type=custom" target="_blank">%s</a> %s ' .
		'<a href="https://www.mercadopago.com/mlu/account/credentials?type=custom" target="_blank">%s</a>',
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
	// Get callbacks
	if (get_option('mercadopago_ticket_url_sucess') != '') {
		$url_sucess = get_option('mercadopago_ticket_url_sucess');
	} else {
		$url_sucess = get_site_url();
	}
	if (get_option('mercadopago_ticket_url_pending') != '') {
		$url_pending = get_option('mercadopago_ticket_url_pending');
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
				<input type='hidden' size='60' value='" . $result['site_id'] . "' name='mercadopago_ticket_siteid' />
				<input type='hidden' size='60' value='" .
					json_encode( $form_labels ) .
					"' name='mercadopago_ticket_checkoutmessage1' />
				<input type='hidden' size='60' value='" . $result['is_test_user'] . "' name='mercadopago_ticket_istestuser' />
				<input type='hidden' size='60' value='" . $result['currency_ratio'] . "' name='mercadopago_ticket_currencyratio' />
				<input type='hidden' size='60' value='" .
					json_encode( $result['payment_methods'] ) .
					"' name='mercadopago_ticket_payment_methods' />
				<strong>" . __('Mercado Pago Credentials', 'wpecomm-mercadopago-module' ) . "</strong>
				<p class='description'>" .
					sprintf( '%s', $credentials_message ) . '<br>' . sprintf(
						__( 'You can obtain your credentials for', 'wpecomm-mercadopago-module' ) . ' %s.',
						$api_secret_locale ) . "
				</p>
			</td>
		</tr>
		<tr>
			<td>" . __('Access Token', 'wpecomm-mercadopago-module' ) . "</td>
			<td>
				<input type='text' size='60' value='" . get_option('mercadopago_ticket_accesstoken') . "' name='mercadopago_ticket_accesstoken' />
				<p class='description'>
					" . __( "Insert your Mercado Pago Access Token.", 'wpecomm-mercadopago-module' ) . "
				</p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><h3><strong>" . __('Checkout Options', 'wpecomm-mercadopago-module' ) . "</strong></h3></td>
		</tr>" .
		/*<tr>
			<td>" . __('Coupons', 'wpecomm-mercadopago-module' ) . "</td>
			<td>" .
				coupom_ticket() . "
				<p class='description'>" .
					__( "If there is a Mercado Pago campaign, allow your store to give discounts to customers.", 'wpecomm-mercadopago-module' ) . "
				</p>
			</td>
		</tr>*/
		"<tr>
			<td>" . __('Store Category', 'wpecomm-mercadopago-module' ) . "</td>
			<td>" .
				category_ticket() . "
				<p class='description'>" .
					__( "Define which type of products your store sells.", 'wpecomm-mercadopago-module' ) . "
				</p>
			</td>
		</tr>
		<tr>
			<td>" . __('Store Identificator', 'wpecomm-mercadopago-module' ) . "</td>
			<td>
				<input type='text' size='60' value='" . (get_option( 'mercadopago_ticket_invoiceprefix') == "" ? "WPeComm-" : get_option( 'mercadopago_ticket_invoiceprefix')) . "' name='mercadopago_ticket_invoiceprefix' />
				<p class='description'>" .
					__( "Please, inform a prefix to your store.", "wpecomm-mercadopago-module" ) . ' ' .
					__( "If you use your Mercado Pago account on multiple stores you should make sure that this prefix is unique as Mercado Pago will not allow orders with same identificators.", "wpecomm-mercadopago-module" ) . "
				</p>
			</td>
		</tr>
		<tr>
			<td>" . __('URL Approved Payment', 'wpecomm-mercadopago-module') . "</td>
			<td>
				<input name='mercadopago_ticket_url_sucess' type='text' value='" . $url_sucess . "'/>
				<p class='description'>" .
					__( 'This is the URL where the customer is redirected if his payment is approved.',
						'wpecomm-mercadopago-module' ) . "
				</p>
			</td>
		</tr>
		<tr>
			<td>" . __('URL Pending Payment', 'wpecomm-mercadopago-module') . "</td>
			<td>
				<input name='mercadopago_ticket_url_pending' type='text' value='" . $url_pending . "'/>
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
			<td>" . __('Currency Conversion', 'wpecomm-mercadopago-module') . "</td>
			<td>" .
				currency_conversion_ticket() . "
				<p class='description'>" .
					__('If the used currency in WPeCommerce is different or not supported by Mercado Pago, convert values of your transactions using Mercado Pago currency ratio', 'wpecomm-mercadopago-module') . "<br >" .
					__(sprintf('%s', $currency_message)) . "
				</p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><h3><strong>" . __('Test and Debug Options', 'wpecomm-mercadopago-module') . "</strong></h3></td>
		</tr>
		<tr>
			<td>" . __('Debug mode', 'wpecomm-mercadopago-module') . "</td>
			<td>" .
				debugs_ticket() . "
				<p class='description'>" .
				__('Enable to display log messages in browser console (not recommended in production environment)', 'wpecomm-mercadopago-module') . "
				</p>
			</td>
		</tr>\n";
	return $output;
}

/**
 * Saving of Mercado Pago Ticket Checkout Settings
 * @access public
 *
 * @since 4.2.0
 */
function submit_mercadopago_ticket() {
	if (isset($_POST['mercadopago_ticket_accesstoken'])) {
		update_option('mercadopago_ticket_accesstoken', trim($_POST['mercadopago_ticket_accesstoken']));
	}
	if (isset($_POST['mercadopago_ticket_siteid'])) {
		update_option('mercadopago_ticket_siteid', trim($_POST['mercadopago_ticket_siteid']));
	}
	if (isset($_POST['mercadopago_ticket_checkoutmessage1'])) {
		update_option('mercadopago_ticket_checkoutmessage1', trim($_POST['mercadopago_ticket_checkoutmessage1']));
	}
	if (isset($_POST['mercadopago_ticket_istestuser'])) {
		update_option('mercadopago_ticket_istestuser', trim($_POST['mercadopago_ticket_istestuser']));
	}
	if (isset($_POST['mercadopago_ticket_currencyratio'])) {
		update_option('mercadopago_ticket_currencyratio', trim($_POST['mercadopago_ticket_currencyratio']));
	}
	if (isset($_POST['mercadopago_ticket_payment_methods'])) {
		update_option('mercadopago_ticket_payment_methods', trim($_POST['mercadopago_ticket_payment_methods']));
	}
	/*if (isset($_POST['mercadopago_ticket_coupom'])) {
		update_option('mercadopago_ticket_coupom', trim($_POST['mercadopago_ticket_coupom']));
	}*/
	if (isset($_POST['mercadopago_ticket_category'])) {
		update_option('mercadopago_ticket_category', trim($_POST['mercadopago_ticket_category']));
	}
	if (isset($_POST['mercadopago_ticket_invoiceprefix'])) {
		update_option('mercadopago_ticket_invoiceprefix', trim($_POST['mercadopago_ticket_invoiceprefix']));
	}
	if (isset($_POST['mercadopago_ticket_currencyconversion'])) {
		update_option('mercadopago_ticket_currencyconversion', trim($_POST['mercadopago_ticket_currencyconversion']));
	}
	if (isset($_POST['mercadopago_ticket_url_sucess'])) {
		update_option('mercadopago_ticket_url_sucess', trim($_POST['mercadopago_ticket_url_sucess']));
	}
	if (isset($_POST['mercadopago_ticket_url_pending'])) {
		update_option('mercadopago_ticket_url_pending', trim($_POST['mercadopago_ticket_url_pending']));
	}
	if ( isset( $_POST['mercadopago_ticket_accesstoken'] ) ) {
		$mp = new MP(
			get_option( 'mercadopago_ticket_accesstoken' )
		);
		$get_request = $mp->get( "/users/me?access_token=" . get_option( 'mercadopago_ticket_accesstoken' ) );
		// analytics
		if ( isset( $get_request['response']['site_id'] ) ) {
			$infra_data = WPeComm_MercadoPago_Module::get_common_settings();
			$infra_data['checkout_custom_ticket_coupon'] = 'false';
			$response = $mp->analytics_save_settings( $infra_data );
		}
	}
	if (isset($_POST['mercadopago_ticket_debug'])) {
		update_option('mercadopago_ticket_debug', trim($_POST['mercadopago_ticket_debug']));
	}
	return true;
}

if ( in_array( 'WPSC_Merchant_MercadoPago_Ticket', (array)get_option( 'custom_gateway_options' ) ) ) {

	$mp = new MP( // Create MP object and set sandbox mode to false
		get_option('mercadopago_ticket_accesstoken')
	);
	$isTestUser = get_option('mercadopago_ticket_istestuser');
	$mp->sandbox_mode( false );

	// Get needed variables
	$site_id = get_option('mercadopago_custom_siteid', 'MLA');
	if ( empty($site_id) || $site_id == null )
		$site_id = 'MLA';
	$amount = wpsc_cart_total(false);
	$account_currency = getCurrencyId_ticket( $site_id );
	$currency_ratio = get_option('mercadopago_ticket_currencyratio');
	$wpecommerce_currency = WPSC_Countries::get_currency_code(absint(get_option('currency_type')));

	// Build payment banner url
	$banners_mercadopago_standard = array(
		"MLA" => 'MLA/standard.jpg',
		"MLB" => 'MLB/standard.jpg',
		"MCO" => 'MCO/standard.jpg',
		"MLC" => 'MLC/standard.gif',
		"MPE" => 'MPE/standard.png',
		"MLV" => 'MLV/standard.jpg',
		"MLM" => 'MLM/standard.jpg',
		"MLU" => 'MLU/standard.png'
	);

	// labels
	$form_labels = json_decode(stripslashes(
		preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', stripslashes(
			get_option('mercadopago_ticket_checkoutmessage1', '')
		))
	), true);
	if ( $form_labels == '' ) {
		$form_labels = array(
			"form" => array(
				"issuer_selection" => __( 'Please, select the ticket issuer of your preference.', 'wpecomm-mercadopago-module' ),
				"payment_instructions" => __( 'Click [Place order] button. The ticket will be generated and you will be redirected to print it.', 'wpecomm-mercadopago-module' ),
				"ticket_note" => __( 'Important - The order will be confirmed only after the payment approval.', 'wpecomm-mercadopago-module' ),
				"ticket_resume" => __( 'Please, pay the ticket to get your order approved.', 'wpecomm-mercadopago-module'),
				"print_ticket" => __( 'Print the Ticket', 'wpecomm-mercadopago-module' ),
				"payment_approved" => __( "Payment <strong>approved</strong>.", "wpecomm-mercadopago-module" ),
				"payment_in_process" => __( "Your payment under <strong>review</strong>.", "wpecomm-mercadopago-module" ),
				"payment_rejected" => __( "Your payment was <strong>refused</strong>.", "wpecomm-mercadopago-module" ),
				"payment_pending" => __( "Your payment is <strong>pending</strong>.", "wpecomm-mercadopago-module" ),
				"payment_cancelled" => __( "Your payment has been <strong>canceled</strong>.", "wpecomm-mercadopago-module" ),
				"payment_in_mediation" => __( "Your payment is in <strong>mediation</strong>.", "wpecomm-mercadopago-module" ),
				"payment_charged_back" => __( "Your payment has been <strong>refunded</strong>.", "wpecomm-mercadopago-module" ),
				"return_and_try" => __( "Return and Try Again", "wpecomm-mercadopago-module" ),
				"tax_fees" => __( "Tax fees applicable in store", "wpecomm-mercadopago-module" ),
				"shipment" => __( "Shipping service used by store", "wpecomm-mercadopago-module" ),
				"payment_converted" => __( "Payment with converted currency", "wpecomm-mercadopago-module" ),
				"to" => __( "to", "wpecomm-mercadopago-module" ),
				"label_other_bank" => __( "Other Bank", "wpecomm-mercadopago-module" ),
				"label_choose" => __( "Choose", "wpecomm-mercadopago-module" ),
			)
		);
	}
	// payment methods
	$payment_methods = json_decode(stripslashes(
		preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', stripslashes(
			get_option('mercadopago_ticket_payment_methods')
		))
	), true);
	if (empty($payment_methods) || $payment_methods == null) {
		$payment_methods = array();
	}

	// header
	$payment_header =
		'<div width="100%" style="margin:0px; padding:16px 36px 16px 36px; background:white;
			border-style:solid; border-color:#DDDDDD" border-radius:1.0px;">
			<img class="logo" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/mplogo.png', plugin_dir_path( __FILE__ ) ) . '"
				" width="156" height="40" />' . ( count( $payment_methods ) > 1 ? '<img class="logo" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/boleto.png', plugin_dir_path( __FILE__ ) ) . '
				" width="90" height="40" style="float:right;"/>' : getPaymentMethodsImages( $payment_methods ) ) .
		'</div>';
	// payment method
	$mercadopago_form =
		'<fieldset style="background:white;">
			<div style="padding:0px 36px 0px 36px;">

				<p>' .
					( count( $payment_methods ) > 1 ? $form_labels[ 'form' ][ 'issuer_selection' ] . ' ' : '' ) .
					$form_labels[ 'form' ][ 'payment_instructions' ] . '<br />' .
					$form_labels[ 'form' ][ 'ticket_note' ] . ( $currency_ratio > 0 ?
						" (" . $form_labels['form']['payment_converted'] . " " . $wpecommerce_currency . " " .
						$form_labels['form']['to'] . " " . $account_currency . ")" : '' ) .
				'</p>' .

				drawTicketOptions( $payment_methods ) .

				'<div class="mp-box-inputs mp-line">
					<div class="mp-box-inputs mp-col-25">
						<div id="mp-box-loading">
						</div>
					</div>
				</div>

				<div class="mp-box-inputs mp-col-100" id="mercadopago-utilities">
					<input type="hidden" id="site_id" name="mercadopago_ticket[site_id]"/>
					<input type="hidden" id="amountTicket" value="' . $amount . '" name="mercadopago_ticket[amount]"/>
				</div>

			</div>
		</fieldset>';

	$page_js = add_checkout_script_ticket();
	$page_js .= '
		<script src="' . plugins_url( 'wpsc-merchants/mercadopago-lib/MPv1Ticket.js?no_cache=' .
			time(), plugin_dir_path( __FILE__ ) ) . '"></script>
		<script type="text/javascript">
			var mercadopago_site_id = "' . $site_id . '";
			MPv1Ticket.paths.loading = "' . getImagePath_ticket('loading.gif') . '";
			MPv1Ticket.getAmount = function() {
				return document.querySelector(MPv1Ticket.selectors.amount).value;
			}
			MPv1Ticket.Initialize(mercadopago_site_id);
		</script>';

	$page_header =
		'<head>
			<link rel="stylesheet" id="ticket-checkout-mercadopago" href="' .
				plugins_url( 'wpsc-merchants/mercadopago-lib/custom_checkout_mercadopago.css', plugin_dir_path( __FILE__ ) ) .
				'?ver=4.5.3" type="text/css" media="all">
			<script src="' . plugins_url( 'wpsc-merchants/mercadopago-lib/MPv1Ticket.js?no_cache=' .
				time(), plugin_dir_path( __FILE__ ) ) . '"></script>
		</head>';

	$output = '
		<tr>
			<td>' .
				$page_header .
				'<div style="width: 600px;">' . $payment_header . '</div>' .
				'<div style="width: 600px;">' . $mercadopago_form  . '</div>'  .
				$page_js .
			'</td>
		</tr>';
	$gateway_checkout_form_fields[$nzshpcrt_gateways[$num]['internalname']] = $output;
}

/*===============================================================================
	FUNCTIONS TO GENERATE VIEWS
================================================================================*/

function drawTicketOptions( $payment_methods ) {
	$output = "";
	if ( count( $payment_methods ) > 1 ) {
		$atFirst = true;
		$output .= '<div class="mp-box-inputs mp-col-100">';
		foreach ( $payment_methods as $payment ) {
			$output .=
				'<div class="mp-box-inputs mp-line">
					<div id="paymentMethodId" class="mp-box-inputs mp-col-10">
						<input type="radio" class="input-radio" name="mercadopago_ticket[paymentMethodId]"
							style="height:16px; width:16px;" value="' . $payment[ "id" ] . '" ' .
							( $atFirst ? 'checked="checked"' : '' ) . ' />
					</div>
					<div class="mp-box-inputs mp-col-45">
						<label>
							<img src="' . $payment[ "secure_thumbnail" ] . '" alt="' . $payment[ 'name' ] . '" />
							&nbsp;(' . $payment[ "name" ] . ')
						</label>
					</div>
				</div>';
			$atFirst = false;
		}
		$output .= '</div>';
	} else {
		$output .=
			'<div class="mp-box-inputs mp-col-100" style="display:none;">
				<select id="paymentMethodId" name="mercadopago_ticket[paymentMethodId]">';
		foreach ( $payment_methods as $payment ) {
			$output .=
				'<option value="' . $payment[ "id" ] . '"style="padding: 8px;
					background: url( "https://img.mlstatic.com/org-img/MP3/API/logos/bapropagos.gif" );
					98% 50% no-repeat;"> ' . $payment[ "name" ] . '</option>';
		}
		$output .= '</select></div>';
	}
	return $output;
}

function getPaymentMethodsImages( $payment_methods ) {
	$html = "";
	foreach ( $payment_methods as $payment ) {
		$html .= '<img class="logo" src="' . $payment[ 'secure_thumbnail' ] . '" width="90" height="40" style="float:right;"/>';
		break;
	}
	return $html;
}

function category_ticket() {
	$category = get_option('mercadopago_ticket_category');
	$category = $category === false || is_null($category) ? "others" : $category;
	// category marketplace
	$list_category = MPRestClient::get( array( "uri" => "/item_categories" ) );
	$list_category = $list_category["response"];
	$select_category = '<select name="mercadopago_ticket_category" id="category" style="max-width:600px;>';
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

/*function coupom_ticket() {
	$coupom = get_option('mercadopago_ticket_coupom');
	$coupom = $coupom === false || is_null($coupom) ? "inactive" : $coupom;
	$coupom_options = array(
		array("value" => "active", "text" => "Active"),
		array("value" => "inactive", "text" => "Inactive")
	);
	$select_coupom = '<select name="mercadopago_ticket_coupom" id="coupom">';
	foreach ($coupom_options as $op_coupom) :
		$selected = "";
		if ($op_coupom['value'] == $coupom) :
		$selected = 'selected="selected"';
		endif;
		$select_coupom .=
			'<option value="' . $op_coupom['value'] .
			'" id="coupom-' . $op_coupom['value'] .
			'" ' . $selected . '>' . __($op_coupom['text'], "wpecomm-mercadopago-module") .
			'</option>';
	endforeach;
	$select_coupom .= "</select>";
	return $select_coupom;
}*/

function currency_conversion_ticket() {
	$currencyconversion = get_option('mercadopago_ticket_currencyconversion');
	$currencyconversion = $currencyconversion === false || is_null($currencyconversion) ? "inactive" : $currencyconversion;
	$currencyconversion_options = array(
		array("value" => "active", "text" => "Active"),
		array("value" => "inactive", "text" => "Inactive")
	);
	$select_currencyconversion = '<select name="mercadopago_ticket_currencyconversion" id="currencyconversion">';
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

function debugs_ticket() {
	if (get_option('mercadopago_ticket_debug') == null || get_option('mercadopago_ticket_debug') == '') {
		$mercadopago_ticket_debug = 'No';
	} else {
		$mercadopago_ticket_debug = get_option('mercadopago_ticket_debug');
	}
	$debugs = array('No','Yes');
	$showdebugs = '<select name="mercadopago_ticket_debug">';
	foreach ($debugs as  $debug ) :
		if ($debug == $mercadopago_ticket_debug) {
			$showdebugs .= '<option value="' . $debug . '" selected="selected">' . __($debug, "wpecomm-mercadopago-module") . '</option>';
		} else {
			$showdebugs .= '<option value="' . $debug . '">' . __($debug, "wpecomm-mercadopago-module") . '</option>';
		}
	endforeach;
	$showdebugs .= '</select>';
	return $showdebugs;
}

/*===============================================================================
	INSTANTIATIONS
================================================================================*/

?>

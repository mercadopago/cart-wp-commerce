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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once 'mercadopago-lib/mercadopago.php';

$nzshpcrt_gateways[$num] = array(
	'name' => __( 'Mercado Pago - Ticket', 'wpecomm-mercadopago-module' ),
	'api_version' => 2.0,
	'class_name' => 'WPSC_Merchant_MercadoPago_Ticket',
	'display_name' => __( 'Mercado Pago - Ticket', 'wpecomm-mercadopago-module' ),
	'requirements' => array(
		// So that you can restrict merchant modules to PHP 5, if you use PHP 5 features.
		'php_version' => 5.6,
		// For modules that may not be present, like curl.
		'extra_modules' => array()
	),
	'internalname' => 'WPSC_Merchant_MercadoPago_Ticket',
	// All array members below here are legacy, and use the code in mercadopago_multiple.php.
	'form' => 'form_mercadopago_ticket',
	'has_recurring_billing' => true,
	'submit_function' => 'submit_mercadopago_ticket',
	'wp_admin_cannot_cancel' => false
);

class WPSC_Merchant_MercadoPago_Ticket extends wpsc_merchant {

	var $name = '';
  	var $purchase_id = null;

	function __construct( $purchase_id = null, $is_receiving = false ) {
		add_action( 'init', array( $this, 'load_plugin_textdomain_wpecomm' ) );
		$this->purchase_id = $purchase_id;
		$this->name = __( 'Mercado Pago - Ticket', 'wpecomm-mercadopago-module' );
	 	parent::__construct( $purchase_id, $is_receiving );
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

	function submit() {

		global $wpdb, $wpsc_cart;

		// Labels.
		$form_labels = json_decode( stripslashes( preg_replace(
			'/u([\da-fA-F]{4})/',
			'&#x\1;',
			stripslashes( get_option( 'mercadopago_ticket_checkoutmessage1', '' ) )
		) ), true);

		if ( $form_labels == '' ) {
			$form_labels = array(
				'form' => array(
					'issuer_selection' => __( 'Please, select the ticket issuer of your preference.', 'wpecomm-mercadopago-module' ),
					'payment_instructions' => __( 'Click [Place order] button. The ticket will be generated and you will be redirected to print it.', 'wpecomm-mercadopago-module' ),
					'ticket_note' => __( 'Important - The order will be confirmed only after the payment approval.', 'wpecomm-mercadopago-module' ),
					'ticket_resume' => __( 'Please, pay the ticket to get your order approved.', 'wpecomm-mercadopago-module'),
					'print_ticket' => __( 'Print the Ticket', 'wpecomm-mercadopago-module' ),
					'payment_approved' => __( 'Payment <strong>approved</strong>.', 'wpecomm-mercadopago-module' ),
					'payment_in_process' => __( 'Your payment under <strong>review</strong>.', 'wpecomm-mercadopago-module' ),
					'payment_rejected' => __( 'Your payment was <strong>refused</strong>.', 'wpecomm-mercadopago-module' ),
					'payment_pending' => __( 'Your payment is <strong>pending</strong>.', 'wpecomm-mercadopago-module' ),
					'payment_cancelled' => __( 'Your payment has been <strong>canceled</strong>.', 'wpecomm-mercadopago-module' ),
					'payment_in_mediation' => __( 'Your payment is in <strong>mediation</strong>.', 'wpecomm-mercadopago-module' ),
					'payment_charged_back' => __( 'Your payment has been <strong>refunded</strong>.', 'wpecomm-mercadopago-module' ),
					'return_and_try' => __( 'Return and Try Again', 'wpecomm-mercadopago-module' ),
					'tax_fees' => __( 'Tax fees applicable in store', 'wpecomm-mercadopago-module' ),
					'shipment' => __( 'Shipping service used by store', 'wpecomm-mercadopago-module' ),
					'payment_converted' => __( 'Payment with converted currency', 'wpecomm-mercadopago-module' ),
					'to' => __( 'to', 'wpecomm-mercadopago-module' ),
					'label_other_bank' => __( 'Other Bank', 'wpecomm-mercadopago-module' ),
					'label_choose' => __( 'Choose', 'wpecomm-mercadopago-module' )
				),
				'error' => array(
					'missing_data_checkout' => __( 'Your payment failed to be processed.<br/>Are you sure you have set all information?', 'wpecomm-mercadopago-module' ),
					'server_error_checkout' => __( 'Your payment could not be completed. Please, try again.', 'wpecomm-mercadopago-module' )
				)
			);
		}

		if ( ! empty( $_REQUEST['mercadopago_ticket']['amount'] ) &&
		! empty( $_REQUEST['mercadopago_ticket']['paymentMethodId'] ) ) {

			// Creates the order parameters by checking the cart configuration.
			$preference = $this->build_payment_preference( $wpdb, $wpsc_cart, $form_labels );
			$preference['payment_method_id'] = $_REQUEST['mercadopago_ticket']['paymentMethodId'];

			// Create order preferences with Mercado Pago API request.
			try {

				$mp = new MP(
					WPeComm_MercadoPago_Module::get_module_version(),
					get_option( 'mercadopago_ticket_accesstoken' )
				);
				$mp->sandbox_mode( false );

				$ticket_info = $mp->create_payment( json_encode( $preference ) );

				if ( is_wp_error( $ticket_info ) ||
				$ticket_info['status'] < 200 || $ticket_info['status'] >= 300 ) {

					get_header();
					echo '<h3>' .
						$form_labels['error']['server_error_checkout'] .
						//'<br>' . json_encode( $preference ) .
						'<br/></h3>' . $ticket_info['response']['message'] .
						'<br/><br/><h3><a href="' . add_query_arg( 'sessionid',
							$this->cart_data['session_id'], get_option( 'shopping_cart_url' ) ) .
							'">' . $form_labels['form']['return_and_try'] . '</a></h3><br/><br/>';
					get_footer();

				} else {

					$response = $ticket_info['response'];
					if ( array_key_exists( 'status', $response ) ) {
						if ( $response['status'] == 'pending' &&
						$response['status_detail'] == 'pending_waiting_payment' ) {
							$message = '<p>' . $form_labels['form']['ticket_resume'] . ' ' .
								'<a id="submit-payment" target="_blank" href="' .
								$response[ 'transaction_details' ][ 'external_resource_url' ] .
								'" class="button alt">' . $form_labels['form']['print_ticket'] .
								'</a></p><br/>';
							update_option( 'mercadopago_ticket_order_result', $message );
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
					'<br/><br/>' . '<a href="' . add_query_arg( 'sessionid',
						$this->cart_data['session_id'], get_option( 'shopping_cart_url' ) ) .
						'">' . $form_labels['form']['return_and_try'] . '</a></h3><br/><br/>';
				get_footer();

			}

		} else {
			get_header();
			echo '<h3>' .
				$form_labels['error']['missing_data_checkout'] .
				'<br/><br/>' . '<a href="' . add_query_arg( 'sessionid',
					$this->cart_data['session_id'], get_option( 'shopping_cart_url' ) ) .
					'">' . $form_labels['form']['return_and_try'] . '</a></h3><br/><br/>';
			get_footer();
		}

		exit();

	}

	/**
	 * Summary: Build Mercado Pago preference.
	 * Description: Create Mercado Pago preference and get init_point URL based in the order options
	 * from the cart.
	 * @return the preference object.
	 */
	function build_payment_preference( $wpdb, $wpsc_cart, $form_labels ) {

		// This grabs the purchase log id from the database that refers to the $sessionid.
		$purchase_log = $wpdb->get_row(
			'SELECT * FROM `' . WPSC_TABLE_PURCHASE_LOGS .
			'` WHERE `sessionid`= ' . $this->cart_data['session_id'] . ' LIMIT 1'
			, ARRAY_A
		);

		// this grabs the customer info using the $purchase_log from the previous SQL query
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
		$site_id = get_option( 'mercadopago_ticket_siteid', 'MLA' );
		if ( empty( $site_id ) || $site_id == null )
			$site_id = 'MLA';
		$country_configs = WPeComm_MercadoPago_Module::get_country_config( $site_id );

		// Get currency ratio.
		$currency_ratio = 1;
		if ( get_option( 'mercadopago_ticket_currencyratio' ) == 'active' ) {
			$currency_ratio = (float) WPeComm_MercadoPago_Module::get_conversion_rate(
				$country_configs['currency']
			);
		}

		// Here we build the array that contains ordered itens, from customer cart.
		$items = array();
		$purchase_description = '';
		if ( sizeof( $wpsc_cart->cart_items ) > 0 ) {
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
					$purchase_description = $purchase_description . ' ' .
						( $item->product_name . ' x ' . $item->quantity );
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
						'category_id' => get_option( 'mercadopago_ticket_category' ),
						'quantity' => 1,
						'unit_price' => $currency_ratio *
							( (float) $item->unit_price * (float) $item->quantity )
					) );
				}
			}

			// Tax fees cost as an item.
			array_push( $items, array(
				'id' => 2147483647,
				'title' => $form_labels['form']['tax_fees'],
				'description' => $form_labels['form']['tax_fees'],
				'category_id' => get_option( 'mercadopago_ticket_category' ),
				'quantity' => 1,
				'unit_price' => $currency_ratio * ( (float) $wpsc_cart->total_tax )
			) );

			// Shipment cost as an item.
			array_push( $items, array(
				'id' => 2147483647,
				'title' => $wpsc_cart->selected_shipping_option,
				'description' => $form_labels['form']['shipment'],
				'category_id' => get_option( 'mercadopago_ticket_category' ),
				'quantity' => 1,
				'unit_price' => $currency_ratio * (
					(float) ( $wpsc_cart->base_shipping ) + (float) ( $wpsc_cart->total_item_shipping )
				)
			) );
		}

		// Build additional information from the customer data.
		$billing_details = wpsc_get_customer_meta();
		$order_id = $billing_details['_wpsc_cart.log_id'][0];
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

		// Create the shipment address information set.
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

		// The payment preference.
		$payment_preference = array (
			'transaction_amount' => (float) number_format(
				$wpsc_cart->calculate_total_price() * $currency_ratio, 2
			),
			'description' => $purchase_description,
			'payer' => array(
				'email' => $arr_info['billingemail']
			),
			'external_reference' => get_option( 'mercadopago_ticket_invoiceprefix' ) . $order_id,
			'additional_info' => array(
				'items' => $items,
				'payer' => $payer_additional_info,
				'shipments' => $shipments
			)
		);

		// Do not set IPN url if it is a localhost.
		if ( ! strrpos( get_site_url(), 'localhost' ) ) {
			$preferences['notification_url'] = WPeComm_MercadoPago_Module::workaround_ampersand_bug(
				// TODO: check if this is really working...
				get_site_url() . '/wpecomm-mercadopago-module'
			);
		}

		// Set sponsor ID.
		if ( get_option( 'mercadopago_ticket_istestuser' ) == 'no' ) {
			$preferences['sponsor_id'] = $country_configs['sponsor_id'];
		}

		// Log created preferences.
		if ( get_option( 'mercadopago_ticket_debug' ) == 'active' ) {
			debug_to_console_basic(
				'@' . __FUNCTION__ . ' - ' .
				'Preferences created, now processing it: ' .
				json_encode( $preferences, JSON_PRETTY_PRINT )
			);
		}

		return $payment_preference;

	}

	/*
	 * ========================================================================
	 * IPN MECHANICS (SERVER SIDE)
	 * ========================================================================
	 */

	/**
	 * parse_gateway_notification method, receives data from the payment gateway.
	 * @access private
	 */
	function parse_gateway_notification() {
		header( 'HTTP/1.1 200 OK' );
		// TODO: implement
	}

	/**
	 * process_gateway_notification method, receives data from the payment gateway.
	 * @access public
	 */
	function process_gateway_notification() {
		header( 'HTTP/1.1 200 OK' );
		// TODO: implement
	}

}

function _wpsc_filter_mercadopago_ticket_merchant_customer_notification_raw_message(
$message, $notification ) {

	$purchase_log = $notification->get_purchase_log();

	if ( $purchase_log->get( 'gateway' ) == 'WPSC_Merchant_MercadoPago_Ticket' )
		$message = get_option(
			'mercadopago_ticket_order_result', 'Your order is <strong>pending</strong>.'
		) . '\r\n' . $message;

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
function validate_credentials_ticket( $access_token ) {

	$result = array(
		'site_id' => null,
		'is_valid' => false,
		'is_test_user' => false,
		'currency_ratio' => -1,
		'collector_id' => false,
		'country_configs' => null
	);

	if ( empty( $access_token ) ) {
		return $result;
	}

	try {

		$mp = new MP(
			WPeComm_MercadoPago_Module::get_module_version(),
			$access_token
		);
		$result['access_token'] = $access_token;
		// TODO: We should refactor this... it makes no sense to fetch access token already having it.
		// This is necessary to set access token in Mercado Pago SDK class.
		$get_request = $mp->get( '/users/me?access_token=' . $access_token );

		if ( isset( $get_request['response']['site_id'] ) ) {

			$result['is_test_user'] = in_array( 'test_user', $get_request['response']['tags'] ) ?
				'yes' : 'no';
			$result['site_id'] = $get_request['response']['site_id'];
			$result['collector_id'] = $get_request['response']['id'];
			$result['country_configs'] =
				WPeComm_MercadoPago_Module::get_country_config( $result['site_id'] );

			// Get ticket payments.
			$payment_methods = array();
			$payments = $mp->get( '/v1/payment_methods/?access_token=' . $access_token );
			foreach ( $payments['response'] as $payment ) {
				if ( $payment['payment_type_id'] != 'account_money' &&
					$payment['payment_type_id'] != 'credit_card' &&
					$payment['payment_type_id'] != 'debit_card' &&
					$payment['payment_type_id'] != 'prepaid_card' ) {

					array_push( $payment_methods, $payment );

				}
			}
			$result['payment_methods'] = $payment_methods;

			// Check for auto converstion of currency (only if it is enabled).
			$result['currency_ratio'] = -1;
			if ( get_option( 'mercadopago_ticket_currencyconversion' ) == 'active' ) {
				$result['currency_ratio'] = WPeComm_MercadoPago_Module::get_conversion_rate(
					$result['country_configs']['currency']
				);
			}

			$result['is_valid'] = true;

		}

	} catch ( MercadoPagoException $e ) {}

	return $result;

}

/*
 * ========================================================================
 * CHECKOUT FORM AND SETTINGS PAGE
 * ========================================================================
 */

/**
 * Form Ticket Checkout Returns the Settings Form Fields
 * @access public
 *
 * @since 4.1.0
 * @return $output string containing Form Fields
 */
function form_mercadopago_ticket() {

	global $wpdb, $wpsc_gateways;

	// Labels.
	$form_labels = array(
		'form' => array(
			'issuer_selection' => __( 'Please, select the ticket issuer of your preference.', 'wpecomm-mercadopago-module' ),
			'payment_instructions' => __( 'Click [Place order] button. The ticket will be generated and you will be redirected to print it.', 'wpecomm-mercadopago-module' ),
			'ticket_note' => __( 'Important - The order will be confirmed only after the payment approval.', 'wpecomm-mercadopago-module' ),
			'ticket_resume' => __( 'Please, pay the ticket to get your order approved.', 'wpecomm-mercadopago-module'),
			'print_ticket' => __( 'Print the Ticket', 'wpecomm-mercadopago-module' ),
			'payment_approved' => __( 'Payment <strong>approved</strong>.', 'wpecomm-mercadopago-module' ),
			'payment_in_process' => __( 'Your payment under <strong>review</strong>.', 'wpecomm-mercadopago-module' ),
			'payment_rejected' => __( 'Your payment was <strong>refused</strong>.', 'wpecomm-mercadopago-module' ),
			'payment_pending' => __( 'Your payment is <strong>pending</strong>.', 'wpecomm-mercadopago-module' ),
			'payment_cancelled' => __( 'Your payment has been <strong>canceled</strong>.', 'wpecomm-mercadopago-module' ),
			'payment_in_mediation' => __( 'Your payment is in <strong>mediation</strong>.', 'wpecomm-mercadopago-module' ),
			'payment_charged_back' => __( 'Your payment has been <strong>refunded</strong>.', 'wpecomm-mercadopago-module' ),
			'return_and_try' => __( 'Return and Try Again', 'wpecomm-mercadopago-module' ),
			'tax_fees' => __( 'Tax fees applicable in store', 'wpecomm-mercadopago-module' ),
			'shipment' => __( 'Shipping service used by store', 'wpecomm-mercadopago-module' ),
			'payment_converted' => __( 'Payment with converted currency', 'wpecomm-mercadopago-module' ),
			'to' => __( 'to', 'wpecomm-mercadopago-module' ),
			'label_other_bank' => __( 'Other Bank', 'wpecomm-mercadopago-module' ),
			'label_choose' => __( 'Choose', 'wpecomm-mercadopago-module' )
		),
		'error' => array(
			'missing_data_checkout' => __( 'Your payment failed to be processed.<br/>Are you sure you have set all information?', 'wpecomm-mercadopago-module' ),
			'server_error_checkout' => __( 'Your payment could not be completed. Please, try again.', 'wpecomm-mercadopago-module' )
		)
	);

	$currency_message = '';
	$result = validate_credentials_ticket(
		get_option( 'mercadopago_ticket_accesstoken' )
	);
	$store_currency = WPSC_Countries::get_currency_code( absint( get_option( 'currency_type' ) ) );

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

	// Trigger API to get payment methods and site_id, also validates Access_token.
	if ( $result['is_valid'] == true ) {
		// Checking the currency.
		if ( ! WPeComm_MercadoPago_Module::is_supported_currency(
		$result['country_configs']['currency'] ) ) {
			if ( get_option( 'mercadopago_ticket_currencyconversion' ) == 'inactive' ) {
				$result['currency_ratio'] = -1;
				$currency_message .= WPeComm_MercadoPago_Module::build_currency_not_converted_msg(
					$result['country_configs']['currency'],
					$result['country_configs']['country_name']
				);
			} elseif ( get_option( 'mercadopago_ticket_currencyconversion' ) == 'active' &&
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

	// Get callbacks.
	if ( get_option( 'mercadopago_ticket_url_sucess' ) != '' ) {
		$url_sucess = get_option( 'mercadopago_ticket_url_sucess' );
	} else {
		$url_sucess = get_site_url();
	}
	if ( get_option( 'mercadopago_ticket_url_pending' ) != '' ) {
		$url_pending = get_option( 'mercadopago_ticket_url_pending' );
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
			'" name="mercadopago_ticket_siteid" />' .

			"<input type='hidden' size='60' value='" .
			json_encode( $form_labels ) . // I really dislike this
			"' name='mercadopago_ticket_checkoutmessage1' />" .

			'<input type="hidden" size="60" value="' .
			$result['is_test_user'] .
			'" name="mercadopago_ticket_istestuser" />
			<input type="hidden" size="60" value="' .
			$result['currency_ratio'] .
			'" name="mercadopago_ticket_currencyratio" />' .

			"<input type='hidden' size='60' value='" .
			json_encode( $result['payment_methods'] ) .
			"' name='mercadopago_ticket_payment_methods' />" .

			'<strong>' . __( 'Mercado Pago Credentials', 'wpecomm-mercadopago-module' ) . '</strong>
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
		<td>' . __( 'Access Token', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' .
			get_option('mercadopago_ticket_accesstoken') .
			'" name="mercadopago_ticket_accesstoken" />
			<p class="description">' .
				__( 'Insert your Mercado Pago Access Token.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><h3><strong>' .
			__( 'Checkout Options', 'wpecomm-mercadopago-module' ) .
		'</strong></h3></td>
	</tr>' .
	/*<tr>
		<td>' . __('Coupons', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			coupom_ticket() . '
			<p class='description">' .
				__( 'If there is a Mercado Pago campaign, allow your store to give discounts to customers.', 'wpecomm-mercadopago-module' ) . '
			</p>
		</td>
	</tr>*/
	'<tr>
		<td>' . __( 'Store Category', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<select name="mercadopago_ticket_category" id="category" style="max-width:600px;>' .
				WPeComm_MercadoPago_Module::get_categories( 'mercadopago_ticket_category' ) .
			'</select>
			<p class="description">' .
				__( 'Define which type of products your store sells.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Store Identificator', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' . (
			get_option( 'mercadopago_ticket_invoiceprefix' ) == '' ?
				'WPeComm-' : get_option( 'mercadopago_ticket_invoiceprefix' )
			) . '" name="mercadopago_ticket_invoiceprefix" />
			<p class="description">' .
				__( 'Please, inform a prefix to your store.', 'wpecomm-mercadopago-module' ) . ' ' .
				__( 'If you use your Mercado Pago account on multiple stores you should make sure that this prefix is unique as Mercado Pago will not allow orders with same identificators.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'URL Approved Payment', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input name="mercadopago_ticket_url_sucess" type="text" value="' . $url_sucess . '"/>
			<p class="description">' .
				__( 'This is the URL where the customer is redirected if his payment is approved.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'URL Pending Payment', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input name="mercadopago_ticket_url_pending" type="text" value="' . $url_pending . '"/>
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
		<td>' . __( 'Currency Conversion', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<select name="mercadopago_ticket_currencyconversion" id="currencyconversion">' .
				WPeComm_MercadoPago_Module::currency_conversion(
					'mercadopago_ticket_currencyconversion'
				) .
			'</select>
			<p class="description">' .
				__( 'If the used currency in WPeCommerce is different or not supported by Mercado Pago, convert values of your transactions using Mercado Pago currency ratio', 'wpecomm-mercadopago-module' ) .
				'<br >' . __( sprintf( '%s', $currency_message ) ) .
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
		<td>' . __( 'Debug mode', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<select name="mercadopago_ticket_debug">' .
				WPeComm_MercadoPago_Module::debugs( 'mercadopago_ticket_debug' ) .
			'</select>
			<p class="description">' .
				__( 'Enable to display log messages in browser console (not recommended in production environment)', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>';

	return $output;

}

/**
 * Saving of Mercado Pago Ticket Checkout Settings.
 * @access public
 *
 * @since 4.1.0
 */
function submit_mercadopago_ticket() {

	if ( isset( $_POST['mercadopago_ticket_accesstoken'] ) ) {
		update_option(
			'mercadopago_ticket_accesstoken',
			trim( $_POST['mercadopago_ticket_accesstoken']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_siteid'] ) ) {
		update_option(
			'mercadopago_ticket_siteid',
			trim( $_POST['mercadopago_ticket_siteid']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_checkoutmessage1'] ) ) {
		update_option(
			'mercadopago_ticket_checkoutmessage1',
			trim( $_POST['mercadopago_ticket_checkoutmessage1']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_istestuser'] ) ) {
		update_option(
			'mercadopago_ticket_istestuser',
			trim( $_POST['mercadopago_ticket_istestuser']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_currencyratio'] ) ) {
		update_option(
			'mercadopago_ticket_currencyratio',
			trim( $_POST['mercadopago_ticket_currencyratio']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_payment_methods'] ) ) {
		update_option(
			'mercadopago_ticket_payment_methods',
			trim( $_POST['mercadopago_ticket_payment_methods']
		) );
	}
	/*if ( isset( $_POST['mercadopago_ticket_coupom'] ) ) {
		update_option(
			'mercadopago_ticket_coupom',
			trim( $_POST['mercadopago_ticket_coupom']
		) );
	}*/
	if ( isset( $_POST['mercadopago_ticket_category'] ) ) {
		update_option(
			'mercadopago_ticket_category',
			trim( $_POST['mercadopago_ticket_category']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_invoiceprefix'] ) ) {
		update_option(
			'mercadopago_ticket_invoiceprefix',
			trim( $_POST['mercadopago_ticket_invoiceprefix']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_currencyconversion'] ) ) {
		update_option(
			'mercadopago_ticket_currencyconversion',
			trim( $_POST['mercadopago_ticket_currencyconversion']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_url_sucess'] ) ) {
		update_option(
			'mercadopago_ticket_url_sucess',
			trim( $_POST['mercadopago_ticket_url_sucess']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_url_pending'] ) ) {
		update_option(
			'mercadopago_ticket_url_pending',
			trim( $_POST['mercadopago_ticket_url_pending']
		) );
	}
	if ( isset( $_POST['mercadopago_ticket_debug'] ) ) {
		update_option(
			'mercadopago_ticket_debug',
			trim( $_POST['mercadopago_ticket_debug']
		) );
	}

	return true;

}

if ( in_array( 'WPSC_Merchant_MercadoPago_Ticket', (array) get_option( 'custom_gateway_options' ) ) ) {

	// Create MP object and set sandbox mode to false.
	$mp = new MP(
		WPeComm_MercadoPago_Module::get_module_version(),
		get_option( 'mercadopago_ticket_accesstoken' )
	);
	$mp->sandbox_mode( false );

	// Get the order amount.
	$amount = wpsc_cart_total( false );

	// Site id.
	$site_id = get_option( 'mercadopago_custom_siteid', 'MLA' );
	if ( empty( $site_id ) || $site_id == null ) {
		$site_id = 'MLA';
	}
	$country_configs = WPeComm_MercadoPago_Module::get_country_config( $site_id );

	// Payment methods.
	$payment_methods = json_decode(stripslashes(
		preg_replace( '/u([\da-fA-F]{4})/', '&#x\1;', stripslashes(
			get_option( 'mercadopago_ticket_payment_methods' )
		) )
	), true );
	if ( empty( $payment_methods ) || $payment_methods == null ) {
		$payment_methods = array();
	}

	// Labels.
	$form_labels = json_decode(stripslashes(
		preg_replace( '/u([\da-fA-F]{4})/', '&#x\1;', stripslashes(
			get_option( 'mercadopago_ticket_checkoutmessage1', '' )
		) )
	), true );
	if ( $form_labels == '' ) {
		$form_labels = array(
			'form' => array(
				'issuer_selection' => __( 'Please, select the ticket issuer of your preference.', 'wpecomm-mercadopago-module' ),
				'payment_instructions' => __( 'Click [Place order] button. The ticket will be generated and you will be redirected to print it.', 'wpecomm-mercadopago-module' ),
				'ticket_note' => __( 'Important - The order will be confirmed only after the payment approval.', 'wpecomm-mercadopago-module' ),
				'ticket_resume' => __( 'Please, pay the ticket to get your order approved.', 'wpecomm-mercadopago-module'),
				'print_ticket' => __( 'Print the Ticket', 'wpecomm-mercadopago-module' ),
				'payment_approved' => __( 'Payment <strong>approved</strong>.', 'wpecomm-mercadopago-module' ),
				'payment_in_process' => __( 'Your payment under <strong>review</strong>.', 'wpecomm-mercadopago-module' ),
				'payment_rejected' => __( 'Your payment was <strong>refused</strong>.', 'wpecomm-mercadopago-module' ),
				'payment_pending' => __( 'Your payment is <strong>pending</strong>.', 'wpecomm-mercadopago-module' ),
				'payment_cancelled' => __( 'Your payment has been <strong>canceled</strong>.', 'wpecomm-mercadopago-module' ),
				'payment_in_mediation' => __( 'Your payment is in <strong>mediation</strong>.', 'wpecomm-mercadopago-module' ),
				'payment_charged_back' => __( 'Your payment has been <strong>refunded</strong>.', 'wpecomm-mercadopago-module' ),
				'return_and_try' => __( 'Return and Try Again', 'wpecomm-mercadopago-module' ),
				'tax_fees' => __( 'Tax fees applicable in store', 'wpecomm-mercadopago-module' ),
				'shipment' => __( 'Shipping service used by store', 'wpecomm-mercadopago-module' ),
				'payment_converted' => __( 'Payment with converted currency', 'wpecomm-mercadopago-module' ),
				'to' => __( 'to', 'wpecomm-mercadopago-module' ),
				'label_other_bank' => __( 'Other Bank', 'wpecomm-mercadopago-module' ),
				'label_choose' => __( 'Choose', 'wpecomm-mercadopago-module' ),
			)
		);
	}

	// header
	$payment_header =
		'<div width="100%" style="margin:0px; padding:16px 36px 16px 36px; background:white;
			border-style:solid; border-color:#DDDDDD" border-radius:1.0px;">
			<img class="logo" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/mplogo.png', plugin_dir_path( __FILE__ ) ) .
				'" width="156" height="40" />' . ( count( $payment_methods ) > 1 ? '<img class="logo" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/boleto.png', plugin_dir_path( __FILE__ ) ) .
				'" width="90" height="40" style="float:right;"/>' : get_payment_methods_images( $payment_methods ) ) .
		'</div>';

	// payment method
	$mercadopago_form =
		'<fieldset style="background:white;">
			<div style="padding:0px 36px 0px 36px;">
				<p>' .
					( count( $payment_methods ) > 1 ?
						$form_labels[ 'form' ][ 'issuer_selection' ] . ' ' : ''
					) .
					$form_labels[ 'form' ][ 'payment_instructions' ] . '<br />' .
					$form_labels[ 'form' ][ 'ticket_note' ] . (
						get_option( 'mercadopago_ticket_currencyratio' ) > 0 ?
							' (' . $form_labels['form']['payment_converted'] . ' ' .
							WPSC_Countries::get_currency_code( absint(
								get_option( 'currency_type' )
							) ) . ' ' . $form_labels['form']['to'] . ' ' .
							$country_configs['currency'] . ')' : '' ) .
				'</p>' . draw_ticket_options( $payment_methods ) .
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

	// javascript
	$page_js =
		'<script src="' . plugins_url( 'wpsc-merchants/mercadopago-lib/MPv1Ticket.js?no_cache=' .
			time(), plugin_dir_path( __FILE__ ) ) . '"></script>
		<script type="text/javascript">
			var mercadopago_site_id = "' . $site_id . '";
			MPv1Ticket.paths.loading = "' . WPeComm_MercadoPago_Module::get_image_path( 'loading.gif' ) . '";
			MPv1Ticket.getAmount = function() {
				return document.querySelector(MPv1Ticket.selectors.amount).value;
			}
			MPv1Ticket.Initialize(mercadopago_site_id);
		</script>';

	// header
	$page_header =
		'<head>
			<link rel="stylesheet" id="twentysixteen-style-css"
				href="https://modules-mercadopago.rhcloud.com/wp-content/themes/twentysixteen/style.css?ver=4.5.3" type="text/css" media="all">
			<link rel="stylesheet" id="ticket-checkout-mercadopago" href="' .
				plugins_url( 'wpsc-merchants/mercadopago-lib/custom_checkout_mercadopago.css', plugin_dir_path( __FILE__ ) ) .
				'?ver=4.5.3" type="text/css" media="all">
			<script src="' . plugins_url( 'wpsc-merchants/mercadopago-lib/MPv1Ticket.js?no_cache=' .
				time(), plugin_dir_path( __FILE__ ) ) . '"></script>
		</head>';

	$output =
		'<tr>
			<td>' .
				$page_header .
				'<div style="width: 600px;">' . $payment_header . '</div>' .
				'<div style="width: 600px;">' . $mercadopago_form  . '</div>'  .
				$page_js .
			'</td>
		</tr>';

	$gateway_checkout_form_fields[$nzshpcrt_gateways[$num]['internalname']] = $output;

}

/*
 * ========================================================================
 * FUNCTIONS TO GENERATE VIEWS (SERVER SIDE)
 * ========================================================================
 */

function draw_ticket_options( $payment_methods ) {

	$output = '';

	if ( count( $payment_methods ) > 1 ) {

		$atFirst = true;
		$output .= '<div class="mp-box-inputs mp-col-100">';
		foreach ( $payment_methods as $payment ) {
			$output .=
				'<div class="mp-box-inputs mp-line">
					<div id="paymentMethodId" class="mp-box-inputs mp-col-10">
						<input type="radio" class="input-radio" name="mercadopago_ticket[paymentMethodId]"
							style="height:16px; width:16px;" value="' . $payment[ 'id' ] . '" ' .
							( $atFirst ? 'checked="checked"' : '' ) . ' />
					</div>
					<div class="mp-box-inputs mp-col-45">
						<label>
							<img src="' . $payment[ 'secure_thumbnail' ] . '" alt="' . $payment[ 'name' ] . '" />
							&nbsp;(' . $payment[ 'name' ] . ')
						</label>
					</div>
				</div>';
			$atFirst = false;
		}
		$output .= '</div>';

	} else {

		$output .= '<div class="mp-box-inputs mp-col-100" style="display:none;">
			<select id="paymentMethodId" name="mercadopago_ticket[paymentMethodId]">';
		foreach ( $payment_methods as $payment ) {
			$output .=
				'<option value="' . $payment[ 'id' ] . '"style="padding: 8px;
					background: url( "https://img.mlstatic.com/org-img/MP3/API/logos/bapropagos.gif" );
					98% 50% no-repeat;"> ' . $payment[ 'name' ] . '</option>';
		}
		$output .= '</select></div>';

	}

	return $output;

}

function get_payment_methods_images( $payment_methods ) {

	$html = '';

	foreach ( $payment_methods as $payment ) {
		$html .= '<img class="logo" src="' . $payment[ 'secure_thumbnail' ] .
			'" width="90" height="40" style="float:right;"/>';
		break;
	}

	return $html;

}

?>

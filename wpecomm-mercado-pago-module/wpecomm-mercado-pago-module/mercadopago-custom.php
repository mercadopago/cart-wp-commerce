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
	'name' => __( 'Mercado Pago - Credit Card', 'wpecomm-mercadopago-module' ),
	'api_version' => 2.0,
	'class_name' => 'WPSC_Merchant_MercadoPago_Custom',
	'display_name' => __( 'Mercado Pago - Credit Card', 'wpecomm-mercadopago-module' ),
	'requirements' => array(
		// So that you can restrict merchant modules to PHP 5, if you use PHP 5 features.
		'php_version' => 5.6,
		// For modules that may not be present, like curl.
		'extra_modules' => array()
	),
	'internalname' => 'WPSC_Merchant_MercadoPago_Custom',
	// All array members below here are legacy, and use the code in mercadopago_multiple.php.
	'form' => 'form_mercadopago_custom',
	'has_recurring_billing' => true,
	'submit_function' => 'submit_mercadopago_custom',
	'wp_admin_cannot_cancel' => false
);

class WPSC_Merchant_MercadoPago_Custom extends wpsc_merchant {

	var $name = '';
	var $purchase_id = null;

	function __construct( $purchase_id = null, $is_receiving = false ) {
		add_action( 'init', array( $this, 'load_plugin_textdomain_wpecomm' ) );
		$this->purchase_id = $purchase_id;
		$this->name = __( 'Mercado Pago - Credit Card', 'wpecomm-mercadopago-module' );
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
			stripslashes( get_option( 'mercadopago_custom_checkoutmessage1', '' ) )
		) ), true);

		if ( $form_labels == '' ) {
			$form_labels = array(
				'form' => array(
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
					'your_card' => __( 'Your Card', 'wpecomm-mercadopago-module' ),
					'other_cards' => __( 'Other Cards', 'wpecomm-mercadopago-module' ),
					'other_card' => __( 'Other Card', 'wpecomm-mercadopago-module' ),
					'ended_in' => __( 'ended in', 'wpecomm-mercadopago-module' ),
					'card_holder_placeholder' => __( ' as it appears in your card ...', 'wpecomm-mercadopago-module' ),
					'payment_method' => __( 'Payment Method', 'wpecomm-mercadopago-module' ),
					'credit_card_number' => __( 'Credit card number', 'wpecomm-mercadopago-module' ),
					'expiration_month' => __( 'Expiration month', 'wpecomm-mercadopago-module' ),
					'expiration_year' => __( 'Expiration year', 'wpecomm-mercadopago-module' ),
					'year' => __( 'Year', 'wpecomm-mercadopago-module' ),
					'month' => __( 'Month', 'wpecomm-mercadopago-module' ),
					'card_holder_name' => __( 'Card holder name', 'wpecomm-mercadopago-module' ),
					'security_code' => __( 'Security code', 'wpecomm-mercadopago-module' ),
					'document_type' => __( 'Document Type', 'wpecomm-mercadopago-module' ),
					'document_number' => __( 'Document number', 'wpecomm-mercadopago-module' ),
					'issuer' => __( 'Issuer', 'wpecomm-mercadopago-module' ),
					'installments' => __( 'Installments', 'wpecomm-mercadopago-module' )
				),
				'error' => array(
					'missing_data_checkout' => __( 'Your payment failed to be processed.<br/>Are you sure you have set all information?', 'wpecomm-mercadopago-module' ),
					'server_error_checkout' => __( 'Your payment could not be completed. Please, try again.', 'wpecomm-mercadopago-module' ),
					//card number
					'205' => __( 'Parameter cardNumber can not be null/empty', 'wpecomm-mercadopago-module' ),
					'E301' => __( 'Invalid Card Number', 'wpecomm-mercadopago-module' ),
					//expiration date
					'208' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
					'209' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
					'325' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
					'326' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
					//card holder name
					'221' => __( 'Parameter cardholderName can not be null/empty', 'wpecomm-mercadopago-module' ),
					'316' => __( 'Invalid Card Holder Name', 'wpecomm-mercadopago-module' ),
					//security code
					'224' => __( 'Parameter securityCode can not be null/empty', 'wpecomm-mercadopago-module' ),
					'E302' => __( 'Invalid Security Code', 'wpecomm-mercadopago-module' ),
					//doc type
					'212' => __( 'Parameter docType can not be null/empty', 'wpecomm-mercadopago-module' ),
					'322' => __( 'Invalid Document Type', 'wpecomm-mercadopago-module' ),
					//doc number
					'214' => __( 'Parameter docNumber can not be null/empty', 'wpecomm-mercadopago-module' ),
					'324' => __( 'Invalid Document Number', 'wpecomm-mercadopago-module' ),
					//doc sub type
					'213' => __( 'The parameter cardholder.document.subtype can not be null or empty', 'wpecomm-mercadopago-module' ),
					'323' => __( 'Invalid Document Sub Type', 'wpecomm-mercadopago-module' ),
					//issuer
					'220' => __( 'Parameter cardIssuerId can not be null/empty', 'wpecomm-mercadopago-module' )
				)
			);
		}

		// Mexico country case.
		if ( empty( $_REQUEST['mercadopago_custom']['paymentMethodId'] ) ) {
			$_REQUEST['mercadopago_custom']['paymentMethodId'] =
				$_REQUEST['mercadopago_custom']['paymentMethodSelector'];
		}

		if ( ! empty( $_REQUEST['mercadopago_custom']['amount'] ) &&
		! empty( $_REQUEST['mercadopago_custom']['token'] ) &&
		! empty( $_REQUEST['mercadopago_custom']['paymentMethodId'] ) &&
		! empty( $_REQUEST['mercadopago_custom']['installments'] ) &&
		$_REQUEST['mercadopago_custom']['installments'] != -1 ) {

			// Creates the order parameters by checking the cart configuration.
			$preference = $this->build_payment_preference( $wpdb, $wpsc_cart, $form_labels );
			$preference['token'] = $_REQUEST['mercadopago_custom']['token'];
			$preference['installments'] = (int) $_REQUEST['mercadopago_custom']['installments'];
			$preference['payment_method_id'] = $_REQUEST['mercadopago_custom']['paymentMethodId'];

			// Customer's Card Feature, add only if it has issuer id.
			if ( array_key_exists( 'token', $_REQUEST['mercadopago_custom'] ) ) {
				$preference['metadata']['token'] = $_REQUEST['mercadopago_custom']['token'];
				if ( array_key_exists( 'issuer', $_REQUEST['mercadopago_custom'] ) ) {
					if ( ! empty( $_REQUEST['mercadopago_custom']['issuer'] ) ) {
						$preference['issuer_id'] =
							(integer) $_REQUEST['mercadopago_custom']['issuer'];
					}
				}
				if ( ! empty( $_REQUEST['mercadopago_custom']['customerId'] ) ) {
					$preference['payer']['id'] = $_REQUEST['mercadopago_custom']['customerId'];
				}
			}

			// Create order preferences with Mercado Pago API request.
			try {

				$mp = new MP(
					WPeComm_MercadoPago_Module::get_module_version(),
					get_option( 'mercadopago_custom_accesstoken' )
				);
				if ( 'active' == get_option( 'mercadopago_custom_sandbox' ) ) {
					$mp->sandbox_mode( true );
				} else {
					$mp->sandbox_mode( false );
				}

				$checkout_info = $mp->post( '/v1/payments', json_encode( $preference ) );

				if ( is_wp_error( $checkout_info ) ||
				$checkout_info['status'] < 200 || $checkout_info['status'] >= 300 ) {

					get_header();
					echo '<h3>' .
						$form_labels['error']['server_error_checkout'] .
						//'<br>' . json_encode( $preference ) .
						'<br/></h3>' . $checkout_info['response']['message'] .
						'<br/><br/><h3><a href="' . add_query_arg( 'sessionid',
							$this->cart_data['session_id'], get_option( 'shopping_cart_url' ) ) .
							'">' . $form_labels['form']['return_and_try'] . '</a></h3><br/><br/>';
					get_footer();

				} else {

					$response = $checkout_info['response'];
					if ( array_key_exists( 'status', $response ) ) {
						switch ( $response['status'] ) {
							case 'approved':
								update_option(
									'mercadopago_custom_order_result',
									$form_labels['form']['payment_approved']
								);
								$this->check_and_save_customer_card( $response );
								break;
							case 'in_process':
								update_option(
									'mercadopago_custom_order_result',
									$form_labels['form']['payment_in_process']
								);
								break;
							case 'rejected':
								update_option(
									'mercadopago_custom_order_result',
									$form_labels['form']['payment_rejected']
								);
								break;
							case 'pending':
								update_option(
									'mercadopago_custom_order_result',
									$form_labels['form']['payment_pending']
								);
								break;
							case 'cancelled':
								update_option(
									'mercadopago_custom_order_result',
									$form_labels['form']['payment_cancelled']
								);
								break;
							case 'in_mediation':
								update_option(
									'mercadopago_custom_order_result',
									$form_labels['form']['payment_in_mediation']
								);
								break;
							case 'charged-back':
								update_option(
									'mercadopago_custom_order_result',
									$form_labels['form']['payment_charged_back']
								);
								break;
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
		$site_id = get_option( 'mercadopago_custom_siteid', 'MLA' );
		if ( empty( $site_id ) || $site_id == null )
			$site_id = 'MLA';
		$country_configs = WPeComm_MercadoPago_Module::get_country_config( $site_id );

		// Get currency ratio.
		$currency_ratio = 1;
		if ( get_option( 'mercadopago_custom_currencyratio' ) == 'active' ) {
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
						'category_id' => get_option( 'mercadopago_custom_category' ),
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
				'category_id' => get_option( 'mercadopago_custom_category' ),
				'quantity' => 1,
				'unit_price' => $currency_ratio * ( (float) $wpsc_cart->total_tax )
			) );

			// Shipment cost as an item.
			array_push( $items, array(
				'id' => 2147483647,
				'title' => $wpsc_cart->selected_shipping_option,
				'description' => $form_labels['form']['shipment'],
				'category_id' => get_option( 'mercadopago_custom_category' ),
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
			'external_reference' => get_option( 'mercadopago_custom_invoiceprefix' ) . $order_id,
			'statement_descriptor' => get_option( 'mercadopago_custom_statementdescriptor' ),
			'binary_mode' => get_option( 'mercadopago_custom_binary' ) == 'active',
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
		if ( get_option( 'mercadopago_custom_istestuser' ) == 'no' ) {
			$preferences['sponsor_id'] = $country_configs['sponsor_id'];
		}

		// Log created preferences.
		if ( get_option( 'mercadopago_custom_debug' ) == 'active' ) {
			debug_to_console_basic(
				'@' . __FUNCTION__ . ' - ' .
				'Preferences created, now processing it: ' .
				json_encode( $preferences, JSON_PRETTY_PRINT )
			);
		}

		return $payment_preference;

	}

	public function check_and_save_customer_card( $checkout_info ) {

		$custId = null;
		$token  = null;
		$issuer_id = null;
		$payment_method_id = null;

		if ( ! empty( $checkout_info['payer']['id'] ) ) {
			$custId = $checkout_info['payer']['id'];
		} else {
			return;
		}
		if ( ! empty( $checkout_info['metadata']['token'] ) ) {
			$token = $checkout_info['metadata']['token'];
		} else {
			return;
		}
		if ( ! empty( $checkout_info['issuer_id'] ) ) {
			$issuer_id = (integer) $checkout_info['issuer_id'];
		}
		if ( ! empty( $checkout_info['payment_method_id'] ) ) {
			$payment_method_id = $checkout_info['payment_method_id'];
		}

		$mp = new MP(
			WPeComm_MercadoPago_Module::get_module_version(),
			get_option( 'mercadopago_custom_accesstoken' )
		);
		if ( 'active' == get_option( 'mercadopago_custom_sandbox' ) ) {
			$mp->sandbox_mode( true );
		} else {
			$mp->sandbox_mode( false );
		}
		$mp->create_card_in_customer( $custId, $token, $payment_method_id, $issuer_id );

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

function _wpsc_filter_mercadopago_custom_merchant_customer_notification_raw_message(
$message, $notification ) {

	$purchase_log = $notification->get_purchase_log();

	if ( $purchase_log->get( 'gateway' ) == 'WPSC_Merchant_MercadoPago_Custom' )
		$message = get_option(
			'mercadopago_custom_order_result', 'Your order is <strong>pending</strong>.'
		) . '\r\n' . $message;

	return $message;

}

add_filter(
	'wpsc_purchase_log_customer_notification_raw_message',
	'_wpsc_filter_mercadopago_custom_merchant_customer_notification_raw_message',
	10, 2
);

add_filter(
	'wpsc_purchase_log_customer_html_notification_raw_message',
	'_wpsc_filter_mercadopago_custom_merchant_customer_notification_raw_message',
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
function validate_credentials_custom( $public_key, $access_token ) {

	$result = array(
		'site_id' => null,
		'is_valid' => false,
		'is_test_user' => false,
		'currency_ratio' => -1,
		'collector_id' => false,
		'country_configs' => null
	);

	if ( empty( $public_key ) || empty( $access_token ) ) {
		return $result;
	}

	try {

		$mp = new MP(
			WPeComm_MercadoPago_Module::get_module_version(),
			$access_token
		);
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

			// Check for auto converstion of currency (only if it is enabled).
			$result['currency_ratio'] = -1;
			if ( get_option( 'mercadopago_custom_currencyconversion' ) == 'active' ) {
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
 * Form Custom Checkout Returns the Settings Form Fields
 * @access public
 *
 * @since 4.1.0
 * @return $output string containing Form Fields
 */
function form_mercadopago_custom() {

	global $wpdb, $wpsc_gateways;

	// Labels.
	$form_labels = array(
		'form' => array(
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
			'your_card' => __( 'Your Card', 'wpecomm-mercadopago-module' ),
			'other_cards' => __( 'Other Cards', 'wpecomm-mercadopago-module' ),
			'other_card' => __( 'Other Card', 'wpecomm-mercadopago-module' ),
			'ended_in' => __( 'ended in', 'wpecomm-mercadopago-module' ),
			'card_holder_placeholder' => __( ' as it appears in your card ...', 'wpecomm-mercadopago-module' ),
			'payment_method' => __( 'Payment Method', 'wpecomm-mercadopago-module' ),
			'credit_card_number' => __( 'Credit card number', 'wpecomm-mercadopago-module' ),
			'expiration_month' => __( 'Expiration month', 'wpecomm-mercadopago-module' ),
			'expiration_year' => __( 'Expiration year', 'wpecomm-mercadopago-module' ),
			'year' => __( 'Year', 'wpecomm-mercadopago-module' ),
			'month' => __( 'Month', 'wpecomm-mercadopago-module' ),
			'card_holder_name' => __( 'Card holder name', 'wpecomm-mercadopago-module' ),
			'security_code' => __( 'Security code', 'wpecomm-mercadopago-module' ),
			'document_type' => __( 'Document Type', 'wpecomm-mercadopago-module' ),
			'document_number' => __( 'Document number', 'wpecomm-mercadopago-module' ),
			'issuer' => __( 'Issuer', 'wpecomm-mercadopago-module' ),
			'installments' => __( 'Installments', 'wpecomm-mercadopago-module' )
		),
		'error' => array(
			'missing_data_checkout' => __( 'Your payment failed to be processed.<br/>Are you sure you have set all information?', 'wpecomm-mercadopago-module' ),
			'server_error_checkout' => __( 'Your payment could not be completed. Please, try again.', 'wpecomm-mercadopago-module' ),
			// Card number.
			'205' => __( 'Parameter cardNumber can not be null/empty', 'wpecomm-mercadopago-module' ),
			'E301' => __( 'Invalid Card Number', 'wpecomm-mercadopago-module' ),
			// Expiration date.
			'208' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
			'209' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
			'325' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
			'326' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
			// Card holder name.
			'221' => __( 'Parameter cardholderName can not be null/empty', 'wpecomm-mercadopago-module' ),
			'316' => __( 'Invalid Card Holder Name', 'wpecomm-mercadopago-module' ),
			// Security code.
			'224' => __( 'Parameter securityCode can not be null/empty', 'wpecomm-mercadopago-module' ),
			'E302' => __( 'Invalid Security Code', 'wpecomm-mercadopago-module' ),
			// Doc type.
			'212' => __( 'Parameter docType can not be null/empty', 'wpecomm-mercadopago-module' ),
			'322' => __( 'Invalid Document Type', 'wpecomm-mercadopago-module' ),
			// Doc number.
			'214' => __( 'Parameter docNumber can not be null/empty', 'wpecomm-mercadopago-module' ),
			'324' => __( 'Invalid Document Number', 'wpecomm-mercadopago-module' ),
			// Doc sub type.
			'213' => __( 'The parameter cardholder.document.subtype can not be null or empty', 'wpecomm-mercadopago-module' ),
			'323' => __( 'Invalid Document Sub Type', 'wpecomm-mercadopago-module' ),
			// Issuer.
			'220' => __( 'Parameter cardIssuerId can not be null/empty', 'wpecomm-mercadopago-module' )
		)
	);

	$currency_message = '';
	$result = validate_credentials_custom(
		get_option( 'mercadopago_custom_publickey' ),
		get_option( 'mercadopago_custom_accesstoken' )
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

	// Trigger API to get payment methods and site_id, also validates Public_key/Access_token.
	if ( $result['is_valid'] == true ) {
		// Checking the currency.
		if ( ! WPeComm_MercadoPago_Module::is_supported_currency(
		$result['country_configs']['currency'] ) ) {
			if ( get_option( 'mercadopago_custom_currencyconversion' ) == 'inactive' ) {
				$result['currency_ratio'] = -1;
				$currency_message .= WPeComm_MercadoPago_Module::build_currency_not_converted_msg(
					$result['country_configs']['currency'],
					$result['country_configs']['country_name']
				);
			} elseif ( get_option( 'mercadopago_custom_currencyconversion' ) == 'active' &&
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
	if ( get_option( 'mercadopago_custom_url_sucess' ) != '' ) {
		$url_sucess = get_option( 'mercadopago_custom_url_sucess' );
	} else {
		$url_sucess = get_site_url();
	}
	if ( get_option( 'mercadopago_custom_url_pending' ) != '' ) {
		$url_pending = get_option( 'mercadopago_custom_url_pending' );
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
			'" name="mercadopago_custom_siteid" />' .

			"<input type='hidden' size='60' value='" .
			json_encode( $form_labels ) . // I really dislike this
			"' name='mercadopago_custom_checkoutmessage1' />" .

			'<input type="hidden" size="60" value="' .
			$result['is_test_user'] .
			'" name="mercadopago_custom_istestuser" />
			<input type="hidden" size="60" value="' .
			$result['currency_ratio'] .
			'" name="mercadopago_custom_currencyratio" />
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
		<td>' . __( 'Public Key', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' .
			get_option( 'mercadopago_custom_publickey' ) .
			'" name="mercadopago_custom_publickey" />
			<p class="description">' .
				__( 'Insert your Mercado Pago Public Key.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Access Token', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' .
			get_option( 'mercadopago_custom_accesstoken' ) .
			'" name="mercadopago_custom_accesstoken" />
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
	</tr>
	<tr>
		<td>' . __( 'Statement Descriptor', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input type="text" size="60" value="' . (
			get_option( 'mercadopago_custom_statementdescriptor' ) == '' ?
				'Mercado Pago' : get_option( 'mercadopago_custom_statementdescriptor' )
			) . '" name="mercadopago_custom_statementdescriptor" />
			<p class="description">' .
				__( 'The description that will be shown in your customer\'s invoice.', 'wpecomm-mercadopago-module' ) . '
			</p>
		</td>
	</tr>' .
	/*<tr>
		<td>' . __( 'Coupons', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_custom_coupom" id="coupom">' .
				WPeComm_MercadoPago_Module::coupom( 'mercadopago_custom_coupom' ) .
			'</select>
			<p class="description">' .
				__( 'If there is a Mercado Pago campaign, allow your store to give discounts to customers.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>*/
	'<tr>
		<td>' . __( 'Binary Mode', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_custom_binary" id="binary">' .
				binary() .
			'</select>
			<p class="description">' .
				__( 'When charging a credit card, only [approved] or [reject] status will be taken.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Store Category', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<select name="mercadopago_custom_category" id="category" style="max-width:600px;>' .
				WPeComm_MercadoPago_Module::get_categories( 'mercadopago_custom_category' ) .
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
			get_option( 'mercadopago_custom_invoiceprefix' ) == '' ?
				'WPeComm-' : get_option( 'mercadopago_custom_invoiceprefix' )
			) . '" name="mercadopago_custom_invoiceprefix" />
			<p class="description">' .
				__( 'Please, inform a prefix to your store.', 'wpecomm-mercadopago-module' ) . ' ' .
				__( 'If you use your Mercado Pago account on multiple stores you should make sure that this prefix is unique as Mercado Pago will not allow orders with same identificators.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'URL Approved Payment', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input name="mercadopago_custom_url_sucess" type="text" value="' . $url_sucess . '"/>
			<p class="description">' .
				__( 'This is the URL where the customer is redirected if his payment is approved.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'URL Pending Payment', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<input name="mercadopago_custom_url_pending" type="text" value="' . $url_pending . '"/>
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
			<select name="mercadopago_custom_currencyconversion" id="currencyconversion">' .
				WPeComm_MercadoPago_Module::currency_conversion(
					'mercadopago_custom_currencyconversion'
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
		<td>' . __( 'Enable Sandbox', 'wpecomm-mercadopago-module' ) . '</td>
		<td>' .
			'<select name="mercadopago_custom_sandbox" id="sandbox">' .
				WPeComm_MercadoPago_Module::sandbox( 'mercadopago_custom_sandbox' ) .
			'</select>
			<p class="description">' .
				__( 'This option allows you to test payments inside a sandbox environment.', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>
	<tr>
		<td>' . __( 'Debug mode', 'wpecomm-mercadopago-module' ) . '</td>
		<td>
			<select name="mercadopago_custom_debug">' .
				WPeComm_MercadoPago_Module::debugs( 'mercadopago_custom_debug' ) .
			'</select>
			<p class="description">' .
				__( 'Enable to display log messages in browser console (not recommended in production environment)', 'wpecomm-mercadopago-module' ) .
			'</p>
		</td>
	</tr>';

	return $output;

}

/**
 * Saving of Mercado Pago Custom Checkout Settings
 * @access public
 *
 * @since 4.1.0
 */
function submit_mercadopago_custom() {

	if ( isset( $_POST['mercadopago_custom_publickey'] ) ) {
		update_option(
			'mercadopago_custom_publickey',
			trim( $_POST['mercadopago_custom_publickey']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_accesstoken'] ) ) {
		update_option(
			'mercadopago_custom_accesstoken',
			trim( $_POST['mercadopago_custom_accesstoken']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_siteid'] ) ) {
		update_option(
			'mercadopago_custom_siteid',
			trim( $_POST['mercadopago_custom_siteid']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_checkoutmessage1'] ) ) {
		update_option(
			'mercadopago_custom_checkoutmessage1',
			trim( $_POST['mercadopago_custom_checkoutmessage1']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_istestuser'] ) ) {
		update_option(
			'mercadopago_custom_istestuser',
			trim( $_POST['mercadopago_custom_istestuser']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_currencyratio'] ) ) {
		update_option(
			'mercadopago_custom_currencyratio',
			trim( $_POST['mercadopago_custom_currencyratio']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_statementdescriptor'] ) ) {
		update_option(
			'mercadopago_custom_statementdescriptor',
			trim( $_POST['mercadopago_custom_statementdescriptor']
		) );
	}
	/*if ( isset( $_POST['mercadopago_custom_coupom'] ) ) {
		update_option(
			'mercadopago_custom_coupom',
			trim( $_POST['mercadopago_custom_coupom']
		) );
	}*/
	if ( isset( $_POST['mercadopago_custom_binary'] ) ) {
		update_option(
			'mercadopago_custom_binary',
			trim( $_POST['mercadopago_custom_binary']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_category'] ) ) {
		update_option(
			'mercadopago_custom_category',
			trim($_POST['mercadopago_custom_category']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_invoiceprefix'] ) ) {
		update_option(
			'mercadopago_custom_invoiceprefix',
			trim( $_POST['mercadopago_custom_invoiceprefix']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_currencyconversion'] ) ) {
		update_option(
			'mercadopago_custom_currencyconversion',
			trim( $_POST['mercadopago_custom_currencyconversion']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_url_sucess'] ) ) {
		update_option(
			'mercadopago_custom_url_sucess',
			trim( $_POST['mercadopago_custom_url_sucess']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_url_pending'] ) ) {
		update_option(
			'mercadopago_custom_url_pending',
			trim( $_POST['mercadopago_custom_url_pending']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_sandbox'] ) ) {
		update_option(
			'mercadopago_custom_sandbox',
			trim( $_POST['mercadopago_custom_sandbox']
		) );
	}
	if ( isset( $_POST['mercadopago_custom_debug'] ) ) {
		update_option(
			'mercadopago_custom_debug',
			trim( $_POST['mercadopago_custom_debug']
		) );
	}

	return true;

}

if ( in_array( 'WPSC_Merchant_MercadoPago_Custom', (array) get_option( 'custom_gateway_options' ) ) ) {

	// Create MP object and checks for sandbox mode.
	$mp = new MP(
		WPeComm_MercadoPago_Module::get_module_version(),
		get_option( 'mercadopago_custom_accesstoken' )
	);
	if ( 'active' == get_option( 'mercadopago_custom_sandbox' ) ) {
		$mp->sandbox_mode( true );
	} else {
		$mp->sandbox_mode( false );
	}

	// Get the order amount.
	$amount = wpsc_cart_total( false );

	// Get customer cards.
	$customerId = null;
	$customer_cards = array();
	$logged_user_email = wp_get_current_user()->user_email;
	$customer = $mp->get_or_create_customer( $logged_user_email );
	if ( isset( $customer['cards'] ) ) {
		$customer_cards = $customer['cards'];
	}
	if ( isset( $customer['id'] ) ) {
		$customerId = $customer['id'];
	}

	// Site id.
	$site_id = get_option( 'mercadopago_custom_siteid', 'MLA' );
	if ( empty( $site_id ) || $site_id == null ) {
		$site_id = 'MLA';
	}
	$country_configs = WPeComm_MercadoPago_Module::get_country_config( $site_id );

	// Labels.
	$form_labels = json_decode( stripslashes(
		preg_replace( '/u([\da-fA-F]{4})/', '&#x\1;', stripslashes(
			get_option( 'mercadopago_custom_checkoutmessage1', '' )
		) )
	), true );
	if ( $form_labels == '' ) {
		$form_labels = array(
			'form' => array(
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
				'your_card' => __( 'Your Card', 'wpecomm-mercadopago-module' ),
				'other_cards' => __( 'Other Cards', 'wpecomm-mercadopago-module' ),
				'other_card' => __( 'Other Card', 'wpecomm-mercadopago-module' ),
				'ended_in' => __( 'ended in', 'wpecomm-mercadopago-module' ),
				'card_holder_placeholder' => __( ' as it appears in your card ...', 'wpecomm-mercadopago-module' ),
				'payment_method' => __( 'Payment Method', 'wpecomm-mercadopago-module' ),
				'credit_card_number' => __( 'Credit card number', 'wpecomm-mercadopago-module' ),
				'expiration_month' => __( 'Expiration month', 'wpecomm-mercadopago-module' ),
				'expiration_year' => __( 'Expiration year', 'wpecomm-mercadopago-module' ),
				'year' => __( 'Year', 'wpecomm-mercadopago-module' ),
				'month' => __( 'Month', 'wpecomm-mercadopago-module' ),
				'card_holder_name' => __( 'Card holder name', 'wpecomm-mercadopago-module' ),
				'security_code' => __( 'Security code', 'wpecomm-mercadopago-module' ),
				'document_type' => __( 'Document Type', 'wpecomm-mercadopago-module' ),
				'document_number' => __( 'Document number', 'wpecomm-mercadopago-module' ),
				'issuer' => __( 'Issuer', 'wpecomm-mercadopago-module' ),
				'installments' => __( 'Installments', 'wpecomm-mercadopago-module' )
			),
			'error' => array(
				'missing_data_checkout' => __( 'Your payment failed to be processed.<br/>Are you sure you have set all information?', 'wpecomm-mercadopago-module' ),
				'server_error_checkout' => __( 'Your payment could not be completed. Please, try again.', 'wpecomm-mercadopago-module' ),
				//card number
				'205' => __( 'Parameter cardNumber can not be null/empty', 'wpecomm-mercadopago-module' ),
				'E301' => __( 'Invalid Card Number', 'wpecomm-mercadopago-module' ),
				//expiration date
				'208' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
				'209' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
				'325' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
				'326' => __( 'Invalid Expiration Date', 'wpecomm-mercadopago-module' ),
				//card holder name
				'221' => __( 'Parameter cardholderName can not be null/empty', 'wpecomm-mercadopago-module' ),
				'316' => __( 'Invalid Card Holder Name', 'wpecomm-mercadopago-module' ),
				//security code
				'224' => __( 'Parameter securityCode can not be null/empty', 'wpecomm-mercadopago-module' ),
				'E302' => __( 'Invalid Security Code', 'wpecomm-mercadopago-module' ),
				//doc type
				'212' => __( 'Parameter docType can not be null/empty', 'wpecomm-mercadopago-module' ),
				'322' => __( 'Invalid Document Type', 'wpecomm-mercadopago-module' ),
				//doc number
				'214' => __( 'Parameter docNumber can not be null/empty', 'wpecomm-mercadopago-module' ),
				'324' => __( 'Invalid Document Number', 'wpecomm-mercadopago-module' ),
				//doc sub type
				'213' => __( 'The parameter cardholder.document.subtype can not be null or empty', 'wpecomm-mercadopago-module' ),
				'323' => __( 'Invalid Document Sub Type', 'wpecomm-mercadopago-module' ),
				//issuer
				'220' => __( 'Parameter cardIssuerId can not be null/empty', 'wpecomm-mercadopago-module' )
			)
		);
	}

	// header
	$payment_header =
		'<div width="100%" style="margin:0px; padding:16px 36px 16px 36px; background:white;
			border-style:solid; border-color:#DDDDDD" border-radius:1.0px;">
			<img class="logo" src="' .
				plugins_url( 'wpsc-merchants/mercadopago-images/mplogo.png', plugin_dir_path( __FILE__ ) ) .
				'" width="156" height="40" />
			<img alt="Mercado Pago" title="Mercado Pago" class="mp-creditcard-banner" src="' .
				$country_configs['checkout_banner_custom'] .
				'" width="312" height="40" />
		</div>';

	// payment method
	$mercadopago_form =
		'<fieldset style="background:white;">
			<div id="mercadopago-form-customer-and-card" style="padding:0px 36px 0px 36px;">
				<div class="mp-box-inputs mp-line">
					<label for="paymentMethodIdSelector">' . $form_labels['form']['payment_method'] . ' <em>*</em></label>
					<select id="paymentMethodSelector" name="mercadopago_custom[paymentMethodSelector]" data-checkout="cardId">
						<optgroup label=' . $form_labels['form']['your_card'] . ' id="payment-methods-for-customer-and-cards">' .
							payment_methods_customer_cards( $customer_cards, $form_labels ) .
						'</optgroup>
						<optgroup label="' . $form_labels['form']['other_cards'] . '" id="payment-methods-list-other-cards">
							<option value="-1">' . $form_labels['form']['other_card'] . '</option>
						</optgroup>
					</select>
				</div>
				<div class="mp-box-inputs mp-line" id="mp-securityCode-customer-and-card">
					<div class="mp-box-inputs mp-col-45">
						<label for="customer-and-card-securityCode">' . $form_labels['form']['security_code'] . ' <em>*</em></label>
						<input type="text" id="customer-and-card-securityCode" data-checkout="securityCode" autocomplete="off"
							maxlength="4" style="padding: 8px; background: url( ' .
								WPeComm_MercadoPago_Module::get_image_path( 'cvv.png' ) .
							' ) 98% 50% no-repeat;"/>
						<span class="mp-error" id="mp-error-224" data-main="#customer-and-card-securityCode"> ' .
							$form_labels['error']['224'] . ' </span>
						<span class="mp-error" id="mp-error-E302" data-main="#customer-and-card-securityCode"> ' .
							$form_labels['error']['E302'] . ' </span>
					</div>
				</div>
			</div>

			<div id="mercadopago-form" style="padding:0px 36px 0px 36px;">
				<div class="mp-box-inputs mp-col-100">
					<label for="cardNumber">' . $form_labels['form']['credit_card_number'] . ' <em>*</em></label>
					<input type="text" id="cardNumber" data-checkout="cardNumber" autocomplete="off" maxlength="19"/>
					<span class="mp-error" id="mp-error-205" data-main="#cardNumber"> ' .
						$form_labels['error']['205'] . ' </span>
					<span class="mp-error" id="mp-error-E301" data-main="#cardNumber"> ' .
						$form_labels['error']['E301'] . ' </span>
				</div>
				<div class="mp-box-inputs mp-line">
					<div class="mp-box-inputs mp-col-45">
						<label for="cardExpirationMonth">' . $form_labels['form']['expiration_month'] . ' <em>*</em></label>
						<select id="cardExpirationMonth" data-checkout="cardExpirationMonth" name="mercadopago_custom[cardExpirationMonth]">
							<option value="-1"> ' . $form_labels['form']['month'] . ' </option>' .
							'<option value="1"> 1 </option>' . '<option value="2"> 2 </option>' . '<option value="3"> 3 </option>' .
							'<option value="4"> 4 </option>' . '<option value="5"> 5 </option>' . '<option value="6"> 6 </option>' .
							'<option value="7"> 7 </option>' . '<option value="8"> 8 </option>' . '<option value="9"> 9 </option>' .
							'<option value="10"> 10 </option>' . '<option value="11"> 11 </option>' . '<option value="12"> 12 </option>' .
						'</select>
					</div>
					<div class="mp-box-inputs mp-col-10">
						<div id="mp-separete-date">
							/
						</div>
					</div>
					<div class="mp-box-inputs mp-col-45">
						<label for="cardExpirationYear">' . $form_labels['form']['expiration_year'] . ' <em>*</em></label>
						<select id="cardExpirationYear" data-checkout="cardExpirationYear" name="mercadopago_custom[cardExpirationYear]">
							<option value="-1"> ' . $form_labels['form']['year'] . ' </option>' .
							build_years_for_date() .
						'</select>
					</div>
					<span class="mp-error" id="mp-error-208" data-main="#cardExpirationMonth"> ' .
						$form_labels['error']['208'] . ' </span>
					<span class="mp-error" id="mp-error-209" data-main="#cardExpirationYear"> </span>
					<span class="mp-error" id="mp-error-325" data-main="#cardExpirationMonth"> ' .
						$form_labels['error']['325'] . ' </span>
					<span class="mp-error" id="mp-error-326" data-main="#cardExpirationYear"> </span>
				</div>
				<div class="mp-box-inputs mp-col-100">
					<label for="cardholderName">' . $form_labels['form']['card_holder_name'] . ' <em>*</em></label>
					<input type="text" id="cardholderName" name="mercadopago_custom[cardholderName]"' .
						' data-checkout="cardholderName" autocomplete="off"/>
					<span class="mp-error" id="mp-error-221" data-main="#cardholderName"> ' .
						$form_labels['error']['221'] . ' </span>
					<span class="mp-error" id="mp-error-316" data-main="#cardholderName"> ' .
						$form_labels['error']['316'] . ' </span>
				</div>
				<div class="mp-box-inputs mp-line">
					<div class="mp-box-inputs mp-col-45">
						<label for="securityCode">' . $form_labels['form']['security_code'] . ' <em>*</em></label>
						<input type="text" id="securityCode" data-checkout="securityCode" autocomplete="off"' .
							'maxlength="4" style="padding: 8px; background: url( ' .
								WPeComm_MercadoPago_Module::get_image_path( 'cvv.png' ) .
							' ) 98% 50% no-repeat;" />
						<span class="mp-error" id="mp-error-224" data-main="#securityCode"> ' .
							$form_labels['error']['224'] . ' </span>
						<span class="mp-error" id="mp-error-E302" data-main="#securityCode"> ' .
							$form_labels['error']['E302'] . ' </span>
					</div>
				</div>
				<div class="mp-box-inputs mp-col-100 mp-doc">
					<div class="mp-box-inputs mp-col-35 mp-docType">
						<label for="docType">' . $form_labels['form']['document_type'] . ' <em>*</em></label>
						<select id="docType" data-checkout="docType" name="mercadopago_custom[docType]"></select>
						<span class="mp-error" id="mp-error-212" data-main="#docType"> ' .
							$form_labels['error']['212'] . ' </span>
						<span class="mp-error" id="mp-error-322" data-main="#docType"> ' .
							$form_labels['error']['322'] . ' </span>
					</div>
					<div class="mp-box-inputs mp-col-65 mp-docNumber">
						<label for="docNumber">' . $form_labels['form']['document_number'] . ' <em>*</em></label>
						<input type="text" id="docNumber" data-checkout="docNumber" name="mercadopago_custom[docNumber]" autocomplete="off" />
						<span class="mp-error" id="mp-error-214" data-main="#docNumber"> ' .
							$form_labels['error']['214'] . ' </span>
						<span class="mp-error" id="mp-error-324" data-main="#docNumber"> ' .
							$form_labels['error']['324'] . ' </span>
					</div>
				</div>
				<div class="mp-box-inputs mp-col-100 mp-issuer">
					<label for="issuer">' . $form_labels['form']['issuer'] . ' <em>*</em></label>
					<select id="issuer" data-checkout="issuer" name="mercadopago_custom[issuer]"></select>
						<span class="mp-error" id="mp-error-220" data-main="#issuer"> ' . $form_labels['error']['220'] . ' </span>
				</div>
			</div>

			<div class="mp-box-inputs mp-col-100" style="padding:0px 36px 0px 36px;">
				<label for="installments">' . $form_labels['form']['installments'] . ( (
					get_option( 'mercadopago_custom_currencyconversion' ) == 'active' ) > 0 ?
						' (' . $form_labels['form']['payment_converted'] . ')' : ''
					) . ' <em>*</em>
				</label>
				<select id="installments" data-checkout="installments" name="mercadopago_custom[installments]"></select>
			</div>

			<div class="mp-box-inputs mp-line" style="padding:0px 36px 0px 36px;">
				<div class="mp-box-inputs mp-col-60">
					<input type="hidden" id="submit" value="Pay With Mercado Pago">
				</div>
				<div class="mp-box-inputs mp-col-15">
					<div class="mp-box-inputs mp-col-10" style="background: #00FFFFFF; float: right;"></div>
					<div id="mp-box-loading" style="margin: 4px 4px 4px 0px;"></div>
				</div>
			</div>

			<div class="mp-box-inputs mp-col-100" id="mercadopago-utilities" style="padding:0px 36px 0px 36px;">
				<input type="hidden" id="site_id" name="mercadopago_custom[site_id]"/>
				<input type="hidden" id="amount" value="' . $amount . '" name="mercadopago_custom[amount]"/>
				<input type="hidden" id="paymentMethodId" name="mercadopago_custom[paymentMethodId]"/>
				<input type="hidden" id="token" name="mercadopago_custom[token]"/>
				<input type="hidden" id="cardTruncated" name="mercadopago_custom[cardTruncated]"/>
				<input type="hidden" id="customerAndCard" name="mercadopago_custom[CustomerAndCard]"/>
				<input type="hidden" id="customerId" value="' . $customerId . '" name="mercadopago_custom[customerId]"/>
			</div>

		</fieldset>';

	// javascript
	$page_js =
		'<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
		<script src="' . plugins_url( 'wpsc-merchants/mercadopago-lib/MPv1.js?no_cache=' .
			time(), plugin_dir_path( __FILE__ ) ) . '"></script>
		<script type="text/javascript">
			var mercadopago_site_id = "' . $site_id . '";
			var mercadopago_public_key = "' . get_option( 'mercadopago_custom_publickey' ) . '";
			MPv1.text.choose = "' . $form_labels['form']['label_choose'] . '";
			MPv1.text.other_bank = "' . $form_labels['form']['label_other_bank'] . '";
			MPv1.paths.loading = "' . WPeComm_MercadoPago_Module::get_image_path( 'loading.gif' ) . '";
			MPv1.setForm = function() {
				if (MPv1.customer_and_card.status) {
					document.querySelector(MPv1.selectors.form).style.display = "none";
					document.querySelector(MPv1.selectors.mpSecurityCodeCustomerAndCard).removeAttribute("style");
				} else {
					document.querySelector(MPv1.selectors.mpSecurityCodeCustomerAndCard).style.display = "none";
					document.querySelector(MPv1.selectors.form).removeAttribute("style");
					document.querySelector(MPv1.selectors.form).style.padding = "0px 36px 0px 36px";
				}
				Mercadopago.clearSession();
				if (MPv1.create_token_on.event) {
					MPv1.createTokenByEvent();
					MPv1.validateInputsCreateToken();
				}
				document.querySelector(MPv1.selectors.customerAndCard).value = MPv1.customer_and_card.status;
			}
			MPv1.getAmount = function() {
				return document.querySelector(MPv1.selectors.amount).value;
			}
			MPv1.showErrors = function(response) {
				var $form = MPv1.getForm();
				for (var x = 0; x < response.cause.length; x++) {
					var error = response.cause[x];
					var $span = $form.querySelector("#mp-error-" + error.code);
					var $input = $form.querySelector($span.getAttribute("data-main"));
					$span.style.display = "inline-block";
					$input.classList.add("mp-error-input");
				}
				return;
			}
			MPv1.Initialize(mercadopago_site_id, mercadopago_public_key);
		</script>';

	// header
	$page_header =
		'<head>
			<link rel="stylesheet" id="twentysixteen-style-css"
				href="https://modules-mercadopago.rhcloud.com/wp-content/themes/twentysixteen/style.css?ver=4.5.3" type="text/css" media="all">
			<link rel="stylesheet" id="custom-checkout-mercadopago" href="' .
				plugins_url( 'wpsc-merchants/mercadopago-lib/custom_checkout_mercadopago.css', plugin_dir_path( __FILE__ ) ) .
				'?ver=4.5.3" type="text/css" media="all">
			<script src="' . plugins_url( 'wpsc-merchants/mercadopago-lib/MPv1.js?no_cache=' .
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

function build_years_for_date() {

	$years_options = '';

	for ( $x=date( 'Y' ); $x<= date( 'Y' ) + 10; $x++ ) {
		$years_options .= '<option value="' . $x . '"> ' . $x . ' </option>';
	}

	return $years_options;

}

function payment_methods_customer_cards( $customer_cards, $form_labels ) {

	$select_customer_cards = '';

	foreach ( $customer_cards as $card ) :
		$select_customer_cards .= '
		<option value="' . $card['id'] . '"
			first_six_digits="' . $card['first_six_digits'] . '"
			last_four_digits="' . $card['last_four_digits'] . '"
			security_code_length="' . $card['security_code']['length'] . '"
			type_checkout="customer_and_card"
			payment_method_id="' . $card['payment_method']['id'] . '">' .
			ucfirst( $card['payment_method']['name'] ) . ' ' .
			$form_labels['form']['ended_in'] . ' ' . $card['last_four_digits'] . '
		</option>';
	endforeach;

	return $select_customer_cards;

}

function binary() {

	$binary = get_option( 'mercadopago_custom_binary' );
	$binary = $binary === false || is_null( $binary ) ? 'inactive' : $binary;
	$binary_options = array(
		array( 'value' => 'active', 'text' => 'Active' ),
		array( 'value' => 'inactive', 'text' => 'Inactive' )
	);
	$select_binary = '';

	foreach ( $binary_options as $op_binary ) :
		$selected = '';
		if ( $op_binary['value'] == $binary ) :
			$selected = "selected='selected'";
		endif;
		$select_binary =
			'<option value="' . $op_binary['value'] .
			'" id="binary-' . $op_binary['value'] .
			'" ' . $selected . '>' . __( $op_binary['text'], 'wpecomm-mercadopago-module' ) .
			'</option>';
	endforeach;

	return $select_binary;

}

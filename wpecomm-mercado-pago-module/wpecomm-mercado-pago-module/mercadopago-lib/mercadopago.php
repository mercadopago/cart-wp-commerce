<?php

/**
 * Part of Woo Mercado Pago Module - MercadoPago Integration Library, Access MercadoPago for payments integration
 * Author - Mercado Pago
 * Developer - hcasatti, Marcelo Tomio Hama / marcelo.hama@mercadolivre.com
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

$GLOBALS['LIB_LOCATION'] = dirname( __FILE__ );

class MP {

	private $version = '4.2.1';
	private $client_id;
	private $client_secret;
	private $ll_access_token;
	private $sandbox = FALSE;

	/**
	 * Summary: Constructor.
	 * Description: Build an object with module version and credentials.
	 */
	function __construct() {

		$i = func_num_args();

		if ( $i > 3 || $i < 2 ) {
			throw new MercadoPagoException(
				'Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN'
			 );
		}

		if ( $i == 2 ) {
			$this->version = func_get_arg( 0 );
			$this->ll_access_token = func_get_arg( 1 );
		}

		if ( $i == 3 ) {
			$this->version = func_get_arg( 0 );
			$this->client_id = func_get_arg( 1 );
			$this->client_secret = func_get_arg( 2 );
		}

	}

	public function sandbox_mode( $enable = NULL ) {
		if ( ! is_null( $enable ) ) {
			$this->sandbox = $enable === TRUE;
		}
		return $this->sandbox;
	}

	/**
	 * Summary: Get Access Token for API use.
	 * Description: Get Access Token for API use.
	 * @return a string that identifies the access token.
	 */
	public function get_access_token() {

		if ( isset( $this->ll_access_token ) && ! is_null( $this->ll_access_token ) ) {
			return $this->ll_access_token;
		}

		$app_client_values = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'client_credentials'
		 );

		$access_data = MPRestClient::post(
			array(
				'uri' => '/oauth/token',
				'data' => $app_client_values,
				'headers' => array(
					'content-type' => 'application/x-www-form-urlencoded'
				 )
			 ),
			$this->version
		 );

		if ( $access_data['status'] != 200 ) {
			throw new MercadoPagoException (
				$access_data['response']['message'], $access_data['status']
			 );
		}

		$access_data = $access_data['response'];
		return $access_data['access_token'];

	}

	/**
	 * Summary: Search for a payment given its id.
	 * Description: Search for a payment given its id.
	 * @param string $id
	 * @return array( json )
	 */
	public function search_paymentV1( $id ) {

		$request = array(
			'uri' => '/v1/payments/' . $id,
			'params' => array(
				'access_token' => $this->get_access_token()
			 )
		 );

		$payment = MPRestClient::get( $request, $this->version );
		return $payment;

	}

	//=== CUSTOMER CARDS FUNCTIONS ===

	/**
	 * Summary: Trigger API to find a card owner and create him if dont find.
	 * Description: Trigger API to find a card owner and create him if dont find.
	 * @param string $payer_email
	 * @return array( json )
	 */
	public function get_or_create_customer( $payer_email ) {

		$customer = $this->search_customer( $payer_email );

		if ( $customer['status'] == 200 && $customer['response']['paging']['total'] > 0 ) {
			$customer = $customer['response']['results'][0];
		} else {
			$resp = $this->create_customer( $payer_email );
			$customer = $resp['response'];
		}

		return $customer;

	}

	/**
	 * Summary: Create a card owner.
	 * Description: Create a card owner.
	 * @param string $payer_email
	 * @return array( json )
	 */
	public function create_customer( $email ) {

		$request = array(
			'uri' => '/v1/customers',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => array(
				'email' => $email
			 )
		 );

		$customer = MPRestClient::post( $request, $this->version );
		return $customer;

	}

	/**
	 * Summary: Try to find a card owner.
	 * Description: Try to find a card owner.
	 * @param string $payer_email
	 * @return array( json )
	 */
	public function search_customer( $email ) {

		$request = array(
			'uri' => '/v1/customers/search',
			'params' => array(
				'access_token' => $this->get_access_token(),
				'email' => $email
			 )
		 );

		$customer = MPRestClient::get( $request, $this->version );
		return $customer;

	}

	/**
	 * Summary: Given a customer, create a card for him.
	 * Description: Given a customer, create a card for him.
	 * @param string $customer_id
	 * @param string $token
	 * @param string $payment_method_id
	 * @param string $issuer_id
	 * @return array( json )
	 */
	public function create_card_in_customer( $customer_id, $token, $payment_method_id = null,
		$issuer_id = null ) {

		$request = array(
			'uri' => '/v1/customers/' . $customer_id . '/cards',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => array(
				'token' => $token,
				'issuer_id' => $issuer_id,
				'payment_method_id' => $payment_method_id
			 )
		 );

		$card = MPRestClient::post( $request, $this->version );
		return $card;

	}

	/**
	 * Summary: Given a customer, find all of his cards.
	 * Description: Given a customer, find all of his cards.
	 * @param string $customer_id
	 * @param string $token
	 * @return array( json )
	 */
	public function get_all_customer_cards( $customer_id, $token ) {

		$request = array(
			'uri' => '/v1/customers/' . $customer_id . '/cards',
			'params' => array(
				'access_token' => $this->get_access_token()
			 )
		 );

		$cards = MPRestClient::get( $request, $this->version );
		return $cards;

	}

	//=== COUPOM AND DISCOUNTS FUNCTIONS ===

	/**
	 * Summary: Check for discounts for a combination of amount, payer and coupm code.
	 * Description: Check for discounts for a combination of amount, payer and coupm code.
	 * @param string $transaction_amount
	 * @param string $payer_email
	 * @param string $coupon_code
	 * @return array( json )
	 */
	public function check_discount_campaigns( $transaction_amount, $payer_email, $coupon_code ) {

		$request = array(
			'uri' => '/discount_campaigns',
			'params' => array(
				'access_token' => $this->get_access_token(),
				'transaction_amount' => $transaction_amount,
				'payer_email' => $payer_email,
				'coupon_code' => $coupon_code
			 )
		 );

		$discount_info = MPRestClient::get( $request, $this->version );
		return $discount_info;

	}

	//=== ACCOUNT SETTINGS FUNCTIONS ===

	/**
	 * Summary: Check the status of a account regarding its option to use two cards for pay.
	 * Description: Check the status of a account regarding its option to use two cards for pay.
	 * @return array( json )
	 */
	public function check_two_cards() {

		$request = array(
			'uri' => '/account/settings?access_token=' . $this->get_access_token()
		 );

		$two_cards_info = MPRestClient::get( $request, $this->version );
		if ( $two_cards_info['status'] == 200 )
			return $two_cards_info['response']['two_cards'];
		else {
			return 'inactive';
		}

	}

	/**
	 * Summary: Set paymennts with two cards for the merchant.
	 * Description: Set paymennts with two cards for the merchant.
	 * @param string $mode ( should be 'active' or 'inactive' string )
	 * @return array( json )
	 */
	public function set_two_cards_mode( $mode ) {

		$request = array(
			'uri' => '/account/settings?access_token=' . $this->get_access_token(),
			'data' => array(
				'two_cards' => $mode
			 ),
			'headers' => array(
				'content-type' => 'application/json'
			 )
		 );

		$two_cards_info = MPRestClient::put( $request, $this->version );
		return $two_cards_info;

	}

	//=== CHECKOUT AUXILIARY FUNCTIONS ===

	/**
	 * Summary: Get information for specific payment.
	 * Description: Get information for specific payment.
	 * @param int $id
	 * @return array( json )
	 */
	public function get_payment_info( $id ) {

		$uri_prefix = $this->sandbox ? '/sandbox' : '';

		$request = array(
			'uri' => $uri_prefix.'/collections/notifications/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 )
		 );

		$payment_info = MPRestClient::get( $request, $this->version );
		return $payment_info;

	}

	/**
	 * Summary: Search payments according to filters, with pagination.
	 * Description: Search payments according to filters, with pagination.
	 * @param array $filters
	 * @param int $offset
	 * @param int $limit
	 * @return array( json )
	 */
	public function search_payment( $filters, $offset = 0, $limit = 0 ) {

		$filters['offset'] = $offset;
		$filters['limit'] = $limit;

		$uri_prefix = $this->sandbox ? '/sandbox' : '';

		$request = array(
			'uri' => $uri_prefix . '/collections/search',
			'params' => array_merge ( $filters, array(
				'access_token' => $this->get_access_token()
			 ) )
		 );

		$collection_result = MPRestClient::get( $request, $this->version );
		return $collection_result;

	}

	/**
	 * Summary: Get information for specific authorized payment.
	 * Description: Get information for specific authorized payment.
	 * @param id
	 * @return array( json )
	*/
	public function get_authorized_payment( $id ) {

		$request = array(
			'uri' => '/authorized_payments/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 )
		 );

		$authorized_payment_info = MPRestClient::get( $request, $this->version );
		return $authorized_payment_info;

	}

	/**
	 * Summary: Create a checkout preference.
	 * Description: Create a checkout preference.
	 * @param array $preference
	 * @return array( json )
	 */
	public function create_preference( $preference ) {

	   $request = array(
		   'uri' => '/checkout/preferences',
		   'params' => array(
			   'access_token' => $this->get_access_token()
			),
		   'headers' => array(
			   'user-agent' => 'platform:desktop,type:woocommerce,so:' . $this->version
			),
		   'data' => $preference
		);

	   $preference_result = MPRestClient::post( $request, $this->version );
	   return $preference_result;

	}

	/**
	 * Summary: Update a checkout preference.
	 * Description: Update a checkout preference.
	 * @param string $id
	 * @param array $preference
	 * @return array( json )
	 */
	public function update_preference( $id, $preference ) {

		$request = array(
			'uri' => '/checkout/preferences/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => $preference
		 );

		$preference_result = MPRestClient::put( $request, $this->version );
		return $preference_result;

	}

	/**
	 * Summary: Get a checkout preference.
	 * Description: Get a checkout preference.
	 * @param string $id
	 * @return array( json )
	 */
	public function get_preference( $id ) {

		$request = array(
			'uri' => '/checkout/preferences/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 )
		 );

		$preference_result = MPRestClient::get( $request, $this->version );
		return $preference_result;

	}

	/**
	 * Summary: Create a checkout preference.
	 * Description: Create a checkout preference.
	 * @param array $preference
	 * @return array( json )
	 */
	public function create_payment( $preference ) {

		$request = array(
			'uri' => '/v1/payments',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'headers' => array(
				'X-Tracking-Id' => 'platform:v1-whitelabel,type:woocommerce,so:' . $this->version
			 ),
			'data' => $preference
		 );

		$payment = MPRestClient::post( $request, $this->version );
		return $payment;
	}

	/**
	 * Summary: Create a preapproval payment.
	 * Description: Create a preapproval payment.
	 * @param array $preapproval_payment
	 * @return array( json )
	 */
	public function create_preapproval_payment( $preapproval_payment ) {

		$request = array(
			'uri' => '/preapproval',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => $preapproval_payment
		 );

		$preapproval_payment_result = MPRestClient::post( $request, $this->version );
		return $preapproval_payment_result;

	}

	/**
	 * Summary: Get a preapproval payment.
	 * Description: Get a preapproval payment.
	 * @param string $id
	 * @return array( json )
	 */
	public function get_preapproval_payment( $id ) {

		$request = array(
			'uri' => '/preapproval/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 )
		 );

		$preapproval_payment_result = MPRestClient::get( $request, $this->version );
		return $preapproval_payment_result;

	}

	/**
	 * Summary: Update a preapproval payment.
	 * Description: Update a preapproval payment.
	 * @param string $preapproval_payment, $id
	 * @return array( json )
	 */
	public function update_preapproval_payment( $id, $preapproval_payment ) {

		$request = array(
			'uri' => '/preapproval/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => $preapproval_payment
		 );

		$preapproval_payment_result = MPRestClient::put( $request, $this->version );
		return $preapproval_payment_result;

	}

	//=== REFUND AND CANCELING FLOW FUNCTIONS ===

	/**
	 * Summary: Refund accredited payment.
	 * Description: Refund accredited payment.
	 * @param int $id
	 * @return array( json )
	 */
	public function refund_payment( $id ) {

		$request = array(
			'uri' => '/collections/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => array(
				'status' => 'refunded'
			 )
		 );

		$response = MPRestClient::put( $request, $this->version );
		return $response;

	}

	/**
	 * Summary: Cancel pending payment.
	 * Description: Cancel pending payment.
	 * @param int $id
	 * @return array( json )
	 */
	public function cancel_payment( $id ) {

		$request = array(
			'uri' => '/collections/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => array(
				'status' => 'cancelled'
			 )
		 );

		$response = MPRestClient::put( $request, $this->version );
		return $response;

	}

	/**
	 * Summary: Cancel preapproval payment.
	 * Description: Cancel preapproval payment.
	 * @param int $id
	 * @return array( json )
	 */
	public function cancel_preapproval_payment( $id ) {

		$request = array(
			'uri' => '/preapproval/{$id}',
			'params' => array(
				'access_token' => $this->get_access_token()
			 ),
			'data' => array(
				'status' => 'cancelled'
			 )
		 );

		$response = MPRestClient::put( $request, $this->version );
		return $response;

	}

	//=== GENERIC RESOURCE CALL METHODS ===

	/**
	 * Summary: Generic resource get.
	 * Description: Generic resource get.
	 * @param request
	 * @param params (deprecated)
	 * @param authenticate = true (deprecated)
	 */
	public function get( $request, $params = null, $authenticate = true ) {

		if ( is_string ( $request ) ) {
			$request = array(
				'uri' => $request,
				'params' => $params,
				'authenticate' => $authenticate
			 );
		}

		$request['params'] = isset( $request['params'] ) && is_array( $request['params'] ) ?
			$request['params'] :
			array();

		if ( ! isset( $request['authenticate'] ) || $request['authenticate'] !== false ) {
			$request['params']['access_token'] = $this->get_access_token();
		}

		$result = MPRestClient::get( $request, $this->version );
		return $result;

	}

	/**
	 * Summary: Generic resource post.
	 * Description: Generic resource get.
	 * @param request
	 * @param data (deprecated)
	 * @param params (deprecated)
	 */
	public function post( $request, $data = null, $params = null ) {

		if ( is_string ( $request ) ) {
			$request = array(
				'uri' => $request,
				'data' => $data,
				'params' => $params
			 );
		}

		$request['params'] = isset( $request['params'] ) && is_array( $request['params'] ) ?
			$request["params"] :
			array();

		if ( ! isset ( $request['authenticate'] ) || $request['authenticate'] !== false ) {
			$request['params']['access_token'] = $this->get_access_token();
		}

		$result = MPRestClient::post( $request, $this->version );
		return $result;

	}

	/**
	 * Summary: Generic resource put.
	 * Description: Generic resource put.
	 * @param request
	 * @param data (deprecated)
	 * @param params (deprecated)
	 */
	public function put( $request, $data = null, $params = null ) {

		if ( is_string ( $request ) ) {
			$request = array(
				'uri' => $request,
				'data' => $data,
				'params' => $params
			 );
		}

		$request['params'] = isset( $request['params'] ) && is_array( $request['params'] ) ?
			$request['params'] :
			array();

		if ( ! isset ( $request['authenticate'] ) || $request['authenticate'] !== false ) {
			$request['params']['access_token'] = $this->get_access_token();
		}

		$result = MPRestClient::put( $request, $this->version );
		return $result;

	}

	/**
	 * Summary: Generic resource delete.
	 * Description: Generic resource delete.
	 * @param request
	 * @param data (deprecated)
	 * @param params (deprecated)
	 */
	public function delete( $request, $params = null ) {

		if ( is_string ( $request ) ) {
			$request = array(
				'uri' => $request,
				'params' => $params
			 );
		}

		$request['params'] = isset( $request['params'] ) && is_array( $request['params'] ) ?
			$request["params"] :
			array();

		if ( ! isset( $request['authenticate'] ) || $request['authenticate'] !== false ) {
			$request['params']['access_token'] = $this->get_access_token();
		}

		$result = MPRestClient::delete( $request, $this->version );
		return $result;

	}

	//=== MODULE ANALYTICS FUNCTIONS ===

	/**
	 * Summary: Save the settings of the module for analytics purposes.
	 * Description: Save the settings of the module for analytics purposes.
	 * @param array( json )
	 * @return array( json )
	 */
	public function analytics_save_settings( $module_info ) {

		$request = array(
			'uri' => '/modules/tracking/saveSettings?access_token=' . $this->get_access_token(),
			'data' => $module_info
		 );

		$result = MPRestClient::get( $request, $this->version );
		return $result;

	}

}

/**
 * MercadoPago cURL RestClient
 */
class MPRestClient {

	const API_BASE_URL = 'https://api.mercadopago.com';

	private static function build_request( $request, $version ) {

		if ( ! extension_loaded ( 'curl' ) ) {
			throw new MercadoPagoException( 'cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.' );
		}

		if ( ! isset( $request['method'] ) ) {
			throw new MercadoPagoException( 'No HTTP METHOD specified' );
		}

		if ( ! isset( $request['uri'] ) ) {
			throw new MercadoPagoException( 'No URI specified' );
		}

		// Set headers
		$headers = array( 'accept: application/json' );
		$json_content = true;
		$form_content = false;
		$default_content_type = true;

		if ( isset( $request['headers'] ) && is_array( $request['headers'] ) ) {
			foreach ( $request['headers'] as $h => $v ) {
				$h = strtolower( $h );
				$v = strtolower( $v );
				if ( $h == 'content-type' ) {
					$default_content_type = false;
					$json_content = $v == 'application/json';
					$form_content = $v == 'application/x-www-form-urlencoded';
				}
				array_push ( $headers, $h . ': ' . $v );
			}
		}
		if ( $default_content_type ) {
			array_push( $headers, 'content-type: application/json' );
		}

		// Build $connect
		$connect = curl_init();

		curl_setopt(
			$connect,
			CURLOPT_USERAGENT,
			'platform:v1-whitelabel,type:woocommerce,so:' . $version );
		curl_setopt(
			$connect,
			CURLOPT_RETURNTRANSFER,
			true );
		curl_setopt(
			$connect,
			CURLOPT_SSL_VERIFYPEER,
			true );
		curl_setopt(
			$connect,
			CURLOPT_CAINFO,
			$GLOBALS['LIB_LOCATION'] . '/cacert.pem' );
		curl_setopt(
			$connect,
			CURLOPT_CUSTOMREQUEST,
			$request['method'] );
		curl_setopt(
			$connect,
			CURLOPT_HTTPHEADER,
			$headers );

		// Set parameters and url
		if ( isset( $request['params'] ) && is_array( $request['params'] ) ) {
			if ( count( $request['params'] > 0 ) ) {
				$request['uri'] .= ( strpos( $request['uri'], '?' ) === false ) ? '?' : '&';
				$request['uri'] .= self::build_query( $request['params'] );
			}
		}
		curl_setopt( $connect, CURLOPT_URL, MPRestClient::API_BASE_URL . $request['uri'] );

		// Set data
		if ( isset( $request['data'] ) ) {
			if ( $json_content ) {
				if ( gettype( $request['data'] ) == 'string' ) {
					json_decode( $request['data'], true );
				} else {
					$request['data'] = json_encode( $request['data'] );
				}
				if( function_exists( 'json_last_error' ) ) {
					$json_error = json_last_error();
					if ( $json_error != JSON_ERROR_NONE ) {
						throw new MercadoPagoException(
							'JSON Error [{$json_error}] - Data: ' . $request['data']
						 );
					}
				}
			} elseif ( $form_content ) {
				$request['data'] = self::build_query( $request['data'] );
			}
			curl_setopt( $connect, CURLOPT_POSTFIELDS, $request['data'] );
		}

		return $connect;

	}

	private static function exec( $request, $version ) {

		$connect = self::build_request( $request, $version );

		$api_result = curl_exec( $connect );
		$api_http_code = curl_getinfo( $connect, CURLINFO_HTTP_CODE );

		if ( $api_result === FALSE ) {
			throw new MercadoPagoException ( curl_error ( $connect ) );
		}

		$response = array(
			'status' => $api_http_code,
			'response' => json_decode( $api_result, true )
		 );

		curl_close( $connect );

		return $response;

	}

	private static function build_query( $params ) {

		if ( function_exists( 'http_build_query' ) ) {
			return http_build_query( $params, '', '&' );
		} else {
			foreach ( $params as $name => $value ) {
				$elements[] = '{$name}=' . urlencode( $value );
			}
			return implode( '&', $elements );
		}

	}

	public static function get( $request, $version ) {
		$request['method'] = 'GET';

		return self::exec( $request, $version );
	}

	public static function post( $request, $version ) {
		$request['method'] = 'POST';

		return self::exec( $request, $version );
	}

	public static function put( $request, $version ) {
		$request['method'] = 'PUT';

		return self::exec( $request, $version );
	}

	public static function delete( $request, $version ) {
		$request['method'] = 'DELETE';

		return self::exec( $request, $version );
	}

}

class MercadoPagoException extends Exception {
	public function __construct( $message, $code = 500, Exception $previous = null ) {
		// Default code 500
		parent::__construct( $message, $code, $previous );
	}
}

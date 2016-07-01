<?php


/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
*  It is also available through the world-wide-web at this URL: *
*  http://opensource.org/licenses/osl-3.0.php * 
*  @category    Payment Gateway * @package    	Mercado Pago 
*  @author      Andre Fuhrman (andrefuhrman@gmail.com) | Edited: Matias Gordon (matias.gordon@mercadolibre.com)
*  @copyright  Copyright (c) Mercado Pago [http://www.mercadopago.com] 
*  @version    3.9.0
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
*/

$nzshpcrt_gateways[$num]['name'] = 'Mercado Pago';
$nzshpcrt_gateways[$num]['internalname'] = 'mercadopago';
$nzshpcrt_gateways[$num]['function'] = 'function_mercadopago';
$nzshpcrt_gateways[$num]['form'] = 'form_mercadopago';
$nzshpcrt_gateways[$num]['submit_function'] = 'submit_mercadopago';
$nzshpcrt_gateways[$num]['payment_type'] = 'mp';
$nzshpcrt_gateways[$num]['display_name'] = 'Mercado Pago';
$nzshpcrt_gateways[$num]['class_name'] = 'wpsc_merchant_mercadopago';

include_once "lib/mercadopago.php";
include_once "lib/MPApi.php";


	function form_mercadopago(){
	
		if(get_option('mercadopago_url_sucess') != ''){
			$url_sucess = get_option('mercadopago_url_sucess');
		} else {
			$url_sucess = get_site_url();
		}
		
		if(get_option('mercadopago_url_pending') != ''){
			$url_pending = get_option('mercadopago_url_pending');
		} else {
			$url_pending = get_site_url();
		}
				
		$output.='<tr><td colspan="2"><h4>Account Credentials</h4>

		<p>Obtain your Client_id and Client_secret, accordingly to your country, in the following links:

		<a href="https://www.mercadopago.com/mla/herramientas/aplicaciones" target="_blank">Argentina</a>,
		<a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes" target="_blank">Brazil</a>,
		<a href="https://www.mercadopago.com/mlc/herramientas/aplicaciones" target="_blank">Chile</a>,
		<a href="https://www.mercadopago.com/mco/herramientas/aplicaciones" target="_blank">Colombia</a>,
		<a href="https://www.mercadopago.com/mlm/herramientas/aplicaciones" target="_blank">Mexico</a>,
		<a href="https://www.mercadopago.com/mpe/herramientas/aplicaciones" target="_blank">Peru</a> or 
		<a href="https://www.mercadopago.com/mlv/herramientas/aplicaciones" target="_blank">Venezuela</a>

		</p></td></tr>';

		$output.='<tr><td>Client Id:</td>';
		$output.='<td><input name="mercadopago_client_id" type="text" value="'. get_option('mercadopago_client_id') .'"/></td></tr>';
		
		$output.='<tr><td>Client Secret:</td>';
		$output.='<td><input name="mercadopago_client_secret" type="text" value="'. get_option('mercadopago_client_secret') .'"/></td></tr>';

		$output.='<tr><td colspan="2"><h4>Cart Customization</h4></td></tr>';

		$output.='<tr><td>Store Country</td>';
		$output.='<td>'. country() .'</td></tr>';

		$output.='<tr><td>Currency</td>';
		$output.='<td>'. currency() .'</td></tr>';

		$output.='<tr><td>Store Category</td>';
		$output.='<td>'. category() .'</td></tr>';
				
		$output.='<tr><td>Checkout Type</td>';
		$output.='<td>'. type_checkout() .'</td></tr>';

		$output.='<tr><td>Excluded payment methods</td>';
		$output.='<td>'. methods(get_option('mercadopago_country')) .'</td></tr>';
		
		$output.='<tr><td>Limit Installments</td>';
		$output.='<td>'. installments() .'<p class="description">Select the max number of installments for your customers.</p></td></tr>';
		
		$output.='<tr><td>Automatic Return After Payment</td>';
		$output.='<td>'. auto_return() .'</td></tr>';
		
		$output.='<tr><td>URL Approved Payment</td>';
		$output.='<td><input name="mercadopago_url_sucess" type="text" value="'. $url_sucess .'"/><p class="description">This is the URL where the customer is redirected if his payment is approved.</p></td></tr>';
		
		$output.='<tr><td>URL Pending Payment</td>';
		$output.='<td><input name="mercadopago_url_pending" type="text" value="'. $url_pending .'"/><p class="description">This is the URL where the customer is redirected if his payment is in process.</p></td></tr>';
		
		$output.='<tr><td colspan="2"><h4>IPN</h4></td></tr>';

		$output.='<tr><td colspan="2"><p>IPN (instant payment notification) will automatically update your sales logs status when payments are successful.</p></td></tr>';

		$output.='<tr><td colspan="2"><h4>Test and Debug Options</h4></td></tr>';

		$output.='<tr><td>Sandbox mode</td>';
		$output.='<td>'. sandbox() .'<p class="description">Enable to test payments inside a sandbox environment.</p></td></tr>';

		$output.='<tr><td>Debug mode</td>';
		$output.='<td>'. debugs() .'<p class="description">Enable to display error messages to frontend (not recommended in production environment).</p></td></tr>';
		return $output;
	
	}



	function submit_mercadopago(){
		
		if ( isset($_POST['mercadopago_client_id'])) {
			update_option('mercadopago_client_id',trim($_POST['mercadopago_client_id']));
		}
		
		if($_POST['mercadopago_client_secret'] != null) {
			update_option('mercadopago_client_secret',trim($_POST['mercadopago_client_secret']));
		}
		
		
		if($_POST['mercadopago_sandbox'] != null) {
			update_option('mercadopago_sandbox',trim($_POST['mercadopago_sandbox']));
		}
		
		
		if($_POST['mercadopago_typecheckout'] != null) {
			update_option('mercadopago_typecheckout',trim($_POST['mercadopago_typecheckout']));
		}
		
		if($_POST['mercadopago_auto_return'] != null) {
			update_option('mercadopago_auto_return',trim($_POST['mercadopago_auto_return']));
		}
		
		if($_POST['mercadopago_category'] != null) {
			update_option('mercadopago_category',trim($_POST['mercadopago_category']));
		}
		
		
		if($_POST['mercadopago_debug'] != null) {
			update_option('mercadopago_debug',trim($_POST['mercadopago_debug']));
		}
				
		if($_POST['mercadopago_url_sucess'] != null) {
			update_option('mercadopago_url_sucess',trim($_POST['mercadopago_url_sucess']));
		}
		
		if($_POST['mercadopago_url_pending'] != null) {
			update_option('mercadopago_url_pending',trim($_POST['mercadopago_url_pending']));
		}
		
		if($_POST['mercadopago_country'] != null) {
			update_option('mercadopago_country',trim($_POST['mercadopago_country']));
		}

		if($_POST['mercadopago_limit_payments'] != null) {
			update_option('mercadopago_limit_payments',trim($_POST['mercadopago_limit_payments']));
		}
		if($_POST['mercadopago_curcode'] != null) {
			update_option('mercadopago_curcode',trim($_POST['mercadopago_curcode']));
		}
		
		if ($_POST['mercadopago_methods'] != null){
			
			$methods = '';
			
			foreach ($_POST['mercadopago_methods'] as $name){
				$methods .= $name.',';
			}
			
			update_option('mercadopago_methods',$methods);
	
		} else {
			update_option('mercadopago_methods','');   
		}
		
		return true;
	
	}


	function function_mercadopago($seperator, $sessionid){
		
		global $wpdb, $wpsc_cart;
		
		
		//This grabs the purchase log id from the database
		//that refers to the $sessionid
		
		$purchase_log = $wpdb->get_row(
		"SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS.
		"` WHERE `sessionid`= ".$sessionid." LIMIT 1"
		,ARRAY_A);
		
		//This grabs the users info using the $purchase_log
		// from the previous SQL query
		
		$usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value,
		`".WPSC_TABLE_CHECKOUT_FORMS."`.`name`,
		`".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM
		`".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN
		`".WPSC_TABLE_SUBMITED_FORM_DATA."` ON
		`".WPSC_TABLE_CHECKOUT_FORMS."`.id =
		`".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE
		`".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$purchase_log['id'];
		
		$userinfo = $wpdb->get_results($usersql, ARRAY_A);
		
		// configs
		$data = array();
		$data['client_id'] = get_option('mercadopago_client_id');
		$data['client_secret'] = get_option('mercadopago_client_secret');
		$data['sucess'] = get_option('mercadopago_url_sucess');
		$data['pending'] = get_option('mercadopago_url_pending');
		
		switch (get_option('mercadopago_country')) {
			case 'MLA': 
				$data['currency'] = 'ARS';
				break;
			case 'MLB': 
				$data['currency'] = 'BRL';
				break;
			case 'MLC':
				$data['currency'] = 'CLP';
				break;
			case 'MCO':
				$data['currency'] = 'COP';
				break;
			case 'MLM':
				$data['currency'] = 'MXN';
				break;
			case 'MPE':
				$data['currency'] = 'PEN';
				break;
			case 'MLV':
				$data['currency'] = 'VEF';
				break;
		}

		$category = get_option('mercadopago_category');
		$type_checkout = get_option('mercadopago_typecheckout');
		$auto_return = get_option('mercadopago_auto_return') == "active" ? true:false;
		$sandbox = get_option('mercadopago_sandbox') == "active" ? true:false;

		switch (get_option('mercadopago_country')) {
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
		}
		
		// order info
		$data['total'] = $wpsc_cart->total_price;
	
		//client
		$arr_info = array();
		foreach((array)$userinfo as $key => $value){
			$arr_info[$value['unique_name']] = $value['value'];
		}
		
		// products
		foreach($wpsc_cart->cart_items as $i => $Item) {
			$data['PROD_NAME'.$i] = $Item->product_name;              
			$data['PROD_TOTAL'.$i] = number_format($Item->unit_price,2);
			$data['PROD_NUMBER'.$i]	= $i;
			$data['PROD_QTY'.$i] = $Item->quantity;      
			$data['prod'] .=  $Item->product_name .'*'. $Item->quantity;
			
			if($Item->thumbnail_image){
				foreach ($Item->thumbnail_image as $key => $Image) {
					if ($key == 'guid')
						$data['image'. $i] = $Image; 
				}
			} else {
				$data['image0'] = 'https://www.mercadopago.com/org-img/MP3/home/logomp3.gif';
			}
		}
		
		// exclude methods
		
		$shippingpostcode = array_key_exists("shippingpostcode", $arr_info) && $arr_info['shippingpostcode'] != ""  ? $arr_info['shippingpostcode'] : "uninformed";
		$shippingaddress = array_key_exists("shippingaddress", $arr_info) && $arr_info['shippingaddress'] != ""  ? $arr_info['shippingaddress'] : "uninformed";
		$shippingcity = array_key_exists("shippingcity", $arr_info) && $arr_info['shippingcity'] != ""  ? $arr_info['shippingcity'] : "uninformed";
		$shippingstate = array_key_exists("shippingstate", $arr_info) && $arr_info['shippingstate'] != ""  ? $arr_info['shippingstate'] : "uninformed";
		$shippingcountry = array_key_exists("shippingcountry", $arr_info) && $arr_info['shippingcountry'] != "" ? $arr_info['shippingcountry'] : "uninformed";
		
		$shipments = array(
			"receiver_address" => array(
				"floor" => "-",
				"zip_code" => $shippingpostcode,
				"street_name" => $shippingaddress . " - " . $shippingcity . " - " . $shippingstate . " - " . $shippingcountry,
				"apartment" => "-",
				"street_number" => "-"
			)
		);
		
		
		$billingpostcode = array_key_exists("billingpostcode", $arr_info) && $arr_info['billingpostcode'] != ""  ? $arr_info['billingpostcode'] : "uninformed";
		$billingaddress = array_key_exists("billingaddress", $arr_info) && $arr_info['billingaddress'] != ""  ? $arr_info['billingaddress'] : "uninformed";
		$billingcity = array_key_exists("billingcity", $arr_info) && $arr_info['billingcity'] != "" ? $arr_info['billingcity'] : "uninformed";
		$billingstate = array_key_exists("billingstate", $arr_info) && $arr_info['billingstate'] != ""  ? $arr_info['billingstate'] : "uninformed";
		$billingcountry = array_key_exists("billingcountry", $arr_info) && $arr_info['billingcountry'] != ""  ? $arr_info['billingcountry'] : "uninformed";
		$billingphone = array_key_exists("billingphone", $arr_info) && $arr_info['billingphone'] != ""  ? $arr_info['billingphone'] : "uninformed";
		
		$payer = array(
			"name" => $data['billingfirstname'],
			"surname" => $data['billinglastname'],
			"email" => $data['email'],
			"date_created" => "",
			"phone" => array(
				"area_code" => "-",
				"number" => $billingphone
			),
			"address" => array(
				"zip_code" => $billingpostcode,
				"street_name" => $billingaddress . " - " . $billingcity . " - " . $billingstate . " - " . $billingcountry,
				"street_number" => "-"
			),
			"identification" => array(
				"number" => "null",
				"type" => "null"
			)
		);
		
		
		
		$items = array(
			array (
				"id" => $sessionid,
				"title" => $data['PROD_NAME0'],
				"description" => $data['PROD_NAME0'] . " x " . $data['PROD_QTY0'],
				"quantity" => 1, //$data['PROD_QTY0'],// Comes full, then no need to send amount.
				"unit_price" =>  $data['total'] , // decimal
				"currency_id" => $data['currency'],// string
				"picture_url"=> $data['image0'],
				"category_id"=> $category
			)
		);
		
		//excludes_payment_methods
		$exclude = get_option('mercadopago_methods');
		$installments = (int)get_option('mercadopago_limit_payments');
		
		if($exclude != ''):
			//case exist exclude methods
			$methods_excludes = preg_split("/[\s,]+/", $exclude);
			$excludemethods = array();
			
			foreach ($methods_excludes as $exclude ){
				if($exclude != "")
					$excludemethods[] = array('id' => $exclude);     
			}
			
			$payment_methods = array(
				"installments" => $installments,
				"excluded_payment_methods" => $excludemethods
			);
		else:
			//case not exist exclude methods
			$payment_methods = array(
				"installments" => $installments
			);
		endif;
		
		
		//set back url
		$back_urls = array(
			"pending" => $data['pending'], // string
			"success" => $data['sucess']  // string
		);
		
		//mount array pref
		$pref = array();
		$pref['external_reference'] = $sessionid;
		$pref['payer'] = $payer;
		$pref['shipments'] = $shipments;
		$pref['items'] = $items;
		$pref['back_urls'] = $back_urls;
		$pref['payment_methods'] = $payment_methods;
		
		if (!$sandbox):
			$pref['sponsor_id'] = $sponsor_id;
		endif;

		if ($auto_return):
			$pref['auto_return'] = "approved";
		endif;

		$pref['notification_url'] = get_site_url();

		$mp = new MP($data['client_id'], $data['client_secret']);
		$preferenceResult = $mp->create_preference($pref);
	
		if($preferenceResult['status'] == 201):
			if ($sandbox):
				$link = $preferenceResult['response']['sandbox_init_point'];
			else:
				$link = $preferenceResult['response']['init_point'];
			endif;
		else:
			echo "Error: " . $preferenceResult['status'];
		endif;
		
	
		//title
		$title = "";
		if(get_option('mercadopago_country') == 'MLB'):
			$title = 'Continue pagando com Mercado Pago';
		else:
			$title = 'Continue pagando con Mercado Pago';    
		endif;


		//add image
		$img_banner = "";
		
		switch (get_option('mercadopago_country')) {
			case 'MLA': 
				$img_banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/ar/medios/468X60.jpg" alt="Mercado Pago" title="Mercado Pago" />'; 
				break;
			case 'MLB':
				$img_banner = '<img src="http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg" alt="Mercado Pago" title="Mercado Pago" />';
				break;
			case 'MLC':
				$img_banner = '<img src="https://www.mercadopago.cl/banner/468X60_banner.jpg" alt="Mercado Pago" title="Mercado Pago" />';
				break;
			case 'MCO':
				$img_banner = '<img src="https://secure.mlstatic.com/developers/site/cloud/banners/co/468x60_Todos-los-medios-de-pago.jpg" title="Mercado Pago" alt="Mercado Pago"/>';
				break;
			case 'MLM':
				$img_banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG" title="Mercado Pago" alt="Mercado Pago" />';
				break; 
			case 'MPE':
				$img_banner = '<img title=Mercado Pago" alt="Mercado Pago" />';
				break; 
			case 'MLV':
				$img_banner = '<img src="https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg" title="Mercado Pago" alt="Mercado Pago" />';
				break; 
		}	
		
		$button = "";
		
		switch($type_checkout):
			case "Redirect":
				header("location: " . $link);
				break;
			
			case "Iframe":
					$button = '
						
						<iframe src="' . $link . '" name="MP-Checkout" width="740" height="600" frameborder="0"></iframe>
						<script type="text/javascript">
							(function(){function $MPBR_load(){window.$MPBR_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;
							s.src = ("https:"==document.location.protocol?"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/":"http://mp-tools.mlstatic.com/buttons/")+"render.js";
							var x = document.getElementsByTagName("script")[0];x.parentNode.insertBefore(s, x);window.$MPBR_loaded = true;})();}
							window.$MPBR_loaded !== true ? (window.attachEvent ? window.attachEvent("onload", $MPBR_load) : window.addEventListener("load", $MPBR_load, false)) : null;})();
						</script>
						
						';
				break;
			
			case "Lightbox":
			default:
				$button = '
						
						<a href="' . $link . '" name="MP-Checkout" class="blue-L-Rn" mp-mode="modal" onreturn="execute_my_onreturn">Pagar</a>
						<script type="text/javascript">
							(function(){function $MPBR_load(){window.$MPBR_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;
							s.src = ("https:"==document.location.protocol?"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/":"http://mp-tools.mlstatic.com/buttons/")+"render.js";
							var x = document.getElementsByTagName("script")[0];x.parentNode.insertBefore(s, x);window.$MPBR_loaded = true;})();}
							window.$MPBR_loaded !== true ? (window.attachEvent ? window.attachEvent("onload", $MPBR_load) : window.addEventListener("load", $MPBR_load, false)) : null;})();
						</script>
						
						';
				break;
		endswitch;
		
		
		//show page 
		get_header();
		
		$html = '<div style="position: relative; margin: 20px 0;" >';
			$html .= '<div style="margin: 0 auto; width: 1080px; ">';
				$html .= '<h3>' . $title . '</h3>';
				
				$html .= '<p>' . $img_banner . '</p>';
				
				$html .= $button;
			$html .= '</div>';
		$html .= '</div>';
		
		echo $html;
		get_footer();
		
		exit;
		
	}

	function mp_retorno(){
		if (isset($_REQUEST['id']) && isset($_REQUEST['topic'])) {
			$id = $_REQUEST['id'];
		
			$client_id     = get_option('mercadopago_client_id');
			$client_secret = get_option('mercadopago_client_secret');
			
			$checkdata = New Shop($client_id,$client_secret);
			
			$dados = $checkdata->GetStatus($id);
			
			
			$order_id = $dados['collection']['external_reference'];
			$order_status = $dados['collection']['status'];
			$mp_id = $dados['collection']['order_id'];
			
			switch ($order_status) {
				case 'approved':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 3, notes = 'Payment Approved by Mercado Pago' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;
			case 'pending':
			case 'in_process':    
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 2, notes = 'Order received, wait for payment confirmation' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;    
			case 'reject':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'Payment declined by Mercado Pago, contact the client and ask to do a new order' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;    
			case 'refunded':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'Payment refunded by Mercado Pago' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;    
			case 'cancelled':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'Payment canceled by Mercado Pago' WHERE `sessionid`= '".$order_id."' LIMIT 1";             
				break;    
			case 'in_mediation':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'This orders has a mediation in Mercado Pago' WHERE `sessionid`= '".$order_id."' LIMIT 1";             
				break;
			default:
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 2, notes = 'Order received, wait for payment confirmation' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
			}
			
			$purchase_log = $wpdb->get_results($purchase_log_sql,ARRAY_A) ;
		}
	}

	function country(){
	
	
		if (get_option('mercadopago_country') == null || get_option('mercadopago_country') == ''){
			$mercadopago_country = 'MLA';        
		} else {
			$mercadopago_country = get_option('mercadopago_country');  
		}
		
		$sites = array(
				'MLA' =>'Argentina',
				'MLB' =>'Brazil',
				'MLC' =>'Chile',
				'MCO' =>'Colombia',
				'MLM' =>'Mexico',
				'MPE' =>'Peru',
				'MLV' =>'Venezuela'
		);
		
		$showsites= '<select name="mercadopago_country">';

		foreach ($sites as $site_id => $site_name):
			if($site_id == $mercadopago_country){
				$showsites .= '<option value="'.$site_id.'" selected="selected" id="'.$site_id.'">'.$site_name.'</option>'; 
			} else {
				$showsites .= '<option value="'.$site_id.'" id="'.$site_id.'">'.$site_name.'</option>';    
			}         
		endforeach;
		
		$showsites .= '</select>';
		return $showsites;

	}

	function currency(){

		if (get_option('mercadopago_country') == null || get_option('mercadopago_country') == ''){
			$mercadopago_currency = 'Select first one country, save and reload the page to show the currency';    
			return $mercadopago_currency;
		}else{	
			switch (get_option('mercadopago_country')) {
				case 'MLA': return $mercadopago_currency = 'ARS';
				case 'MLB': return $mercadopago_currency = 'BRL';
				case 'MLC': return $mercadopago_currency = 'CLP';
				case 'MCO': return $mercadopago_currency = 'COP';
				case 'MLM': return $mercadopago_currency = 'MXN';
				case 'MPE': return $mercadopago_currency = 'PEN';
				case 'MLV': return $mercadopago_currency = 'VEF';
				default: return '';
			}
		}
	}

	function category(){
		
		$category = get_option('mercadopago_category');
		$category = $category === false || is_null($category) ? "others" : $category;		
		
		//category marketplace
		$mp = new MPApi();
		$list_category = $mp->getCategories();
		$select_category = '<select name="mercadopago_category" id="category" style="max-width:600px;>';
		foreach($list_category as $category_arr):
		
			$selected = "";
			if($category_arr['id'] == $category):
				$selected = 'selected="selected"';
			endif;
			
			$select_category .= '<option value="' . $category_arr['id'] . '" id="type-checkout-' . $category_arr['description'] . '" ' . $selected . ' >' . $category_arr['description'] . '</option>';
		endforeach;
		
		$select_category .= "</select>";
		
		return $select_category;
	}

	function methods($country = null ){
	
		$activemethods = preg_split("/[\s,]+/",get_option('mercadopago_methods'));
		
		if($country != '' || $country != null){
			$mp = new MPApi();
			$methods = $mp->getPaymentMethods($country);
			
			$showmethods = '';
			foreach ($methods as $method):
				if($method['id'] != 'account_money'){
					if($activemethods != null && in_array($method['id'], $activemethods)){
						$showmethods .= '<input name="mercadopago_methods[]" type="checkbox" checked="yes" value="'.$method['id'].'">'.$method['name'].'<br />'; 
					} else {
						$showmethods .= '<input name="mercadopago_methods[]" type="checkbox" value="'.$method['id'].'"> '.$method['name'].'<br />';    
					}
				}
			endforeach;
			
			$showmethods.= '<p class="description">Select the payment methods you do not want to accept with Mercado Pago.</p>';

			$showmethods.= '<p class="description"><b>Note:</b> If you change the country, first <b>save and reload</b> the page, and after that do the selection.</p>';

			return $showmethods;
		
		} else {
			$showmethods = 'Select first one country, save and reload the page to show the methods';    
			return $showmethods;
		}
	
	}

	function installments(){
	
		if (get_option('mercadopago_limit_payments') == null || get_option('mercadopago_limit_payments') == ''){
			$mercadopago_limit_payments = 24;        
		} else {
			$mercadopago_limit_payments = get_option('mercadopago_limit_payments');  
		}
		
		$times = array('1','3','6','9','12','15','18','24','36');
		$showinstallment = '<select name="mercadopago_limit_payments">';
		
		foreach ($times as $installment):
			if($installment == $mercadopago_limit_payments){
				$showinstallment .= '<option value="'.$installment.'" selected="selected">'.$installment.'</option>'; 
			} else {
				$showinstallment .= '<option value="'.$installment.'">'.$installment .'</option>';    
			}         
		endforeach;
		
		$showinstallment .= '</select>';
		
		return $showinstallment;
	}
	
	function type_checkout(){
		
		$type_checkout = get_option('mercadopago_typecheckout');
		$type_checkout = $type_checkout === false || is_null($type_checkout) ? "Redirect" : $type_checkout;

		//Type Checkout
		$type_checkout_options = array(
			"Iframe",
			"Lightbox",
			"Redirect"
		);
		
		
		$select_type_checkout = '<select name="mercadopago_typecheckout" id="type_checkout">';

		foreach($type_checkout_options as $select_type):
		
			$selected = "";
			if($select_type == $type_checkout):
				$selected = 'selected="selected"';
			endif;
			
			$select_type_checkout .= '<option value="' . $select_type . '" id="type-checkout-' . $select_type . '" ' . $selected . ' >' . $select_type . '</option>';
		endforeach;
		$select_type_checkout .= "</select>";
		
		return $select_type_checkout;
	}

	function sandbox(){
		
		$sandbox = get_option('mercadopago_sandbox');
		$sandbox = $sandbox === false || is_null($sandbox) ? "inactive" : $sandbox;
		
		//sandbox
		$sandbox_options = array(
			array("value" => "active", "text" => "Active"),
			array("value" => "inactive", "text" => "Inactive")
		);
		
		$select_sandbox = '<select name="mercadopago_sandbox" id="sandbox">';
		foreach($sandbox_options as $op_sandbox):
		
			$selected = "";
			if($op_sandbox['value'] == $sandbox):
			$selected = 'selected="selected"';
			endif;
			
			$select_sandbox .= '<option value="' . $op_sandbox['value'] . '" id="sandbox-' . $op_sandbox['value'] . '" ' . $selected . '>' . $op_sandbox['text'] . '</option>';
		endforeach;
		
		$select_sandbox .= "</select>";
		
		return $select_sandbox;
	}

	function debugs(){
		
		if (get_option('mercadopago_debug') == null || get_option('mercadopago_debug') == ''){
			$mercadopago_debug = 'No';        
		} else {
			$mercadopago_debug = get_option('mercadopago_debug');  
		}
		
		$debugs = array('No','Yes');
		$showdebugs = '<select name="mercadopago_debug">';
		
		foreach ($debugs as  $debug ):
			if($debug == $mercadopago_debug){
				$showdebugs .= '<option value="'.$debug.'" selected="selected">'.$debug.'</option>'; 
			} else {
				$showdebugs .= '<option value="'.$debug.'">'.$debug .'</option>';    
			}
		endforeach;
		
		$showdebugs .= '</select>';

		return $showdebugs;
	}

	function auto_return(){
		
		$auto_return = get_option('mercadopago_auto_return');
		$auto_return = $auto_return === false || is_null($auto_return) ? "inactive" : $auto_return;
		
		$auto_return_options = array(
			array("value" => "active", "text" => "Active"),
			array("value" => "inactive", "text" => "Inactive")
		);
		
		$select_auto_return = '<select name="mercadopago_auto_return" id="auto_return">';
		foreach($auto_return_options as $op_auto_return):
		
			$selected = "";
			if($op_auto_return['value'] == $auto_return):
			$selected = 'selected="selected"';
			endif;
			
			$select_auto_return .= '<option value="' . $op_auto_return['value'] . '" id="auto_return-' . $op_auto_return['value'] . '" ' . $selected . '>' . $op_auto_return['text'] . '</option>';
		endforeach;
		
		$select_auto_return .= "</select>";
		
		return $select_auto_return;

	}


add_action('init', 'mp_retorno');

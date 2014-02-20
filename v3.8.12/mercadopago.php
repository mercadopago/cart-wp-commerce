<?php


/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
*  It is also available through the world-wide-web at this URL: *
*  http://opensource.org/licenses/osl-3.0.php * 
*  @category    Payment Gateway * @package    	MercadoPago 
*  @author      Andre Fuhrman (andrefuhrman@gmail.com) | Edited: Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
*  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
*/

$nzshpcrt_gateways[$num]['name'] = 'MercadoPago';
$nzshpcrt_gateways[$num]['internalname'] = 'mercado_pago';
$nzshpcrt_gateways[$num]['function'] = 'function_mercado_pago';
$nzshpcrt_gateways[$num]['form'] = 'form_mercado_pago';
$nzshpcrt_gateways[$num]['submit_function'] = 'submit_mercado_pago';
$nzshpcrt_gateways[$num]['payment_type'] = 'mp';
$nzshpcrt_gateways[$num]['display_name'] = 'MercadoPago';
$nzshpcrt_gateways[$num]['class_name'] = 'wpsc_merchant_mercadopago';

include_once "lib/mercadopago.php";
include_once "lib/MPApi.php";


	function form_mercado_pago(){
	
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
		$output ='<br /><tr><td>';
		
		$output.='Client Id</td>';
		$output.='<td><input name="mercadopago_client_id" type="text" value="'. get_option('mercadopago_client_id') .'"/></td></tr>';
		
		$output.='<tr><td>Client Secret</td>';
		$output.='<td><input name="mercadopago_client_secret" type="text" value="'. get_option('mercadopago_client_secret') .'"/></td></tr>';
		$output.='<tr><td></td><td><small>To get fields above, follow: 
		<a href="https://www.mercadopago.com/mla/herramientas/aplicaciones" target="_blank">Argentina</a> or
		<a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes" target="_blank">Brasil</a> or <a href="https://www.mercadopago.com/mlm/herramientas/aplicaciones" target="_blank">Mexico</a> or <a href="https://www.mercadopago.com/mlv/herramientas/aplicaciones" target="_blank">Venezuela</a> <br /><br /></small></td></tr>';
		
		
		$output.='<tr><td>Store Category</td>';
		$output.='<td>'. category() .'</td></tr>';
		
		$output.='<tr><td>Type Checkout</td>';
		$output.='<td>'. type_checkout() .'</td></tr>';
		
		$output.='<tr><td>Sandbox</td>';
		$output.='<td>'. sandbox() .'</td></tr>';
		
		
		$output.='<tr><td>Url Sucess Payment</td>';
		$output.='<td><input name="mercadopago_url_sucess" type="text" value="'. $url_sucess .'"/></td></tr>';
		
		$output.='<tr><td>Url Peding Payment</td>';
		$output.='<td><input name="mercadopago_url_pending" type="text" value="'. $url_pending .'"/></td></tr>';
		$output.='<tr><td></td><td><small>This is just the url where the custumer is redirect after his payment is done, you can set in both fields above any url of your site, but needs to be a <b>valid URL.</b>.<br /><br /> Please set your <b>instant payment notification</b> to receive your automatic order status changes at: 
		<a href="https://www.mercadopago.com/mla/herramientas/notificaciones" target="_blank">Argentina</a> or
		<a href="https://www.mercadopago.com/mlb/ferramentas/notificacoes" target="_blank">Brasil</a> or <a href="https://www.mercadopago.com/mlm/herramientas/notificaciones" target="_blank">Mexico</a> or <a href="https://www.mercadopago.com/mlv/herramientas/notificaciones" target="_blank">Venezuela</a><br />
		Set your url follwing this exemple: http://yourstore.com</b></small></td></tr>';
		
		$output.='<tr><td>Store Country</td>';
		$output.='<td>'. country() .'</td></tr>';
		
		$output.='<tr><td>Currency</td>';
		$output.='<td>'. currency() .'</td></tr>';
		$output.='<tr><td></td><td><small>Select Real to Brasil, or Pesos or Dollar to Argentina</small></td></tr>';
		
		$output.='<tr><td>Excluded methods</td>';
		$output.='<td>'. methods(get_option('mercadopago_country')) .'</td></tr>';
		$output.='<tr><td></td><td><small>SELECT only the methods that you <b>DO NOT</b>want to accept by MercadoPago<br />
		<br /><b>Attention: </b> Payment methods depends on what country your account was created, if you change the country,<b> save the module first</b> and just after that select the Exclude Payment Methods!
		<br /><br /><b>DO NOT</b> exclude All methods<br /><br /></small></td></tr>';
		
		$output.='<tr><td>Limit Payments</td>';
		$output.='<td>'. instalments() .'</td></tr>';
		$output.='<tr><td></td><td><small>This option allow you to limit the maximum number of instalments of MercadoPago</small></td></tr>';
		
		$output.='<tr><td>Debug mode</td>';
		$output.='<td>'. debugs() .'</td></tr>';
		$output.='<tr><td></td><td><small>Turn debug mode on to see erro log with your getting error on checkout</small></td></tr>';
		return $output;
	
	}



	function submit_mercado_pago(){
		
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
		
		if($_POST['mercadopago_currency'] != null) {
			update_option('mercadopago_currency',trim($_POST['mercadopago_currency']));
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


	function function_mercado_pago($seperator, $sessionid){
		
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
		$data['currency'] = get_option('mercadopago_currency');
		
		$sandbox = get_option('mercadopago_sandbox') == "active" ? true:false;
		$type_checkout = get_option('mercadopago_typecheckout');
		$category = get_option('mercadopago_category');
	
	
		
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
				"unit_price" =>  $data['total'] , //decimal
				"currency_id" => $data['currency'],// string Argentina: ARS (peso argentino) � USD (D�lar estadounidense); Brasil: BRL (Real).,
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
			$title = 'Continue pagando com MercadoPago';
		else:
			$title = 'Continue pagando con MercadoPago';    
		endif;
		
		//add image
		$img_banner = "";
		if(get_option('mercadopago_country') == 'MLB'):
			$img_banner = '<img src="http://img.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg" alt="MercadoPago" title="MercadoPago" />';
		elseif (get_option('mercadopago_country') == 'MLM'): 
			$img_banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X108.JPG" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="108"/>';
		elseif (get_option('mercadopago_country') == 'MLV'): 
			$img_banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/ar/medios/468X60.jpg" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="60"/>';
		else:
			$img_banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/ar/medios/468X60.jpg" alt="MercadoPago" title="MercadoPago" />';    
		endif;
		
		
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
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 3, notes = 'Payment Approved by MercadoPago' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;
			case 'pending':
			case 'in_process':    
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 2, notes = 'Order received, wait for payment confirmation' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;    
			case 'reject':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'Payment declined by MercadoPago, contact the client and ask to do a new order' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;    
			case 'refunded':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'Payment refunded by MercadoPago' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
				break;    
			case 'cancelled':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'Payment canceled by MercadoPago' WHERE `sessionid`= '".$order_id."' LIMIT 1";             
				break;    
			case 'in_metiation':
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 6, notes = 'This orders has a mediation in MercadoPago' WHERE `sessionid`= '".$order_id."' LIMIT 1";             
				break;
			default:
				$purchase_log_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET transactid = ".$mp_id.", processed = 2, notes = 'Order received, wait for payment confirmation' WHERE `sessionid`= '".$order_id."' LIMIT 1";           
			}
			
			$purchase_log = $wpdb->get_results($purchase_log_sql,ARRAY_A) ;
		}
	}


	function country(){
	
		$mp = new MPApi();
		$countries = $mp->getCountries();    
		
		$showcountries  = '<select name="mercadopago_country">';
		foreach ($countries as $country) {
			if ($country['id'] == get_option('mercadopago_country')) { 
				$showcountries  .=  '<option value="'. $country["id"].'" selected="selected" id="'. $country["id"] .'">'.$country["name"].'</option>';
			} else { 
				$showcountries  .=  '<option value="'. $country['id'] .'" id="'.$country["id"].'">'.$country["name"] .'</option>';
			} 
		}
	
		$showcountries  .=  '</select>';
		return $showcountries;
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
			
			return $showmethods;
		
		} else {
			$showmethods = 'Select first one country, save and reload the page to show the methods';    
			return $showmethods;
		}
	
	}

	function instalments(){
	
		if (get_option('mercadopago_limit_payments') == null || get_option('mercadopago_limit_payments') == ''){
			$mercadopago_limit_payments = 18;        
		} else {
			$mercadopago_limit_payments = get_option('mercadopago_limit_payments');  
		}
		
		$times = array('1','3','6','9','12','15','18','24');
		$showinstalmant = '<select name="mercadopago_limit_payments">';
		
		foreach ($times as $instalment):
			if($instalment == $mercadopago_limit_payments){
				$showinstalmant .= '<option value="'.$instalment.'" selected="selected">'.$instalment.'</option>'; 
			} else {
				$showinstalmant .= '<option value="'.$instalment.'">'.$instalment .'</option>';    
			}         
		endforeach;
		
		$showinstalmant .= '</select>';
		
		return $showinstalmant;
	}

	function currency(){
	
	
		if (get_option('mercadopago_currency') == null || get_option('mercadopago_currency') == ''){
			$mercadopago_currency = 'BRL';        
		} else {
			$mercadopago_currency = get_option('mercadopago_currency');  
		}
		
		$currencys = array(
				'BRL' =>'Real',
				'USD' =>'Dollar',
				'ARS' =>'Pesos Argentinos',
				'MXN' =>'Peso mexicano',
				'VEF' =>'Bolivar fuerte'
		);
		
		$showcurrency = '<select name="mercadopago_currency">';

		foreach ($currencys as  $currency => $key):
			if($currency == $mercadopago_currency){
				$showcurrency .= '<option value="'.$currency.'" selected="selected">'.$key.'</option>'; 
			} else {
				$showcurrency .= '<option value="'.$currency.'">'.$key .'</option>';    
			}         
		endforeach;
		
		$showcurrency .= '</select>';
		return $showcurrency;

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
	
	
	function category(){
		
		$category = get_option('mercadopago_category');
		$category = $category === false || is_null($category) ? "others" : $category;		
		
		//category marketplace
		$mp = new MPApi();
		$list_category = $mp->getCategories();
		$select_category = '<select name="mercadopago_category" id="category">';
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
	
	function type_checkout(){
		
		$type_checkout = get_option('mercadopago_typecheckout');
		$type_checkout = $type_checkout === false || is_null($type_checkout) ? "Lightbox" : $type_checkout;

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
		$sandbox = $sandbox === false || is_null($sandbox) ? "deactivate" : $sandbox;
		
		//sandbox
		$sandbox_options = array(
			array("value" => "active", "text" => "Active"),
			array("value" => "deactivate", "text" => "Deactivate")
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

add_action('init', 'mp_retorno');

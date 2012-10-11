<?php


/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
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







function form_mercado_pago(){
    
            if(get_option('mercadopago_url_sucess') != '' || get_option('mercadopago_url_sucess') != ''){ $url_sucess = get_option('mercadopago_url_sucess');} else {$url_sucess = 'http://www.mercadopago.com' ;} ;
            if(get_option('mercadopago_url_pending') != '' || get_option('mercadopago_url_pending') != ''){ $url_pending = get_option('mercadopago_url_pending');} else {$url_sucess = 'http://www.mercadopago.com' ;} ;

            $output ='<br /><tr><td>';
            $output.='Client Id</td>';
            $output.='<td><input name="mercadopago_client_id" type="text" value="'. get_option('mercadopago_client_id') .'"/></td></tr>';
            $output.='<tr><td>Client Secret</td>';
            $output.='<td><input name="mercadopago_client_secret" type="text" value="'. get_option('mercadopago_client_secret') .'"/></td></tr>';
            $output.='<tr><td></td><td><small>To get fields above, follow: 
                  <a href="https://www.mercadopago.com/mla/herramientas/aplicaciones" target="_blank">Argentina</a> or
                  <a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes" target="_blank">Brasil</a><br /><br /></small></td></tr>';
            $output.='<tr><td>Url Sucess Payment</td>';
            $output.='<td><input name="mercadopago_url_sucess" type="text" value="'. $url_sucess .'"/></td></tr>';
            $output.='<tr><td>Url Sucess Payment</td>';
            $output.='<td><input name="mercadopago_url_pending" type="text" value="'. $url_pending .'"/></td></tr>';
            $output.='<tr><td></td><td><small>This is just the url where the custumer is redirect after his payment is done, you can set in both fields above any url of your site, but needs to be a <b>valid URL.</b>.<br /><br /> Please set your <b>instant payment notification</b> to receive your automatic order status changes at: 
                  <a href="https://www.mercadopago.com/mla/herramientas/notificaciones" target="_blank">Argentina</a> or
                  <a href="https://www.mercadopago.com/mlb/ferramentas/notificacoes" target="_blank">Brasil</a><br />
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
            $output.='<td>'. instalments().'</td></tr>';
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
            
            
            if($_POST['mercadopago_debug'] != null) {
            update_option('mercadopago_debug',trim($_POST['mercadopago_debug']));
            }
            

            if($_POST['mercadopago_client_secret'] != null) {
            update_option('mercadopago_client_secret',trim($_POST['mercadopago_client_secret']));
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
                      
            if($_POST['mercadopago_limit'] != null) {
            update_option('mercadopago_limit',trim($_POST['mercadopago_limit']));
            }
            if($_POST['mercadopago_curcode'] != null) {
            update_option('mercadopago_curcode',trim($_POST['mercadopago_curcode']));
            }
            if ($_POST['mercadopago_methods'] != null)
            {
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
    $data['client_id']          = get_option('mercadopago_client_id');
    $data['client_secret'] 	= get_option('mercadopago_client_secret');
    $data['sucess']             = get_option('mercadopago_url_sucess');
    $data['pending']            = get_option('mercadopago_url_pending');
    $data['currency']           = get_option('mercadopago_currency');
    // order info
    $data['total']              = number_format($wpsc_cart->total_price,2);
    
    
    //client 
    foreach((array)$userinfo as $key => $value){

        if(($value['unique_name'] == 'billingfirstname')
        && $value['value'] != ''){
        $data['firstname'] = $value['value'];    
        }

        if(($value['unique_name']=='billinglastname')
        && $value['value'] != ''){
        $data['lastname']	= $value['value'];
        }
        
        if(($value['unique_name']=='billingemail')
        && $value['value'] != ''){
        $data['email']	= $value['value'];
        }
    }
    
    
    
  
    
    
 //   var_dump($wpsc_cart);die;
    
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
        }} else {
         $data['image0'] = 'https://www.mercadopago.com/org-img/MP3/home/logomp3.gif';
        }
    }
    
    // exclude methods
    
    

    
    
   
    // array to create preference key
       $dados = array(
       "external_reference" => $sessionid ,// seu codigo de referencia, i.e. Numero do pedido da sua loja 
       "currency" => $data['currency'] ,// string Argentina: ARS (peso argentino) ó USD (Dólar estadounidense); Brasil: BRL (Real).
       "title" => $data['PROD_NAME0'],  //string
       "description" => $data['PROD_QTY0'], // string
       "quantity" => $data['PROD_QTY0'],// int 
       "image" => $data['image0'],  // Imagem, string
       "amount" => $data['total'] , //decimal
       "payment_firstname" => $data['firstname'],// string
       "payment_lastname" => $data['lastname'],// string
       "email" => $data['email'],// string
       "pending" =>  $data['pending'], // string
       "approved" => $data['sucess']  // string
       );
   
    
    $exclude = get_option('mercadopago_methods');  // string
    
    
    
    $pagamento = New Shop($data['client_id'],$data['client_secret']);
    
    $botton = $pagamento->GetCheckout($dados,$exclude);

    
    get_header();
        echo  '<style type="text/css">
              #branding{z-index:100;}
              </style>';

    
    
        $html = '<br /><br /><div style="float:left;widht:50%;>';
        if(get_option('mercadopago_country') == 'MLB'):
        $html .= '<div style="position:relative;float:left;"/><h3 style="margin: 10px;">Continue pagando com MercadoPago</h3></div><div style="position:relative;float:right;" />';
        else:
        $html .= '<div style="position:relative;float:left;"/><h3 style="margin: 10px;">Continue pagando con MercadoPago</h3></div><div style="position:relative;float:right;" />';    
        endif;
        $html  .= $botton . '</div>';
  
        if(get_option('mercadopago_country') == 'MLB'):
        $html .= '<div><img src="http://img.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg" alt="MercadoPago" title="MercadoPago" /></div>';
        else:
        $html .= '<div><img src="http://imgmp.mlstatic.com/org-img/banners/ar/medios/468X60.jpg" alt="MercadoPago" title="MercadoPago" /></div>';    
        endif;
        $html .= '</div>';

        $html .= '  <script type="text/javascript">';
        $html .= '     function fireEvent(obj,evt){';
        $html .= '         var fireOnThis = obj;';
        $html .= '         if( document.createEvent ) {';
        $html .= '            var evObj = document.createEvent(\'MouseEvents\');';
        $html .= '            evObj.initEvent( evt, true, false );';
        $html .= '            fireOnThis.dispatchEvent( evObj );';
        $html .= '         } else if( document.createEventObject ) {';
        $html .= '            var evObj = document.createEventObject();';
        $html .= '            fireOnThis.fireEvent( \'on\' + evt, evObj );';
        $html .= '         }';
        $html .= '     }';
        $html .= '     fireEvent(document.getElementById("btnPagar"), \'click\')';
        $html .= '  </script><br /><br /><div>';
        
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

class Basic {

    //put your code here
    
     
        public     $accesstoken;
        protected  $client_id;
        protected  $client_secret;
        public     $error;
        protected  $date;
        protected  $expired;


        
       ///// function just to debug the code if is needed
        
      
        static function debug($error){
               
              $debug = get_option('mercadopago_debug');
              if($debug=='Yes'){
               echo ('<pre>');
               print_r($error);
               echo ('</pre>');
              } else {
              echo '<span style="background-color:red;width:100%;">MercadoPago fail to connect, if you are the store owner turn debug mode on to see the erro log</span>';    
              }
              
       } 
       
         ///// function to post the datas
         public function DoPost($fields,$url,$heads,$codeexpect,$type,$method){
                    
                    // buld the post data follwing the api needs
                 if($type == 'json'){
                 $posts = json_encode($fields);
                    } else if ($type == 'none') {
                    $posts = $fields;
                    } else {
                    $posts = http_build_query($fields);    
                    }
                    
                   
                    
                  
                    // change the curl method follwing the api needs
                    switch ($method):
                    case 'get':
                    $options = array(
                               CURLOPT_RETURNTRANSFER => '1',
                               CURLOPT_HTTPHEADER => $heads,
                               CURLOPT_SSL_VERIFYPEER => 'false',
                               CURLOPT_URL => $url,
                               CURLOPT_POSTFIELDS => $posts ,
                               CURLOPT_CUSTOMREQUEST => "GET"
                            );
                    break;
                    case 'put':
                      $options = array(
                                CURLOPT_RETURNTRANSFER => 1,
                                CURLOPT_HTTPHEADER => $heads,
                                CURLOPT_SSL_VERIFYPEER => 'false',
                                CURLOPT_URL => $url,
                                CURLOPT_POSTFIELDS => $posts,    
                                CURLOPT_CUSTOMREQUEST => "PUT",
                                CURLOPT_HEADER => 1
                             );  
                    break;
                    case 'post':
                         $options = array(
                                CURLOPT_RETURNTRANSFER => '1',
                                CURLOPT_HTTPHEADER => $heads,
                                CURLOPT_SSL_VERIFYPEER => 'false',
                                CURLOPT_URL => $url,
                                CURLOPT_POSTFIELDS => $posts,    
                                CURLOPT_CUSTOMREQUEST => "POST",
                             ); 
                    break;
                    case 'delete':
                        $options = array(
                                CURLOPT_RETURNTRANSFER => '1',
                                CURLOPT_HTTPHEADER => $heads,
                                CURLOPT_SSL_VERIFYPEER => 'false',
                                CURLOPT_URL => $url,
                                CURLOPT_POSTFIELDS => $posts,    
                                CURLOPT_CUSTOMREQUEST => "DELETE",
                             ); 
                        
                    break;      
                    default:
                            $options = array(
                               CURLOPT_RETURNTRANSFER => '1',
                               CURLOPT_HTTPHEADER => $heads,
                               CURLOPT_SSL_VERIFYPEER => 'false',
                               CURLOPT_URL => $url,
                               CURLOPT_POSTFIELDS => $posts ,
                               CURLOPT_CUSTOMREQUEST => "GET"
                            );
                    break;
                    endswitch;
  
                // do a curl call
                $call = curl_init();
                curl_setopt_array($call,$options);
                // execute the curl call
                $dados = curl_exec($call);
                // get the curl statys
                $status = curl_getinfo($call);
                // close the call
                curl_close($call);
                // check to see if the call was succesful 
                if ($status['http_code'] != $codeexpect){
                $this->debug($dados);
              //  $this->debug($status);
                return false;
                } else {
               // change the json retur to a php array and return it
                return json_decode($dados,true);        
                } 
        }
        
        public function getAccessToken(){
            
            $data = getdate();
            $time = $data[0];
             
            
            // verifica se já existe accesstoken valido, caso exista, retorna o accesstoken
            if(isset($this->accesstoken) && isset($this->date)){          
                $timedifference = $time - $this->date;
                if($timedifference < $this->expired){
                return $this->accesstoken;
                }
           }
            // get the clients variables
                $post = array(
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type' => 'client_credentials'
                 );
                // set the header
                $header = array('Accept: application/json','Content-Type: application/x-www-form-urlencoded');
                // set the url to get the access token
                $url = 'https://api.mercadolibre.com/oauth/token';
                // call the post function. expection 200 as return
                $dados = $this->DoPost($post,$url,$header,'200','post','post');
                // set the access token
                $this->accesstoken = $dados['access_token'];
                 // guarta o hoarario, prazo de expiraç?o e returna o access token
                $this->date = $time;
                $this->expired = $dados['expires_in'];
                return $dados['access_token'];
       }
     
       
    

}

Class Mpublic extends Basic {
    
     public function getCountries() {
            
        $url = 'https://api.mercadolibre.com/sites/';
        $header = array('Accept: application/json');
        $countries = $this->DoPost(null,$url,$header,'200','none','get');
	return $countries;
    }  
       
     public function GetMethods($country_id){
       
        $url = "https://api.mercadolibre.com/sites/" . $country_id .  "/payment_methods";
        $header = array('Accept: application/json');  
        $methods = $this->DoPost(null,$url,$header,'200','none','get');
        return $methods;
    
     }
}

Class Shop extends Basic {
        
       // do the client authentication
    public function __construct($client,$secret){
                   $this->client_id = $client;
                   $this->client_secret = $secret; 
       }
       
       
       
     public function GetMethods($country_id) {
       
        $url = "https://api.mercadolibre.com/sites/" . $country_id .  "/payment_methods";
        $header = null;   
        $methods = $this->DoPost(null,$url,$header,'200','none','get');
        return $methods;
    
        }

      // Generate the botton
      public function GetCheckout($data,$excludes){
                
            if($excludes != ''){
                
                 $methods_excludes = preg_split("/[\s,]+/", $excludes); 
                 foreach ($methods_excludes as $exclude ){
                 $excludemethods[] = array('id' => $exclude);     
                 }
                
                 
                $opt = array(
                "external_reference" => $data['external_reference'],
                "items" => array(
                    array ("id" => $data['external_reference'], // updated
                    "title" => $data['title'],
                    "description" => $data['quantity'] . ' x ' . $data['title'],
                    "quantity" => 1,
                    "unit_price" => round($data['amount'], 2),
                    "currency_id" => $data['currency'],
                    "picture_url"=> $data['image'],
                    )),
                    "payer" => array(
                     "name" => $data['payment_firstname'],
                     "surname" => $data['payment_lastname'],
                     "email" => $data['email']
                    ),
                   "back_urls" => array(
                   "pending" => $data['pending'],
                   "success" => $data['approved']
                   ),           
                   "payment_methods" => array(
                   "excluded_payment_methods" => $excludemethods
                   )
                );
            }else{
                $opt = array(
                "external_reference" => $data['external_reference'],
                "items" => array(
                    array ("id" => $data['external_reference'], // updated
                    "title" => $data['title'],
                    "description" => $data['quantity'] . ' x ' . $data['title'],
                    "quantity" => 1,
                    "unit_price" => round($data['amount'], 2),
                    "currency_id" => $data['currency'],
                    "picture_url"=> $data['image'],
                    )),
                    "payer" => array(
                     "name" => $data['payment_firstname'],
                     "surname" => $data['payment_lastname'],
                     "email" => $data['email']
                    ),
                   "back_urls" => array(
                   "pending" => $data['pending'],
                   "success" => $data['approved']
                   ),  
                );
                
            }
            
             

            $this->getAccessToken(); 
            $url = 'https://api.mercadolibre.com/checkout/preferences?access_token=' . $this->accesstoken;
            $header = array('Content-Type:application/json','Accept: application/json');
            $dados = $this->DoPost($opt,$url,$header,'201','json','post');
            $link = $dados['init_point'];
            $bt = '<a href="'.$link.'" name="MP-payButton" class="blue-l-rn-ar" id="btnPagar">Comprar</a>
            <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>';
            return $bt;
      }
      
           

      public function GetStatus($id){
          
            $this->getAccessToken(); 
            $url = "https://api.mercadolibre.com/collections/notifications/" . $id . "?access_token=" . $this->accesstoken;
            $header = array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded');
            $retorno = $this->DoPost($opt=null,$url,$header,'200','none','post');
            return $retorno;
                   
      }
      

}

         function country(){

         $mp = new Mpublic();
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
                      $mp = new Mpublic();
                      $methods = $mp->GetMethods($country);
                      $showmethods = '';
                      foreach ($methods as $method):
                      if($method['id'] != 'account_money'){
                      if($activemethods != null && in_array($method['id'], $activemethods)){
                      $showmethods .= '<input name="mercadopago_methods[]" type="checkbox" checked="yes" value="'.$method['id'].'">'.$method['name'].'<br />'; 
                      } else {
                      $showmethods .= '<input name="mercadopago_methods[]" type="checkbox" value="'.$method['id'].'"> '.$method['name'].'<br />';    
                      }}
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
            $times = array('3','6','9','12','15','18');
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
            $currencys = array('BRL' =>'Real','USD'=>'Dollar','ARS'=>'Pesos');
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

add_action('init', 'mp_retorno');
        

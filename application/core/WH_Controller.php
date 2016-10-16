<?php
/**
 * WH_Controller
 *
 * @package        pohcecommerce
 * @author        Williams Helde
 * @copyright    Copyright (c) 2016, Williams Helde
 * @link        http://www.williams-helde.com
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class WH_Controller
 *
 * Extension of default CI_Controller
 *
 * Adds:
 * - sitewide template variables
 * - consistent view rendering
 * - vehicle for including js and css files dependent on page
 * - default exception handling
 *
 * @package pohcecommerce
 * @author Williams Helde
 * @copyright Copyright (c) 2016, Williams Helde
 * @link http://www.williams-helde.com
 */
class WH_Controller extends CI_Controller{
	//Page info
	protected $data 								= array();
	protected $pageName 							= false;
	protected $notifications					= array('error' => array(), 'success' => array(), 'info' => array(), 'warning' => array());

	//Page contents
	protected $javascript 							= array();
	protected $css 									= array();
	protected $fonts 								= array();

	//Page Meta
	protected $title 								= false;
	protected $description 							= false;
	protected $keywords 							= false;
	protected $author 								= false;
	protected $country_abbr	 						= false;
	protected $country	 							= false;
	protected $language_abbr 						= false;
	protected $language	 							= false;

	/**
	 *
	 */
	public function __construct(){
		parent::__construct();

		$this->_init_debug_vars();
		$this->_init_notifications();

		$this->benchmark->mark('wh_controller_start');
		$this->debug_out('----- wh controller constructing...', get_class($this));

		$this->load->model("Products_model");
		$this->load->model("Discus_cart_model");

		$this->load->helper('form_helper');
		$this->load->library('form_validation');

		// start :: let's load all language files (cuz we're lazy and loading in 1 place is better than in 30)
		$arr_lang_files	= array(
												'errors',
												'global',
												'labels',
												'merchant',
												'meta',
												'order',
												'pagetitles',
												'registration'
											);
		foreach($arr_lang_files as $alf) {
			$this->lang->load($alf . '_lang', $this->config->item('language'));
		}
		// end :: let's load all language files

		// start :: get global_ language keys
		$arr_global_values = array_filter_key(
														$this->lang->language,
														function($key){
    														return strpos($key,'global_') === 0;
														}
													);
		// end :: get global_ language keys

		// start :: set global_ language items to data array
		foreach($arr_global_values as $k=>$v) {
			$this->data[$k] = $this->lang->line($k);
		}
		// end :: set global_ language items to data array


		$this->load->library('auth');
		$this->auth->set_current_user();

		$this->data['site_title'] 					= $this->lang->line('site_title');
		$this->title 								= "";
		$this->description 							= $this->lang->line('site_description');
		$this->keywords 							= $this->lang->line('site_keywords');
		$this->author 								= $this->lang->line('site_author');
		$this->country_abbr							= $this->config->item('site_country_abbr');
		$this->language_abbr						= $this->config->item('site_language_abbr');
		$this->language								= $this->config->item('language');
		$this->country								= $this->config->item('country');
		$this->endpoints 							= $this->config->item('endpoints');

		$this->data["tracking_ga_account"] 			= $this->config->item('tracking_ga_account');
		$this->data['path'] 						= $this->uri->uri_string();
		$this->data['locales'] 						= $this->config->item('site_locales');

		$this->data['locale'] 						= get_locale();
		$this->data['css_version'] 					= $this->config->item('site_css_version');

		$this->data['customer_info'] 				= $this->current_user != null ? $this->current_user->to_array() : array();

		$isloggedin									= (isset($this->session) && trim($this->session->userdata('company_skey')) !== '') ? true : false;
		$this->data['isloggedin']					= $isloggedin;
		$userinfo									= (isset($this->session) && trim($this->session->userdata('company_skey')) !== '') ? $this->ci->current_user->CustomerRows->CustomerRow : null;

		$onlinebillpaylink 							= $this->showRMS($isloggedin,$this->current_user->cust_no);
		$this->data['onlinebillpaylink']			= $onlinebillpaylink;

		$this->data['userfullname']					= (is_array($userinfo)) ? $userinfo->FirstName . ' ' . $userinfo->LastName : ' [ no name returned ]';

		// let's see if we're logged in and need to build a menu
		$menu 										= ($isloggedin) ? $this->build_menu() : array();
		$this->data['menu']							= $menu;

		$userdata 									= $this->session->all_userdata();

		$this->pageName 							= $this->uri->segment(2);

		set_exception_handler(array($this,'catch_exceptions'));

		date_default_timezone_set($this->config->item('site_timezone'));

		$this->include_js_raw('login.tpl.js.php');
	}

	/**
	 * Get cart for current user, create a URL link for each product item, and calculate the sub total
	 *
	 * @return array
	 */
	public function build_cart(){
		if(isset($this->current_user)) {
			$this->company_skey     					= $this->current_user->company_skey;
		}

		$this_cart 										= $this->get_shopping_cart($this->company_skey);
		$this->debug_out('WH_Controller::build_cart -- render time cart',$this_cart);

		$products 										= $this->Products_model->buildLink($this_cart);
		$this->debug_out('WH_Controller::build_cart -- after getting URL to products',$products);

		$tmpproducts									= array();
		$tmpcartsubtotal								= 0;

		foreach($products as $indieprod) {
			if ( isset( $indieprod['Quantity'] ) && isset( $indieprod['PartPrice'] ) ) {
				$indieprod['SubTotal'] 					= $indieprod['Quantity'] * $indieprod['PartPrice'];
				$tmpcartsubtotal 					   += $indieprod['SubTotal'];
			}
			$tmpproducts[] 								= $indieprod;
		}

		$this->debug_out('WH_Controller::build_cart -- after calculating subtotal',$tmpproducts);
		$products 										= $tmpproducts;
		$presavingssubtotal								= $tmpcartsubtotal;
		if($this->session->userdata('promoSavings')) {
			$tmpcartsubtotal -= $this->session->userdata('promoSavings');
		}

		$cart_count 									= ($this->data['cart_count'] > 0) ? $this->data['cart_count'] : 0;

		if(isset($products[0]) || isset($products[1])) {
			foreach ($products as $p) {
				$cart_count += $p['Quantity'];
			}
		} else {
			$cart_count += $products['Quantity'];
		}
		$this->products 								= $products;

		$this->session->set_userdata('showcart',$products);
		$this->data['shopping_cart']					= $products;
		$this->data['shopping_cart_subtotal'] 			= $presavingssubtotal;
		$this->data['cart_count'] 						= $cart_count;

		return $this->data;
	}

	/**
	 * Build the navigation menu
	 *
	 * The data-driven nav menu is stored serialized and zipped in the session. To build it
	 * there are a series of web service calls that need to be made in sequence.
	 *
	 * @return array|mixed
	 */
	public function build_menu(){
		$zipped_session_menu = $this->session->userdata('pre_built_menu');

		if(!empty($zipped_session_menu)) {
			$encoded = gzuncompress($zipped_session_menu);
			$serialized = base64_decode($encoded);
			$menu = unserialize($serialized);
			$this->debug_out('WH_Controller::build_menu -- got serailized menu', $serialized);
		} else {
			$menu 											= array();
			$tmpmenu 										= array();
			$sitecategories 								= $this->Products_model->getSiteCategories();
			if($sitecategories['rows']['row'] !== 'No Results Returned' || count($sitecategories['rows']['row']) > 0):
				for($si=0;$si<count($sitecategories['rows']['row']);$si++):
					$sc 									= $sitecategories['rows']['row'][$si];
					$sitecategorychildren 					= $this->Products_model->getSiteCategoryChildren($sc['CategoryKey']);
					if($sitecategorychildren['rows']['row'] !== 'No Results Returned' && count($sitecategorychildren['rows']['row']) > 0):
						$tmpmenu[$si] 						= array(
							'CategoryDisplayName' 	=> $sc['CategoryDisplayName'],
							'CategoryKey'			=> $sc['CategoryKey'],
							'CategorySlug'			=> strtolower(str_replace(' ','-',$sc['CategoryDisplayName']))
						);
						if(isset($sitecategorychildren['rows']['row']['CategoryKey'])):
							$scc 							= $sitecategorychildren['rows']['row'];

							$tmpmenu[$si]['SubCategories'][] 	= array(
								'CategoryDisplayName' 	=> $scc['CategoryDisplayName'],
								'CategoryKey'			=> $scc['CategoryKey'],
								'CategorySlug'			=> strtolower(
									str_replace(
										' ',
										'-',
										str_replace(
											'  ',
											' ',
											str_replace(
												str_split('\\/:*?"<>|&'),
												'',
												$scc['CategoryDisplayName']
											)
										)
									)
								)
							);

						else:
							foreach($sitecategorychildren['rows']['row'] as $scc):

								$tmpmenu[$si]['SubCategories'][]= array(
									'CategoryDisplayName' 	=> $scc['CategoryDisplayName'],
									'CategoryKey'			=> $scc['CategoryKey'],
									'CategorySlug'			=> strtolower(str_replace(' ','-',$scc['CategoryDisplayName']))
								);

							endforeach;
						endif;
					endif;
				endfor;
			endif;

			$menu = $tmpmenu;
			$serialized = serialize($menu);
			$encoded = base64_encode($serialized);
			$zipped = gzcompress($encoded);

			$this->debug_out('WH_Controller::build_menu -- saving serailized menu', $serialized);
			$this->session->set_userdata('pre_built_menu', $zipped);
		}

		return $menu;
	}

	public function get_web_order_number($company_skey){
		$order_number = null;

		$cart = $this->get_shopping_cart( $company_skey );

		if ( is_array( $cart ) && isset( $cart[0]['WebOrderNbr'] )) {
			$order_number = $cart[0]['WebOrderNbr'];
		}

		return $order_number;
	}

	public function get_shopping_cart($company_skey){
		$result = $this->Discus_cart_model->GetShoppingCart($company_skey);

		$this->debug_out('WH_Controller::get_shopping_cart result', $result);

		return $result;
	}

	/**
	 * Catch all uncaught exceptions
	 */
	public function catch_exceptions($exception){
		$debug_exception_message = '';
		if($this->config->item('is_debug')) {
			$debug_exception_message = '<br /><br />'. $exception->getMessage();
		}
		$this->notify_user($this->lang->line('error_unexpected_exception_messsage') . $debug_exception_message, 'error');
		$this->load->library('Emailer');

		//$emailer_result = $this->emailer->notify_error_unexpected_exception($exception);

		safe_redirect(locale_url());
	}

	/**
	 * Called by safe_redirect() prior to redirecting
	 *
	 * Chance to clean up before redirecting to another page
	 */
	public function pre_redirect_handler() {
		$this->flashstore_debug_vars();
		$this->flashstore_notifications();
	}

	/**
	 * Handle a call from safe_exit (in utilities helper)
	 *
	 * Chance to do something during debug before exit
	 */
	public function pre_exit() {
		echo "<pre>". print_r($this->debug_vars, true) ."</pre>";
	}

	/**
	 * Store the current debug vars in flashdata
	 */
	public function flashstore_debug_vars() {
		if(!empty($this->debug_vars)) {
			$serialized = serialize($this->debug_vars);
			$encoded = base64_encode($serialized);
			$zipped = gzcompress($encoded);
			$this->session->set_flashdata('debug_vars', $zipped);
		}
	}

	/**
	 * Store the current notification vars in flashdata
	 */
	public function flashstore_notifications() {
		if(!empty($this->notifications)) {
			$serialized = serialize($this->notifications);
			$encoded = base64_encode($serialized);
			$zipped = gzcompress($encoded);
			$this->session->set_flashdata('notifications', $zipped);
		}
	}

	/**
	 * Looks for any debug vars posted to flash data (e.g., from a redirect)
	 */
	protected function _init_debug_vars() {
		$zipped = $this->session->flashdata('debug_vars');

		if(!empty($zipped)) {
			$encoded = gzuncompress($zipped);
			$serialized = base64_decode($encoded);
			$debug_vars = unserialize($serialized);

			foreach($debug_vars as $dv) {
				$this->debug_out('PRE REDIRECT - '. $dv['label'], $dv['var'], $dv['file'], $dv['function'],$dv['line'],$dv['time'], $dv['time_of_day']);
			}
		}
	}

	/**
	 * Looks for any notifications posted to flash data (e.g., from a redirect)
	 */
	protected function _init_notifications() {
		$zipped = $this->session->flashdata('notifications');

		if(!empty($zipped)) {
			$encoded = gzuncompress($zipped);
			$serialized = base64_decode($encoded);
			$this->notifications = unserialize($serialized);
		}
	}

	/**
	 * @param $message
	 * @param string $type
	 */
	public function notify_user($message, $type = 'info'){
		$this->notifications[$type][] = $message;
	}

	/**
	 * @param $path_to_file
	 */
	public function include_js($path_to_file){
		$this->include_js_body($path_to_file);
	}

	/**
	 * Include a JS file at the bottom of the HTML body
	 *
	 * @param $path_to_file
	 */
	public function include_js_body($path_to_file){
		$this->javascript['body'][] 				= $path_to_file;
	}

	/**
	 * Include a JS file in the HTML header
	 *
	 * @param $path_to_file
	 */
	public function include_js_head($path_to_file){
		$this->javascript['head'][] 				= $path_to_file;
	}

	/**
	 * Include js files that can be templated with PHP and inserted as raw js at the bottom of the HTML
	 *
	 * To include raw javascript templated with PHP vars at the foot of the HTML, include a tpl.js.php file
	 * from the views/js folder in your controller function. This keep the page specific javascript out of the
	 * skeleton template.
	 *
	 * e.g., in your controller:
	 * $this->include_js_raw('myjavascript.tpl.js.php');
	 *
	 * This will get rendered upon by the _render function and inserted before the end body tag (i.e., it gets executed after
	 * all the other files have loaded.
	 *
	 * @param $path_to_file
	 */
	public function include_js_raw($path_to_file){
		$this->javascript['raw'][] 				= $path_to_file;
	}

	/**
	 * @param $path_to_file
	 */
	public function include_css($path_to_file){
		$this->css[] 								= $path_to_file;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function add_template_var($name, $value){
		$this->data[$name] 							= $value;
	}

	/**
	 * Useful for feeding a back button on review order page
	 */
	protected function set_previous_page_url() {
		$prev = $this->session->userdata('previous_page');
		$current_uri = uri_string();

		if(empty($prev)) {
			$prev = array();
		}

		switch(count($prev)) {
			case 0:
				array_push($prev, $current_uri);
				break;
			case 1:
				if($prev[0] != $current_uri) {
					array_push($prev, $current_uri);
				}
				break;
			case 2:
				if($prev[1] != $current_uri) {
					array_push($prev, $current_uri);
					array_shift($prev);
				}
				break;
		}

		$this->session->set_userdata('previous_page', $prev);
	}

	/**
	 * Useful for feeding a back button
	 *
	 * @param bool $use_default_fallback if no previous page is set, then fall back to the main page
	 *
	 * @return string
	 */
	protected function get_previous_page_url($use_default_fallback = true) {
		$prev = $this->session->userdata('previous_page');
		$goto = '';
		$default = base_url();
		$current_uri = uri_string();

		if((empty($prev)) && $use_default_fallback) {
			$goto = $default;
		} else {
			if(count($prev) > 1 && $prev[1] != $current_uri) {
				$goto = base_url() . $prev[1];
			} else if($prev[0] != uri_string()) {
				$goto = base_url() . $prev[0];
			} else if($use_default_fallback) {
				$goto = $default;
			}
		}

		return $goto;
	}

	/**
	 * @param $view
	 * @param bool $has_head
	 * @param bool $has_nav
	 * @param array $data
	 */
	protected function _render($view, $has_head = true, $has_nav = true, $data = array()){

		//data
		$this->data 							= array_merge($this->data, $data);

		// let's verify we're not live so we can only display the 
		// philips requested meta tag when we're supposed to
		$thisserverhost 						= $_SERVER['HTTP_HOST'];
		$data_for_template['islive'] 			= (substr($thisserverhost,0,21) !== 'dev2.discusdental.com') ? true : false;

		//static
		$this->_render_raw_js($this->data);
		$data_for_template["javascript"]		= $this->javascript;
		$data_for_template["css"]				= $this->css;
		$data_for_template["fonts"] 			= $this->fonts;

		//meta
		$data_for_template["site_title"]		= $this->title;
		$data_for_template["site_description"]	= $this->description;
		$data_for_template["site_keywords"]		= $this->keywords;
		$data_for_template["site_author"]		= $this->author;
		$data_for_template["site_country_abbr"]	= $this->country_abbr;
		$data_for_template["site_country"]		= $this->country;
		$data_for_template["site_language_abbr"]= $this->language_abbr;
		$data_for_template["site_language"]		= $this->language;
		$data_for_template['notifications']		= $this->notifications;
		$data_for_template['is_company_admin'] 		= false;
		$data_for_template['current_user_id'] 			= $this->current_user->id;
		$data_for_template['current_user_fullname'] 	= $this->current_user->first . ' ' . $this->current_user->last;
		$data_for_template['current_user_firstname'] 	= $this->current_user->first;
		$data_for_template['current_user_lastname'] 	= $this->current_user->last;

		if($this->current_user != null) {
			$this->build_cart();
		}

		$this->data 							= array_merge($this->data,$data_for_template);

		// Start rendering the body
		$this->data["content_body"] 			= $this->load->view($this->data['locale'] . "/pages/" . $view, array_merge($this->data), true);

		// Put the header and footer into the body
		if(file_exists(VIEWPATH . $this->data['locale'] . "/templates/breadcrumbs.php") && !empty($this->data['breadcrumbs'])){
			$this->data["breadcrumbs_component"] 	= $this->load->view($this->data['locale'] . "/templates/breadcrumbs", $this->data, true);
		}

		$this->data["header"] 					= $this->load->view($this->data['locale'] . "/templates/header", $this->data, true);

		if(file_exists(VIEWPATH . $this->data['locale'] . "/templates/footer.php")){
			$this->data["footer"] 			= $this->load->view($this->data['locale'] . "/templates/footer", null, true);
		}

		if(file_exists(VIEWPATH . $this->data['locale'] . "/templates/alerts.php")){
			$this->data["alerts"] 			= $this->load->view($this->data['locale'] . "/templates/alerts", $this->data, true);
		}

		if($this->config->item('is_debug')) {
			$debug_elements = array();

			if(class_exists('Datastore')) {
				$debug_elements['datastore_calls'] = $this->datastore->call_history();
			}

			if(!empty($this->debug_vars)) {
				$debug_elements['debug_vars'] = $this->debug_vars;
			}

			if(!empty($debug_elements)) {
				$this->data['debug_console'] = $this->load->view($this->data['locale'] . "/templates/debugconsole", $debug_elements, true);
			}
		}

		//render view
		$this->load->view($this->data['locale'] . "/templates/skeleton", array_merge($this->data));

		// Post render...this will bw ready for the next page.
		$this->set_previous_page_url();
	}

	/**
	 * Capture a debugging variable, display in a debug footer
	 *
	 * If there is a redirect in this operation, then the debug vars are stored in flashdata to be picked up
	 * on page load after the redirect.
	 *
	 * NOTE: Previously, this function outputted the debug vars directly to the page or to html comments.
	 * But this breaks redirecting if the operation needs to go to another page after the debug var was printed to screen.
	 * This begins outputting an HTTP header before the redirect, thus breaking redirection altogether.
	 *
	 * @param $label
	 * @param $var
	 */
	public function debug_out($label, $var, $file = '', $function = '', $line = '', $time = '', $time_of_day = '') {
		//$message = "";
		if($this->config->item('is_debug')) {
			if(!isset($this->debug_vars)) {
				$this->debug_vars = array();
			}

			if(empty($file)) {
				$calling_location = debug_backtrace();

				if(isset($calling_location[0])) {
					$file = $calling_location[0]['file'];
					$function = $calling_location[1]['function'];
					$line = $calling_location[0]['line'];
				}
			}

			if(empty($time)) {
				$time_label = uniqid('debug_point');
				$this->benchmark->mark($time_label);
				$time = $this->benchmark->elapsed_time('wh_controller_start', $time_label);
				$time_of_day = $this->benchmark->marker[$time_label];
			}

			$this->debug_vars[] = array(
				'label' => $label,
				'var' => print_r($var, true),
				'file'=> $file,
				'function' => $function,
				'line' => $line,
				'time' => $time,
				'time_of_day' => $time_of_day
			);

/*				$message .= $as_html_comment ? "<!--\r\n" : "";
				$message .= !$as_html_comment ? "<h5>". $label ."</h5>\r\n" : $label. "\r\n";
				$message .= $now ." - In ". $calling_location[0]['file'] ." - function: ". $calling_location[1]['function'] ." - line, ". $calling_location[0]['line'] ."\r\n";
				$message .= !$as_html_comment ? "<br /><pre>". print_r($var, true) ."</pre>" : print_r($var, true) ."\r\n";
				$message .= $as_html_comment ? "-->\r\n" : "";*/
		}

		//echo $message;
	}

	/**
	 * To include raw javascript with PHP vars at the foot of the HTML, include a tpl.js.php file
	 * from the views/js folder in your controller function.
	 * @param $data
	 */
	protected function _render_raw_js($data) {
		if(!empty($this->javascript['raw'])) {
			$raw_javascript = "<script type='text/javascript'>\n";
			foreach($this->javascript['raw'] as $phptemplated_jsfile) {
				$raw_javascript .= "\n\n" . $this->load->view($this->data['locale'] . "/js/" . $phptemplated_jsfile, $data, true);
			}
			$raw_javascript .= '</script>';
			$this->javascript['raw'] = $raw_javascript;
		}
	}

	public function showRMS($isloggedin,$custno){
  		$rmsSubmitURL					= $this->config->item('rmsSubmitURL');
  		if($isloggedin){
   			// Encode the customer ID and session ID
   			$sessionID 					= session_id();
   			$accountID 					= $custno;
   			$accountDelimiter 			= "a=";
   			$sessionDelimiter 			= "&s=";
   			$queryString 				= $accountDelimiter . $accountID . $sessionDelimiter . $sessionID;
   			$encodedBase64 				= base64_encode($queryString);
   			// Remove special characters, munges some browsers
   			$encodedBase64 				= str_replace(array('=','+','/'),'',$encodedBase64);
   			$url 						= $rmsSubmitURL . $encodedBase64;
   			// $url 						= $rmsSubmitURL . 'YT0xMDIyNzQmcz1lZjkwNzkwZDcwZTU1Yjc2YjdhOThmMWY0Nzk3N2NhNA'; // DEMO LINK
   			$target 					= '';
  		}else{
    		$url 						= locale_url('/'); //"{$sitePathBase}/login.php";
   			$target 					= '';
  		}
   		$retVal 						= $url;
 		return($retVal);
	}

}
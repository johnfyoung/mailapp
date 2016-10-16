<?php
/**
 * MY_Controller.php
 *
 * @package mailapp
 * @author johny
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class MY_Controller extends CI_Controller {
	/**
	 * Each controller can set whether it needs to require a login
	 *
	 * @var bool
	 */
	var $require_login = false;

	/**
	 * The current locale in the form xx_yy where xx = language code and yy = country code.
	 *
	 * @var string
	 */
	var $locale = '';


	/**
	 * things to store in session to notify the user
	 *
	 * @var array
	 */
	protected $notifications = array(
		'danger' => array(),
		'success' => array(),
		'info' => array(),
		'warning' => array()
	);

	public function __construct() {
		parent::__construct();

		set_exception_handler(array($this,'catch_exceptions'));

		$this->benchmark->mark('my_controller_start');
		$this->debug_out('----- my controller constructing...', get_class($this));

		$this->_init_notifications();

		$this->_set_locale_from_url_path();
		$this->config->set_item('language', get_language());

		$this->load_language_files($this->get_language());

		$this->check_authorization();
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
	 * Catch all uncaught exceptions
	 */
	public function catch_exceptions($exception){
		$debug_exception_message = '';
		if($this->config->item('is_debug')) {
			$debug_exception_message = '<br /><br />'. $exception->getMessage();
		}
		$this->notify_user($this->lang->line('error_unexpected_exception_messsage') . $debug_exception_message, 'error');
		/*$this->load->library('Emailer');*/

		//$emailer_result = $this->emailer->notify_error_unexpected_exception($exception);

		safe_redirect(base_url());
	}


	/**
	 * Called by safe_redirect() prior to redirecting
	 *
	 * Chance to clean up before redirecting to another page
	 */
	public function pre_redirect_handler() {
		$this->flashstore_notifications();
	}

	public function check_authorization() {
		if($this->require_login) {
			if(!$this->auth->authorize()) {
				safe_redirect('access/login');
			}
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
	 * Get's the current locale
	 *
	 * @return string
	 */
	public function get_locale() {
		if(empty($this->locale)) {
			$this->_set_locale_from_url_path();
		}

		return $this->locale;
	}

	/**
	 * Get's the current language
	 *
	 * @return string
	 */
	public function get_language() {
		$locale = $this->get_locale();

		return $this->config->item('locales')[$locale]['language'];
	}

	/**
	 * Derive the desired locale from the URI path
	 */
	protected function _set_locale_from_url_path() {
		$uri_parts = explode('/', uri_string());

		$locale = $this->config->item('default_locale');

		if(count($uri_parts) > 0) {
			if($this->is_valid_locale($uri_parts[0])) {
				$locale = $uri_parts[0];
			}
		}

		$this->set_locale($locale);
	}

	/**
	 * Check the locales config to see if the locale is supported
	 *
	 * @param string $locale
	 *
	 * @return bool
	 */
	public function is_valid_locale($locale) {
		return array_key_exists($locale, $this->config->item('locales'));
	}

	/**
	 * Set the locale
	 *
	 * @param string $locale
	 */
	public function set_locale($locale) {
		$this->locale = $locale;
	}

	/**
	 * Load the default lang files
	 *
	 * As far as I can tell, there is no easy, CI way of setting the actual
	 * lang before the autoloader loads the language files (it uses the default
	 * language set in config)
	 *
	 * @param string $lang
	 */
	public function load_language_files($lang) {
		$lang_files = array('general');

		$this->lang->load($lang_files, $lang);
	}

	/**
	 * Get the GA ID or use the default for the environment

	 *
	 * @return mixed
	 */
	public function get_google_analytics_id() {
		$ga_id = $this->config->item('google_analytics_id');

		return $ga_id;
	}

	/**
	 * Report the user upon constructing the view
	 *
	 * @param $message
	 * @param string $type
	 */
	public function notify_user($message, $type = 'info'){
		// bootstrap uses danger instead of error
		if($type == 'error') {
			$type = 'danger';
		}

		$this->notifications[$type][] = $message;
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
		}
	}

	public function debug_to_screen($name, $val) {
		echo '<pre>'. $name ."\n\n". print_r($val, true) .'</pre>';
	}

	/**
	 * Render the HTML
	 *
	 * @param string $view
	 * @param array $data
	 * @param bool $has_menu
	 */
	public function render($view, $data, $has_menu = true ) {
		$data['raw_notifications'] = $this->notifications;
		$data['notifications'] = $this->load->view('templates/notifications', $data, true);
		$data['has_menu'] = $has_menu;
		$data['current_user'] = $this->auth->current_user();

		if($this->config->item('is_debug')) {
			$debug_elements = array();

			if(!empty($this->debug_vars)) {
				$debug_elements['debug_vars'] = $this->debug_vars;
			}

			if(!empty($debug_elements)) {
				$data['debug_console'] = $this->load->view("templates/debugconsole", $debug_elements, true);
			}
		}

		$data['google_analytics_id'] = get_google_analytics_id();
		$data['nav_top'] = $this->load->view('templates/nav_top',$data, true);
		$data['breadcrumbs_component'] = $this->load->view('templates/breadcrumbs',$data, true);
		$data['footer'] = $this->load->view('templates/footer',$data, true);
		$data['content_body'] = $this->load->view($view, $data, true);



		$this->load->view('templates/skeleton',$data);
	}
}


/* End of file MY_Controller.php */
 
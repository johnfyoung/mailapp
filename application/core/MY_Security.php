<?php
/**
 * MY_Security.php
 *
 * @package philipszoomtrialoffer
 * @author johny
 * @copyright Copyright (c) 2016, Williams Helde
 * @link http://www.williams-helde.com
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Security extends CI_Security {
	public function __construct() {
		parent::__construct();
	}

	public function csrf_show_error() {
		// show_error('The action you have requested is not allowed.');  // default code

		// force page "refresh" - redirect back to itself with sanitized URI for security
		// a page refresh restores the CSRF cookie to allow a subsequent login

		$SESS = load_class('Session', 'libraries/Session');
		$SESS->set_flashdata('has_failed_CSRF','true');
		header('Location: ' . htmlspecialchars($_SERVER['REQUEST_URI']), TRUE, 200);
	}
}

/* End of file MY_Security.php */
 
<?php
/**
 * MY_Model.php
 *
 * @package philipszoomtrialoffer
 * @author johny
 * @copyright Copyright (c) 2016, Williams Helde
 * @link http://www.williams-helde.com
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');
 
class MY_Model extends CI_Model {
	protected $CI;

	public function __construct() {
		parent::__construct();

		$this->CI =& get_instance();

		$this->CI->load->database();
	}

	protected function _load_db($dbgroup) {
		$db = $this->load->database('default', true);
		$db_utils = $this->load->dbutil($db, true);

		if($db_utils->database_exists($dbgroup)) {
			$this->CI->load->database($dbgroup);
		}
	}

}


/* End of file MY_Model.php */
 
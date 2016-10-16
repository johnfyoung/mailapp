<?php
/**
 * Mailservice_driver.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */

class Mailservice_driver extends CI_Driver {
	protected $CI = null;
	protected $mailservice_config = null;

	public function __construct() {
		$this->CI =& get_instance();
	}

	public function get_config() {
		return $this->mailservice_config;
	}

	public function get_request_lifetime() {
		if(isset($this->mailservice_config['request_lifetime'])) {
			return $this->mailservice_config['request_lifetime'];
		}

		return 0;
	}

	protected function _get_api_key() {
		if(!empty($this->mailservice_config['api_key'])) {
			return $this->mailservice_config['api_key'];
		} else {
			throw new Exception('Mailservice API Key is not set.');
		}
	}

	protected function _format_result($result) {
		if(is_array($result)) {
			foreach($result as $k => $v) {
				$result[$k] = $this->_format_result($v);
			}
		}

		if(is_object($result)) {
			$result = get_object_vars($result);
			foreach($result as $k => $v) {
				$result[$k] = $this->_format_result($v);
			}
		}

		return $result;
	}
}
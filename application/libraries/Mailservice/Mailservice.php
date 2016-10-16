<?php
/**
 * Mailservice.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/1/16
 * Time: 8:34 AM
 */

class Mailservice extends CI_Driver_Library {
	protected $_adapter			= 'mailchimp';
	protected $valid_drivers	= array('mailchimp');

	var $CI;

	/**
	 * Construct
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
		$this->CI =& get_instance();

		if(!empty($config)) {
			$this->_initialize($config);
		}
	}

	/**
	 * Initialize
	 *
	 * Initialize class properties based on the configuration array.
	 *
	 * @param array $config
	 * @return void
	 */
	protected function _initialize($config) {
		$default_config = array(
			'adapter'
		);

		foreach ($default_config as $key)
		{
			if (isset($config[$key]))
			{
				$param = '_'.$key;
				$this->{$param} = $config[$key];
			}
		}
	}

	/**
	 * Send an email
	 *
	 * @param array $to with the keys email and name (can also be an array of arrays)
	 * @param array $from with the keys email and name
	 * @param string $subject
	 * @param string $html_body
	 * @param string $html_text
	 * @param string $tag
	 *
	 * @return mixed
	 */
/*	public function send($to, $from, $subject, $html_body = '', $html_text = '', $tag = 'trial-offer-submission') {
		$result = $this->{$this->_adapter}->send($to, $from, $subject, $html_body, $html_text, $tag);

		return $result;
	}*/

	public function get_service_id() {
		return $this->_adapter;
	}

	public function get_service_config() {
		return $result = $this->{$this->_adapter}->get_config();
	}

	public function get_request_lifetime() {
		return $result = $this->{$this->_adapter}->get_request_lifetime();
	}

	public function get_lists() {
		$result = $this->{$this->_adapter}->get_lists();

		return $result;
	}

	public function get_list($list_id) {
		$result = $this->{$this->_adapter}->get_list($list_id);

		return $result;
	}

	public function get_list_fields($list_id) {
		$result = $this->{$this->_adapter}->get_list_fields($list_id);

		return $result;
	}

	public function get_list_members($list_id) {
		$result = $this->{$this->_adapter}->get_list_members($list_id);

		return $result;
	}

	public function tag_list_member($list_id, $member_id, $member_tag) {
		$result = $this->{$this->_adapter}->tag_list_member($list_id,$member_id, $member_tag);

		return $result;
	}
}
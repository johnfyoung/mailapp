<?php
/**
 * Auth.php
 *
 * @package pohc_email_data
 * @author johny
 * @copyright Copyright (c) 2014, Williams Helde
 * @link http://www.williams-helde.com
 */

class Auth {
	/**
	*
	* Controller Instance
	* @var object
	*/
	var $CI = null;

	/**
	* Construct
	*/
	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->load->model('/User_model');
	}

	/**
	* Authorize a previously authenticated user to use a site asset
	*
	* To authorize a user, the controller requesting authorization calls this function
	* to legitimize the user stored in the session against the datastore.
	*/
	public function authorize() {
		$is_auth = false;
		$user = $this->current_user();

		if(!empty($user)) {
			if($this->CI->User_model->validate_user_id($user->id,$user->email_address)) {
				$this->CI->user = $user;
				$is_auth = true;
			}
			else {
				$this->CI->user = null;
				$this->_destroy_session();
			}
		}

		return $is_auth;
	}

	/**
	*
	* Authenticate a user against the datastore
	*
	* Loads a User entity for the authenticated user into the session
	* @param string $username
	* @param string $password
	* @return array
	*/
	public function authenticate($username, $password) {
		$datastore_result = $this->CI->User_model->authenticate($username,$password);
		$result           = array('success' => false, 'message' => lang('error_unknown'));
		if(!empty($datastore_result)) {
			$this->_establish_session($datastore_result->id);
			$result['success'] = true;
			$result['result'] = $datastore_result;
		} else {
			array('success' => false, 'message' => lang('login_message_failure_notfound'));
		}

		return $result;
	}

	/**
	 * @param $user_id
	 */
	protected function _establish_session($user_id) {
		$this->CI->session->set_userdata(array('user_id' => $user_id));
		$user = $this->CI->User_model->get_user_by_id($this->CI->session->userdata('user_id'));
		$this->CI->session->set_userdata(array('user_data' => $user->_dataobj));
	}

	/**
	 *
	 * @return null|User
	 */
	public function current_user() {
		$user_id = $this->CI->session->userdata('user_id');
		$user_data = $this->CI->session->userdata('user_data');
		$user = null;

		if(!empty($user_id)) {
			$this->CI->load->library('entities/User_entity');
			$user = new User_entity($user_data);
		}

		return $user;
	}

	/**
	 * @param $password
	 * @param int $cost
	 * @return string
	 */
	public function generate_hashed_password($password, $cost = 10) {
		$cost = 10;
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		$salt = sprintf("$2a$%02d$", $cost) . $salt;
		$hash = crypt($password, $salt);
		return $hash;
	}

	/**
	* De-authorize the logged in user
	*/
	public function logout() {
		$this->_destroy_session();
	}

	/**
	* Helper function for cleaning auth variables form the session
	*/
	protected function _destroy_session() {
		$this->CI->session->unset_userdata('user_id');
		$this->CI->session->unset_userdata('user_data');
	}
}

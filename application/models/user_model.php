<?php
/**
 * User_model.php
 *
 * @package mailapp
 * @author johny
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');
 

class User_model extends MY_Model {

	public function __construct() {
		parent::__construct();

		//$this->_load_db(get_application_dbgroup());

		$this->load->library('entities/user_entity');
		$this->load->model('permissions_model');
	}

	/**
	 * @param $user
	 */
	public function persist_user($user) {
		$data = array(
			'email_address' => $user->email,
			'first_name' => $user->firstname,
			'last_name' => $user->lastname,
			'password' => $user->password
		);

		$query = $this->db->insert('users',$data);
	}

	/**
	 * @param $user
	 * @return mixed
	 */
	public function update_user($user) {
		$data = array(
			'email_address' => $user->email,
			'first_name' => $user->firstname,
			'last_name' => $user->lastname,
			'password' => $user->password
		);

		$this->db->where('id', $user->id);

		return $this->db->update('users',$data);
	}

	/**
	 * @param $user
	 */
	public function delete_user($user) {
		$permission_delete_result = $this->Permssions_model->delete_by_user_id($user->id);

		$this->db->delete('users', array('id' => $user->id));
	}

	/**
	 * @param $id
	 * @return null|User_entity
	 */
	public function get_user_by_id($id) {
		$user = null;

		$this->db->select('*');
		$this->db->where('id', $id);
		$this->db->from('users');
		$query = $this->db->get();

		// We expect only one user per email address
		if($query->num_rows() == 1) {
			$row = $query->row();

			$user = new User_entity($row);
		}

		return $user;
	}

	/**
	 * @param $email
	 * @return null|User_entity
	 */
	public function get_user_by_email($email) {
		$user = null;

		$this->db->select('id, email_address, first_name, last_name, password');
		$this->db->or_where('email_address =', $email);
		$this->db->or_where('email_address =', strtolower($email));
		$this->db->or_where('email_address =', strtoupper($email));
		$this->db->or_where('email_address =', ucfirst($email));
		$this->db->or_where('email_address =', ucwords($email));
		$this->db->from('users');
		//$sql = $this->db->get_compiled_select();
		$query = $this->db->get();

		// We expect only one user per email address
		if($query->num_rows() == 1) {
			$row = $query->row();

			$user = new User_entity($row);
		}

		return $user;
	}

	/**
	 * @param $user_id
	 * @param $user_email
	 * @return bool
	 */
	public function validate_user_id($user_id, $user_email) {
		$user = $this->get_user_by_email($user_email);

		if($user) {
			return $user_id == $user->id;
		}

		return false;
	}

	/**
	 * @param $username
	 * @param $password
	 * @return null|User_entity
	 */
	public function authenticate($username,$password) {
		return $this->_authenticate(array('email_address' => $username,'password' => $password));
	}

	/**
	 * @param $params
	 * @return null|User_entity
	 */
	public function _authenticate($params) {
		$result = null;
		$user = $this->get_user_by_email($params['email_address']);

		if(!empty($user)) {
			$is_valid = $this->_validate_password($user->password, $params['password']);

			if($is_valid) {
				//$user = $this->_rehash_password($user);
				$result = $user;
			}
		}

		return $result;
	}

	/**
	 * for extra security, generate new hash after every successful login
	 * @param $user
	 */
	protected function _rehash_password($user) {
		$hash = $this->_generate_hashed_password($user->password);
		$user->password = $hash;
		$this->update_user($user);
	}

	/**
	 * @param $stored_password
	 * @param $supplied_password
	 * @return bool
	 */
	protected function _validate_password($stored_password, $supplied_password) {
		if ( crypt($supplied_password, $stored_password) === $stored_password ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param $password
	 * @param int $cost
	 * @return string
	 */
	protected function _generate_hashed_password($password, $cost = 10) {
		return $this->CI->auth->generate_hased_password($password, $cost);
	}
} 
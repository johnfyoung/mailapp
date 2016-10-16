<?php
/**
 * Utility.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/7/16
 * Time: 1:59 PM
 */

class Utility extends MY_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function encrypt() {
		$data['page_name'] = lang('page_name_encrypt');
		$data['page_title'] = lang('page_title_home');

		if($this->config->item('is_debug')) {
			$this->load->library('encryption');
			$key = bin2hex($this->encryption->create_key(16));

			$this->render('pages/encrypt', array('encrypt_key' => $key), false);
		} else {
			safe_redirect(base_url());
		}
	}

	public function encrypt_password($value = null) {
		$data['page_name'] = lang('page_name_encrypt');
		$data['page_title'] = lang('page_title_home');

		if($this->config->item('is_debug')) {
			$encrypted_password = $this->auth->generate_hashed_password($value);

			$this->render('pages/encrypt', array('encrypt_key' => $encrypted_password), false);
		} else {
			safe_redirect(base_url());
		}
	}

	public function test_compare() {
		$this->load->model('Mailservice_model');

		$this->Mailservice_model->test_diff();
	}
}
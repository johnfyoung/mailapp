<?php
/**
 * access.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */

class Access extends MY_Controller {
	public function __construct() {
		parent::__construct();

		$this->lang->load('auth');
	}

	public function login() {
		if(empty($this->auth->current_user())) {
			$this->load->library('Form_validation');
			$this->form_validation->set_rules('identity', lang('login_identity_label'), 'trim|required');
			$this->form_validation->set_rules('password', lang('login_password_label'), 'trim|required');

			if($this->form_validation->run()) {
				$auth_result = $this->auth->authenticate($this->input->post('identity'), $this->input->post('password'));
				if($auth_result['success']) {
					$this->notify_user(lang('login_message_success'),'success');
				} else {
					$this->notify_user(lang('login_message_failure_notfound'),'error');
				}
			} else {
				$this->notify_user(lang('login_message_failure_required'),'error');
			}

			safe_redirect('/');

		} else {
			$this->notify_user(lang('login_message_already_logged_in'),'info');
			safe_redirect('/');
		}
	}

	public function logout() {
		if(!empty($this->auth->current_user())) {
			$this->auth->logout();
			$this->notify_user(lang('logout_message_success'),'success');
			safe_redirect(base_url());
		}
	}
}
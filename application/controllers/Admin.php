<?php
/**
 * Admin.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */


class Admin extends MY_Controller {
	var $data = array();

	public function __construct() {
		$this->require_login = true;

		parent::__construct();

		$this->data['page_title'] = lang('page_title_admin');
	}

	public function index() {
		$this->data['page_name'] = lang('page_name_admin');


		$this->data['breadcrumbs'] = array(
			array(
				'name' => 'Home',
				'url' => base_url(),
				'is_active' => false
			),
			array(
				'name' => 'Admin',
				'url' => base_url('admin'),
				'is_active' => true
			)
		);

		$this->render('pages/admin', $this->data);
	}

	public function mailservice($service_name = null, $refresh = null) {
		$this->data['page_name'] = $this->data['page_title'] = "Mailservices";

		$this->load->helper('mailservice');

		$this->data['breadcrumbs'] = array(
			array(
				'name' => 'Home',
				'url' => base_url(),
				'is_active' => false
			),
			array(
				'name' => 'Admin',
				'url' => base_url('admin'),
				'is_active' => false
			),
			array(
				'name' => 'Mailservices',
				'url' => base_url('admin/mailservice'),
				'is_active' => false
			)
		);

		if(!empty($service_name)){
			$mailservice_config = get_mailservice_config($service_name);

			if(!empty($mailservice_config)) {
				$this->service_name = $service_name;
				$this->load->driver('Mailservice',array('adapter' => $this->service_name));
				$this->load->model('Mailservice_model');
				$this->data['page_title'] = lang('page_title_mailservice') .' - '. $mailservice_config['name'];

				$this->data['service_name'] = $this->service_name;

				$this->data['breadcrumbs'][] = 				array(
					'name' => $mailservice_config['name'],
					'url' => base_url('admin/mailservice/'.$this->service_name),
					'is_active' => true
				);

				call_user_func_array(array($this, '_handle_'. $this->service_name), array($refresh));
			} else {
				$this->notify_user('There is no service by that name', 'error');
				safe_redirect(base_url('admin/mailservice'));
			}
		} else {
			$this->data['mailservices'] = $this->config->item('mailservices');
			$this->render('pages/admin_mailservice', $this->data);
		}
	}

	protected function _handle_mailchimp($refresh_request = null) {
		$refresh = array('lists' => false);

		if($refresh_request) {
			$refresh[$refresh_request] = true;
			$lists_result = $this->Mailservice_model->get_lists($refresh['lists'], $refresh['lists']);

			$this->notify_user('Data refreshed', 'success');
			safe_redirect(base_url('admin/mailservice/mailchimp'));
		}

		$lists_result = $this->Mailservice_model->get_lists($refresh['lists'], $refresh['lists']);

		$this->data['lists'] = $lists_result['result'];
		$this->data['list_expire_time'] = date('M j Y g:i:s a', $lists_result['expiration']);

		$this->render('pages/admin_mailservice', $this->data);
	}

	public function mailinglistmembers($service_name, $list_id, $command = null) {
		$this->data['page_name'] = lang('page_name_admin') . ' - List members';

		$this->load->helper('mailservice');
		$this->load->model('Mailservice_model');

		if(!empty($service_name)){
			$this->service_name = $service_name;
			$mailservice_config = get_mailservice_config($this->service_name);

			if(!empty($mailservice_config)) {
				$this->load->driver('Mailservice',array('adapter' => $this->service_name));
				$this->data['page_title'] = lang('page_title_mailservice') .' - '. $mailservice_config['name'];

				$refresh_member_list = false;
				if(!empty($command)) {
					switch($command) {
						case 'refresh':
							$refresh_member_list = true;
							break;
						case 'import_leads':
							$this->_handle_mailinglistmembers_imports_leads($list_id);
							$this->notify_user('Leads created', 'success');
							safe_redirect(base_url('admin/mailinglistmembers/'.$this->service_name.'/'.$list_id));
							break;
					}
				}

				$member_result = $this->_handle_mailinglistmembers_get($list_id, $refresh_member_list);
				$list = $this->Mailservice_model->get_list($list_id);
				$this->data['list_name'] = $list['result']['list_name'];
				$this->data['list_id'] = $list_id;
				$this->data['members'] = $member_result['result'];
				$this->data['members_list_expiration'] = date('M j Y g:i:s a', $member_result['expiration']);
				$this->data['service_name'] = $this->service_name;

				$this->data['breadcrumbs'] = array(
					array(
						'name' => 'Home',
						'url' => base_url(),
						'is_active' => false
					),
					array(
						'name' => 'Admin',
						'url' => base_url('admin'),
						'is_active' => false
					),
					array(
						'name' => $mailservice_config['name'],
						'url' => base_url('admin/mailservice/'.$this->service_name),
						'is_active' => false
					),
					array(
						'name' => 'List Members - '.$list['result']['list_name'],
						'url' => base_url('admin/mailinglistmembers/'.$this->service_name.'/'.$list_id),
						'is_active' => true
					),
				);

			}
		}

		$this->render('pages/admin_mailservice_memberlist', $this->data);
	}

	public function leads($lead_id = null) {
		$this->data['page_name'] = $this->data['page_title'] = lang('page_name_admin') . ' - Leads';

		$this->load->model('Lead_model');

		$this->data['leads'] = $this->Lead_model->get_leads();

		$this->data['breadcrumbs'] = array(
			array(
				'name' => 'Home',
				'url' => base_url(),
				'is_active' => false
			),
			array(
				'name' => 'Admin',
				'url' => base_url('admin'),
				'is_active' => false
			),
			array(
				'name' => 'Leads',
				'url' => base_url('admin/leads'),
				'is_active' => true
			),
		);

		$this->render('pages/admin_leads', $this->data);
	}

	protected function _handle_mailinglistmembers_get($list_id, $refresh, $service_name = null) {
		$service_name = $service_name == null ? $this->service_name : $service_name;

		$member_result = $this->Mailservice_model->get_list_members($list_id, $refresh, $refresh);
		if($refresh) {
			$this->notify_user('Data refreshed', 'success');
			safe_redirect(base_url('admin/mailinglistmembers/'. $service_name .'/'. $list_id));
		}

		$this->load->model('Lead_model');
		$member_result['result'] = $this->Lead_model->connect_mailinglistmembers_to_leads($member_result['result']);

		return $member_result;
	}

	protected function _handle_mailinglistmembers_imports_leads($list_id, $service_name = null) {
		$this->load->model('Lead_model');

		$service_name = $service_name == null ? $this->service_name : $service_name;

		$member_result = $this->Mailservice_model->get_list_members($list_id);
		$mailing_list_member_schema = $this->Mailservice_model->get_list_member_schema($list_id);

		foreach($member_result['result'] as $member) {
			$email_address = $member['email_address'];
			unset($member['email_address']);

			$lead = $this->Lead_model->save_lead($email_address, $member, $mailing_list_member_schema['fields'], $service_name, 'mailing_service_list');
			$this->Mailservice_model->tag_list_member_as_lead($list_id, $member, $lead);
		}

		return $member_result;
	}
}
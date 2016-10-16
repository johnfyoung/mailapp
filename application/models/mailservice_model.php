<?php
/**
 * mailservice_model.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/7/16
 * Time: 3:04 PM
 */

/**
 * Class Mailservice_model
 *
 * To control the number of API calls to Mailservices, the API data requests have ages so that API calls only occur
 * when explicitly asked for or after the data has expired
 */
class Mailservice_model extends MY_Model {

	/**
	 * Cache database requests to cut down on data requests
	 *
	 * @var
	 */
	var $cache = array();

	public function __construct() {
		parent::__construct();

		$this->_load_db(get_application_dbgroup());
	}

	/**
	 * Get all of the lists from the current mailservice
	 *
	 * @param bool|false $refresh_cache
	 * @param bool|false $refresh_db
	 * @return mixed
	 */
	public function get_lists($refresh_cache = false, $refresh_db = false) {
		$is_expired = $this->is_service_request_expired(MAILSERVICE_REQ_GETLISTS);

		if($refresh_db || $refresh_cache || $is_expired || empty($this->cache['lists'])) {
			$service_id = $this->CI->mailservice->get_service_id();

			if($refresh_db || $is_expired) {
				$raw_result = $this->CI->mailservice->get_lists();

				foreach($raw_result as $list) {
					$list['service_id'] = $service_id;

					$this->db->query(generate_duplicate_key_sql('service_leadlist', $list));
				}

				$this->log_service_request($service_id,MAILSERVICE_REQ_GETLISTS);
			}

			$this->db->select('*');
			$this->db->where('service_id', $service_id);
			$this->db->from('service_leadlist');
			$query = $this->db->get();

			if($query->num_rows() > 0) {
				$this->cache['lists'] = array('result' =>  $query->result_array(), 'expiration' => $this->get_service_request_expiration_time(MAILSERVICE_REQ_GETLISTS));
			}
		}

		return $this->cache['lists'];
	}

	/**
	 * Get a list for a mailservice
	 *
	 * @param $list_id
	 * @param bool|false $refresh_cache
	 * @param bool|false $refresh_db
	 * @return null
	 */
	public function get_list($list_id, $refresh_cache = false, $refresh_db = false) {
		$result = null;

		if($refresh_db || $refresh_cache || empty($this->cache['lists'])) {
			$this->get_lists($refresh_cache, $refresh_db);
		}

		foreach($this->cache['lists']['result'] as $list) {
			if($list['id'] == $list_id) {
				$result = array('result' => $list, 'expiration' => $this->cache['lists']['expiration']);
				break;
			}
		}

		return $result;
	}

	public function get_list_member_schema($list_id) {
		$list = $this->get_list($list_id);

		return unserialize($list['result']['list_fields']);
	}

	/**
	 * Get the members for a mailing list
	 *
	 * @param $list_id
	 * @param bool|false $refresh_cache
	 * @param bool|false $refresh_db
	 * @return mixed
	 */
	public function get_list_members($list_id, $refresh_cache = false, $refresh_db = false) {
		$list = $this->get_list($list_id);
		$this->CI->load->library('dataforge');

		$service_id = $this->CI->mailservice->get_service_id();
		$is_expired = $this->is_service_request_expired(MAILSERVICE_REQ_GETLISTMEMBERS);

		$table_name = $this->generate_service_member_list_table_name($service_id, $list['result']['list_unique_id']);
		$table_exists = $this->CI->dataforge->does_table_exist($table_name);

		if($refresh_db || $refresh_cache || $is_expired || !$table_exists || empty($this->cache['list_members'][$list_id])) {
			if(!$table_exists) {
				$this->make_list_member_table($service_id,$list['result']['list_unique_id'],$this->get_list_member_schema($list_id));
			}

			if($refresh_db || $is_expired) {
				$raw_result = $this->CI->mailservice->get_list_members($list['result']['list_unique_id']);

				foreach($raw_result as $result_row) {
					$this->db->query($this->CI->dataforge->generate_duplicate_key_sql($table_name, $result_row));
				}

				$this->log_service_request($service_id, MAILSERVICE_REQ_GETLISTMEMBERS);
			}

			$query = $this->db->get($table_name);

			if($query->num_rows() > 0) {
				$this->cache['list_members'][$list_id] = array('result' => $query->result_array(), 'expiration' => $this->get_service_request_expiration_time(MAILSERVICE_REQ_GETLISTMEMBERS));
			} else {
				$this->cache['list_members'][$list_id] = null;
			}
		}

		return $this->cache['list_members'][$list_id];
	}

	/**
	 * Log a request to a mailservice. Used for controlling uneccessary requests to the mailservice api
	 *
	 * @param $service_id
	 * @param $request_id
	 * @param null $params
	 */
	public function log_service_request($service_id, $request_id, $params = null) {
		$result = null;
		$now = time();

		$query_params = array(
			'service_id' => $service_id,
			'request_id' => $request_id,
			'params' => !empty($params) ? serialize($params) : '',
			'last_update' => $now,
			'expiration' => $now + $this->CI->mailservice->get_request_lifetime(),
		);

		$this->db->replace('service_request_log',$query_params);
	}

	/**
	 * Check to see if the info from a request has expired
	 *
	 * @param $request_id
	 * @param null $params
	 * @return bool
	 */
	public function is_service_request_expired($request_id, $params = null) {
		$result = true;

		$expiration_ts = $this->get_service_request_expiration_time($request_id, $params);

		if(!empty($expiration_ts)) {
			$result = time() - $expiration_ts >= 0;
		}

		return $result;
	}

	/**
	 * Get the expiration time for a service request
	 *
	 * @param $request_id
	 * @param null $params
	 * @return bool
	 */
	public function get_service_request_expiration_time($request_id, $params = null) {
		$result = false;

		$log_entry = $this->get_service_request_log_entry($request_id, $params);

		if(!empty($log_entry)) {
			$result = $log_entry['expiration'];
		}

		return $result;
	}

	/**
	 * Get a log entry for a mailservice request
	 *
	 * @param $request_id
	 * @param null $params
	 * @return bool
	 */
	public function get_service_request_log_entry($request_id, $params = null) {
		$result = false;

		$this->db->select('*');
		$this->db->where('service_id', $this->CI->mailservice->get_service_id());
		$this->db->where('request_id', $request_id);
		if(!empty($params)) {
			$this->db->where('params', serialize($params));
		}

		$this->db->from('service_request_log');
		$this->db->order_by('last_update', 'DESC');

		$query = $this->db->get();

		if($query->num_rows() != 0) {
			$result = $query->row_array();
		}

		return $result;
	}

	/**
	 * Make a table for mailing list members from mail service
	 *
	 * @param $service_id
	 * @param $list_id
	 * @param $table_schema
	 */
	public function make_list_member_table($service_id, $list_id, $table_schema) {
		$this->CI->load->library('dataforge');

		$table_name = $this->generate_service_member_list_table_name($service_id, $list_id);

		if($this->CI->dataforge->does_table_exist($table_name)) {
			$this->CI->dataforge->update_custom_data_table($table_name, $table_schema);
		} else {
			$this->CI->dataforge->create_custom_data_table($table_name, $table_schema);
		}
	}

	/**
	 * Create a table name for a list member table based on a template
	 *
	 * @param $service_id
	 * @param $list_id
	 * @return string
	 */
	public function generate_service_member_list_table_name($service_id, $list_id) {
		return 'custom_table_'. $service_id .'_'. $list_id;
	}

	public function tag_list_member_as_lead($list_id, $member, $lead){
		$list = $this->get_list($list_id);
		$this->CI->mailservice->tag_list_member($list['result']['list_unique_id'], $member['service_unique_id'], $lead['uuid']);
	}


	public function test_diff() {
		$schema_old = array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'name' => array(
					'type' => 'VARCHAR',
					'constraint' => 36,
					'null' => false
				),
				'thing' => array(
					'type' => 'VARCHAR',
					'constraint' => 48,
					'default' => 'hankie'
				),
				'gravy' => array(
					'type' => 'TINYINT',
					'default' => 1
				),
			),
			'keys' => array(
				'unique' => array(
					array('garbage'),
					'kinder',
					array('poop', 'dragon')
				)
			),
			'indexes' => array(

			)
		);

		$schema_new = array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'name' => array(
					'type' => 'VARCHAR',
					'constraint' => 48,
					'null' => false
				),
				'gravy' => array(
					'type' => 'TINYINT'
				),
				'hubub' => array(
					'type' => 'VARCHAR',
					'constraint' => 48,
					'null' => false
				)
			),
			'keys' => array(
				'unique' => array(
					array('garbage'),
					array('garbage', 'trouble'),
					'kinder',
				)
			),
			'indexes' => array(

			)
		);

		$this->CI->load->library('dataforge');

		$diff = $this->CI->dataforge->compare_table_schemas($schema_old, $schema_new);

		$this->CI->debug_to_screen('table_schema diff', $diff);
	}
}
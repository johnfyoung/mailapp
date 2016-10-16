<?php
/**
 * lead_model.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/9/16
 * Time: 9:57 AM
 */

class Lead_model extends MY_Model {
	public function __construct() {
		parent::__construct();

		$this->_load_db(get_application_dbgroup());
	}

	public function save_lead($email_address, $fields, $field_schema, $source, $source_type) {
		$this->CI->load->library('dataforge');

		$lead = $this->get_lead_by_email($email_address);
		$lead_id = null;
		if(empty($lead)) {
			$params = array(
				'email_address' => $email_address,
				'uuid' => uniqid('',true)
			);

			$query = $this->db->insert('leads',$params);

			$lead_id = $this->db->insert_id();
		} else {
			$lead_id = $lead['id'];
		}

		$this->save_lead_fields($lead_id, $fields, $field_schema, $source, $source_type);

		return $this->get_lead_by_id($lead_id);
	}

	public function get_lead_by_email($email) {
		$result = null;
		$this->db->where('email_address',$email);
		$query = $this->db->get('leads');

		if($query->num_rows() == 1) {
			$result = $query->row_array();
		}

		return $result;
	}

	public function get_lead_by_id($id) {
		$result = null;
		$this->db->where('id',$id);
		$query = $this->db->get('leads');

		if($query->num_rows() == 1) {
			$result = $query->row_array();
		}

		return $result;
	}

	public function get_leads() {
		$result = null;

		$query = $this->db->get('leads');

		if($query->num_rows() > 0) {
			$result = $query->result_array();
		}

		return $result;
	}

	public function connect_mailinglistmembers_to_leads($member_list) {
		foreach($member_list as $index => $member) {
			$member_list[$index]['lead_id'] = $this->get_lead_by_email($member['email_address'])['id'];
		}

		return $member_list;
	}


	public function save_lead_fields($lead_id, $fields, $field_schema, $source = null, $source_type = null) {
		$this->CI->load->library('dataforge');

		foreach($fields as $field_name => $field_data) {
			$this->save_lead_field($lead_id, $field_name, $field_data, $field_schema[$field_name]['type'], $source, $source_type);
		}
	}

	public function save_lead_field($lead_id, $field_name, $field_value, $field_type = null, $source = null, $source_type = null) {
		$this->CI->load->library('dataforge');

		$now = time();

		$params = array(
			'lead_id' => $lead_id,
			'field_name' => $field_name,
			'field_value' => $field_value,
			'field_type' => $this->CI->dataforge->normalize_data_type($field_type),
			'field_normalized_name' => $this->CI->dataforge->normalize_field_type($field_name),
			'source' => $source,
			'source_type' => $source_type,
			'update_ts' => $now,
			'discovery_ts' => $now
		);

		$sql = $this->CI->dataforge->generate_duplicate_key_sql('lead_fields', $params);
		$sql = preg_replace('/,discovery_ts\=[\d]*/', '', $sql);

		$this->db->query($sql);
	}
}
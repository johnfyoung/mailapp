<?php
/**
 * Mailservice_mailchimp.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/1/16
 * Time: 8:34 AM
 */

require_once(__DIR__ . '/../Mailservice_driver.php');

class Mailservice_mailchimp extends Mailservice_driver {
	var $mc;

	const MAILAPP_ID_MERGETAG = 'MAILAPP_ID';

	var $mailapp_id_mergetag_spec = array(
		'tag' => Mailservice_mailchimp::MAILAPP_ID_MERGETAG,
		'name' => 'Mailapp ID',
		'public' => false,
		'type' => 'text'
	);

	public function __construct() {
		parent::__construct();

		$this->mailservice_config = $this->CI->config->item('mailservices')['mailchimp'];
		$this->mc = new \Mailchimp\Mailchimp($this->_get_api_key());
	}

	/**
	 * Request list descriptions from Mailchimp
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public function get_lists() {
		$lists_result = $this->mc->request('lists', [
			'fields' => 'lists.id,lists.name,lists.stats.member_count'
		]);

		if(!empty($lists_result)) {
			$items = $lists_result->ToArray();

			$items = $this->_format_result($items);
			foreach($items as $index => $item) {
				$items[$index]['list_unique_id'] = $item['id'];
				unset($items[$index]['id']);
				$items[$index]['list_name'] = $item['name'];
				unset($items[$index]['name']);
				$items[$index]['list_size'] = $item['stats']['member_count'];
				unset($items[$index]['stats']);
			}

			$this->_get_fields_for_lists($items);

			return $items ;
		}

		return null;
	}

	/**
	 * Request a list description from Mailchimp
	 *
	 * @param $list_id
	 * @return array|null
	 * @throws Exception
	 */
	public function get_list($list_id) {
		$list_result = $this->mc->request('lists/'. $list_id, [
			'fields' => 'lists.id,lists.name,lists.stats.member_count'
		]);

		if(!empty($list_result)) {
			$items = $list_result->ToArray();
			$items = $this->_format_result($items);

			foreach($items as $index => $item) {
				$items[$index]['list_size'] = $item['stats']['member_count'];
			}
			return $items;
		}

		return null;
	}

	/**
	 * Request list members from Mailchimp
	 *
	 * @param $list_id
	 * @return array|null
	 * @throws Exception
	 */
	public function get_list_members($list_id) {
		$result = $this->mc->request('lists/'. $list_id .'/members', [
			'fields' => 'members.id,members.email_address,members.merge_fields'
		]);

		if(!empty($result)) {
			$items = $result->ToArray();
			$items = $this->_format_result($items);

			return $this->_format_member_list_result($items);
		}

		return null;
	}

	public function tag_list_member($list_id, $member_id, $member_tag) {
		$this->_add_merge_field_to_list($list_id, $this->mailapp_id_mergetag_spec);

		$result = $this->mc->put('lists/'. $list_id .'/members/'.$member_id, [
			'merge_fields' => array(Mailservice_mailchimp::MAILAPP_ID_MERGETAG => $member_tag)
		]);

		if(!empty($result)) {
			$items = $result->ToArray();
		}
	}

	/**
	 *
	 * @param $list_id
	 * @param $field_spec
	 * @throws Exception
	 */
	protected function _add_merge_field_to_list($list_id, $field_spec) {
		$result = null;
		$merge_field = $this->_get_merge_field($list_id, $field_spec['tag']);

		if(empty($merge_field)) {
			$result = $this->mc->post('lists/'. $list_id .'/merge-fields', $field_spec);
		}

		return $result;
	}

	/**
	 * @param string $list_id the Mailchimp List ID
	 * @return array|null
	 * @throws Exception
	 */
	protected function _get_merge_fields($list_id) {
		$items = null;
		$result = $this->mc->request('lists/'. $list_id .'/merge-fields', ['merge_fields']);

		if(!empty($result)) {
			$items = $result->ToArray();
			$items = $this->_format_result($items['merge_fields']);
		}

		return $items;
	}

	/**
	 * @param string $list_id the Mailchimp list ID
	 * @param string $merge_tag the field's merge tag
	 * @return \Illuminate\Support\Collection|null
	 * @throws Exception unknown error
	 */
	protected function _get_merge_field($list_id, $merge_tag) {
		$result = null;
		$merge_fields = $this->_get_merge_fields($list_id);

		foreach($merge_fields as $field) {
			if($field['tag'] == $merge_tag) {
				$result = $merge_tag;
				break;
			}
		}

		/*
		$result = null;
		try {
			// if this merge field doesn't exist, mc throws an exception
			$result = $this->mc->get('lists/'. $list_id .'/merge-fields/'.$merge_tag, []);
		} catch (Exception $e) {
			$responseObject = json_decode($e->getMessage());
			if($responseObject->title != "Resource Not Found") {
				throw $e;
			}
		}
		*/

		return $result;
	}

	/**
	 * Get the member fields for a list
	 *
	 * @param $lists
	 */
	protected function _get_fields_for_lists(&$lists) {
		foreach($lists as $index => $list) {
			$result = $this->mc->request('lists/'. $list['list_unique_id'] .'/merge-fields', [
				'fields' => 'merge_fields'
			]);

			if(!empty($result)) {
				$field_descriptions = $result->ToArray();

				$lists[$index]['list_fields'] = $this->_format_fields($field_descriptions);

				// Mailchimp member fields are called merge fields which are separate from the unique id and emails
				// Mailchimp includes a md5'd version of the email address
				$extra_fields = array(
					'id' => array(
						'type' => 'INT',
						'constraint' => 11,
						'unsigned' => true,
						'auto_increment' => true,
					),
					'email_address' => array(
						'type' => 'VARCHAR',
						'constraint' => 100,
						'unique' => true,
						'null' => false
					),
					'service_unique_id' => array(
						'type' => 'VARCHAR',
						'constraint' => 100,
						'unique' => true,
						'null' => false
					)
				);

				$keys = array(
					'primary' => array(
						'id'
					),
				);

				$lists[$index]['list_fields'] = array_merge($extra_fields, $lists[$index]['list_fields']);
				$lists[$index]['list_fields'] = array('fields' => $lists[$index]['list_fields'], 'keys' => $keys);
				$lists[$index]['list_fields'] = serialize($lists[$index]['list_fields']);
			}
		}
	}

	/**
	 * Format the merge field descriptions for storage
	 *
	 * @param $field_list
	 */
	protected function _format_fields($field_list) {
		$this->CI->load->library('dataforge');

		$ddl_fields = array();

		foreach($field_list as $field) {
			$ddl_fields[$field->tag] = array(
				'type' => $this->CI->dataforge->normalize_data_type($field->type),
			);

			if(isset($field->options->size)) {
				$ddl_fields[$field->tag]['constraint'] = $field->options->size;
			}

			if(!empty($field->default_value)) {
				$ddl_fields[$field->tag]['default'] = $field->default_value;
			}

			if($field->required) {
				$ddl_fields[$field->tag]['null'] = true;
			} else {
				$ddl_fields[$field->tag]['null'] = false;
			}
		}

		return $ddl_fields;
	}

	protected function _format_member_list_result($unformatted_result) {
		$result = array();

		foreach($unformatted_result as $row) {
			$id_fields = array(
				'email_address' => $row['email_address'],
				'service_unique_id' => $row['id']
			);

			$result[] = array_merge($id_fields, $row['merge_fields']);
		}

		return $result;
	}
}
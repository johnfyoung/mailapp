<?php

/**
 * Dataforge.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/8/16
 * Time: 8:17 AM
 */

/**
 * Class Dataforge
 *
 * Utilities for creating, updating and destoying data models
 */
class Dataforge {
	// data type dictionary
	const Varchar = "VARCHAR";
	const Int = "INT";
	const Float = "FLOAT";
	const Double = "DOUBLE";
	const Tinyint = "TINYINT";
	const Decimal = "DECIMAL";

	// field type dictionary
	const first_name = "first_name";
	const last_name = "last_name";
	const email_address = "email";
	const phone = "phone";
	const mobile = "phone_mobile";
	const street_address_1 = "street_address_1";
	const street_address_2 = "street_address_2";
	const city = "city";
	const state = "state_province";
	const province = "state_province";
	const county = "county";
	const postal_code = "postal_code";

	/**
	 * @var CI_Controller|object
	 */
	var $CI;

	/**
	 * @var DB|object
	 */
	var $db;

	/**
	 * @var CI_DB_utility|object
	 */
	var $dbutil;

	/**
	 * @var CI_DB_forge|object
	 */
	var $dbforge;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->CI =& get_instance();

		$this->db = $this->CI->load->database('default', true);
		$this->dbutil = $this->CI->load->dbutil($this->db, true);
		$this->dbforge = $this->CI->load->dbforge($this->db, true);
	}

	/**
	 * Get a DB connection based on the given db group name (see database config)
	 *
	 * @param string $db_group_name
	 * @return DB|object
	 */
	protected function _get_db($db_group_name = '') {
		$db = $this->db;

		if(!empty($db_group_name)) {
			$db = $this->CI->load->database($db_group_name, true);
		}

		return $db;
	}

	/**
	 * Get a DB forge based on the given db group name (see database config)
	 *
	 * @param string $db_group_name
	 * @return CI_DB_forge|object
	 */
	protected function _get_dbforge($db_group_name = '') {
		$db_forge = $this->dbforge;

		if(!empty($db_group_name)) {
			$db_forge = $this->CI->load->dbforge($db_group_name, true);
		}

		return $db_forge;
	}

	/**
	 * Install an application database schema
	 *
	 * @param array $database_schema
	 * @param bool|false $with_database
	 * @return void
	 */
	public function install_database_schema($database_schema, $with_database = false) {
		if($with_database) {
			if (!$this->dbutil->database_exists($database_schema['database']['name'])) {
				if($this->dbforge->create_database($database_schema['database']['name'])) {
					log_message('info', 'Created database '.$database_schema['database']['name'].'.');
					/*$newdb = $this->CI->load->database($database_schema['database']['name'], true);
					$newdbforge = $this->CI->load->dbforge($newdb, true);*/

					$this->create_tables($database_schema['tables'], $database_schema['database']['name']);
				} else {
					log_message('error', 'Unable to create database '.$database_schema['database']['name'].'. Unknown error.');
				}
			} else {
				log_message('error', 'Unable to create database '.$database_schema['database']['name'].'. It already exists.');
			}
		} else {
			$this->create_tables($database_schema['tables']);
		}
	}

	/**
	 *
	 * @param array $tables_schema
	 * @param string $db_group_name
	 */
	public function create_tables($tables_schema, $db_group_name = null) {
		foreach($tables_schema as $table_name => $table_schema) {
			$this->create_table($table_name, $table_schema, $db_group_name);
			$this->CI->notify_user('Created '. $table_name, 'success');
		}
	}

	/**
	 * Uninstall an application database schema
	 *
	 * @param array $database_schema
	 * @param bool|false $with_database
	 * @return void
	 */
	public function uninstall_database_schema($database_schema, $with_database = false) {
		if($with_database) {
			if ($this->dbutil->database_exists($database_schema['database']['name'])) {
				$this->dbforge->drop_database($database_schema['database']['name']);
				log_message('info', 'Database '.$database_schema['database']['name'].' dropped.');
			} else {
				log_message('error', 'Unable to uninstall database '.$database_schema['database']['name'].'. It does not exist.');
			}
		} else {
			$this->drop_custom_data_tables();
			$this->CI->notify_user('Dropped custom tables', 'success');

			foreach($database_schema['tables'] as $table_name => $table_schema) {
				$this->drop_table($table_name);
				$this->CI->notify_user('Dropped '.$table_name, 'success');
			}
		}
	}

	/**
	 * Create a table
	 *
	 * @param string $table_name
	 * @param array $table_schema
	 * @param string $db_group_name
	 * @return void
	 */
	public function create_table($table_name, $table_schema, $db_group_name = '') {
		$db = $this->_get_db($db_group_name);
		$db_forge = $this->_get_dbforge($db_group_name);

		$db_forge->add_field($table_schema['fields']);

		// primary keys
		if(!empty($table_schema['keys']['primary'])) {
			$db_forge->add_key($table_schema['keys']['primary'], true);
		}

		// create the table
		$db_forge->create_table($table_name, true);

		// alter table to add unique keys
		if(!empty($table_schema['keys']['unique'])) {
			foreach($table_schema['keys']['unique'] as $unique_key) {
				$this->alter_table_add_unique_key($table_name, $unique_key, $db_group_name);
			}
		}

		if(!empty($table_schema['indexes'])) {
			foreach($table_schema['indexes'] as $index) {
				$this->alter_table_add_index($table_name, $index, $db_group_name);
			}
		}

		// insert any default data
		if(!empty($table_schema['data'])) {
			foreach($table_schema['data'] as $insert_data) {
				$db->insert($table_name, $insert_data);
			}
		}

		log_message('info', 'Created table: '.$table_name.'.');
	}

	/**
	 * Update a table's schema
	 *
	 * @param string $table_name
	 * @param array $table_schema
	 * @param string $db_group_name
	 * @return void
	 */
	public function update_table($table_name, $table_schema, $old_table_schema = null, $db_group_name = '') {
		$db = $this->_get_db($db_group_name);
		$db_forge = $this->_get_dbforge($db_group_name);

		if(empty($old_table_schema)) {
			if(isset($this->CI->config->item('database_schema')['tables'][$table_name])) {
				$old_table_schema = $this->CI->config->item('database_schema')['tables'][$table_name];
			} else {
				// todo derive schema from database table
				throw new Exception("Can't update a database table. No old schema to compare to.");
			}
		}

		// calc the difference
		$schema_diff = $this->compare_table_schemas($old_table_schema, $table_schema);

		// add new fields
		if(!empty($schema_diff['fields']['new'])) {
			$db_forge->add_column($table_name, $schema_diff['fields']['new']);
		}

		// delete unneeded fields
		if(!empty($schema_diff['fields']['delete'])) {
			foreach($schema_diff['fields']['delete'] as $delete_col_key => $delete_col_schema) {
				$db_forge->drop_column($table_name, $delete_col_key);
			}
		}

		// update fields
		if(!empty($schema_diff['fields']['update'])) {
			$db_forge->modify_column($table_name, $schema_diff['fields']['update']);
		}

		// changes to primary key
		if(!empty($schema_diff['keys']['new']['primary'])) {
			$primary_key_sql = sprintf("ALTER TABLE %s DROP PRIMARY KEY ADD PRIMARY KEY (%s)", $table_name, join(',',$schema_diff['keys']['new']['primary']));

			$db->query($primary_key_sql);
		}

		// changes to unique keys
		if(!empty($schema_diff['keys']['new']['unique'])) {
			foreach($schema_diff['keys']['new']['unique'] as $new_unique_key) {
				$this->alter_table_add_unique_key($table_name, $new_unique_key, $db_group_name);
			}
		}

		if(!empty($schema_diff['keys']['delete']['unique'])) {
			foreach($schema_diff['keys']['delete']['unique'] as $del_unique_key) {
				$this->alter_table_drop_unique_key($table_name, $del_unique_key, $db_group_name);
			}
		}

		// changes to indexes
		if(!empty($schema_diff['indexes']['new'])) {
			foreach($schema_diff['indexes']['new'] as $new_index) {
				$this->alter_table_add_index($table_name, $new_index, $db_group_name);
			}
		}

		if(!empty($schema_diff['indexes']['delete'])) {
			foreach($schema_diff['indexes']['delete'] as $del_index) {
				$this->alter_table_drop_index($table_name, $del_index, $db_group_name);
			}
		}
	}

	/**
	 * Add a unique key to a table
	 *
	 * @param string $table_name
	 * @param array $column_names
	 * @param string $db_group_name
	 * @return void
	 */
	public function alter_table_add_unique_key($table_name, $column_names, $db_group_name = '') {
		$db = $this->_get_db($db_group_name);
		$column_list = is_array($column_names) ? join(',',$column_names) :  $column_names;
		$new_unique_key_sql = sprintf("ALTER TABLE %s ADD CONSTRAINT %s UNIQUE KEY (%s)", $table_name, $this->_generate_unique_key_symbol($table_name, $column_names), $column_list);

		$db->query($new_unique_key_sql);
	}

	/**
	 * Drop a unique key
	 *
	 * @param string $table_name
	 * @param array $column_names
	 * @param string $db_group_name
	 * @return void
	 */
	public function alter_table_drop_unique_key($table_name, $column_names, $db_group_name = '') {
		$db = $this->_get_db($db_group_name);
		$del_unique_key_sql = sprintf("ALTER TABLE %s DROP KEY %s", $table_name, $this->_generate_unique_key_symbol($table_name, $column_names));

		$db->query($del_unique_key_sql);
	}

	/**
	 * Add an index
	 *
	 * @param string $table_name
	 * @param array $column_names
	 * @param string $db_group_name
	 * @return void
	 */
	public function alter_table_add_index($table_name, $column_names, $db_group_name = '') {
		$db = $this->_get_db($db_group_name);
		$column_list = is_array($column_names) ? join(',',$column_names) :  $column_names;
		$new_index_sql = sprintf("ALTER TABLE %s ADD INDEX %s (%s)", $table_name, $this->_generate_index_symbol($table_name, $column_names), $column_list);

		$db->query($new_index_sql);
	}

	/**
	 * Drop an index
	 *
	 * @param string $table_name
	 * @param array $column_names
	 * @param string $db_group_name
	 * @return void
	 */
	public function alter_table_drop_index($table_name, $column_names, $db_group_name = '') {
		$db = $this->_get_db($db_group_name);
		$del_index_sql = sprintf("ALTER TABLE %s DROP INDEX %s", $table_name, $this->_generate_index_symbol($table_name, $column_names));

		$db->query($del_index_sql);
	}

	/**
	 * Drop a table
	 *
	 * @param string $table_name
	 * @param string $db_group_name
	 * @return void
	 */
	public function drop_table($table_name, $db_group_name = '') {
		$db_forge = $this->_get_dbforge($db_group_name);

		$this->_drop_table($table_name, $db_forge);
	}

	/**
	 * Helper for drop_table
	 *
	 * @param string $table_name
	 * @param CI_DB_forge $dbforge
	 * @return void
	 */
	protected function _drop_table($table_name, $dbforge) {
		$dbforge->drop_table($table_name,TRUE);
		log_message('info', 'Table dropped: '. $table_name);
	}

	/**
	 * Generate SQL for an INSERT...UPDATE on DUPLICATE KET query
	 *
	 * @param string $table_name
	 * @param array $params
	 * @return string
	 */
	public function generate_duplicate_key_sql($table_name, $params) {

		$keys = array_keys($params);
		$keys_string = join(',', $keys);

		$values = array_values($params);
		$values_string = '';
		foreach($values as $i => $value) {
			if($i > 0) {
				$values_string .= ',';
			}

			$values_string .= $this->db->escape($value);
		}

		$update_string = '';
		foreach($params as $key => $val) {
			$update_string .= $key .'='. $this->db->escape($val) .',';
		}

		$update_string = trim($update_string, ',');

		$sql = sprintf('INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s', $table_name, $keys_string, $values_string, $update_string);
		return $sql;
	}

	/**
	 * Generate a unique symbol id for a unique key
	 *
	 * @param string $table_name
	 * @param array $unique_key_column_names
	 * @return string
	 */
	protected function _generate_unique_key_symbol($table_name, $unique_key_column_names) {
		$columns = is_array($unique_key_column_names) ? join('_',$unique_key_column_names) :  $unique_key_column_names;
		return  "uk_". $table_name .'_'. $columns;
	}

	/**
	 * @param $table_name
	 * @param $index_column_names
	 * @return string
	 */
	protected function _generate_index_symbol($table_name, $index_column_names) {
		$columns = is_array($index_column_names) ? join('_',$index_column_names) :  $index_column_names;
		return  "idx_". $table_name .'_'. $columns;
	}

	/**
	 * Custom data tables are those the application makes ad hoc, i.e., not based on the orginal schema. These custom
	 * tables are tracked in the custom_data_tables table
	 *
	 * @param string $db_group_name
	 * @return array[]
	 */
	public function get_custom_data_tables($db_group_name = '') {
		$db = $this->_get_db($db_group_name);

		return $this->_get_custom_data_tables($db);
	}

	/**
	 * Helper for get_custom_data_tables
	 *
	 * @param DB $db
	 * @return array[]
	 */
	protected function _get_custom_data_tables($db) {
		$result = null;

		$query = $db->get('custom_data_tables');

		if($query->num_rows() > 0) {
			$result = $query->result_array();
		}

		return $result;
	}

	/**
	 * Drop all custom data tables and their tracking entries
	 *
	 * @param string $db_group_name
	 * @return void
	 */
	public function drop_custom_data_tables($db_group_name = '') {
		$custom_tables = $this->get_custom_data_tables($db_group_name);

		if(!empty($custom_tables)) {
			foreach($custom_tables as $table_description) {
				$this->drop_custom_data_table($table_description['table_name'], $db_group_name);
			}
		}
	}

	/**
	 * Drop a custom data table and remove it's tracking entry
	 *
	 * @param string $table_name
	 * @param string $db_group_name
	 * @return void
	 */
	public function drop_custom_data_table($table_name, $db_group_name = '') {
		$this->drop_table($table_name, $db_group_name);

		$db = $this->db;

		if(!empty($db_group_name)) {
			$db = $this->CI->load->database($db_group_name, true);
		}

		$db->where('table_name', $table_name);
		$db->delete('custom_data_tables');
	}

	/**
	 * Create a custom data table and create its tracking entry
	 *
	 * @param string $table_name
	 * @param array $table_schema
	 * @param string $db_group_name
	 * @return void
	 */
	public function create_custom_data_table($table_name, $table_schema, $db_group_name = '') {
		$this->create_table($table_name, $table_schema, $db_group_name);

		$db = $this->_get_db($db_group_name);

		$params = array(
			'table_name' => $table_name,
			'table_schema' => serialize($table_schema)
		);

		$db->insert('custom_data_tables',$params);
	}

	/**
	 * Update a custom data table and update its schema in the tracking entry
	 *
	 * @param string $table_name
	 * @param array $table_schema
	 * @param string $db_group_name
	 * @return void
	 */
	public function update_custom_data_table($table_name, $table_schema, $db_group_name = '') {
		$this->update_table($table_name, $table_schema, $db_group_name);

		$db = $this->_get_db($db_group_name);

		$params = array(
			'table_schema' => $table_schema
		);

		$db->where('table_name', $table_name);
		$db->update('custom_data_tables', $params);
	}

	/**
	 * Normalize the data dictionary for common data types
	 *
	 * @param string $type_description
	 * @return string
	 */
	public function normalize_data_type($type_description) {

		switch($type_description){
			case 'text':
			case 'string':
				$data_type = Dataforge::Varchar;
				break;
			case 'int':
			case 'integer':
				$data_type = Dataforge::Int;
				break;
			case 'float':
				$data_type = Dataforge::Float;
				break;
			case 'double':
				$data_type = Dataforge::Double;
				break;
			case 'number':
				$data_type = Dataforge::Float;
				break;
			case 'decimal':
				$data_type = Dataforge::Decimal;
				break;
			case 'bool':
			case 'boolean':
			case 'tinyint':
				$data_type = Dataforge::Tinyint;
				break;
			default:
				$data_type = Dataforge::Varchar;
		}

		return $data_type;
	}

	/**
	 * Normalize the data dictionary for common lead field types
	 *
	 * @param string $field_name
	 * @return string
	 */
	public function normalize_field_type($field_name) {
		$field_name = strtolower($field_name);
		switch($field_name) {
			case 'first':
			case 'firstname':
			case 'first_name':
			case 'fname':
				$field_type = Dataforge::first_name;
				break;
			case 'last':
			case 'lastname':
			case 'last_name':
			case 'lname':
				$field_type = Dataforge::last_name;
				break;
			case 'email':
			case 'email_address':
			case 'emailaddress':
				$field_type = Dataforge::email_address;
				break;
			// todo complete the field types
			default:
				$field_type = false;
		}

		return $field_type;
	}

	/**
	 * Check to see if a table exists
	 *
	 * @param string $table_name
	 * @return bool
	 */
	public function does_table_exist($table_name) {
		$db = $this->_get_db();

		return $db->table_exists($table_name);
	}

	/**
	 * Find differences between schemas (as used by CI dbforge)
	 *
	 * Field diff's are labelled, 'update', 'new' and 'delete' which correspond to table column alterations
	 * Key diff's are separated between primary and unique, each showing new keys and keys to delete
	 * Index diff's are labelled 'new' and 'delete' which correspond to index alterations
	 *
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	public function compare_table_schemas($old, $new) {
		$diff = array(
			'fields' => $this->get_table_field_diff($old, $new),
			'keys' => $this->get_table_keys_diff($old, $new),
			'indexes' => $this->get_table_indexes_diff($old, $new)
		);

		return $diff;
	}

	/**
	 * Find differences bewteen table field schemas
	 *
	 * Field diff's are labelled, 'update', 'new' and 'delete' which correspond to table column alterations
	 *
	 * NOTE: if you're changing the field name, the field has to keep the same key but have a `name` in its schema
	 * @link https://www.codeigniter.com/user_guide/database/forge.html#modifying-a-column-in-a-table
	 *
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	public function get_table_field_diff($old, $new) {
		$col_diff = array(
			'update' => array(),
			'new' => array(),
			'delete' => array()
		);

		$field_diff_new = array_udiff_assoc($new['fields'], $old['fields'], array($this, "compare_field_schemas"));
		$field_diff_old = array_udiff_assoc($old['fields'], $new['fields'], array($this, "compare_field_schemas"));

		foreach($field_diff_new as $field_name => $field_schema) {
			if(array_key_exists($field_name, $field_diff_old)) {
				$col_diff['update'][$field_name] = $field_schema;
			} else {
				$col_diff['new'][$field_name] = $field_schema;
			}
		}

		foreach($field_diff_old as $field_name => $field_schema) {
			if(array_key_exists($field_name, $field_diff_new)) {
				$col_diff['update'][$field_name] = $field_diff_new[$field_name];
			} else {
				$col_diff['delete'][$field_name] = $field_schema;
			}
		}

		return $col_diff;
	}

	/**
	 * Find differences bewteen table keys
	 *
	 * Key diff's are separated between primary and unique, each showing new keys and keys to delete
	 *
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	public function get_table_keys_diff($old, $new) {
		$key_diff = array(
			'new' => array(),
			'delete' => array()
		);

		if(!isset($old['keys']['primary'])) {
			$old['keys']['primary'] = array();
		}

		if(!isset($new['keys']['primary'])) {
			$new['keys']['primary'] = array();
		}

		$diff_primary = compare_arrays($old['keys']['primary'], $new['keys']['primary']);
		if(!empty($diff_primary['add']) || !empty($diff_primary['remove'])) {
			$key_diff['new']['primary'] = $new['keys']['primary'];
		}

		if(!isset($old['keys']['unique'])) {
			$old['keys']['unique'] = array();
		}

		if(!isset($new['keys']['unique'])) {
			$new['keys']['unique'] = array();
		}

		$diff_unique = compare_arrays($old['keys']['unique'], $new['keys']['unique']);
		$key_diff['new']['unique'] = $diff_unique['add'];
		$key_diff['delete']['unique'] = $diff_unique['remove'];

		return $key_diff;
	}

	/**
	 * Find differences between table indexes
	 *
	 * Index diff's are labelled 'new' and 'delete' which correspond to index alterations
	 *
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	public function get_table_indexes_diff($old, $new) {
		$index_diff = array(
			'new' => array(),
			'delete' => array()
		);

		if(!isset($old['indexes'])) {
			$old['indexes'] = array();
		}

		if(!isset($new['indexes'])) {
			$new['indexes'] = array();
		}

		$diff = compare_arrays($old['indexes'], $new['indexes']);
		$index_diff['new'] = $diff['add'];
		$index_diff['delete'] = $diff['remove'];

		return $index_diff;
	}

	/**
	 * Helper function compares whether one field schema is the same or different
	 *
	 * @param array $new
	 * @param array $old
	 * @return int
	 */
	public function compare_field_schemas($new, $old) {
		$schema_diff = array_diff_assoc($new,$old);

		if(empty($schema_diff)) {
			// they are equal
			return 0;
		}

		// they are different. If this was scalar, -1 would be less than , 1 would be greater than
		return 1;
	}
}
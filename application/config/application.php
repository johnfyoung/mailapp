<?php

/**
 * mailservices.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */

$config['application_version'] = '0.9.0';

$config['database_schema'] = array(
	'database' => array(
		'name' => 'default',
		'table_attributes' => array(
			'CHARACTER SET' => 'utf8',
			'COLLATE' => 'utf8_bin',
			'ENGINE' => 'InnoDB'
		)
	),
	'tables' => array(
		'users' => array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'username' => array(
					'type' => 'VARCHAR',
					'constraint' => 128,
					'null' => false
				),
				'password' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => false
				),
				'email_address' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => false
				),
				'first_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 48,
					'null' => false
				),
				'last_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 48,
					'null' => false
				),
				'created_on' => array(
					'type' => 'INT',
					'unsigned' => true,
				),
				'last_login' => array(
					'type' => 'INT',
					'unsigned' => true,
				),
				'active' => array(
					'type' => 'TINYINT',
					'unsigned' => true,
					'default' => false
				)
			),
			'keys' => array(
				'primary' => array(
					'id'
				),
				'unique' => array(
					array('email_address'),
					array('username')

				)
			),
			'data' => array(
				array(
					'username' => 'admin',
					'password' => '$2a$10$0A1/gur1RhZELJnGsPOGmOU/Fo/yUdgHE.QWqwJGvUrYZ6Kcgs3TG',
					'email_address' => 'john@codeandcreative.com',
					'first_name' => 'Default',
					'last_name' => 'Administrator',
					'created_on' => time(),
					'last_login' => 0,
					'active' => false
				)
			)
		),
		'users_ip_addresses' => array(
			'fields' => array(
				'user_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => TRUE
				),
				'ip_address' => array(
					'type' => 'VARCHAR',
					'constraint' => 48
				)
			),
			'keys' => array(
				'primary' => array(
					'user_id',
					'ip_address'
				)
			)
		),
		'service_leadlist' => array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'service_id' => array(
					'type' => 'VARCHAR',
					'constraint' => 36,
					'null' => false
				),
				'list_unique_id' => array(
					'type' => 'VARCHAR',
					'constraint' => 128,
					'null' => true
				),
				'list_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 128,
					'null' => true
				),
				'list_size' => array(
					'type' => 'INT',
					'constraint' => 11,
					'null' => true
				),
				'list_fields' => array(
					'type' => 'VARCHAR',
					'constraint' => 1024,
					'null' => true
				),
				'list_mailappid_field_id' => array(
					'type' => 'VARCHAR',
					'constraint' => 1024,
					'null' => true
				),
				'keep_synced' => array(
					'type' => 'TINYINT',
					'null' => false,
					'default' => 0
				)
			),
			'keys' => array(
				'primary' => array(
					'id'
				),
				'unique' => array(
					array('service_id','list_unique_id')
				)
			)
		),
		'service_request_log' => array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'service_id' => array(
					'type' => 'VARCHAR',
					'constraint' => 36,
					'null' => false
				),
				'request_id' => array(
					'type' => 'VARCHAR',
					'constraint' => 128,
					'null' => false
				),
				'params' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => true
				),
				'last_update' => array(
					'type' => 'INT',
					'null' => false,
					'unsigned' => true
				),
				'expiration' => array(
					'type' => 'INT',
					'null' => false,
					'unsigned' => true
				),
			),
			'keys' => array(
				'primary' => array('id'),
				'unique' => array(
					array('service_id', 'request_id')
				)
			)
		),
		'custom_data_tables' => array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'table_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 128,
					'null' => false
				),
				'table_schema' => array(
					'type' => 'VARCHAR',
					'constraint' => 2048,
					'null' => false
				)
			),
			'keys' => array(
				'primary' => array(
					'id'
				),
				'unique' => array(
					'table_name'
				)
			)
		),
/*		'ip_addresses' => array(
			'fields' => array(

			)
		),*/
		'leads' => array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'email_address' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => false
				),
				'uuid' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => false
				)
			),
			'keys' => array(
				'primary' => array(
					'id'
				),
				'unique' => array(
					'email_address',
					'uuid'
				)
			)
		),
		'lead_fields' => array(
			'fields' => array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'lead_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'null' => false
				),
				'field_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 128,
					'null' => false
				),
				'field_type' => array(
					'type' => 'VARCHAR',
					'constraint' => 24,
				),
				'field_value' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => false
				),
				'field_normalized_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 128,
				),
				'source' => array(
					'type' => 'VARCHAR',
					'constraint' => 255
				),
				'source_type' => array(
					'type' => 'VARCHAR',
					'constraint' => 255
				),
				'update_ts' => array(
					'type' => 'INT',
					'unsigned' => true
				),
				'discovery_ts' => array(
					'type' => 'INT',
					'unsigned' => true
				)
			),
			'keys' => array(
				'primary' => array('id'),
				'unique' => array(
					array('lead_id', 'field_name', 'source')
				)
			),
			'indexes' => array(
				array('lead_id', 'field_name', 'source')
			)
		)
		/*'groups' => array(
			//todo data model for groups
		),
		'users_groups' => array(
			//todo data model for users_groups
		),
		'login_attenpts' => array(
			//todo data model for login_attempts
		),
		'ip_addresses' => array(
			'fields' => array(

			)
		),
		'leads' => array(
			'fields' => array(

			)
		),
		'leads_ip_addresses' => array(
			'fields' => array(

			)
		),
		'hit' => array(
			'fields' => array(

			)
		),
		'hit_ip_address' => array(
			'fields' => array(

			)
		)*/
	)
);

$config['mailservices'] = array(
	'mailchimp' => array(
		'id'	=> 'mailchimp',
		'name'	=> 'Mailchimp',
		'api_version' => '3.0',
		'api_key' => '<YOUR MAILCHIMP API KEY HERE>',
		'request_lifetime' => 86400
	)
);

// CONSTANTS
define('MAILSERVICE_REQ_GETLISTS','get_lists');
define('MAILSERVICE_REQ_GETLIST','get_list');
define('MAILSERVICE_REQ_GETLISTMEMBERS', 'get_list_members');

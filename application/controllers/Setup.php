<?php
/**
 * Setup.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/7/16
 * Time: 12:17 PM
 */

class Setup extends MY_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function install() {
		$this->data['page_title'] = lang('page_title_install');
		$this->load->library('dataforge');

		$this->dataforge->install_database_schema($this->config->item('database_schema'));

		$this->render('pages/installer',$this->data, false);
	}

	public function uninstall() {
		$this->data['page_title'] = lang('page_title_uninstall');
		$this->load->library('dataforge');
		$this->dataforge->uninstall_database_schema($this->config->item('database_schema'));

		$this->render('pages/installer',$this->data, false);
	}

	/*protected function _install_database() {
		$database_config = $this->config->item('data_model');
		$this->load->database();
		$this->load->dbutil();

		if (!$this->dbutil->database_exists($database_config['database']['name']))
		{
			$this->load->dbforge();

			if($this->dbforge->create_database($database_config['database']['name'])) {
				$this->data['database_created'] = true;

				$this->newdb = $this->load->database($database_config['database']['name'], true);
				$this->newdbforge = $this->load->dbforge($this->newdb, true);

				foreach($database_config['tables'] as $table_name => $table_description) {
					$this->newdbforge->add_field($table_description['fields']);

					if(!empty($table_description['keys']['primary'])) {
						$this->newdbforge->add_key($table_description['keys']['primary'], true);
					}

					$this->newdbforge->create_table($table_name);

					if(!empty($table_description['keys']['unique'])) {
						foreach($table_description['keys']['unique'] as $unique_key) {
							$unique_key_name = "uk_". $table_name .'_'. join('_', $unique_key);
							$unique_key_list  = join(',', $unique_key);
							$this->newdb->query("ALTER TABLE ". $table_name ." ADD CONSTRAINT ". $unique_key_name ." UNIQUE (". $unique_key_list .")");
						}
					}

					if(!empty($table_description['data'])) {
						foreach($table_description['data'] as $insert_data) {
							if($this->newdb->insert($table_name, $insert_data)) {
								$this->notify_user('Insert successful','success');
							} else {
								$this->notify_user('Insert unsuccessful','success');
							}
						}
					}
				}
			}
		}
	}*/
}
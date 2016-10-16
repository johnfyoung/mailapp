<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {

	public function __construct() {
		parent::__construct();
	}
	/**
	 * Index Page for this controller.
	 *
	 */
	public function index() {
		$data['page_name'] = lang('page_name_home');
		$data['page_title'] = lang('page_title_home');

		$this->render('pages/home',$data);
	}

}

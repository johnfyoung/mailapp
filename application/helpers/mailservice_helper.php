<?php
/**
 * mailservice_helper.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 * Date: 10/1/16
 * Time: 10:30 AM
 */

function get_mailservice_config($servicename) {
	$CI =& get_instance();

	if(array_key_exists($servicename, $CI->config->item('mailservices'))) {
		return $CI->config->item('mailservices')[$servicename];
	}

	return null;
}
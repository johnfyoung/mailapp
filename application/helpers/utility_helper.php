<?php
/**
 * utility_helper.php
 *
 * @package mailapp
 * @author johny
 */
 
defined('BASEPATH') OR exit('No direct script access allowed');

function get_locale() {
	$ci =& get_instance();

	return $ci->get_locale();
}

function get_language() {
	$ci =& get_instance();

	return $ci->get_language();
}

function get_google_analytics_id() {
	$ci =& get_instance();

	return $ci->get_google_analytics_id();
}

/**
 * Derive a site url including the current locale
 *
 * @param string $path
 *
 * @return string
 */
function locale_url($path = "") {
	$ci =& get_instance();

	if(!empty($path)) {
		trim($path,"\\/");
		$path_parts = explode('/',$path);

		if(!in_array($path_parts[0],locale_keys($ci))) {
			$path = base_url(get_locale() .'/'. $path);
		}
	} else {
		$path = base_url(get_locale() .'/');
	}

	return $path;
}

/**
 * Gives the application a chance to prepare session state for redirect
 *
 * @param string $uri
 * @param string $method
 * @param null $code
 */
function safe_redirect($uri = '', $method = 'auto', $code = NULL) {
	$ci = get_instance();

	$calling_location = debug_backtrace();

	if(isset($calling_location[0])) {
		$file = $calling_location[0]['file'];
		$function = $calling_location[1]['function'];
		$line = $calling_location[0]['line'];
	}

	$time_mark = get_benchmark_timestamp();

	$ci->pre_redirect_handler();

	redirect($uri, $method, $code);
}

/**
 * Get a time elapsed from beginning of execution
 *
 * @return array
 */
function get_benchmark_timestamp() {
	$ci =& get_instance();

	$time_label = uniqid('debug_point');
	$ci->benchmark->mark($time_label);
	$time = $ci->benchmark->elapsed_time('my_controller_start', $time_label);
	$time_of_day = $ci->benchmark->marker[$time_label];

	return array(
		'mark' 			=> $time_label,
		'time' 			=> $time,
		'time_of_day' 	=> $time_of_day
	);
}

/**
 * HTML Helper - check to see if an input has a value after a redirect
 *
 * @param string $field_name
 *
 * @return string
 */
function set_value_after_redirect($field_name) {
	$ci =& get_instance();

	$form_field_values = $ci->session->flashdata('form_field_values');

	if(!empty($form_field_values[$field_name])) {
		return $form_field_values[$field_name];
	} else {
		return '';
	}
}

/**
 * HTML Helper - check to see if a form checkbox should be checked after a redirect
 *
 * @param string $checkbox_name
 *
 * @return string
 */
function set_checkbox_after_redirect($checkbox_name) {
	$ci =& get_instance();

	$form_field_values = $ci->session->flashdata('form_field_values');

	if(!empty($form_field_values[$checkbox_name]) && $form_field_values[$checkbox_name] == 'on') {
		return 'checked';
	} else {
		return '';
	}
}

/**
 * Drop-down Menu
 *
 * @param	mixed	$data
 * @param	mixed	$options
 * @param	mixed	$selected
 * @param	mixed	$extra
 * @return	string
 */
function form_dropdown($data = '', $options = array(), $selected = array(), $extra = '', $first_option = '')
{
	$defaults = array();

	if (is_array($data))
	{
		if (isset($data['selected']))
		{
			$selected = $data['selected'];
			unset($data['selected']); // select tags don't have a selected attribute
		}

		if (isset($data['options']))
		{
			$options = $data['options'];
			unset($data['options']); // select tags don't use an options attribute
		}
	}
	else
	{
		$defaults = array('name' => $data);
	}

	$selected = is_array($selected) ? $selected : (!empty($selected) ? array($selected) : array());
	$options = is_array($options) ? $options : (!empty($options) ? array($options) : array());

	if(!empty($first_option)) {
		$first_option_element = array('value' => $first_option, 'extra' => array('disabled' => 'disabled','hidden' => 'hidden'));
		if(empty($selected)) {
			/*$first_option_element['extra']['selected'] = 'selected';*/
			$selected[] = 0;
		}
		array_unshift($options, $first_option_element);
	}

	// If no selected state was submitted we will attempt to set it automatically
	if (empty($selected))
	{
		if (is_array($data))
		{
			if (isset($data['name'], $_POST[$data['name']]))
			{
				$selected = array($_POST[$data['name']]);
			}
		}
		elseif (isset($_POST[$data]))
		{
			$selected = array($_POST[$data]);
		}
	}

	$extra = _attributes_to_string($extra);

	$multiple = (count($selected) > 1 && stripos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

	$form = '<select '.rtrim(_parse_form_attributes($data, $defaults)).$extra.$multiple.">\n";

	foreach ($options as $key => $val)
	{
		$key = (string) $key;
		$value = $key !== 0 && $key !== '0' ? $key : '';

		if (is_array($val))
		{
			if (empty($val))
			{
				continue;
			}

			if(array_key_exists('extra', $val)) {
				$extras = _attributes_to_string($val['extra']);

				$form .= '<option value="'.html_escape($value).'"'
						 .(in_array($key, $selected) ? ' selected="selected"' : '')
						 .$extras .'>'
						 .(string) $val['value']."</option>\n";
			} else {
				$form .= '<optgroup label="'.$key."\">\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
					$form .= '<option value="'.html_escape($optgroup_key).'"'.$sel.'>'
							 .(string) $optgroup_val."</option>\n";
				}

				$form .= "</optgroup>\n";
			}
		}
		else
		{
			$form .= '<option value="'.html_escape($value).'"'
					 .(in_array(trim($key), $selected, true) ? ' selected="selected"' : '').'>'
					 .(string) $val."</option>\n";
		}
	}

	return $form."</select>\n";
}

/**
 * Attributes To String
 *
 * Helper function used by some of the form helpers
 *
 * @param	mixed
 * @return	string
 */
function _attributes_to_string($attributes)
{
	if (empty($attributes))
	{
		return '';
	}

	if (is_object($attributes))
	{
		$attributes = (array) $attributes;
	}

	if (is_array($attributes))
	{
		$atts = '';

		foreach ($attributes as $key => $val)
		{
			if(in_array($key, array('disabled', 'required'))) {
				$atts .= ' '.$key;
			} else {
				$atts .= ' '.$key.'="'.$val.'"';
			}
		}

		return $atts;
	}

	if (is_string($attributes))
	{
		return ' '.$attributes;
	}

	return FALSE;
}

/**
 * Extracts Google Analytics tracking tags from GET
 *
 * @return array
 */
function extract_ga_tags() {
	$ci =& get_instance();

	$ga_tags = array();

	if(!empty($_GET)) {
		$ga_tags['source'] = $ci->input->get('utm_source', true);
		$ga_tags['medium'] = $ci->input->get('utm_medium', true);
		$ga_tags['campaign'] = $ci->input->get('utm_campaign', true);
		$ga_tags['term'] = $ci->input->get('utm_term', true);
		$ga_tags['content'] = $ci->input->get('utm_content', true);
	}

	return $ga_tags;
}

function get_application_dbgroup() {
	$ci =& get_instance();

	return $ci->config->item('database_schema')['database']['name'];
}

function generate_duplicate_key_sql($table_name, $params) {
	$ci =& get_instance();

	$ci->load->library('dataforge');

	return $ci->dataforge->generate_duplicate_key_sql($table_name, $params);
}

/**
 * Compare non-keyed arrays
 *
 * @param $old
 * @param $new
 * @return array
 */
function compare_arrays($old, $new) {
	$result = array(
		'add' => array(),
		'same' => array(),
		'remove' => array()
	);

/*	if(!empty($new) && !empty($old)) {
		$result['add'] = array_diff($new, $old);
		$result['remove'] = array_diff($old, $new);
		$result['same'] = array_intersect($new, $old);

	} else if(!empty($new) && empty($old)) {
		$result['add'] = $new;
	} else if(empty($new) && !empty($old)) {
		$result['remove'] = $old;
	}*/

	if(!empty($new) && !empty($old)) {
		foreach($new as $item_new) {
			if(in_array($item_new, $old)) {
				$result['same'][] = $item_new;
			} else {
				$result['add'][] = $item_new;
			}
		}

		foreach($old as $item_old) {
			if(!in_array($item_old, $new)) {
				$result['remove'][] = $item_old;
			}
		}

	} else if(!empty($new) && empty($old)) {
		$result['add'] = $new;
	} else if(empty($new) && !empty($old)) {
		$result['remove'] = $old;
	}

	return $result;
}

/* End of file utility_helper.php */
 
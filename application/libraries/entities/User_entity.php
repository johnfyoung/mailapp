<?php
/**
 * user_entity.php
 *
 * @package mailapp
 * @author johny
 */
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(__DIR__ . '/Base_Entity.php');

/**
 * 
 * User Entity
 * 
 * @package   mailapp
 * @subpackage Libraries 
 * @category Entities
 */
class User_entity extends Base_Entity
{
  public function __construct($data = null) {
    parent::__construct($data);
  }
}
<?php
/**
 * base_entity.php
 *
 * @package mailapp
 * @author johny
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');

class Base_Entity {
  /**
   * The object returned from datastore. Keys vary depending on entity:
   *
   * @var object
   */
  var $_dataobj;

  /**
   *
   * Construct
   *
   * Takes an object as returned by the datastore
   * @param object $data
   */
  public function __construct($data = null)
  {
    if(!empty($data))
    {
      if(is_object($data))
      {
        $this->_dataobj = $data;
      }
      else if(is_array($data))
      {
        $this->_dataobj = $data[0];
      }
    }

  }

  public function __get($key)
  {
    if(!empty($this->_dataobj->{$key}))
    {
      return $this->_dataobj->{$key};
    }

    return null;
  }

	public function to_array() {
		return get_object_vars($this->_dataobj);
	}

  public function __set($key, $value)
  {
    $this->_dataobj->{$key} = $value;
  }

	public function __isset($key) {
		if(isset($this->_dataobj->{$key}) && !empty($this->_dataobj->{$key})) {
			return true;
		}

		return false;
	}
}

/* End of file base_entity.php */
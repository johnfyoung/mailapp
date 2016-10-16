<?php
/**
 * permissions_model.php
 *
 * @package mailapp
 * @author johny
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');
 

class Permissions_model extends MY_Model{
  public function __construct(){
    parent::__construct();

    $this->load->library('entities/permission');
  }

  public function persist_permission($permission) {
    $data = array(
      'userid' => $permission->userid,
      'companyid' => $permission->companyid,
      'site_admin' => $permission->site_admin,
      'user_parent' => $permission->user_parent,
      'user_child' => $permission->user_child
    );

    return $this->persist($data, 'users_permissions');
  }

  public function update_permission($permission) {
    $data = array(
      'companyid' => $permission->companyid,
      'site_admin' => $permission->site_admin,
      'user_parent' => $permission->user_parent,
      'user_child' => $permission->user_child
    );

    return $this->update($data,'users_permissions', array('userid' => $permission->userid));
  }

  public function delete_permission_by_user_id($user_id) {
    return $this->db->delete('users_permissions',array('userid' => $user_id));
  }

  public function get_permission_by_user_id($user_id) {
    $permission = null;

    $this->db->where('userid', $user_id);
    $query = $this->db->get('users_permissions');

    if($query->num_rows() == 1) {
      $permission = new Permission($query->row());
    }

    return $permission;
  }

  public function set_permission_for_user($user_id, $account_number, $site_admin, $is_parent, $is_child) {
    $userPermission = new Permission();
    $userPermission->userid = $user_id;
    $userPermission->companyid = $account_number;
    $userPermission->site_admin = $site_admin;
    $userPermission->user_parent = $is_parent;
    $userPermission->user_child = $is_child;
    $this->persist_permission($userPermission);
  }
}

/* End of file permissions_model.php */ 
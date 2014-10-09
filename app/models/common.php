<?php
class Common extends CI_Model{
	function loggedin(){
		$id = $this->session->userdata("uid");
		if($id){
			return true;
		}
		else{
			return false;
		}
	}

	function isAdmin(){
		$id = $this->session->userdata("uid");

		$user = $this->db->where(array(
			"id" => $id,
			"admin" => 1
		))->count_all_results("users");
	}

	function email_unique($email){
		$user = $this->db->where("email",$email)->count_all_results("users");
		if($user){
			return false;
		}
		else{
			return true;
		}
	}

	function username_unique($username){
		$user = $this->db->where("username",$username)->count_all_results("users");
		if($user){
			return false;
		}
		else{
			return true;
		}
	}
}
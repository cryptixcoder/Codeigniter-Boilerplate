<?php
class MY_Model extends CI_Model{
	protected $table = "";
	protected $primary_id = "id";

	function __construct(){
		parent::__construct();
	}

	function find($id){
		$this->db->where($this->primary_id, $id);
		return $this->db->get($this->table)->row_array();
	}

	function find_where($where, $single = null, $start = null, $limit = null){
		$type = "result_array";
		$this->db->where($where);

		if($single){
			$type = "row_array";
		}

		if($start){
			$this->db->limit($start, $limit);
		}

		return $this->db->get($this->table)->$type();
	}

	function find_slug($slug){
		$this->db->where("slug", $slug);
		return $this->db->get($this->table)->row_array();
	}

	function save($data, $id = null){
		if($id){
			$this->db->where($this->primary_id, $id);
			$this->db->update($this->table, $data);
		}
		else{
			$this->db->insert($this->table, $data);
			$id = $this->db->insert_id();
		}

		return $id;
	}

	function save_where($data, $where){
		$this->db->where($where);
		$this->db->update($this->table, $data);
	}

	function remove($id){
		$this->db->where($this->primary_id, $id);
		$this->db->delete($this->table);
	}

	function remove_where($where){
		$this->db->where($where);
		$this->db->delete($this->table);
	}
}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	public function getListInfo($table,$where,$offset,$perpage=20,$sort="",$field=array()){
		//add cache
		$info = $this->mdb->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get($table);
		return $info;
	}
	public function getInfoCount($table,$where) {
		$info = $this->mdb->where($where)->count($table);
		return $info;
	}
	public function getTableByRef($table,$ref) {
		//add cache
		$info = $this->mdb->getRef($table,$ref);
		return $info;
	}
	public function getInfoAll($table,$where,$sort="") {
		$info = $this->mdb->where($where)->order_by($sort)->get($table);
		if(!empty($info)){
			return $info;
		}else{
			return array();
		}
	}
	public function getInfo($table,$where,$fields=array()) {
		$info = $this->mdb->where($where)->select($fields)->limit(1)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			return array();
		}
	}

	public function getInfoSort($table,$where,$fields=array(),$sort="") {
		$info = $this->mdb->where($where)->order_by($sort)->select($fields)->limit(1)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			return array();
		}
	}
	public function updateSetRecord($table,$where,$update) {
		//add cache
		$check = $this->mdb->where($where)->updateset($table,$update);
		if(!empty($check)){
			return true;
		}else{
			return false;
		}
	}
	public function updateRecord($table,$where,$update) {
		//add cache
		$check = $this->mdb->where($where)->update($table,$update);
		if(!empty($check)){
			return true;
		}else{
			return false;
		}
	}
	public function insertInfo($table,$info){
		$check = $this->mdb->insert($table,$info);
		if(!empty($check)){
			return $check;
		}else{
			return false;
		}
	}
}


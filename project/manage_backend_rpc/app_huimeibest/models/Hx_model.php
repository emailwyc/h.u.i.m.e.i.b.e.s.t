<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hx_model extends CI_Model {
	public $table = "hx_history";

	function __construct() {
		parent::__construct();
	}
	

	public function insertInfo($table,$info){
		$check = $this->mdb->insert($table,$info);
		if(!empty($check)){
			return $check;
		}else{
			return false;
		}
	}

	public function getInfo($table,$where) {
		$info = $this->mdb->where($where)->limit(1)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			array();
		}
	}

	public function getRefInfo($table,$ref) {
		$info = $this->mdb->getRef($table,$ref);
		if(!empty($info)){
			return $info;
		}else{
			array();
		}
	}

	public function getInfoAll($table,$where) {
		$info = $this->mdb->where($where)->get($table);
		if(!empty($info)){
			return $info;
		}else{
			array();
		}
	}

	public function getInfoCount($table,$where) {
		$info = $this->mdb->where($where)->count($table);
		return $info;
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

	public function getListInfo($table,$where,$offset,$perpage=20,$sort="",$field=array()){
		//add cache
		$info = $this->mdb->or_where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get($table);
		return $info;
	}

	public function getListInfo1($table,$where,$offset,$perpage=20,$sort="",$field=array()){
		//add cache
		$info = $this->mdb->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get($table);
		return $info;
	}



}


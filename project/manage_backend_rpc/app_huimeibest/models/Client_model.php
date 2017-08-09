<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Client_Model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	public function getInfo($table,$where,$fields=array()) {
		$info = $this->mdb->where($where)->select($fields)->limit(1)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			return array();
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



}


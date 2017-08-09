<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
	public $table = "user";

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 根据条件手机验证信息
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getUserByMobile($mobile,$table="user",$reg_type) {
		//add cache
		$where = array('mobile'=>$mobile,'from'=>$reg_type);
		$info = $this->mdb->where($where)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			return array();
		}
	}

	public function insertUserInfo($info){
		$check = $this->mdb->insert('user',$info);
		if(!empty($check)){
			return $check;
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

	public function getUserInfo($openid) {
		$where = array("_id"=>getMdbId($openid));
		$info = $this->mdb->where($where)->limit(1)->get('patient');
		if(!empty($info)){
			return $info[0];
		}else{
			array();
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
			return array();
		}
	}

	public function getInfoAll($table,$where) {
		$info = $this->mdb->where($where)->get($table);
		if(!empty($info)){
			return $info;
		}else{
			return array();
		}
	}

	public function getInfoCount($table,$where) {
		$info = $this->mdb->where($where)->count($table);
		return $info;
	}

	/**
	 * 得到默认患者
	 */
	public function getPatientFamily($id) {
		$where = array("openid"=>$id);
		$info = $this->mdb->where($where)->order_by(array('isdefault'=>-1))->get('patient_family');
		if(!empty($info)){
			return $info;
		}else{
			array();
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

	public function getListInfo($table,$where,$offset,$perpage=20,$sort="",$field=array()){
		//add cache
		$info = $this->mdb->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get($table);
		return $info;
	}

	public function delInfo($table,$where) {
		$info = $this->mdb->where($where)->delete($table);
		if(!empty($info)){
			return $info;
		}else{
			array();
		}
	}



}


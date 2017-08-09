<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Msg_model extends CI_Model {
	public $table = "user_msg";

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
	public function getMobileInfo($where) {
		//add cache
		$info = $this->mdb->where($where)->get('user_msg');
		if(!empty($info)){
			return $info[0];
		}else{
			array();
		}
	}

	/**
	 * 插入手机验证信息
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function insertMobileMsg($info) {
		//add cache
		$check = $this->mdb->insert('user_msg',$info);
		if(!empty($check)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 更新手机验证信息
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function updateMobileMsg($id,$update) {
		//add cache
		$id = getMdbId($id);
		$where = array('_id'=>$id);
		$check = $this->mdb->where($where)->update('user_msg',$update);
		if(!empty($check)){
			return true;
		}else{
			return false;
		}
	}


}


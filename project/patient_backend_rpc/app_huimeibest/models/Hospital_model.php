<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hospital_Model extends CI_Model {

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
	
	/**
	 * 得到医院筛选
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getHospitalLook($field,$limit){
		//add cache
		$where = array('status'=>1);
		$order = array("order"=>1);
		$info = $this->mdb->where($where)->order_by($order)->select($field)->limit($limit)->get('hospital');
		return $info;
	}

	public function getDoctorByWhere($where,$fields=array()) {
		//add cache
		$info = $this->mdb->where($where)->select($fields)->get('hospital');
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

	public function getHosByWhere($where,$field=array(),$limit=300){
		//add cache
		$order = array("order"=>1);
		$info = $this->mdb->where($where)->order_by($order)->select($field)->limit($limit)->get('hospital');
		return $info;
	}

}


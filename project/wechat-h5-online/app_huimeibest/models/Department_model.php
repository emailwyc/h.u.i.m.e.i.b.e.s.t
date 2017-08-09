<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Department_Model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 获取数据
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getDepartment(){
		//add cache
		$where = array("tags"=>1);
		$order = array("order"=>-1);
		$info = $this->mdb->where($where)->order_by($order)->limit(6)->get('department');
		return $info;
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
	 * 获取数据
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getDepartmentLook($field,$limit){
		//add cache
		$where = array('parent'=>array('$exists'=>0));
		$order = array("tags"=>-1,"order"=>-1);
		$info = $this->mdb->where($where)->order_by($order)->select($field)->limit($limit)->get('department');
		return $info;
	}

	/**
	 * 获取数据
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getAllDepartment($field,$limit){
		//add cache
		$where = array('status'=>1);
		$order = array("tags"=>-1,"order"=>1);
		$info = $this->mdb->where($where)->order_by($order)->select($field)->limit($limit)->get('department');
		return $info;
	}

	/**
	 * 获取数据
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getSecondDepartment($field,$limit,$parent_id){
		//add cache
		$where = array('status'=>1,'parent'=>$parent_id);
		$order = array("order"=>1);
		$info = $this->mdb->where($where)->order_by($order)->select($field)->limit($limit)->get('department');
		return $info;
	}

	public function getTjDepartment($field,$limit=10){
		//add cache
		$where = array();
		$order = array('tags'=>1,"order"=>-1);
		$info = $this->mdb->where($where)->order_by($order)->select($field)->limit($limit)->get('department');
		return $info;
	}

}


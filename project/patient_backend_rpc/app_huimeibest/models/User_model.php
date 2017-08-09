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
	//验证app运行记录
	public function checkRunRecord($device_id,$agent,$user_id='')
	{
		$recordWhere = array('device_id'=>$device_id,'user_id'=>$user_id);
		$recordInfo = $this->Common_model->getInfo('pat_app_record',$recordWhere);
		if(empty($recordInfo)){
			$insertDate = array('device_id'=>$device_id,'user_id'=>$user_id,'agent'=>$agent,'active_time'=>time());
			$this->Common_model->insertInfo('pat_app_record',$insertDate);
		}else{
			$updateWhere = array('_id'=>$recordInfo['_id']);
			$this->Common_model->updateRecord('pat_app_record',$updateWhere,array('active_time'=>time()));
		}
	}

	/*用户登陆刷新运行记录
		 * 1:查看是否有当前设备的记录（user_id不存在）
		 * 2：记录存在针对该记录添加用户id，刷新时间
		 * 3：查看是否有设备id，user_id同事存在的记录，存在刷新时间;*/
	public function refreshRunLog($device_id,$user_id,$agent)
	{
		$recordWhere = array('device_id'=>$device_id,'user_id'=>array('$exists'=>-1));
		$recordInfo = $this->Common_model->getInfo('pat_app_record',$recordWhere);
		if($recordInfo){
			$updateWhere = array('_id'=>$recordInfo['_id']);
			$this->Common_model->updateRecord('pat_app_record',$updateWhere,array('user_id'=>$user_id,'active_time'=>time()));
		}else{
			$recordWhere = array('device_id'=>$device_id,'user_id'=>$user_id);
			$recordInfo = $this->Common_model->getInfo('pat_app_record',$recordWhere);
			if($recordInfo){
				$updateWhere = array('_id'=>$recordInfo['_id']);
				$this->Common_model->updateRecord('pat_app_record',$updateWhere,array('active_time'=>time()));
			}
		}
	}
}


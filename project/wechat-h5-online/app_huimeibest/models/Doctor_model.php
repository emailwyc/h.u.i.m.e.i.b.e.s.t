<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Doctor_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 得到医生列表
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getDoctor($department) {
		//add cache
		$where = array();
		if(!empty($department)){
			$where['department_id'] = getMdbId($department);
		}
		$order = array();
		$info = $this->mdb->where($where)->order_by($order)->get('doctor');
		return $info;
	}

	/**
	 * 根据条件得到医生
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getDoctorByRef($ref) {
		//add cache
		$info = $this->mdb->getRef('doctor',$ref);
		return $info;
	}

	public function getTableByRef($table,$ref) {
		//add cache
		$info = $this->mdb->getRef($table,$ref);
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
	 * 根据条件得到医生
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getDoctorByWhere($where,$fields=array()) {
		//add cache
		$info = $this->mdb->where($where)->select($fields)->get('doctor');
		return $info;
	}

	public function getInfoByWhere($table,$where,$fields=array()) {
		//add cache
		$info = $this->mdb->where($where)->select($fields)->get($table);
		return $info;
	}

	public function getPhonecall($where,$fields=array(),$sort=array()){
		$date_H = date("H:i",time());
		$date_D = (string)date("Y-m-d",time());
		$info = $this->mdb->where($where)->order_by($sort)->select($fields)->get("doctor_timetable");
		$result = array();
		$result1 = array();
		if($info){ 
			$kv = array();
			foreach($info as $key=>$val){
				$showt = date('m月d日',strtotime($val['date']));
				$week = !empty($this->date_model->week1[$val['weekday']])?$this->date_model->week1[$val['weekday']]:"未知";
				$slot_time = explode(',',$val['interval']);
				// 
				if($date_D==$val['date'] && $date_H>$slot_time[0]){ continue; }

				if($slot_time[1]<="12:00"){
					$slot = 1;
				}elseif($slot_time[1]>"12:00" && $slot_time[1]<="18:00"){
					$slot = 2;
				}else{
					$slot = 3;
				}
				$result[$val['date']]['showt'] = $showt;
				$result[$val['date']]['week'] = $week;
				$result[$val['date']][$slot] = $val;
			} 
			foreach($result as $v){
				$result1[] = $v;
			}
		}
		return $result1;
	}

	/**
	 * getDoctorByReq($dep,$perpage,$offset,$sort); 
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getDoctorByReq($find,$offset,$perpage=20,$sort=""){
		//add cache
		$where = array();
		if(!empty($find['depk'])){
			$where[$find['depk']] = $find['depv'];
		}
		if(!empty($find['hosk'])){
			$where[$find['hosk']] = $find['hosv'];
		}
		$field = array();
		if(empty($find['kw'])){
			$where['freeze'] = "no";
			$info = $this->mdb->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get('doctor');
		}else{
			if($find['kw']=="hm12321"){
				$info = $this->mdb->where(array('freeze'=>'yes'))->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get('doctor');
			}else{
				$where['freeze'] = "no";
				$info = $this->mdb->likes('name',$find['kw'])->likes('department',$find['kw'])->likes('hospital',$find['kw'])->likes('speciality',$find['kw'])->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get('doctor');
			}
		}
		$infos = array();
		$ref_arr = array();
		if($info){
			foreach($info as $v){
				$infos[(string)$v['_id']] = $v;
				$ref_arr[] = MongoDBRef::create("doctor", $v['_id']);
			}
		}
		return array('doc'=>$infos,'doc_ref'=>$ref_arr);
	}

	/**
	 * getDoctorByKw
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getDoctorByKw($kw,$offset,$perpage=20,$sort=""){
		//add cache
		$field = array();
		$infos = array();
		$ref_arr = array();
		if($info){
			foreach($info as $v){
				$infos[(string)$v['_id']] = $v;
				$ref_arr[] = MongoDBRef::create("doctor", $v['_id']);
			}
		}
		return array('doc'=>$infos,'doc_ref'=>$ref_arr);
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

	public function timeTableDeal($doctors) {
		$s_date = date('Y-m-d',time());
		$e_date = $this->Date_model->getSelEndDate();
		if(!empty($doctors['doc_ref'])){
			$dtb_where = array('date'=>array('$gte'=>$s_date,'$lte'=>$e_date),'remain'=>array('$gte'=>1),'doctor'=>array('$in'=>$doctors['doc_ref']),'service'=>'clinic');
			$sub_date = $this->getInfoByWhere('doctor_timetable',$dtb_where,array('doctor','date','price','remain','interval'));
			if(!empty($sub_date)){
				foreach($sub_date as $val){
					if(empty($doctors['doc'][(string)$val['doctor']['$id']]['issub'])){
						if($s_date!=$val['date']){
							$doctors['doc'][(string)$val['doctor']['$id']]['issub'] = "1";
						}else{
							$date_H = date("H",time());
							$temTimeEnd = explode(',',$val['interval']);
							if($temTimeEnd[1] <="12:00" && $date_H<11){
								$doctors['doc'][(string)$val['doctor']['$id']]['issub'] = "1";
							}elseif($temTimeEnd[1]>"12:00" && $temTimeEnd[1] <="18:00" && $date_H<16){
								$doctors['doc'][(string)$val['doctor']['$id']]['issub'] = "1";
							}
						}
					
					}
				}
			}
		}
		return $doctors;
	}

	public function timeTableDealByMobile($doctors) {
		$s_date = date('Y-m-d',time());
		$e_date = $this->Date_model->getSelEndDate();
		if(!empty($doctors['doc_ref'])){
			$dtb_where = array('date'=>array('$gte'=>$s_date,'$lte'=>$e_date),'remain'=>array('$gte'=>1),'minutes_remain'=>array('$gte'=>5),'doctor'=>array('$in'=>$doctors['doc_ref']),'service'=>'phonecall');
			$sub_date = $this->getInfoByWhere('doctor_timetable',$dtb_where,array('doctor','date','interval','quantity','minutes_remain'));
			if(!empty($sub_date)){
				foreach($sub_date as $val){
					if(empty($doctors['doc'][(string)$val['doctor']['$id']]['isph']) && $doctors['doc'][(string)$val['doctor']['$id']]['service_provided']['phonecall']['on'] && $val['minutes_remain'] >= $doctors['doc'][(string)$val['doctor']['$id']]['service_provided']['phonecall']['minutes_min']){
						if($s_date!=$val['date']){
							$doctors['doc'][(string)$val['doctor']['$id']]['isph'] = "1";
						}else{
							$date_H = date("H:i",time());
							$day_H = explode(',',$val['interval']);
							if($date_H<$day_H[0]){
								$doctors['doc'][(string)$val['doctor']['$id']]['isph'] = "1";
							}
						}
					
					}
				}
			}
		}
		return $doctors;
	}
	public function timeTableDealOneByMobile($doctor) {
		if(!empty($doctor['service_provided']['phonecall']['on'])){
			$s_date1 = date('Y-m-d',time());
			$e_date = $this->date_model->getSelEndDate();
			$docRef = MongoDBRef::create("doctor", $doctor['_id']); 
			$where1 = array('date'=>array('$gte'=>$s_date1,'$lte'=>$e_date),'remain'=>array('$gte'=>1),'minutes_remain'=>array('$gte'=>5),'doctor'=>$docRef,'service'=>"phonecall");
			$doctable = $this->getInfoByWhere('doctor_timetable',$where1,array());
			$doctable1 = 0;
			if($doctable){
				foreach($doctable as $val){
					if($s_date1!=$val['date'] &&  $val['minutes_remain'] >= $doctor['service_provided']['phonecall']['minutes_min']){
						$doctable1 = 1;break;
					}else{
						$date_H = date("H:i",time());
						$day_H = explode(',',$val['interval']);
						if($date_H<$day_H[0] && $val['minutes_remain'] >= $doctor['service_provided']['phonecall']['minutes_min']){
							$doctable1=1;
						}
					}
					if($doctable1==1){
						break;
					}
				}
			}
			$issub = $doctable1;
		}else{
			$issub = 0;
		}
		return $issub;
	}

	public function timeTableDealOne($doctor) {
		if(!empty($doctor['service_provided']['clinic']['on'])){
			$s_date1 = date('Y-m-d',time());
			$e_date = $this->date_model->getSelEndDate();
			$docRef = MongoDBRef::create("doctor", $doctor['_id']); 
			$where1 = array('date'=>array('$gte'=>$s_date1,'$lte'=>$e_date),'remain'=>array('$gte'=>1),'doctor'=>$docRef,'service'=>"clinic");
			$doctable = $this->getInfoByWhere('doctor_timetable',$where1,array());
			$doctable1 = 0;
			if($doctable){
				foreach($doctable as $val){
					if($s_date1!=$val['date']){
						$doctable1 = 1;break;
					}else{
						$date_H = date("H",time());
						$temTimeEnd = explode(',',$val['interval']);
						if($temTimeEnd[1] <="12:00" && $date_H<11){
							$doctable1 = 1;break;
						}elseif($temTimeEnd[1]>"12:00" && $temTimeEnd[1] <="18:00" && $date_H<16){
							$doctable1 = 1;break;
						}
					}
				}
			}
			$issub = $doctable1;
		}else{
			$issub = 0;
		}
		return $issub;
	}


}


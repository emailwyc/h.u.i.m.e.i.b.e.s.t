<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Home.php
 * 科室选择
 */

class Home extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('department_model');
		$this->load->model('doctor_model');
		$this->load->model('hospital_model');
		$this->load->model('region_model');
		$this->load->model('Date_model');
	}

    /** 
     * 科室列表
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function index() {
		/*
		if(@$_GET['shubo']){
			$_SESSION['pid'] = "56d969bdb7ef6a573a8b459e";
			$_SESSION['aid'] = "oV5DDvlDkSaW8jtkOa4QYK4MWgwI";
			$_SESSION['st'] = 1;
		}
		*/
		$this->Login_model->weixinLoginCheck("home/index");
		$data['info'] = $this->department_model->getDepartment();
		//log_message('error', 'error message.');
		$data['cur_nav'] = "home";
		$this->load->view('home/index',$data);
	}

	public function recdoc(){
		if(!$this->input->is_ajax_request()){ echo 0;exit; }
		$date1 = "2016-04-11";//周六
		$date2 = "2016-04-23";//周六
		$curdate = date("Y-m-d",time());
		$result = array();
		if($curdate < $date2){
			$dateInfo = $this->Date_model->getWeekTime($curdate);
			$info = $this->Common_model->getListInfo('act_free',array('endT'=>array('$gte'=>$dateInfo['start'],'$lte'=>$dateInfo['end'])),0,20,array('endT'=>1));
			if($info){
				foreach($info as $k=>$v){
					$docInfo = $this->Common_model->getInfo('doctor',array('_id'=>$v['doctor']));
					if(!empty($docInfo)){
						$info[$k]['doctor'] = $docInfo;
						if($v['endT']>=$curdate){
							$info[$k]['able'] = 1;
							$result[] = $info[$k];
						}else{
							$info[$k]['able'] = 0;
						}
					}
				}
				foreach($info as $i=>$j){
					if($j['able']==0){
						$result[] = $j;
					}
				}
			}
		}else{
			$info = $this->Common_model->getListInfo('act_free',array('endT'=>array('$gte'=>$date1,'$lte'=>$date2)),0,20,array('endT'=>1));
			foreach($info as $k=>$v){
				$docInfo = $this->Common_model->getInfo('doctor',array('_id'=>$v['doctor']));
				if(!empty($docInfo)){
					$info[$k]['doctor'] = $docInfo;
					$info[$k]['able'] = 0;
					$result[] = $info[$k];
				}
			}
		}
		echo json_encode($result);exit;
	}

    /** 
     * 医生列表
     * 
     * @param  $status page dep hos kw sort
     * @access public
     * @return void
     */
	public function doctor() {
		$page = getCurPage(); $perpage = 10;
		$offset = getPage($page,$perpage);
		$setting = getSelParams();
		$sort_arr = array();
		if(!empty($setting['find']['sort'])) $sort_arr = array($setting['find']['sort']=>'desc');
		//根据科室|关键字得到医生列表
		if(!empty($setting['find']['depk'])){ $department = $this->department_model->getInfo("department",array('_id'=>$setting['find']['depv']));}
		$data['dep_name'] = isset($department['name'])?$department['name']:"全部科室";
		if(!empty($setting['find']['hosk'])){ $hospital= $this->hospital_model->getInfo($setting['find']['host'],array('_id'=>$setting['find']['hosv']));}
		$data['hos_name'] = isset($hospital['name'])?$hospital['name']:"全部医院";
		$data['setting'] = $setting['view'];
		$this->load->view('home/doctor',$data);
	}
	//得到数据
	public function doctorJson() {
		$page = getCurPage(); $perpage = 10;
		$offset = getPage($page,$perpage);
		$setting = getSelParams();
		$sort_arr = array();
		if(!empty($setting['find']['sort'])) $sort_arr = array($setting['find']['sort']=>'desc');
		//根据科室|关键字得到医生列表
		$data['setting'] = $setting['view'];
		$doctors = $this->doctor_model->getDoctorByReq($setting['find'],$offset,$perpage,$sort_arr);
		//处理医生是否预约已满start
		$doctors = $this->doctor_model->timeTableDeal($doctors);
		$doctors = $this->doctor_model->timeTableDealByMobile($doctors);
		echo json_encode($doctors['doc']);
	}

    /** 
     * 选择科室
     * @param  $status page dep hos kw sort
     * @access public
     * @return void
     */
	public function seldep($st=1) {
		//$this->output->cache(300);
		$setting = getSelParams();
		if(!empty($setting['find']['depk']) && $setting['find']['depk'] == "department_child_id"){
			$pInfo = $this->Common_model->getInfo("department",array('_id'=>$setting['find']['depv']));
			if(!empty($pInfo)){ $setting['view']['dep'] = (string)$pInfo['parent']."_1"; }
		}
		$data['setting'] = $setting['view'];
		if($st==1){ $data['back_url'] = "/home/doctor"; }else{ $data['back_url'] = "/home/doctor"; }
		$dep = $this->department_model->getDepartmentLook(array(),100);
		$data['dep'] = $dep;
		$this->load->view('home/seldep',$data);
	}

    /** 
     * 选择科室
     * @param  $status page dep hos kw sort
     * @access public
     * @return void
     */
	public function selhos($st=1) {
		//$this->output->cache(300);
		$setting = getSelParams();
		$data['setting'] = $setting['view'];
		if($st==1){ $data['back_url'] = "/home/doctor"; }else{ $data['back_url'] = "/home/doctor"; }
		$area= $this->region_model->getFirstArea(array(),300);
		$data['area'] = $area;
		$this->load->view('home/selhos',$data);
	}

	private function test() {
		$Info = $this->doctor_model->getDoctorByWhere(array());
		foreach($Info as $k=>$v){
			$where = array('_id'=>$v['_id']);
			$uparr = array('$set'=>array('comment'=>array('star'=>0,'num'=>0,'per'=>100)));
			$this->doctor_model->updateSetRecord('doctor',$where,$uparr);
		}
	}

	private function test1() {
		$Info = $this->hospital_model->getDoctorByWhere(array());
		$areaid = getMdbId("562a7191f154d62f2b8b457f");//北京
		foreach($Info as $k=>$v){
			$where = array('_id'=>$v['_id']);
			$update = array('region'=>$areaid);
			$this->hospital_model->updateRecord('hospital',$where,$update);
		}
	}

	public function show_error() {
		$key = isset($_GET['k'])?substr($_GET['k'],0,200):"参数错误!";
		show_error($key);
	}



}

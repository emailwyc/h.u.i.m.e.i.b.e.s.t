<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User.php
 * 用户相关
 */

class User extends CI_Controller {
	public $uid;
	public $contact_num=100;

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->uid = checkAuth3();
		$this->openid = getPatientOpenid();
		$this->load->model('user_model');
		$this->load->model('doctor_model');
		$this->load->model('Order_model');
		$this->load->model('Chat_model');
		$this->load->model('Date_model');
	}
	public function index(){
		$data['patient'] = $this->user_model->getUserInfo($this->uid);
		$data['fescoInfo'] = $this->Common_model->getInfo('fesco_user',array('_id'=>getMdbId($this->openid)));
		$data['cur_nav'] = "my";
		$this->load->view('user/index',$data);
	}

    /** 
     * 添加就诊人
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function addPatient() {
		$btn = $this->input->post();
		$data['msg'] = "";
		if(isset($btn['btn1'])){
			//处理
			if(!empty($btn['isdefault'])){ $btn['isdefault'] = 1; }else{ $btn['isdefault'] = 0;}
			if(isset($btn['sex'])){
			   	$btn['sex'] = (int)$btn['sex']==1 ? "male":"female";
		   	}else{ $btn['sex'] = "male"; }
			if(empty($btn['name']) || empty($btn['age']) || empty($btn['mobile'])){
				$data['msg'] = "提交参数错误，请检查时候为空！";
			}
			if(!empty($btn['age']) && ($btn['age'] <=0 || $btn['age'] >120)){
				$data['msg'] = "年龄错误，请检查！";
			}
			if(!preg_match(config_item('global_mobile_format'), $btn['mobile'])){
				$data['msg'] = "手机号码格式错误，请检查！";
			}
			$btn['name'] = trim($btn['name']);
			$btn['relation'] = addslashes(trim($btn['relation']));
			if(empty($data['msg'])){
				$where = array('openid'=>$this->openid,'name'=>$btn['name']);
				$fname = $this->user_model->getInfo('patient_family',$where);
				if(!empty($fname)){
					$data['msg'] = "该成员已经存在，请检查！";
				}
			}
			if(empty($data['msg'])){
				$where = array('openid'=>$this->openid);
				$fnum = $this->user_model->getInfoCount('patient_family',$where);
				if($fnum>=$this->contact_num){
					$data['msg'] = "最多可添加！".$this->contact_num."家庭联系人!";
				}
			}
			if(empty($data['msg'])){
				$cur_date = new MongoDate(time());
				$userInfo = $this->user_model->getUserInfo($this->uid);
				if(empty($userInfo)){
					show_error("error");
				}
				$pref= MongoDBRef::create("patient", $userInfo['_id']);
				$info = array(
						'patient'=>$pref,
						"openid"=>$this->openid,
						"name"=>$btn['name'],
						"mobile"=>$btn['mobile'],
						"gender"=>$btn['sex'],
						"age"=>(int)$btn['age'],
						"relation"=>$btn['relation'],
						'idcard'=>@(string)trim($btn['idcard']),
						"created_at"=>$cur_date,
						"updated_at"=>$cur_date,
						"isdefault" =>$btn['isdefault'],
						"from" =>"fesco"
						);
				$id = $this->user_model->insertInfo('patient_family',$info);
				if(!empty($btn['isdefault'])){
					$where = array('openid'=>$this->openid,'name'=>array('$ne'=>$btn['name']));
					$update = array('isdefault'=>0);
					$this->user_model->updateRecord('patient_family',$where,$update);
				}
				if(!$id){ $data['msg'] = "添加失败"; }else{
					//跳转
					redirect($btn['from11']);
				}
				
			}

		}
		if(!isset($btn['from11'])){
			$data['from'] = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"/";
		}else{
			$data['from'] = $btn['from11'];
		}
		$this->load->view('user/addpatient',$data);
	}

	public function checkPatient() {
		$name= $this->input->post('name');
		$name = trim($name);
		if(empty($name)){ echo 3;exit; }
		$where = array('openid'=>$this->openid,'name'=>$name);
		$fname = $this->user_model->getInfo('patient_family',$where);
		if(!empty($fname)){ echo 2;exit; }else{ echo 1;exit; }
	}

	public function editPat($pid="") {
		$btn = $this->input->post();
		$data['msg'] = "";
		$pid1 = getMdbId($pid);
		if(empty($pid)){ $data['msg'] = "参数错误，请检查！";}
		if(isset($btn['btn1']) && !empty($pid)){
			//处理
			if(!empty($btn['isdefault'])){ $btn['isdefault'] = 1; }else{ $btn['isdefault'] = 0;}
			if(!preg_match(config_item('global_mobile_format'), $btn['mobile'])){
				$data['msg'] = "手机号码格式错误，请检查！";
			}
			$idcard = trim($btn['idcard']);
			if(empty($data['msg'])){
				$cur_date = new MongoDate(time());
				$info = array(
						"mobile"=>$btn['mobile'],
						"idcard"=>$idcard,
						"updated_at"=>$cur_date,
						"isdefault" =>$btn['isdefault']
						);
				$id = $this->user_model->updateRecord('patient_family',array("_id"=>$pid1),$info);
				if(!empty($btn['isdefault'])){
					$where = array('openid'=>$this->openid,'_id'=>array('$ne'=>$pid1));
					$update = array('isdefault'=>0);
					$this->user_model->updateRecord('patient_family',$where,$update);
				}
				if(!$id){ $data['msg'] = "编辑失败"; }else{
					redirect($btn['from11']);
				}
				
			}

		}
		$data['from'] = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"/";
		$data['pid'] = $pid;
		$pInfo = $this->user_model->getInfo('patient_family',array("_id"=>$pid1));
		if(empty($pInfo)){ show_error("未找到联系人"); }//错误跳转
		if(!checkPatIsSelf((string)$pInfo["patient"]['$id'])){ show_error("不是本人订单，不允许操作"); }
		//隐藏手机号码
		
		$data['info'] = $pInfo;
		$this->load->view('user/editpat',$data);
	}

	//家庭联系人
	public function contact($isdata="") {
		$this->Login_model->weixinLoginCheck("user/contact");
		$data['climit'] = $this->contact_num;
		$where = array("openid"=>$this->openid);
		if($isdata){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			$gzInfo = $this->user_model->getListInfo('patient_family',$where,$offset,$perpage,array("_id"=>-1));
			if($gzInfo){ foreach($gzInfo as $k=>$v){
				$idstr = strlen($v['idcard']); $mbstr = strlen($v['mobile']);
					if($idstr>10){ $gzInfo[$k]['idcard'] = substr_replace($v['idcard'],str_repeat('*',($idstr-7)),3,-4); }
					if($mbstr>10){ $gzInfo[$k]['mobile'] =substr_replace($v['mobile'],"****",3,-4); }

			} }
			echo json_encode($gzInfo);exit;
		}
		$data['patnum']= $this->user_model->getInfoCount('patient_family',$where);
		$this->load->view('user/contact',$data);
	}

	//优惠券
	public function coupons($isdata="") {
		if($isdata){
			$cur_date = date('Y-m-d',time());
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			$where = array("openid"=>$this->openid);
			$gzInfo = $this->user_model->getListInfo('user_coupons',$where,$offset,$perpage,array("st"=>-1,'_id'=>-1));
			if($gzInfo){ foreach($gzInfo as $k=>$v){
					$gzInfo[$k]['star_time'] = date('Y-m-d',$v['star_time']);
					$gzInfo[$k]['end_time'] = date('Y-m-d',$v['end_time']);
					if($cur_date>date('Y-m-d',$v['end_time'])){
						$gzInfo[$k]['outtime'] = 0;
					}else{
						$gzInfo[$k]['outtime'] = 1;
					}
			} }
			echo json_encode($gzInfo);exit;
		}
		$this->Login_model->weixinLoginCheck("user/coupons");
		$this->load->view('user/coupons');
	}

	/*
	//删除联系人
	public function delcontact($id="") {
		if(empty($id)){ show_error("参数有误");}
		$uId = getMdbId($id);
		$where = array('_id'=>$uId,'openid'=>$this->uid);
		$userInfo = $this->user_model->getInfo('patient_family',$where);
		if(!checkPatIsSelf((string)$userInfo["patient"]['$id'])){ show_error("不是本人订单，不允许操作"); }
		if(empty($userInfo)){ show_error("未找到该家庭联系人，请确认！");}else{
			$check = $this->user_model->delInfo('patient_family',array('_id'=>$uId));
			redirect("/user/contact");
		}
	}
	*/

	//我的医生
	public function doctor($isdata="") {
		if($isdata){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			//得到用户关注列表
			$where = array('openid'=>$this->openid,"st"=>"1");
			$gzInfo = $this->user_model->getListInfo('doctor_fans',$where,$offset,$perpage,array("_id"=>-1));
			$gzIds = getFieldArr($gzInfo,'tid');
			//得到医生信息
			$fields = array();
			$docInfo = $this->doctor_model->getDoctorByWhere(array('_id'=>array('$in'=>$gzIds)),$fields);

			$ref_arr = array();
			$infos = array();
			if($docInfo){
				foreach($docInfo as $v){
					$infos[(string)$v['_id']] = $v;
					$ref_arr[] = MongoDBRef::create("doctor", $v['_id']);
				}
			}
			$doctors = array('doc'=>$infos,'doc_ref'=>$ref_arr);
			//处理医生是否预约已满start
			$doctors = $this->doctor_model->timeTableDeal($doctors);
			$doctors = $this->doctor_model->timeTableDealByMobile($doctors);
			$data = !empty($doctors['doc'])?arrSort($gzIds,$doctors['doc']):array();
			echo json_encode($data);exit;
		}
		//排序
		$this->Login_model->weixinLoginCheck("user/doctor");
		$data['test'] = 1;
		$this->load->view('user/doctor',$data);
	}

	//我的服务
	public function service($isJson="") {
		if($isJson){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			//得到订单
			$pref= MongoDBRef::create("patient", getMdbId($this->uid));
			$where = array('patient'=>$pref,'status'=>array('$nin'=>array("新订单","已取消")),'service'=>'consult');
			$gzInfo = $this->user_model->getListInfo('order',$where,$offset,$perpage,array('nomsg'=>-1,"status"=>-1,"updated_at"=>-1));
			$gzIds = getFieldArr1($gzInfo);
			$fields = array('name','avatar');
			$docInfo = $this->doctor_model->getDoctorByWhere(array('_id'=>array('$in'=>$gzIds)),$fields);
			$docInfo = ArrKeyFromId($docInfo);
			foreach($gzInfo as $k=>$v){
				if(!empty($docInfo[(string)$v['doctor']['$id']])){
					$gzInfo[$k]['doc_name'] = $docInfo[(string)$v['doctor']['$id']]['name'];
				}else{
					$gzInfo[$k]['doc_name'] = ""; 
				}
				$status = $this->Order_model->getOrderStatus($v['service'],$v['status']);
				$gzInfo[$k]['stat'] = !empty($status)?$status:"错误状态";
				$gzInfo[$k]['tm'] = @date('Y-m-d',(string)$v['created_at']->sec);
			}
			echo json_encode($gzInfo);exit;
		}
		$this->Login_model->weixinLoginCheck("user/service");
		$data['hx_user'] = $this->uid;
		$data['hx_pass'] = md5($this->uid."hmjz");
		$data['hx_appk'] = config_item('global_hx_appkey');
		$data['cur_nav'] = "service";
		$this->load->view('user/service',$data);
	}

	//我的服务
	public function servicepc($isJson="") {
		if($isJson){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			//得到订单
			$pref= MongoDBRef::create("patient", getMdbId($this->uid));
			$where = array('patient'=>$pref,'status'=>array('$nin'=>array("新订单","已取消")),'service'=>'phonecall');
			$gzInfo = $this->user_model->getListInfo('order',$where,$offset,$perpage,array("updated_at"=>-1));
			$gzIds = getFieldArr1($gzInfo);
			$fields = array('name','avatar');
			$docInfo = $this->doctor_model->getDoctorByWhere(array('_id'=>array('$in'=>$gzIds)),$fields);
			$docInfo = ArrKeyFromId($docInfo);
			foreach($gzInfo as $k=>$v){
				if(!empty($docInfo[(string)$v['doctor']['$id']])){
					$gzInfo[$k]['doc_name'] = $docInfo[(string)$v['doctor']['$id']]['name'];
				}else{ $gzInfo[$k]['doc_name'] = ""; }
				$status = $this->Order_model->getOrderStatus($v['service'],$v['status']);
				$gzInfo[$k]['stat'] = !empty($status)?$status:"错误状态";
				$gzInfo[$k]['tm'] = @date('Y-m-d',(string)$v['created_at']->sec);
			}
			echo json_encode($gzInfo);exit;
		}
		$data['cur_nav'] = "service";
		$this->load->view('user/servicepc',$data);
	}

	//我的服务
	public function servicecl($isJson="") {
		if($isJson){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			//得到订单
			$pref= MongoDBRef::create("patient", getMdbId($this->uid));
			$where = array('patient'=>$pref,'status'=>array('$nin'=>array("新订单","已取消")),'service'=>'clinic');
			$gzInfo = $this->user_model->getListInfo('order',$where,$offset,$perpage,array("updated_at"=>-1));
			$gzIds = getFieldArr1($gzInfo);
			$fields = array('name','avatar');
			$docInfo = $this->doctor_model->getDoctorByWhere(array('_id'=>array('$in'=>$gzIds)),$fields);
			$docInfo = ArrKeyFromId($docInfo);
			foreach($gzInfo as $k=>$v){
				if(!empty($docInfo[(string)$v['doctor']['$id']])){
					$gzInfo[$k]['doc_name'] = $docInfo[(string)$v['doctor']['$id']]['name'];
				}else{ $gzInfo[$k]['doc_name'] = ""; }
				$status = $this->Order_model->getOrderStatus($v['service'],$v['status']);
				$gzInfo[$k]['stat'] = !empty($status)?$status:"错误状态";
				$gzInfo[$k]['tm'] = @date('Y-m-d',(string)$v['created_at']->sec);
			}
			echo json_encode($gzInfo);exit;
		}
		$data['cur_nav'] = "service";
		$this->load->view('user/servicecl',$data);
	}

	//服务订单
	public function clinicser($st=1,$sid="",$check="") {
		if($check){
			$this->Login_model->weixinLoginCheck("user/clinicser/".$st."/".$sid);
		}
		$sid = getMdbId($sid);
		$info = $this->Order_model->getInfo('order',array('_id'=>$sid));
		if(empty($info)){ log_message('error',"订单没有找到"); show_error("订单没有找到"); }
		$status = $this->Order_model->getOrderStatus($info['service'],$info['status']);
		$info['stat'] = !empty($status)?$status:"错误状态";
		$info['tm'] = @date('Y-m-d',(string)$info['created_at']->sec);
		$data['info'] = $info;
		$data['doctor'] = $this->doctor_model->getDoctorByRef($info['doctor']);
		$data['doctor_table'] = $this->doctor_model->getTableByRef('doctor_timetable',$info['doctor_timetable']);
		$data['imgs'] = getThumbByOrg($info['attachments']);
		if($st==1){
			$this->load->view('user/clinicser',$data);
		}else{
			$this->load->view('user/clinicsers',$data);
		}
	}

	//服务订单
	public function pcdetails($sid="") {
		$sid = getMdbId($sid);
		$info = $this->Order_model->getInfo('order',array('_id'=>$sid));
		if(empty($info)){ log_message('error',"订单没有找到"); show_error("订单没有找到"); }
		$status = $this->Order_model->getOrderStatus($info['service'],$info['status']);
		$info['stat'] = !empty($status)?$status:"错误状态";
		$info['tm'] = @date('Y-m-d',(string)$info['created_at']->sec);
		$data['info'] = $info;
		$data['doctor'] = $this->doctor_model->getDoctorByRef($info['doctor']);
		$data['imgs'] = getThumbByOrg($info['attachments']);
		$this->load->view('user/pcdetails',$data);
	}

	//聊天
	public function chat($order="",$issend="") {
		if($issend=="1"){ sleep(3); };
		if($issend=="2"){ $this->Login_model->weixinLoginCheck("user/chat/".$order."/".$issend); };
		$orderInfo = $this->Order_model->getInfo('order',array('_id'=>getMdbId($order)));
		//校验订单
		if(empty($orderInfo)){ show_error("未找到该订单");}
		$patient = $this->user_model->getRefInfo('patient',$orderInfo['patient']);
		if(!checkPatIsSelf($patient["_id"])){ show_error("不是本人订单，不允许操作"); }
		if(!isset($orderInfo['question'])){ redirect("/consult/question/".$order); }
		$doctor = $this->doctor_model->getDoctorByRef($orderInfo['doctor']);
		$this->Order_model->updateRecord('order',array("_id"=>$orderInfo['_id']),array('nomsg'=>0));
		//调取数量
		$hx_where = array('oid'=>(string)$orderInfo['_id'],"otp"=>"1",'from'=>(string)$orderInfo['patient']['$id']);
		$data['historyNum'] = $this->Common_model->getInfoCount('hx_history',$hx_where);
		$data['order'] = $orderInfo;
		$data['timestamp'] = time()."000";
		$data['doctor'] = $doctor;
		$data['patient'] = $patient;
		$data['images'] = getThumbByOrg($orderInfo['attachments']);
		$data['issend'] = $issend;
		if($orderInfo['status'] == "已支付"){ 
			$data['issends'] = 1;
	   	}else{
			$data['issends'] = 0;
		}
		$data['appkey'] = config_item("global_hx_appkey");
		if(!$orderInfo['pay_at']) $orderInfo['pay_at'] = $orderInfo['updated_at']->sec;
		$this->load->view('user/chat',$data);
	}

	public function chat1($order="",$issend="") {
		if($issend=="1"){ sleep(3); };
		if($issend=="2"){ $this->Login_model->weixinLoginCheck("user/chat/".$order."/".$issend); };
		$orderInfo = $this->Order_model->getInfo('order',array('_id'=>getMdbId($order)));
		//校验订单
		if(empty($orderInfo)){ show_error("未找到该订单");}
		$patient = $this->user_model->getRefInfo('patient',$orderInfo['patient']);
		if(!checkPatIsSelf($patient["_id"])){ show_error("不是本人订单，不允许操作"); }
		if(!isset($orderInfo['question'])){ redirect("/consult/question/".$order); }
		$doctor = $this->doctor_model->getDoctorByRef($orderInfo['doctor']);
		$this->Order_model->updateRecord('order',array("_id"=>$orderInfo['_id']),array('nomsg'=>0));
		//调取数量
		$hx_where = array('oid'=>(string)$orderInfo['_id'],"otp"=>"1",'from'=>(string)$orderInfo['patient']['$id']);
		$data['historyNum'] = $this->Common_model->getInfoCount('hx_history',$hx_where);
		$data['order'] = $orderInfo;
		$data['timestamp'] = time()."000";
		$data['doctor'] = $doctor;
		$data['patient'] = $patient;
		$data['images'] = getThumbByOrg($orderInfo['attachments']);
		$data['issend'] = $issend;
		if($orderInfo['status'] == "已支付"){ 
			$data['issends'] = 1;
	   	}else{
			$data['issends'] = 0;
		}
		$data['appkey'] = config_item("global_hx_appkey");
		if(!$orderInfo['pay_at']) $orderInfo['pay_at'] = $orderInfo['updated_at']->sec;
		$this->load->view('user/chat1',$data);
	}

}

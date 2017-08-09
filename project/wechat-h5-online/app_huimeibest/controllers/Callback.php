<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户聊天
 */

class Callback extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('Jssdk_model');
		$this->load->model('Template_model');
		$this->load->model('Chat_model');
		$this->load->model('Order_model');
		$this->load->model('Doctor_model');
		$this->load->model('Hx_model');
		$this->load->model('Message_model');
		$this->load->model('Api_model');
	}

	public function consult_pay(){
		header("Access-Control-Allow-Origin: *");
		header("Content-type: application/json");
		$post = $this->input->post();
		log_message('error',json_encode($post));
		if(!checkIsSelfHost()){ echo 0;exit; }
		if(empty($post['orderId'])){ show_error("访问错误！"); }
		//得到订单
		$info = $this->Order_model->getInfo('order',array('_id'=>getMdbId($post['orderId'])));
		if(empty($info) || $info['service']!='consult' || $info['status']!="已支付"){ echo 0;exit; }
		$where1 = array('_id'=>$info['doctor']['$id']);
		$update1= array('$inc'=>array('con_num'=>1,'mul_num'=>2,'rc_num'=>1));
		$this->Doctor_model->updateSetRecord('doctor',$where1,$update1);
		if(!empty($info['coupons'])){
			$this->Common_model->updateRecord('user_coupons',array('_id'=>getMdbId($info['coupons'])),array('st'=>0));
		}
		//医生端推送
		$order_id = (string)$info['_id'];
		$order_id_md5 = md5($order_id.'huimei123456');
		$ts_url = config_item('global_doc_url').$order_id."/".$order_id_md5;
		$tuisong = httpGet($ts_url);
		//hx推送
		$patInfo= $this->Common_model->getTableByRef('patient',$info['patient']);
		if(empty($patInfo)){ echo 0;exit;}
		$cont = array( 'name'=> $info['name'], 'gender'=> $info['gender'], 'age' =>$info['age'], 'detail'=> $info['message'],
			   	'attachments'=>getThumbByOrg($info['attachments']));

		$arr = array(
			"target_type" => "users",
			'target'=>array((string)$info['doctor']['$id']),
			'msg'=>array('type'=>'txt','msg'=>"[病情描述]"),
			'from'=>(string)$patInfo['_id'],
			'ext' =>array(
				'msg_type'=>"notice_record",
				'nickname'=>$patInfo['name'],
				'avatar' =>$patInfo['avatar'],
				'msg_content'=>$cont,
				'order_id'=>(string)$info['_id']
			)
		);
		$check = $this->hx_push($arr);
		$this->doctor_fans((string)$info['doctor']['$id'],(string)$info['fid']);
		echo 1;exit;
	}
	public function clinic_pay(){
		header("Access-Control-Allow-Origin: *");
		header("Content-type: application/json");
		$post = $this->input->post();
		log_message('error',json_encode($post));
		if(!checkIsSelfHost()){ echo 0;exit; }
		if(empty($post['orderId'])){ show_error("访问错误！"); }
		//得到订单
		$info = $this->Order_model->getInfo('order',array('_id'=>getMdbId($post['orderId'])));
		if(empty($info) || $info['service']!='clinic' || $info['status']!="已支付"){ echo 0;exit; }
		//处理医生日程
		$where2 = array('_id'=>@$info['doctor_timetable']['$id']);
		$update2= array('$inc'=>array('remain'=>-1));
		$this->Doctor_model->updateSetRecord('doctor_timetable',$where2,$update2);
		//处理医生关注
		$where1 = array('_id'=>@$info['doctor']['$id']);
		$update1= array('$inc'=>array('reg_num'=>1,'mul_num'=>2,'rc_num'=>1));
		$this->Doctor_model->updateSetRecord('doctor',$where1,$update1);
		if(!empty($info['coupons'])){
			$this->Common_model->updateRecord('user_coupons',array('_id'=>getMdbId($info['coupons'])),array('st'=>0));
		}

		$order_id = (string)$info['_id'];
		$order_id_md5 = md5($order_id.'huimei123456');
		$ts_url = config_item('global_doc_url').$order_id."/".$order_id_md5;
		$tuisong = httpGet($ts_url);
		//推送消息
		$postdata = array();
		$postdata['type'] = "clinic";
		$postdata['fid'] = $info['fid'];
		$postdata['name'] = $info['name'];
		$postdata['seq'] = $info['seq'];
		$postdata['_id'] = $order_id;
		$postdata['schedule'] = (string)date('Y-m-d ',$info['schedule']->sec);
		$postdata['hour'] = (string)date('H',$info['schedule']->sec);
		$postdata['gender'] = $info['gender'];
		$postdata['hos'] = $info['location'];
		$docInfo= $this->Common_model->getTableByRef('doctor',$info['doctor']);
		$postdata['docname'] = @$docInfo['name'];
		$postdata['dep'] = (string)@$docInfo['department'];
		$this->wx_push($postdata);
		$ts = $this->config->item('global_wx_pubts');
		if(!empty($ts)){
			$postdata['fid'] = $ts;
			$postdata['mobile'] = (string)$info['mobile'];
			$ret = @$this->Template_model->sendClinicSuccess1($postdata);
		}
		//短信发送
		if(!empty($docInfo['assistant'])){
			$assInfo = $this->Common_model->getInfo('doctor_assistant',array("_id"=>$docInfo['assistant']));
			if(!empty($docInfo)){
				$postdata = array();
				$postdata['mobile'] = str_replace("-","",$assInfo['mobile']);
				$postdata['assistant'] = $assInfo['name'];//遗嘱姓名
				$postdata['patient'] = @$info['name'];
				$postdata['show_mobile'] = @$info['mobile'];
				$postdata['hospital'] = @$docInfo['hospital'];
				$postdata['doctor'] = @$docInfo['name']."医生";
				$postdata['datetime'] = (string)date('Y-m-d',$info['schedule']->sec);
				$postdata['serial_number'] = $info['seq'];
				$postdata['type'] = "clinic";
				$this->msg_push($postdata);
			}
		}

		$this->doctor_fans((string)$info['doctor']['$id'],(string)$info['fid']);
		echo 1;exit;
	}
	public function phonecall_pay(){
		header("Access-Control-Allow-Origin: *");
		header("Content-type: application/json");
		$post = $this->input->post();
		log_message('error',json_encode($post));
		if(!checkIsSelfHost()){ echo 0;exit; }
		if(empty($post['orderId'])){ show_error("访问错误！"); }
		//得到订单
		$info = $this->Order_model->getInfo('order',array('_id'=>getMdbId($post['orderId'])));
		if(empty($info) || $info['service']!='phonecall' || $info['status']!="已支付"){ echo 0;exit; }
		//处理医生日程
		$where2 = array('_id'=>@$info['doctor_timetable']['$id']);
		$update2= array('$inc'=>array('remain'=>-1,'minutes_remain'=>-$info['longTime']));
		$this->Doctor_model->updateSetRecord('doctor_timetable',$where2,$update2);
		//处理医生关注
		$where1 = array('_id'=>@$info['doctor']['$id']);
		$update1= array('$inc'=>array('phonecall_num'=>1,'mul_num'=>2,'rc_num'=>1));
		$this->Doctor_model->updateSetRecord('doctor',$where1,$update1);
		if(!empty($info['coupons'])){
			$this->Common_model->updateRecord('user_coupons',array('_id'=>getMdbId($info['coupons'])),array('st'=>0));
		}
		$order_id = (string)$info['_id'];
		$order_id_md5 = md5($order_id.'huimei123456');
		$ts_url = config_item('global_doc_url').$order_id."/".$order_id_md5;
		$tuisong = httpGet($ts_url);
		//推送消息
		$postdata = array();
		$postdata['type'] = "phonecall";
		$postdata['fid'] = $info['fid'];
		$postdata['seq'] = $info['seq'];
		$postdata['_id'] = $order_id;
		$phonetime = (string)date('Y-m-d',$info['schedule']->sec);
		$postdata['time'] = $phonetime." ".$info['interval']."之间";
		$docInfo= $this->Common_model->getTableByRef('doctor',$info['doctor']);
		$postdata['docname'] = @$docInfo['name'];
		$postdata['hospital'] = (string)@$docInfo['hospital'];
		$this->wx_push($postdata);

		$this->doctor_fans((string)$info['doctor']['$id'],(string)$info['fid']);
		echo 1;exit;
	}
	//扫码支付回调函数
	public function codepay_pay(){
		header("Access-Control-Allow-Origin: *");
		header("Content-type: application/json");
		$post = $this->input->post();
		log_message('error',json_encode($post));
		if(!checkIsSelfHost()){ echo 0;exit; }
		if(empty($post['orderId'])){ show_error("访问错误！"); }
		//得到订单
		$info = $this->Order_model->getInfo('order_qrcode',array('_id'=>getMdbId($post['orderId'])));
		if(empty($info) || $info['service']!='codepay' || $info['status']!=1){ echo 0;exit; }
		//做支付完成处理
		echo 1;exit;
	}

	public function anwser(){
		$this->load->model('Order_model');
		$post = file_get_contents('php://input');
		$post = json_decode($post,'true');
		if(empty($post['msg_content']) || empty($post['msg_id']) || empty($post['order_id'])){ echo json_encode(array('st'=>0,'msg'=>'failed!'));exit; }
		$orderInfo = $this->Order_model->getInfo('order',array('_id'=>getMdbId($post['order_id'])));
		if(empty($orderInfo)){ echo json_encode(array('st'=>0,'msg'=>'failed!'));exit;}
		//校验是否就离线消息
		$hx_user = (string)$orderInfo['patient']['$id'];
		$msg_online = $this->Chat_model->checkUserOnline($hx_user);
		if($msg_online){ echo json_encode(array('st'=>"0",'msg'=>"user online!"));exit; }
		//校验消息重复发送
		$log = $this->Hx_model->getInfo('hx_push_log',array('key'=>(string)$post['msg_id']));
		if(!empty($log)){ echo json_encode(array('st'=>0,'msg'=>'failed!'));exit;}
		$que = $orderInfo['message'];
		$ans = $post['msg_content'];
		$orderid = $post['order_id'];
		$fid = @$orderInfo['fid'];
		if(!empty($fid)){
			$this->Order_model->updateRecord('order',array('_id'=>getMdbId($post['order_id'])),array('nomsg'=>1));
			$this->Hx_model->insertInfo('hx_push_log',array('key'=>(string)$post['msg_id']));
			$ret = $this->Template_model->sendDoctorAnwser($fid,$orderid,$que,$ans);
			echo json_encode(array('st'=>"1",'msg'=>"send success!"));exit;
		}else{
			echo json_encode(array('st'=>"0",'msg'=>"send failed!"));exit;
		}
	}
	
	//医生修改三种服务时推送消息
	public function pushMsgByDoctor(){
		set_time_limit(0);
		$post = $this->input->post();
		log_message('error',json_encode($post));
		/*
		$post = array(
			'sign'=>'788b7e2add46507c7751d7ebba4bca057bac4fd1',
			'type'=>'2',
			'timestamp'=>'1045632568',
			'doctor'=>'561c9c78f154d653238b456b'
		);
		 */
		//校验参数合法性
		if(empty($post['sign']) || empty($post['type']) || empty($post['timestamp']) || empty($post['doctor']) || count($post)>5 ){
			echo $this->Api_model->getErrorStatus(10001);exit;
		}
		//验证签名是否正确
		if(!$this->Api_model->checkSign($post)){
			echo $this->Api_model->getErrorStatus(10002);exit;
		}
		//查找医生信息
		$doctor = $this->Doctor_model->getInfo('doctor',array('_id'=>getMdbId($post['doctor'])),array());
		if(empty($doctor)){
			echo $this->Api_model->getErrorStatus(10103);exit;
		}
		//查找医生粉丝
		$doctor_fans = $this->Doctor_model->getInfoByWhere('doctor_fans',array('tid'=>$post['doctor'],'st'=>"1"));
		//遍历粉丝，发送推送消息
		$push_Info=array('dname'=>$doctor['name'],'dposition'=>$doctor['position'],'dhospital'=>$doctor['hospital'],'ddepartment'=>$doctor['department'],'type'=>$post['type']);
		if($doctor_fans){
			foreach($doctor_fans as $k=>$v){
				$push_Info['fid'] = $v['openid'];
				$ret = $this->Template_model->pushMsgByDoctor($push_Info);
			}
			
		}
		echo $this->Api_model->getErrorStatus(0);exit;
	}

	private function doctor_fans($doctorid,$userid){
		$where = array('tid'=>$doctorid,"openid"=>$userid);
		$where1 = array('_id'=>getMdbId($doctorid));
		$careInfo = $this->Common_model->getInfo('doctor_fans',$where);
		if(empty($careInfo)){
			$info = array("tid"=>trim($doctorid),"openid"=>$userid,"ct"=>(string)time(),"st"=>"1");
			$this->Common_model->insertInfo('doctor_fans',$info);
			$this->Common_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
		}else{
			if($careInfo['st']!="1"){
				$info = array("st"=>"1");
				$this->Common_model->updateRecord('doctor_fans',$where,$info);
				$this->Common_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
			}
		}
		return true;
	}
	private function hx_push($post){
		if(empty($post)){ return false; }
		log_message('error',json_encode($post));
		$wang = $this->Chat_model->getAccessToken();
		$ret = $this->Chat_model->sendMsg($wang,$post);
		return true;
	}

	private function wx_push($post){
		if(empty($post)){ return false; }
		log_message('error',json_encode($post));
		if($post['type']=="phonecall"){
			$ret = $this->Template_model->sendPhoneCall($post);
		}elseif($post['type']=="clinic"){
			$ret = $this->Template_model->sendClinicSuccess($post);
		}else{
			$ret = "type error";
		}
		return true;
	}

	private function msg_push($post){
		if(empty($post)){ return false; }
		log_message('error',json_encode($post));
		if($post['type']=="phonecall"){
			$ret = $this->Message_model->sendMsgFromPc($post);
		}elseif($post['type']=="clinic"){
			$ret = $this->Message_model->sendMsgFromCl1($post);
		}else{
			$ret = "type error";
		}
		log_message('error',json_encode($ret));
		return true;
	}

}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Doctor.php
 */
class Consult extends CI_Controller {
	public $uid;
	public $openid;
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
		$this->load->model('order_model');
		$this->load->model('doctor_model');
		$this->load->model('Img_model');
		$this->load->model('Chat_model');
		$this->load->model('date_model');
		$this->load->model('Coupons_model');
	}

    /** 
     * 图文咨询
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function index($doctorId) {
		if(empty($doctorId)){ log_message('error',"doctorID not find!");exit;}
		//检测用户是否注册
		$btn = $this->input->post();

		$docRef= MongoDBRef::create("doctor", getMdbId($doctorId));
		$patRef= MongoDBRef::create("patient", getMdbId($this->uid));
		$dWhere = array('doctor'=>$docRef,"status"=>"已支付",'patient'=>$patRef,'service'=>"consult");
		$checkOd = $this->order_model->getInfo("order",$dWhere);
		if(!empty($checkOd) && isset($checkOd['question'])){ redirect("/user/chat/".$checkOd['_id']);exit; }
		if(!empty($checkOd) && !isset($checkOd['question'])){ redirect("/consult/question/".$checkOd['_id']);exit; }
		if(isset($btn['btn1'])){
			$patInfo = $this->user_model->getInfo('patient_family',array('_id'=>getMdbId($btn['patient'])));
			$docInfo = $this->doctor_model->getInfo('doctor',array('_id'=>getMdbId($btn['doctor'])));
			if(empty($docInfo['service_provided']['consult']['on'])){
				show_error("预约已满!");
			}
			if(empty($patInfo) || empty($docInfo)){ show_error("数据错误"); }
			$msg = addslashes(trim($btn['message']));
			//验证处理上传图片
			$imgArr = isset($btn['img'])?$btn['img']:array();
			//优惠券处理start
			$cou_id = addslashes(trim($btn['coupons']));
			if(!empty($cou_id)){
				$coupons = $this->Coupons_model->getInfo('user_coupons',array('_id'=>getMdbId($cou_id)));
			}
			if(!empty($coupons)){
				if($coupons['type']!=1 && $coupons['type']!=2){ show_error('优惠券类型错误'); }
				$price_ext = $docInfo['service_provided']['consult']['price']-$coupons['price'];
				$price_t = $price_ext<0?0:$price_ext;
				$coup_id = (string)$coupons['_id'];
			}else{
				$price_t = $docInfo['service_provided']['consult']['price'];	
				$coup_id = "";
			}
			$price_ori = $docInfo['service_provided']['consult']['price'];	
			//优惠券处理end
			$stat = $price_t<=0?"已支付":"新订单";
			$price_t = $price_t<=0?0:$price_t;
			//db添加数据
			$docref= MongoDBRef::create("doctor", $docInfo['_id']);
			$patref= MongoDBRef::create("patient",$patInfo['patient']['$id']);
			$cur_date = new MongoDate(time());
			$randomtm = getRandomPass(3,'NUMBER');
			$ordernum = date('YmdHis',time())."1".$randomtm;
			$insertData = array(
					"doctor"=>$docref,
					"patient"=>$patref,
					"service"=>"consult",
					"schedule"=>$cur_date,
					"name"=>$patInfo['name'],
					"gender"=>$patInfo['gender'],
					"age"=>$patInfo['age'],
					"mobile"=>$patInfo['mobile'],
					"idcard"=>"",
					"price"=>$price_t,
					"price_ori"=>$price_ori,
					"message"=>$msg,
					"status"=>$stat,
					"created_at"=>$cur_date,
					"updated_at"=>$cur_date,
					"pay_at"=>(string)time(),
					"location"=>"",
					"attachments"=>$imgArr,
					"fid"=>(string)@$_SESSION['aid'],
					"nomsg"=>0,
					"iscom"=>"0",
					"seq"=>(string)$ordernum,
					"coupons"=>$coup_id
					);
			$check = (string)$this->order_model->insertInfo("order",$insertData);
			if($check){
				if($price_t<=0){
					$url = config_item('global_base_url')."/Callback/consult_pay";
					$postdata = http_build_query(array("orderId" =>(string)$check));
					$order_data = do_post_request($url, $postdata);
					redirect("/consult/question/".$check);
					exit;
				}else{
					$sign = array('oid'=>$check,'type'=>'consult','timestamp'=>time());
					$sign = authcode(json_encode($sign),"ENCODE");
					redirect(config_item("global_wx_payurl")."?sign=".$sign);exit;
				}
			}else{
				show_error("提交失败");
			}
		}
		//得到医生信息
		$doctor = $this->doctor_model->getDoctorByWhere(array('_id'=>getMdbId($doctorId)));
		if(empty($doctor)){show_error("未找到医生");} $doctor = $doctor[0];
		$data['isSub'] = $this->doctor_model->timeTableDealOne($doctor);
		$data['doctor'] = $doctor;

		$data['carest'] = 0;
		if($this->openid){
			$gzWhere = array('tid'=>(string)$doctor['_id'],"openid"=>(string)$this->openid,"st"=>"1");
			$gzInfo = $this->user_model->getInfo("doctor_fans",$gzWhere);
			if(!empty($gzInfo)){ $data['carest'] = 1; }
		}
		$this->load->view('consult/index',$data);
	}

    /** 
     * 填写问题
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function question($orderid) {
		if(empty($orderid)){ show_error("参数错误!");}
		//检测用户是否注册
		$patRef= MongoDBRef::create("patient", getMdbId($this->uid));
		$dWhere = array('_id'=>getMdbId($orderid),'patient'=>$patRef,'service'=>"consult");
		$checkOd = $this->order_model->getInfo("order",$dWhere);
		if(empty($checkOd)){ show_error("订单错误!");}
		if(isset($checkOd['question'])){ redirect("/user/chat/".$checkOd['_id']);exit; }
		$btn = $this->input->post();
		if(isset($btn['btn1'])){
			//db添加数据
			if(!isset($btn['question'])){ show_error("提交数据有误!");}
			$question = array();
			foreach($btn['question'] as $v){
				$v = trim($v);
				if(!empty($v)){
					$question[]=$v;
				}
			}
			$where = array('_id'=>getMdbId($orderid));
			$check = $this->order_model->updateRecord("order",$where,array('question'=>$question));
			if($check && !empty($question)){
				//环信推送消息
				$patInfo = $this->Common_model->getInfo('patient',array('_id'=>$checkOd['patient']['$id']));
				$arr = array(
					"target_type" => "users",
					'target'=>array((string)$checkOd['doctor']['$id']),
					'msg'=>array('type'=>'txt','msg'=>"[病情描述]"),
					'from'=>(string)$checkOd['patient']['$id'],
					'ext' =>array(
						'msg_type'=>"notice_problem",
						'nickname'=>$patInfo['name'],
						'avatar' =>@$patInfo['avatar'],
						'msg_content'=>$question,
						'order_id'=>(string)$checkOd['_id']
					)
				);
				$check = $this->hx_push($arr);
				//跳转
				redirect("/user/chat/".$checkOd['_id']);exit;
			}
			redirect("/user/chat/".$checkOd['_id']);exit;
		}
		$data['order'] = $checkOd;
		$this->load->view('consult/question',$data);
	}
	private function hx_push($post){
		$wang = $this->Chat_model->getAccessToken();
		$ret = $this->Chat_model->sendMsg($wang,$post);
		return true;
	}



}

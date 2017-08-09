<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Subscribe.php
 */
class Subscribe extends CI_Controller {
	
	public $sub_limit = 50;
    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		 show_error("该功能已暂停使用!");
		parent::__construct();
		$this->uid = checkAuth3();
		$this->openid = getPatientOpenid();
		$this->load->model('user_model');
		$this->load->model('order_model');
		$this->load->model('doctor_model');
		$this->load->model('date_model');
		$this->load->model('Img_model');
		$this->load->model('Coupons_model');
	}

    /** 
     * 门诊预约
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function index($doctorId) {
		if(empty($doctorId)){ log_message('error',"doctorID not find!");exit;}
		//检测用户时候注册
		$docObjId = getMdbId($doctorId);
		$doctor = $this->doctor_model->getDoctorByWhere(array('_id'=>$docObjId));
		if(empty($doctor)){show_error("没有找到医生");} $doctor = $doctor[0];
		//检查医生是否可以预约
		if(empty($doctor['service_provided']['clinic']['on'])){ show_error("该医生暂时没有开放预约",200,"错误"); }
		$data['doctor'] = $doctor;
		$data['time_slot'] = $this->date_model->getSelTime();
		//print_r($data['time_slot']);exit;
		$data['clander'] = $this->date_model->getClientClander();
		$s_datestr = $this->date_model->getWeekMondayTime();	
		$s_date1 = date('Y-m-d',$s_datestr);
		$e_date = $this->date_model->getSelEndDate();
		$docRef = MongoDBRef::create("doctor", $docObjId); 
		$where = array('date'=>array('$gte'=>$s_date1,'$lte'=>$e_date),'doctor'=>$docRef,'service'=>'clinic');
		$doctable = $this->doctor_model->getInfoByWhere('doctor_timetable',$where,array());
		//处理医生开放时间
		$opentime = array();
		foreach($doctable as $k=>$v){
			$tempTm = date('Ymd',strtotime($v['date']));
			$temTimeEnd = explode(',',$v['interval']);
			if(!empty($temTimeEnd[1]) && $temTimeEnd[1]<="12:00"){
					$opentime[$tempTm."1"] = array('id'=>(string)$v['_id'],'quantity'=>(int)$v['remain']);
			}elseif(!empty($temTimeEnd[1]) && $temTimeEnd[1]<="18:00" && $temTimeEnd[1]>"12:00"){
				$opentime[$tempTm."2"] = array('id'=>(string)$v['_id'],'quantity'=>(int)$v['remain']);
			}
		}
		$data['sub_able'] = $opentime;
		$cur_time = time();
		$date_H = date("H",$cur_time);
		if($date_H>=11 && $date_H<16){
			$data['now_time1'] = date('Ymd',$cur_time+86400)."1";
			$data['now_time2'] = date('Ymd',$cur_time)."2";
		}elseif($date_H>=16){
			$data['now_time1'] = date('Ymd',$cur_time+86400)."1";
			$data['now_time2'] = date('Ymd',$cur_time+86400)."2";
		}else{
			$data['now_time1'] = date('Ymd',$cur_time)."1";
			$data['now_time2'] = date('Ymd',$cur_time)."2";
		}

		$data['carest'] = 0;
		if($this->openid){
			$gzWhere = array('tid'=>(string)$doctorId,"openid"=>(string)$this->openid,"st"=>"1");
			$gzInfo = $this->user_model->getInfo("doctor_fans",$gzWhere);
			if(!empty($gzInfo)){ $data['carest'] = 1; }
		}
		$this->load->view('subscribe/index',$data);
	}

	public function order($dayid="") {
		if(empty($dayid)){show_error("参数错误");}
		//检测用户是否注册
		$btn = $this->input->post();
		//得到该条预约信息
		$dayInfo = $this->doctor_model->getInfo('doctor_timetable',array('_id'=>getMdbId($dayid)));
		if($dayInfo['service']!="clinic"){ show_error("日程错误！"); }
		$data['day'] = $dayInfo;
		if(empty($dayInfo)){show_error("该医生已经预约满，请确认",200,"提示:");}
		$dateinfo = getdate(strtotime($dayInfo['date']));
		$data['week'] = $this->date_model->week[$dateinfo['wday']];
		$docInfo = $this->doctor_model->getDoctorByRef($dayInfo['doctor']);
		if(empty($docInfo)){show_error("该医生不存在");}
		if((int)$dayInfo['remain']<=0){show_error("该医生已经预约满，请确认");}

		if(isset($btn['btn1'])){
			$patInfo = $this->user_model->getInfo('patient_family',array('_id'=>getMdbId($btn['patient'])));
			if(empty($docInfo['service_provided']['clinic']['on'])){
				show_error("该医生未开放预约，请确认");
			}
			if((int)$dayInfo['remain']<=0){show_error("该医生已经预约满，请确认");}
			if(empty($patInfo) || empty($docInfo)) { show_error("数据错误");	}
			$msg = addslashes(trim($btn['message']));
			//验证处理上传图片
			$imgArr = isset($btn['img'])?$btn['img']:array();
			$temTimeEnd = explode(',',$dayInfo['interval']);
			$shedu = $temTimeEnd[1]>"12:00"? "12:00:00":"00:00:00";
			$cur_d = (string)date('Y-m-d',time());
			$cur_h = (string)date('H:i',(time()+300));
			if($cur_d>$dayInfo['date'] || ($cur_d==$dayInfo['date'] && $cur_h>=$temTimeEnd[1])){
				show_error("日程已经过期！");
			}
			$shedu = $dayInfo['date']." ".$shedu;
			$shedule = strtotime($shedu);
			$shedule = new MongoDate($shedule);
			//db添加数据
			$docref= MongoDBRef::create("doctor", $docInfo['_id']);
			$patref= MongoDBRef::create("patient",$patInfo['patient']['$id']);
			$doctor_timetable= MongoDBRef::create("doctor_timetable",$dayInfo['_id']);
			$cur_date = new MongoDate(time());
			//优惠券处理start
			$cou_id = addslashes(trim($btn['coupons']));
			if(!empty($cou_id)){
				$coupons = $this->Coupons_model->getInfo('user_coupons',array('_id'=>getMdbId($cou_id)));
			}
			if(!empty($coupons)){
				if($coupons['type']!=1 && $coupons['type']!=3){ show_error('优惠券类型错误'); }
				$price_t = $dayInfo['price']-$coupons['price'];
				$coup_id = (string)$coupons['_id'];
			}else{
				$price_t = $dayInfo['price'];
				$coup_id = "";
			}
			$price_ori = $dayInfo['price'];
			//优惠券处理end
			$stat = $price_t<=0?"已支付":"新订单";
			$price_t = $price_t<=0?0:$price_t;
			//得到未支付订单（检查是否满足下单条件）
			$dayInfo = $this->doctor_model->getInfo('doctor_timetable',array('_id'=>$dayInfo['_id']));
			$pay_date = new MongoDate(time()-300);
			$o_where = array('status'=>'新订单','created_at'=>array('$gte'=>$pay_date),'service'=>"clinic",'doctor_timetable'=>$doctor_timetable);
			$check = $this->order_model->getInfoCount("order",$o_where);
			if($check>=$dayInfo['remain']){show_error("当前排号人数较多，请稍后再试!");}
			$brhos = @empty($dayInfo['location']['branch'])?$dayInfo['location']['hospital']:$dayInfo['location']['hospital']."(".$dayInfo['location']['branch'].")";
			$randomtm = getRandomPass(3,'NUMBER');
			$ordernum = date('YmdHis',time())."2".$randomtm;

			$insertData = array(
					"doctor"=>$docref,
					"patient"=>$patref,
					"doctor_timetable"=>$doctor_timetable,
					"service"=>"clinic",
					"schedule"=>$shedule,
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
					"location"=>$brhos,
					"attachments"=>$imgArr,
					"fid"=>(string)@$_SESSION['aid'],
					"iscom"=>"0",
					"seq"=>(string)$ordernum,
					"coupons"=>$coup_id
					);
			$check = (string)$this->order_model->insertInfo("order",$insertData);
			if($check){
				if($price_t<=0){
					$url = config_item('global_base_url')."/Callback/clinic_pay";
					$postdata = http_build_query(array("orderId" =>(string)$check));
					$order_data = do_post_request($url, $postdata);
					redirect("/user/clinicser/2/".$check);
					exit;
				}else{
					$sign = array('oid'=>$check,'type'=>'clinic','timestamp'=>time());
					$sign = authcode(json_encode($sign),"ENCODE");
					redirect(config_item("global_wx_payurl")."?sign=".$sign);exit;
				}
			}else{
				show_error("该医生不存在，请确认");
			}
		}

		//得到医生信息
		$data['doctor'] = $docInfo;

		$this->load->view('subscribe/order',$data);
	}


}

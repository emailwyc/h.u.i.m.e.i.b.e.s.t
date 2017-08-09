<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Phonecall.php
 */
class Phonecall extends CI_Controller {
	
    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$userid = checkAuth3();
		$this->openid = $userid;
		$this->load->model('user_model');
		$this->load->model('order_model');
		$this->load->model('doctor_model');
		$this->load->model('date_model');
		$this->load->model('Img_model');
		$this->load->model('Coupons_model');
	}

    /** 
     * 电话预约
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
		if(empty($doctor['service_provided']['phonecall']['on'])){ show_error("该医生暂时没有开放预约",200,"错误"); }
		$data['doctor'] = $doctor;


		$s_date1 = date('Y-m-d',time()); $e_date = $this->date_model->getSelEndDate();
		$docRef = MongoDBRef::create("doctor", $docObjId);
		$minute_min = empty($doctor['service_provided']['phonecall']['minutes_min'])?5:$doctor['service_provided']['phonecall']['minutes_min'];
		$where = array('date'=>array('$gte'=>$s_date1,'$lte'=>$e_date),'remain'=>array('$gte'=>1),'minutes_remain'=>array('$gte'=>$minute_min),'doctor'=>$docRef,'service'=>'phonecall');
		$doctable = $this->doctor_model->getPhonecall($where,array(),array('date'=>1,'interval'=>1));

		$data['table'] = $doctable;
		$data['table_json'] = json_encode($doctable);

		//关注
		$data['carest'] = 0;
		if($this->openid){
			$gzWhere = array('tid'=>(string)$doctorId,"pid"=>(string)$this->openid,"st"=>"1");
			$gzInfo = $this->user_model->getInfo("doctor_fans",$gzWhere);
			if(!empty($gzInfo)){
				$data['carest'] = 1;
			}
		}
		$this->load->view('phonecall/index',$data);
	}

	public function order($dayid="",$str="") {
		//检测用户是否注册
		$str_arr = array('05','10','15','20');
		$btn = $this->input->post();
		if(!empty($btn['hours'])){$str = $btn['hours'];}
		if(empty($dayid) || !in_array($str,$str_arr,true)){show_error("参数错误");}
		//得到该条预约信息
		$dayInfo = $this->doctor_model->getInfo('doctor_timetable',array('_id'=>getMdbId($dayid)));
		if(empty($dayInfo)){show_error("该医生已经预约满，请确认",200,"提示:");}
		if($dayInfo['service']!="phonecall"){ show_error("日程错误！"); }
		if($dayInfo['remain']<=0 || $dayInfo['minutes_remain']<(int)$str){ show_error("哎呀，手慢了一点哟，换个日程吧！"); }
		$data['day'] = $dayInfo;
		$slot = explode(',',$dayInfo['interval']);
		$data['timestr'] = $slot[1]<="12:00"?"上午":($slot[1]<="18:00"?"下午":"晚上");
		$data['hours'] = $str;
		$data['pricestr'] = "price_".$str;
		$reg_time = strtotime($dayInfo['date']." ".$slot['0']);
		if($reg_time<time()){ show_error("该日程已经过期，请更换日程！");}
		$docInfo = $this->doctor_model->getDoctorByRef($dayInfo['doctor']);
		if(empty($docInfo)){show_error("该医生不存在");}
		if(empty($docInfo["service_provided"]["phonecall"]["on"])){show_error("对不起,医生已经关闭了电话预约!");}
		if($docInfo['service_provided']['phonecall'][$data['pricestr']]<0){
			show_error("对不起，该医生已经停诊!");	
		}

		if(isset($btn['btn1'])){
			$patInfo = $this->user_model->getInfo('patient_family',array('_id'=>getMdbId($btn['patient'])));
			if(empty($patInfo) || empty($docInfo)) { show_error("数据错误");	}
			$msg = addslashes(trim($btn['message']));
			//验证处理上传图片
			$imgArr = isset($btn['img'])?$btn['img']:array();
			$shedu = $dayInfo['date']." ".$slot[0] ;
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
			if($docInfo['service_provided']['phonecall'][$data['pricestr']]<0){
				show_error("对不起，该医生已经停诊!");	
			}
			if(!empty($coupons) && $docInfo['service_provided']['phonecall'][$data['pricestr']]>0){
				if($coupons['type']!=1 && $coupons['type']!=4){ show_error('优惠券类型错误'); }
				$price_t = $docInfo['service_provided']['phonecall'][$data['pricestr']]-$coupons['price'];
				$coup_id = (string)$coupons['_id'];
			}else{
				$price_t = $docInfo['service_provided']['phonecall'][$data['pricestr']];	
				$coup_id = "";
			}
			$price_ori = $docInfo['service_provided']['phonecall'][$data['pricestr']];
			//优惠券处理end
			$stat = $price_t<=0?"已支付":"新订单";
			$price_t = $price_t<=0?0:$price_t;

			//得到未支付订单（检查是否满足下单条件）
			$pay_date = new MongoDate(time()-300);
			$o_where = array('created_at'=>array('$gte'=>$pay_date),'service'=>"phonecall",'doctor_timetable'=>$doctor_timetable,'status'=>"新订单");
			$check = $this->order_model->getInfoAll("order",$o_where);
			$totalT = 1;
			$TotalN = (int)$str;
			foreach($check as $k=>$v){
				$totalN+=1;
				$totalT+=$v['longTime'];
			}
			if($totalN>=$dayInfo['remain']){show_error("当前排号人数较多，您下手慢了");}
			if($totalT>=$dayInfo['minutes_remain']){show_error("当前预约时间已满，您下手慢了");}
			$randomtm = getRandomPass(3,'NUMBER');
			$ordernum = date('YmdHis',time())."3".$randomtm;
			$insertData = array(
					"doctor"=>$docref,
					"patient"=>$patref,
					"doctor_timetable"=>$doctor_timetable,
					"service"=>"phonecall",
					"schedule"=>$shedule,
					"longTime"=>(int)$str,
					"interval"=>$dayInfo['interval'],
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
					"location"=>"",
					"attachments"=>$imgArr,
					"fid"=>(string)@$_SESSION['aid'],
					"iscom"=>"0",
					"seq"=>(string)$ordernum,
					"coupons"=>$coup_id
				);
			$check = (string)$this->order_model->insertInfo("order",$insertData);
			if($check){
				if($price_t<=0){
					$url = config_item('global_base_url')."/Callback/phonecall_pay";
					$postdata = http_build_query(array( "orderId" =>(string)$check));
					$order_data = @do_post_request($url, $postdata);
					redirect("/user/pcdetails/".$check);
					exit;
				}else{
					$sign = array('oid'=>$check,'type'=>'phonecall','timestamp'=>time());
					$sign = authcode(json_encode($sign),"ENCODE");
					redirect(config_item("global_wx_payurl")."?sign=".$sign);
					exit;
				}
			}else{
				show_error("该医生不存在，请确认");
			}
		}

		//检测用户是否
		$patient = $this->user_model->getPatientFamily($this->openid);
		if(empty($patient)){ redirect("/user/addpatient"); }
		$data['patient'] = $patient;
		//得到医生信息
		$data['doctor'] = $docInfo;
		$this->load->view('phonecall/order',$data);
	}


}

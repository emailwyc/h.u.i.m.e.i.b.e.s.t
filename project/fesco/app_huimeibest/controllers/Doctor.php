<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Doctor.php
 */
class Doctor extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('doctor_model');
		$this->load->model('user_model');
		$this->load->model('date_model');
	}

    /** 
     * 医生详情
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function details($id="") {
		if(empty($id)){ show_error("没有找到医生");}
		$this->Login_model->weixinLoginCheck("doctor/details/".$id);
		//是否关注
		$pid = getPatientOpenid(); $data['carest'] = 0;
		if($pid){
			$gzWhere = array('tid'=>(string)$id,"openid"=>(string)$pid,"st"=>"1");
			$gzInfo = $this->user_model->getInfo("doctor_fans",$gzWhere);
			if(!empty($gzInfo)){ $data['carest'] = 1;}
		}
		//得到医生信息
		$where['_id'] = getMdbId($id);
		$doctor = $this->doctor_model->getDoctorByWhere($where);
		if(empty($doctor)){ show_error("没有找到医生");}
		//处理日程
		$data['isSub'] = $this->doctor_model->timeTableDealOne($doctor[0]);
		$data['isph'] = $this->doctor_model->timeTableDealOneByMobile($doctor[0]);
		$data['com_num'] = $this->Common_model->getInfoCount('order_comment',array('doctor'=>$id));
		//得到就诊地点
		$docRef = MongoDBRef::create("doctor", $doctor[0]['_id']); 
		$data['location'] = $this->Common_model->getInfoAll('doctor_location',array('doctor'=>$docRef));

		$data['doctor'] = !empty($doctor)?$doctor[0]:array();
		$this->load->view('doctor/details',$data);
	}

    /** 
     * 医生描述
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function content($class,$id) {
		//log_message('error', 'error message.'); if(empty($id)){ log_message('error',"doctor not find!");}
		$where['_id'] = getMdbId($id);
		$doctor = $this->doctor_model->getDoctorByWhere($where);
		$info = !empty($doctor)?$doctor[0]:array();
		if($class==1){
			$data['content'] = $info['speciality'];
			$data['title'] = "医生擅长";
		}elseif($class==2){
			$data['content'] = $info['description'];
			$data['title'] = "医生描述";
		}else{
			$data['content'] = "暂无内容";
		}
		$this->load->view('common/content',$data);
	}
	//关注1取消2--1成功，2失败，3没有登录
	public function fans($doctorid="",$status="") {
		//
		if(!$this->input->is_ajax_request()){ echo 2;exit; }
		if(empty($doctorid)||empty($status)){ echo 2;exit;}
		$userid = getPatientOpenid();
		if(!$userid){ echo 3;exit;}
		$where = array('tid'=>$doctorid,"openid"=>$userid);
		$where1 = array('_id'=>getMdbId($doctorid));
		if($status==1){
			$careInfo = $this->user_model->getInfo('doctor_fans',$where);
			if(empty($careInfo)){
				$info = array("tid"=>trim($doctorid),"openid"=>$userid,"ct"=>(string)time(),"st"=>"1",'from'=>"fesco");
				$this->user_model->insertInfo('doctor_fans',$info);
				$this->doctor_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
			}else{
				$info = array("st"=>"1");
				$this->user_model->updateRecord('doctor_fans',$where,$info);
			}
			//医生关注量+1
			if(isset($careInfo['st']) && $careInfo['st']!="1"){
				$this->doctor_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
			}
			echo 1;exit;
		}elseif($status==2){
			$info = array("st"=>"0");
			$this->user_model->updateRecord('doctor_fans',$where,$info);
			$this->doctor_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>-1,'mul_num'=>-1)));
			//医生取消量-1
			echo 1;exit;
		
		}else{
			echo 2;exit;
		}
	}

	public function comment($id="") {
		if(empty($id)){ show_error("参数错误");}
		$pid= checkAuth3();
		//得到订单
		$order = $this->Common_model->getInfo('order',array('_id'=>getMdbId($id)));
		$doctor = $this->Common_model->getTableByRef('doctor',$order['doctor']);
		if(empty($order)){ show_error("订单不存在，请确认！"); }
		if($pid!=(string)$order['patient']['$id']){ show_error("订单不属于本人，不允许评论");}
		if($order['iscom']=="1"){ show_error("该订单已经评论了，不能再次评论哦！");}
		$btn = $this->input->post();
		if(isset($btn['btn1'])){
			$star = (int)$btn['star'];
			$msg = addslashes(trim($btn['message']));
			if(empty($star) || empty($msg)){ show_error("提交参数错误！"); }
			$info = array(
					'order'   =>(string)$order['_id'],
					"doctor"  =>(string)$order['doctor']['$id'],
					"patient" =>(string)$order['patient']['$id'],
					"p_name"  =>$order['name'],
					"p_gender"=>$order['gender'],
					"service" =>$order['service'],
					"star"    =>$star,
					"msg"     =>$msg,
					"tm"      =>time()
					);
			 $commentId = $this->Common_model->insertInfo('order_comment',$info);
			 if($commentId){
				//更新订单
				$this->Common_model->updateRecord('order',array('_id'=>$order['_id']),array('iscom'=>"1"));
				//更新医生好评率
				$mul_num = (int)$doctor['mul_num']+$star;
				$star = (int)((int)$doctor['comment']['star']+$star);
				$num = (int)((int)$doctor['comment']['num']+1);
				$per = ($star/($num*5))*100;
				$per =(int)round($per,0); 
				$uparr = array('star'=>$star,'num'=>$num,'per'=>$per);
				$this->Common_model->updateSetRecord('doctor',array('_id'=>$order['doctor']['$id']),array('$set'=>array('comment'=>$uparr,'mul_num'=>$mul_num)));
			 }
			 redirect($btn['from1']);
		}
		//得到医生
		if(empty($doctor)){ show_error("医生不存在!"); }
		$data['from'] = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"/";
		$data['order'] = $order;
		$data['doctor'] = $doctor;
		$this->load->view('doctor/comment',$data);
	}

	public function getcomment($id) {
		if(empty($id)){ echo 0;}
		$page = getCurPage(); $perpage = 10;
		$offset = getPage($page,$perpage);
		$field = array('p_name','p_gender','service','star','msg','tm');
		$comment = $this->Common_model->getListInfo('order_comment',array("doctor"=>$id),$offset,$perpage,array("tm"=>-1),$field);
		foreach($comment as $k=>$v){
			$comment[$k]['tm'] = date('Y-m-d',$v['tm']);
			$comment[$k]['p_name'] = mb_substr($v['p_name'],0,1);
		}
		echo json_encode($comment);
		exit;
	}

	public function qrcode($id) {
		if(empty($id)){ show_error("参数错误");}
		$where = array('_id'=>getMdbId($id));
		$doctor = $this->doctor_model->getInfo('doctor',$where,array('scene_id','position','name','department','title','hospital','avatar'));
		$data['doctor'] = $doctor;
		
		if(!isset($doctor['scene_id'])){ show_error("未找到该医生"); }
		$qrcode = $this->Common_model->getInfo('doctor_rqcode',array("scene_id"=>(int)$doctor['scene_id']));
		if(empty($qrcode)){ show_error("该医生还没有二维码"); }
		$data['qrcode']  = $qrcode;
		$this->load->view('doctor/qrcode',$data);
	}

	//精品文章
	public function article($doctor,$isdata) {
		if(empty($doctor)){ show_error("参数错误!"); }
		if($isdata){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			$where = array('doctor'=>getMdbId($doctor));
			$gzInfo = $this->Common_model->getListInfo('doctor_article',$where,$offset,$perpage,array("_id"=>-1));
			echo json_encode($gzInfo);exit;
		}
		$doctorInfo = $this->Common_model->getInfo('doctor',array('_id'=>getMdbId($doctor)),array('name'));
		if(empty($doctorInfo)){ show_error("该医生不存在!");}
		$data['doctorinfo'] = $doctorInfo;
		$data['doctor'] = $doctor;
		$this->load->view('doctor/article',$data);
	}





}

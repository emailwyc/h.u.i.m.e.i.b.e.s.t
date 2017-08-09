<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Act.php
 * 患教活动
 */
class Act extends CI_Controller {

	private $escape_code = "ZJHM";

	public function __construct(){
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('order_model');
		$this->load->model('doctor_model');
		$this->load->model('Img_model');
		$this->load->model('Chat_model');
		$this->load->model('date_model');
		$this->load->model('Coupons_model');
	}
	//医生节日
	public function day330() {
		$this->load->view('act/day330');
	}

	//公益义诊
	public function freeshare($id,$check="") {
		$aid = checkisScan(90086);
		$id = getMdbId($id);
		$info = $this->Common_model->getInfo('act_free',array('_id'=>$id));
		if(empty($info)){ show_error("没有找到该记录"); }
		$doctor = $this->Common_model->getInfo('doctor',array('_id'=>$info['doctor']));
		if(empty($doctor)){ show_error("该医生不存在，请检查"); }
			$code = $this->Common_model->getInfo('act_code',array('openid'=>$aid,'actid'=>$id));
		if(!empty($code) && empty($check)){
			redirect('/act/freesharecode/'.$id);exit;
		}
		$data['doctor'] = $doctor;
		$data['info'] = $info;
		$this->load->view('act/freeshare',$data);
	}

	public function freeshareover() {
		$this->load->view('act/freeshareover');
	}

	public function freesharecode($id) {
		$aid = checkisScan();
		$info = $this->Common_model->getInfo('act_free',array('_id'=>getMdbId($id)));
		if(empty($info)){ show_error("没有找到该记录"); }
		$code = $this->Common_model->getInfo('act_code',array('openid'=>$aid,'actid'=>getMdbId($id)));
		$data['code'] = $code;
		$data['info'] = $info;
		$this->load->view('act/freesharecode',$data);
	}

	public function getfreecode($id) {
		if(!$this->input->is_ajax_request() || empty($id)){ echo json_encode(array('st'=>0,'msg'=>"请求错误"));exit; }
		if(!$aid=getPatientOpenid()){ echo json_encode(array('st'=>0,'msg'=>"您还没有关注"));exit; }
		$info = $this->Common_model->getInfo('act_free',array('_id'=>getMdbId($id)));
		if(empty($info)){ json_encode(array('st'=>0,'msg'=>"没有找到该活动")); }
		if($info['regnum']['reg']>=$info['regnum']['all']){ echo json_encode(array('st'=>0,'msg'=>"报名已满"));exit;}
		//检查该用户是否已经生成码
		$code = $this->Common_model->getInfo('act_code',array('openid'=>$aid,'actid'=>$info['_id']));
		if($code){ echo json_encode(array('st'=>0,'msg'=>"您已经生成过该医生义诊口令"));exit; }
		$codem = getRandomPass(4,'CAPITAL');
		$info = array('openid'=>$aid,'actid'=>$info['_id'],'code'=>(string)$codem,'act'=>"义诊活动",'st'=>1,'add_time'=>time());
		$this->Common_model->insertInfo('act_code',$info);
		$this->Common_model->updateSetRecord('act_free',array('_id'=>getMdbId($id)),array('$inc'=>array('regnum.reg'=>1)));
		echo json_encode(array('st'=>1,'msg'=>$codem));exit;
	}

	public function recent() {
		$this->Login_model->weixinLoginCheck("act/recent");
		$this->load->view('act/recent');
	}

	public function doclist() {
		$this->Login_model->weixinLoginCheck("act/doclist");
		$data['curdate'] = date('Y-m-d H:i:s',time());
		$this->load->view('act/doclist',$data);
	}

	public function doclistjson(){
		if(!$this->input->is_ajax_request()){ echo 0;exit; }
		$curtime = time();
		$curdate = (string)date('Y-m-d',$curtime);
		$curdatetime = (string)date('H:i:s',$curtime);
		$info = $this->Common_model->getInfoAll('act_free',array('startT'=>array('$lte'=>$curdate),'endT'=>array('$gte'=>$curdate)));
		$result = array();
		if($info){
			foreach($info as $k=>$v){
				$docInfo = $this->Common_model->getInfo('doctor',array('_id'=>$v['doctor']));
				if(!empty($docInfo)){
					$v['doctor'] = $docInfo;
					$v['isshow'] = ($curdatetime>=$v['daysT'] && $curdatetime<=$v['dayeT'])?1:0;
					$v['daysT'] = timeChange12($v['daysT']);
					$v['dayeT'] = timeChange12($v['dayeT']);
					$result[] = $v;
				}
			}
		}
		echo json_encode($result);exit;
	}

	public function doclistjson1(){
		if(!$this->input->is_ajax_request()){ echo 0;exit; }
		$curdate = (string)date('Y-m-d',time()+86400);
		$info = $this->Common_model->getListInfo('act_free',array('startT'=>array('$gte'=>$curdate)),0,20,array('startT'=>1));
		$result = array();
		if($info){
			foreach($info as $k=>$v){
				$docInfo = $this->Common_model->getInfo('doctor',array('_id'=>$v['doctor']));
				if(!empty($docInfo)){
					$v['doctor'] = $docInfo;
					$v['daysT'] = timeChange12($v['daysT']);
					$v['dayeT'] = timeChange12($v['dayeT']);
					$result[] = $v;
				}
			}
		}
		echo json_encode($result);exit;
	}

	//活动
	public function index() {
		$this->Login_model->weixinLoginCheck("act/index");
		$data['content'] = "content";
		$this->load->view('act/index',$data);
	}

	//精品订阅
	public function article($isdata) {
		if($isdata){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			$openid = getPatientOpenid();
			if($openid){
				$doc_arr = array();
				$doctor = $this->Common_model->getListInfo('doctor_fans',array('openid'=>$openid,'st'=>"1"),0,500,"",array('tid'));
				foreach($doctor as $v){
					$doc_arr[]=getMdbId($v['tid']);
				}
				$where = array('doctor'=>array('$in'=>$doc_arr));
			}else{
				$where = array();
			}
			$gzInfo = $this->Common_model->getListInfo('doctor_article',$where,$offset,$perpage,array("_id"=>-1));
			echo json_encode($gzInfo);exit;
		}
		$this->Login_model->weixinLoginCheck("act/article");
		//排序
		$this->load->view('act/article');
	}

	//精品文章
	public function artwell($isdata) {
		if($isdata){
			$page = getCurPage(); $perpage = 10;
			$offset = getPage($page,$perpage);
			$where = array();
			$gzInfo = $this->Common_model->getListInfo('article_hot',$where,$offset,$perpage,array("_id"=>-1));
			echo json_encode($gzInfo);exit;
		}
		$this->Login_model->weixinLoginCheck("act/artwell");
		//排序
		$this->load->view('act/artwell');
	}
	//过年活动
	public function healthy(){
		$ip = (string)@$this->input->ip_address();
		$info = $this->Common_model->getInfo('act_log',array('name'=>'healthy','ip'=>$ip));
		if(empty($info)){
			$this->Common_model->insertInfo('act_log',array('name'=>'healthy','ip'=>$ip,'info'=>array('step1'=>1,'step2'=>0),'ct'=>time()));
		}else{
			$this->Common_model->updateSetRecord('act_log',array('name'=>'healthy','ip'=>$ip),array('$inc'=>array('info.step1'=>1)));
		}
		$this->load->view('act/healthy');
	}
	public function healthyJson($type="img",$tag=""){
		$ip = (string)@$this->input->ip_address();
		if(!$this->input->is_ajax_request()){ echo 0;exit; }
		$type = $type=="img"?"info.step2":"info.step3";
		$tag = (int)$tag;
		$tag  = ($tag>100 || $tag<=0)?0:$tag;
		$tag = "info.tag".$tag;
		$this->Common_model->updateSetRecord('act_log',array('name'=>'healthy','ip'=>$ip),array('$inc'=>array($type=>1,$tag=>1)));
		echo 1;exit;
	}

	public function healthyImg(){
		$data = $this->input->post();
		//dir,size,msg,type,quality,isthumb
		$data['quality'] = false;
		$data['dir'] = "act";
		$data['size']['x'] = 600;
		$data['size']['y'] = 600;
		$result = $this->Img_model->ImgUploadOne($data);
		echo json_encode($result);
	}
	public function day330Img(){
		$data = $this->input->post();
		//dir,size,msg,type,quality,isthumb
		$data['quality'] = false;
		$data['dir'] = "act";
		$data['size']['x'] = 540;
		$data['size']['y'] = 900;
		$result = $this->Img_model->ImgUploadOne($data);
		echo json_encode($result);
	}

	//公益活动
	public function free($id) {
		$data = array();
		$id = getMdbId($id);
		$info = $this->Common_model->getInfo('act_free',array('_id'=>$id));
		if(empty($info)){ show_error("没有找到该记录"); }
		$aid = checkisScan();
		$code = $this->Common_model->getInfo('act_code',array('openid'=>$aid,'actid'=>$id));
		$data['code'] = $code;
		$data['info'] = $info;
		$this->load->view('act/free',$data);
	}
	public function freeJson(){
		if(!$this->input->is_ajax_request()){ echo json_encode(array('st'=>0,'msg'=>"请求错误"));exit; }
		$data = $this->input->post();
		$aid=getPatientOpenid();
		if(empty($aid)){ echo json_encode(array('st'=>0,'msg'=>"您还没有关注，请先关注“找明医”"));exit;}
		if(!empty($data['code']) && !empty($data['actid'])){
			$code = (string)trim($data['code']);
			if($code==$this->escape_code){
				echo json_encode(array('st'=>1,'msg'=>"success"));exit;
			}
			$info = $this->Common_model->getInfo('act_code',array('openid'=>$aid,'code'=>$code,'actid'=>getMdbId($data['actid'])));
			if($info){
				//if($info['st']!=1){ echo json_encode(array('st'=>0,'msg'=>"口令已经使用"));exit; }
				echo json_encode(array('st'=>1,'msg'=>"success"));exit;
		   	}else{ echo json_encode(array('st'=>0,'msg'=>"口令不存在"));exit; }
		}else{ echo json_encode(array('st'=>0,'msg'=>"参数错误"));exit; }
	}
	public function consult($actid,$code="") {
		if(empty($actid) || empty($code)){ show_error("参数错误");}
		$cur_time = time();
		$cur_date = date("Y-m-d",$cur_time);
		$cur_datetime = date("H:i:s",$cur_time);
		$pid =checkAuth3();
		$aid = checkisScan();
		//检查口令是否使用
		$actInfo = $this->Common_model->getInfo('act_free',array('_id'=>getMdbId($actid)));
		if($actInfo['startT']>$cur_date || $actInfo['endT']<$cur_date){ show_error("不在活动时间内"); }
		if($cur_datetime<$actInfo['daysT'] || $cur_datetime>$actInfo['dayeT']){ show_error("活动还没有开始"); }

		$codeInfo = $this->Common_model->getInfo('act_code',array('actid'=>getMdbId($actid),'openid'=>$aid));
		if(empty($codeInfo) && $code ==$this->escape_code){
			$codeInfo = array('openid'=>$aid,'actid'=>$actInfo['_id'],'code'=>$this->escape_code,"act"=>"义诊活动","st"=>1,"add_time"=>time());
			$this->Common_model->insertInfo('act_code',$codeInfo);
		}
		if(empty($codeInfo)){ show_error("未找到口令,请检查");}

		if(!empty($codeInfo['oid'])){ redirect("/user/chat/".$codeInfo['oid']); exit;}
		$btn = $this->input->post();
		$patInfo= $this->Common_model->getInfo('patient',array('_id'=>getMdbId($pid)));
		if(isset($btn['btn1'])){
			$docInfo = $this->doctor_model->getInfo('doctor',array('_id'=>getMdbId($btn['doctor'])));
			if(empty($docInfo)|| empty($patInfo)){ show_error("参数错误!"); }
			//参数处理start
			$msg = addslashes(trim($btn['message']));
			$imgArr = isset($btn['img'])?$btn['img']:array();
			$price_t = 0; $coup_id = ""; $price_ori = 0; $stat = "已支付";
			//db添加数据
			$docref= MongoDBRef::create("doctor", $docInfo['_id']);
			$patref= MongoDBRef::create("patient", $patInfo['_id']);;
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
					"mobile"=>"",
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
					"fid"=>(string)$_SESSION['aid'],
					"nomsg"=>0,
					"iscom"=>"0",
					"seq"=>(string)$ordernum,
					"coupons"=>$coup_id,
					"ext"=>array('type'=>"义诊","code"=>$code,"actid"=>$actInfo['_id'])
					);
			$check = (string)$this->order_model->insertInfo("order",$insertData);
			if($check){
				$this->Common_model->updateRecord('act_code',array("actid"=>$actInfo['_id'],"openid"=>$aid,"code"=>$code),array('st'=>0,'pid'=>$patInfo['_id'],'oid'=>$check));
				$url = config_item('global_base_url')."/Callback/consult_pay";
				$postdata = http_build_query(array("orderId" =>(string)$check));
				$order_data = do_post_request($url, $postdata);
				redirect("/consult/question/".$check);
				exit;
			}else{
				show_error("提交失败");
			}
		}
		//得到医生信息
		$doctor = $this->doctor_model->getDoctorByWhere(array('_id'=>$actInfo['doctor']));
		if(empty($doctor)){show_error("未找到医生");} $doctor = $doctor[0];
		$data['doctor'] = $doctor;
		$data['code'] = $code;
		$data['patient'] = $patInfo;
		$data['actinfo'] = $actInfo;
		$this->load->view('act/consult',$data);
	}

}

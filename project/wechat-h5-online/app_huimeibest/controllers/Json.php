<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Json.php
 * Json数据处理
 */

class Json extends CI_Controller {

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
		$this->load->model('hospital_model');
		$this->load->model('Jssdk_model');
		$this->load->model('Order_model');
		$this->load->model('Img_model');
	}

	public function getdep($id) {
		//得到一级科室
		if($id=="tuijian"){
			$data = $this->department_model->getTjDepartment(array());
		}else{
			$id = explode('_',$id);
			if(empty($id[0]) || $id[1]!="1"){ echo 0;exit;}
			$pid = getMdbId($id[0]);
			$data = $this->department_model->getSecondDepartment(array(),300,$pid);
		}
		echo json_encode($data);exit;
	}

	public function gethos($id) {
		//得到一级科室
		if(empty($id)){echo 0;exit;}
		if($id=="tuijian"){
			$data = $this->hospital_model->getHosByWhere(array(),array(),10);
		}else{
			$pid = getMdbId($id);
			$where = array('region_id'=>$pid);
			$data = $this->hospital_model->getHosByWhere($where);
		}
		echo json_encode($data);exit;
	}

	//得到优惠券
	public function getcoupons($type) {
		//0失败
		$userInfo = checkAuth2();
		if(empty($userInfo) || empty($type)){ echo 0;exit; }
		$cur_time = time();
		$type = (int)$type;
		$where = array('openid'=>$userInfo['aid'],'end_time'=>array('$gte'=>$cur_time),'start_time'=>array('$lte'=>$cur_time),'st'=>1,'type'=>array('$in'=>array($type,1)));
		$coupons = $this->Common_model->getInfoAll('user_coupons',$where);
		if($coupons){
			foreach($coupons as $k=>$v){
				$coupons[$k]['end_time']=date('Y-m-d',$v['end_time']);
			}
		}
		echo json_encode($coupons);exit;
	}

	//上传图片
	public function uploadImg() {
		$pid= checkAuth1();
		if(empty($pid)){ echo json_encode(array('st'=>0,'msg'=>"没有登录"));exit; }
		$data = $this->input->post();
		//$data = array('img_info'=>"asdf",'img_type'=>'png');
		$data['pid'] = $pid;
		$result = $this->Img_model->patientImgUploadOne($data);
		echo json_encode($result);
	}

	//图文发送消息
	public function consultMsg() {
		$pid= checkAuth1();
		if(empty($pid)){ echo json_encode(array('st'=>0,'msg'=>"没有登录"));exit; }
		$data = $this->input->post();
		//$data = array('msg'=>"你好",'type'=>'txt','remarks'=>"txt",'orderid'=>'5678f11db7ef6acd118b45ef','doctor'=>'55f95ab283cdf8575d62dd87');
		$msg_id = (string)time().getRandomPass(7,"NUMBER");
		if(empty($data['msg'])){
			echo json_encode(array('st'=>2,'msg'=>"消息为空"));exit;
		}
		$data['pid'] = $pid;
		$cur_date = new MongoDate(time());
		if($data['type']=="txt"){
			//处理文字消息
			$data['msg'] = addslashes(trim($data['msg']));
			$insertdata = array('orderid'=>$data['orderid'],'doctor'=>$data['doctor'],'patient'=>$pid,'msg_id'=>$msg_id,'sender'=>"patient",'type'=>'txt','msg'=>$data['msg'],'ext'=>(object)array(),'isshow'=>true,'ct'=>$cur_date);
			$id = $this->Common_model->insertInfo('hx_history_local',$insertdata);
			if($id){
				echo json_encode(array('st'=>1,'msg'=>$data['msg'],'id'=>(string)$id));exit;
			}else{
				echo json_encode(array('st'=>2,'msg'=>"服务器处理失败"));exit;
			}
		}elseif($data['type']=='img'){
			//处理图片消息
			$result = $this->Img_model->ConsultImgUploadOne($data);
			if($result['st']==1){
				$insertdata = array('orderid'=>$data['orderid'],'doctor'=>$data['doctor'],'patient'=>$pid,'msg_id'=>$msg_id,'sender'=>"patient",'type'=>'img','msg'=>$result['thumb'],'ext'=>array('size'=>$result['size'],'url'=>$result['msg']),'isshow'=>true,'ct'=>$cur_date);
				$id = $this->Common_model->insertInfo('hx_history_local',$insertdata);
				if($id){
					$result['id'] = (string)$id;
					echo json_encode($result);exit;
				}else{
					echo json_encode(array('st'=>2,'msg'=>"服务器处理失败"));exit;
				}
			}else{
				echo json_encode($result);exit;
			}
		}else{
			echo json_encode(array('st'=>2,'msg'=>"消息类型错误"));exit;
		}
	}

	public function getClientSign() {
		$url = $this->input->post();
		if(!$this->input->is_ajax_request() || empty($url['url'])){ echo 0;exit; }
		echo $this->Jssdk_model->getSignPackage($url['url']);
	}
	//得到就诊人
	public function getPatientFamily() {
		$pid = checkAuth1();
		if(empty($pid) || !$this->input->is_ajax_request()){
			echo 0;exit;
		}
		//检测用户是否添加就诊人
		$this->load->model('user_model');
		$patient = $this->user_model->getPatientFamily($pid);
		echo json_encode($patient);exit;
	}

	//得到小红点
	public function getNoMsg(){
		$pid= checkAuth1();
		if(empty($pid)){ echo 0;exit; }
		$patref= MongoDBRef::create("patient",getMdbId($pid));
		$where = array('service'=>'consult',"nomsg"=>1,'patient'=>$patref);
		$result = $this->Common_model->getInfoCount('order',$where);
		if($result){
			echo 1;
		}else{
			echo 0;
		}
	}

	//优惠券兑换码，
	public function CodeExchange(){
		$pid = getPatientOpenid();
		$cur_time = time();
		if(!$pid){ echo json_encode(array('st'=>1,'msg'=>"您还没有登录"));exit; }
		$data = $this->input->post();
		if(empty($data['code'])){ echo json_encode(array('st'=>2,'msg'=>"兑换码不能为空"));exit; }
		$code = addslashes(trim($data['code']));
		//查找兑换码
		$codeInfo = $this->Common_model->getInfo('user_exchange',array('code'=>(string)$code));
		if(empty($codeInfo)){
			echo json_encode(array('st'=>3,'msg'=>"兑换码错误！"));exit;		
		}
		//验证兑换码时间
		if($codeInfo['start_time']>$cur_time || $codeInfo['end_time']<$cur_time){
			echo json_encode(array('st'=>4,'msg'=>"兑换码不再有效期内！"));exit;		
		}
		//验证对话码是否使用
		if($codeInfo['status']!=1){
			echo json_encode(array('st'=>5,'msg'=>"该兑换码已经使用,不可重复使用！"));exit;		
		}
		//验证该用户是否兑换过该活动
		$userIsUse = $this->Common_model->getInfoCount('user_exchange',array('from'=>$pid,'act'=>$codeInfo['act']));
		if($userIsUse>0){
			echo json_encode(array('st'=>6,'msg'=>"您已经参与该活动，不可重复参与！"));exit;	
		}
		//赠送优惠券
		$actArr = array(1,2);
		if(in_array($codeInfo['act'],$actArr)){
			$starT = strtotime('2016-01-09 00:00:00');
			$endT  = strtotime('2017-01-09 23:59:59');
		}else{
			$starT = strtotime('2016-01-09 00:00:00');
			$endT  = strtotime('2017-01-09 23:59:59');
		}
		$insertData3 = array('openid'=>$pid,'type'=>$codeInfo['type'],'price'=>$codeInfo['price'],'start_time'=>$starT,'end_time'=>$endT,'st'=>1,'from'=>3,'remark'=>$codeInfo['remarks']);
		$check = $this->Common_model->insertInfo('user_coupons',$insertData3);
		//更新兑换码状态
		if($check){
			$this->Common_model->updateRecord('user_exchange',array('_id'=>$codeInfo['_id']),array('status'=>0,'from'=>$pid));
			$error = array('st'=>0,"msg"=>"兑换成功");
			echo json_encode($error);exit;
		}else{
			$error = array('st'=>7,"msg"=>"兑换失败！");
			echo json_encode($error);exit;
		}
	}

	//音频转换
	public function videoExchange(){
		$pid = getPatientOpenid();
		$cur_time = time();
		$month = date('Ym',$cur_time);
		if(!$pid){ echo json_encode(array('st'=>1,'msg'=>"您还没有登录"));exit; }
		$data = $this->input->post();
		if(empty($data['msgid']) || empty($data['url'])){
			echo json_encode(array('st'=>2,'msg'=>"参数错误!"));exit;
		}
		$dbfiles = './ui/patient/video/'.$month."/";
		$dbfile  = './ui/patient/video/'.$month."/".$data['msgid'].".mp3";
		$url     = config_item('global_base_url').'/ui/patient/video/'.$month."/".$data['msgid'].".mp3";
		mk_dir($dbfiles);
		$orgfiles = "/tmp/".$data['msgid'].".amr";
		if(!file_exists($orgfiles)){
			$check = copy($data['url'],$orgfiles);
			if(!$check){ echo json_encode(array('st'=>3,'msg'=>"音频地址错误!"));exit; }
			@exec("/usr/bin/ffmpeg -i $orgfiles  $dbfile 2>&1",$out,$ret);

		}
		$this->Common_model->updateRecord('hx_history',array('msg_id'=>(string)$data['msgid']),array('video'=>array('url'=>$url)));
		echo json_encode(array('st'=>0,'msg'=>$url));exit;	
	}

	//订单支付失败
	public function payFail() {
		$fid = getPatientOpenid();
		if(empty($fid) || !$this->input->is_ajax_request()){ echo 0;exit; }
		$data = $this->input->post();
		if(empty($data['order_id']) || empty($data['st'])){
			echo 0;exit;
		}
		$data['st'] = $data['st']=="1"?"已取消":"已取消";
		$this->Common_model->updateRecord('order',array('fid'=>$fid,'_id'=>getMdbId($data['order_id']),'status'=>"新订单"),array('status'=>$data['st']));
		echo 1;exit;

	}

	//订单支付失败
	public function codepayFail() {
		if($this->input->is_ajax_request()){ echo 0;exit; }
		$data = $this->input->post();
		if(empty($data['order_id']) || empty($data['st'])){
			echo 0;exit;
		}
		$data['st'] = $data['st']=="1"?2:2;
		$this->Common_model->updateRecord('order_qrcode',array('_id'=>getMdbId($data['order_id']),'status'=>0),array('status'=>$data['st']));
		echo 1;exit;

	}

	//义诊活动
	public function freeclinic() {
		if(!$this->input->is_ajax_request()){ echo 0;exit; }
		$data = $this->input->post();
		if($data['tel']){
			$data['name'] = "freeclinic";
			$data['ct'] = time();
			$this->Common_model->insertinfo('act_log',$data);
			echo 1;exit;
		}else{
			echo 0;exit;
		}
	}

	//websiteQue
	public function websiteQue() {
		header('Access-Control-Allow-Origin: *');
		$data1 = $this->input->post();
		$ip = $this->input->ip_address();
		if(!empty($data1)){
			$data = $data1;
			$data['ip'] = $ip;
			$data['ct'] = time();
			$this->Common_model->insertinfo('act_free_log',$data);
			echo 1;exit;
		}else{
			echo 0;exit;
		}
	}

	//得到官网文章列表
	public function websiteGetArticleList(){
		header('Access-Control-Allow-Origin: *');
		$page = getCurPage(); $perpage = 6;
		$offset = getPage($page,$perpage);
		//得到总分页
		$where = array();
		if(!empty($_REQUEST['class'])){ $where['class'] =  (int)$_REQUEST['class']; }
		$allcount = $this->Common_model->getInfoCount('website_article',$where);
		$allpage = ceil($allcount/$perpage);
		//文章列表
		$fields = array('_id','title','description','icon','class','like_num','comment_num','created_at','order');
		$result = $this->Common_model->getListInfo('website_article',$where,$offset,$perpage,array('order'=>-1,"_id"=>-1),$fields);
		if($result){
			foreach($result as $k=>$v){
				$result[$k]['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
			}
		}
		display(array('page'=>$allpage,'data'=>$result));
	}

	//得到文章详情
	public function websiteGetArticleDetails(){
		header('Access-Control-Allow-Origin: *');
		if(empty($_POST['_id'])){ echo display(array(),2,"参数有误");}
		$artId = getMdbId($_REQUEST['_id']);
		$where = array('_id'=>$artId);
		$result = $this->Common_model->getInfo('website_article',$where);
		if($result){
			$result['top'] = $this->Common_model->getInfoSort('website_article',array('_id'=>array('$lt'=>$artId),'class'=>$result['class']),array('_id','title'),array('_id'=>-1));
			$result['next'] = $this->Common_model->getInfoSort('website_article',array('_id'=>array('$gt'=>$artId),'class'=>$result['class']),array('_id','title'),array('_id'=>1));
			$result['updated_at'] = date('Y-m-d H:i:s',$v['updated_at']);
			$result['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
		}
		display($result);
	}


}

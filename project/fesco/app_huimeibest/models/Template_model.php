<?php
class Template_model extends CI_Model{

	private $appId = "";
	function __construct()
	{
		parent::__construct();
		$this->appId = $this->config->item('global_wx_appid');
		$this->load->helper('weixin_helper');
	}
	
	public function sendClinicSuccess($order){
		$data = array();
		$sex = $order['gender']=="male"?"男":"女";
		$shedu1 = (string)$order['schedule'];
		$shedu2 = $order['hour']<12?" 上午":" 下午";
		$fid = $order['fid'];
		$data['touser'] = $fid;
		$data['template_id'] = $this->config->item('global_wx_template1');
		$url = weixin_redirect_uri($this->config->item('global_base_url')."/user/clinicser/2/".$order['_id']."/1?auth=wx",$this->appId,'userclinicser');
		$data['url'] = $url;
		$data['data'] = array();
		$data['data']['first'] = array('value'=>"您的预约已经成功，请按时就诊！","color"=>"#000000");
		$data['data']['patientName'] = array('value'=>$order['name'],"color"=>"#000000");
		$data['data']['patientSex'] = array('value'=>$sex,"color"=>"#000000");
		$data['data']['hospitalName'] = array('value'=>$order['hos'],"color"=>"#000000");
		$data['data']['department'] = array('value'=>$order['dep'],"color"=>"#000000");
		$data['data']['doctor'] = array('value'=>$order['docname'],"color"=>"#000000");
		$data['data']['seq'] = array('value'=>$order['seq'],"color"=>"#000000");
		$remark = "就诊时间：".$shedu1.$shedu2."\n就诊状态：待就诊\n小贴士：请携带好医保卡或身份证、病例本，如果有其他检查身体的资料请带齐。我们会有专业客服在就诊前一天一对一与您联系，沟通具体取号流程，助您便捷就医。如有其他问题请联系客服： 400-068-6895 ，谢谢！";
		$data['data']['remark'] = array('value'=>$remark,"color"=>"#000000");
		$data = json_encode($data);
		if(!empty($fid)){
			$result = $this->Jssdk_model->sendTemplateMsg($data);
		}else{
			$result = false;
		}
		return $result;
	}

	public function sendClinicSuccess1($order){
		$data = array();
		$sex = $order['gender']=="male"?"男":"女";
		$shedu1 = (string)$order['schedule'];
		$shedu2 = $order['hour']<12?" 上午":" 下午";
		$fid = $order['fid'];
		$data['touser'] = $fid;
		$data['template_id'] = $this->config->item('global_wx_template1');
		$url = weixin_redirect_uri($this->config->item('global_base_url')."/user/clinicser/2/".$order['_id']."/1?auth=wx",$this->appId,'userclinicser');
		$data['url'] = $url;
		$data['data'] = array();
		$data['data']['first'] = array('value'=>"您有患者挂号预约成功！","color"=>"#000000");
		$data['data']['patientName'] = array('value'=>$order['name'],"color"=>"#000000");
		$data['data']['patientSex'] = array('value'=>$sex,"color"=>"#000000");
		$data['data']['hospitalName'] = array('value'=>$order['hos'],"color"=>"#000000");
		$data['data']['department'] = array('value'=>$order['dep'],"color"=>"#000000");
		$data['data']['doctor'] = array('value'=>$order['docname'],"color"=>"#000000");
		$data['data']['seq'] = array('value'=>$order['seq'],"color"=>"#000000");
		$remark = "就诊时间：".$shedu1.$shedu2."\n就诊状态：待就诊\n患者电话：".$order['mobile'];
		$data['data']['remark'] = array('value'=>$remark,"color"=>"#000000");
		$data = json_encode($data);
		if(!empty($fid)){
			$result = $this->Jssdk_model->sendTemplateMsg($data);
		}else{
			$result = false;
		}
		return $result;
	}

	public function sendPhoneCall($order){
		$data = array();
		$fid = $order['fid'];
		$data['touser'] = $fid;
		$data['template_id'] = $this->config->item('global_wx_template5');
		$url = weixin_redirect_uri($this->config->item('global_base_url')."/user/pcdetails/".$order['_id']."?auth=wx",$this->appId,'userpcdetails');
		$data['url'] = $url;
		$data['data'] = array();
		$data['data']['first'] = array('value'=>"您的预约已经成功，请注意接听来电！\n","color"=>"#000000");
		$data['data']['keyword1'] = array('value'=>$order['hospital'],"color"=>"#000000");
		$data['data']['keyword2'] = array('value'=>$order['docname'],"color"=>"#000000");
		$data['data']['keyword3'] = array('value'=>$order['time'],"color"=>"#000000");
		$remark = "流水号码：".$order['seq']."\n\n小贴士：医生会在就诊时间段内给您打电话，来电显示为400-068-6895，请您注意查看手机来电，及时接收医生电话。如服务中有其他问题请联系客服： 400-068-6895 ，谢谢！";
		$data['data']['remark'] = array('value'=>$remark,"color"=>"#000000");
		$data = json_encode($data);

		if(!empty($fid)){
			$result = $this->Jssdk_model->sendTemplateMsg($data);
		}else{
			$result = false;
		}
		return $result;
	}

	public function sendDoctorAnwser($fid,$orderid,$que,$ans){
		$data = array();
		$data['touser'] = $fid;
		$data['template_id'] = $this->config->item('global_wx_template2');
		$url = weixin_redirect_uri($this->config->item('global_base_url')."/user/chat/".$orderid."/2?auth=wx",$this->appId,'userchat');
		$data['url'] = $url;
		$data['data'] = array();
		$data['data']['first'] = array('value'=>"您有新的回复，请及时查看！\n","color"=>"#333333");
		$data['data']['keyword1'] = array('value'=>$que,"color"=>"#000000");
		$data['data']['keyword2'] = array('value'=>$ans,"color"=>"#000000");
		$remark = "\n小贴士：点击详情可以查看完整对话哦！";
		$data['data']['remark'] = array('value'=>$remark,"color"=>"#333333");
		$data = json_encode($data);
		if(!empty($fid)){
			$result = $this->Jssdk_model->sendTemplateMsg($data);
		}else{
			$result = false;
		}
		return $result;
	}

	public function sendCouponsByInvi($post){
		$data = array();
		$data['touser'] = $post['fid'];
		$data['template_id'] = $this->config->item('global_wx_template3');
		$data['url'] = $post['url'];
		$data['data'] = array();
		$data['data']['first'] = array('value'=>"感谢您的推荐，您已获得一张优惠券\n","color"=>"#000000");
		$data['data']['keyword1'] = array('value'=>"邀请好友得优惠券礼包","color"=>"#000000");
		$data['data']['keyword2'] = array('value'=>"--","color"=>"#000000");
		$data['data']['keyword3'] = array('value'=>$post['end_time'],"color"=>"#000000");
		$remark = "受邀人：".$post['beinvi'];
		$data['data']['remark'] = array('value'=>$remark,"color"=>"#000000");
		$data = json_encode($data);
		if(!empty($post['fid'])){
			$result = $this->Jssdk_model->sendTemplateMsg($data);
		}else{
			$result = false;
		}
		return $result;
	}

	public function pushMsgByDoctor($post){
		$data = array();
		$data['touser'] = $post['fid'];
		$data['template_id'] = $this->config->item('global_wx_template4');
		$url = weixin_redirect_uri($this->config->item('global_base_url')."/doctor/details/".$post['did']."?auth=wx",$this->appId,'doctordetails');
		$data['url'] = $post['url'];
		$data['data'] = array();
		$data['data']['first'] = array('value'=>"您好，您关注的医生有新服务开放，特此通知","color"=>"#000000");
		$data['data']['keyword1'] = array('value'=>$post['dname']."医生","color"=>"#000000");
		$data['data']['keyword2'] = array('value'=>$post['dposition'],"color"=>"#000000");
		$data['data']['keyword3'] = array('value'=>$post['dhospital'],"color"=>"#000000");
		$data['data']['keyword4'] = array('value'=>$post['ddepartment'],"color"=>"#000000");
		$type = array('1'=>"图文咨询",'2'=>"预约挂号",'3'=>"电话咨询");
		$remark = "开放服务类型：".$type[$post['type']];
		$data['data']['remark'] = array('value'=>$remark,"color"=>"#000000");
		$data = json_encode($data);
		if(!empty($post['fid'])){
			$result = $this->Jssdk_model->sendTemplateMsg($data);
		}else{
			$result = false;
		}
		return $result;
	}


}

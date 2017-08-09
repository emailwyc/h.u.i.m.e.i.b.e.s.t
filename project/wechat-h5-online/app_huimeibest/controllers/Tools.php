<?php
//定时任务
class Tools extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if($_SERVER['USER']!="root"){
			show_error("访问错误");	
		}
		$this->load->model('Order_model');
		$this->load->model('Chat_model');
		$this->load->model('Message_model');
	}
	
	//图文咨询48小时内自动结束
    public function updateConSt($clikey=""){
		$this->checkclikey($clikey);
		$cur_time = time();
		$cur_date = new MongoDate($cur_time);
		$yes_date = new MongoDate($cur_time-172800);
		$where= array('status'=>"已支付","updated_at"=>array('$lte'=>$yes_date),"service"=>"consult");
		$update= array('status'=>"已完成","updated_at"=>$cur_date);
		$this->Order_model->updateRecord('order',$where,$update);
	}
	//预约门诊1天后自动结束
    public function updateCliSt($clikey=""){
		$this->checkclikey($clikey);
		$cur_time = time();
		$cur_date = new MongoDate($cur_time);
		$where= array('status'=>"已支付","schedule"=>array('$lte'=>$cur_date),"service"=>"clinic");
		$update= array('status'=>"已完成","updated_at"=>$cur_date);
		$this->Order_model->updateRecord('order',$where,$update);
	}
	//环信得到历史记录
    public function getChatHistory($clikey=""){
		set_time_limit(0);
		$this->load->helper('file');
		$this->checkclikey($clikey);
		$wang = $this->Chat_model->getAccessToken();
		$this->Chat_model->getChatRecord($wang);
	}

	//每天下午5点发送挂号短信
	public function ClinicSendMsg($clikey=""){
		$this->checkclikey($clikey);
		set_time_limit(0);
		$cur_time = time()+86400;
		$cur_date = date('Y-m-d',$cur_time);
		$starT = new MongoDate(strtotime($cur_date." 00:00:00"));
		$endT = new MongoDate(strtotime($cur_date." 23:59:59"));
		$where = array('status'=>"已支付","schedule"=>array('$gte'=>$starT,'$lte'=>$endT),"service"=>"clinic");
		$result = $this->Common_model->getInfoAll('order',$where);
		foreach($result as $v){
			$postdata1 = array();
			$postdata1['mobile'] = $v['mobile'];
			$postdata1['name'] = $v['name'];
			$postdata1['location'] = $v['location'];
			$docInfo = $this->Common_model->getTableByRef('doctor',$v['doctor']);
			$postdata1['doctor'] = @$docInfo['name'];
			$postdata1['time'] = (string)date('Y-m-d',$v['schedule']->sec);
			$hour = date('H',$v['schedule']->sec);
			$postdata1['hours'] = $hour<13?"上午":"下午";
			$postdata1['type'] = "clinic";
			$ret = $this->Message_model->sendMsgFromCl($postdata1);
			sleep(1);
		}
	}


	private function checkclikey($clikey){
		$site_clikey = config_item("global_cron_clikey");
		if($site_clikey==$clikey){
			return true;
		}else{
			show_error("访问错误");	
		}
	}
}

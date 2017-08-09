<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户聊天
 */

class Chat extends CI_Controller {
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
		$userid = checkAuth3();
		$this->openid = $userid;
		$this->load->model('Chat_model');
		$this->load->model('Hx_model');
	}

	public function hx_history($oId="",$ts=""){
		if(empty($oId)){ echo 0;exit;}
		if(empty($ts)){ $ts = (int)time()."000"; }
		$ts = (float)$ts;
		$page = getCurPage(); $perpage = 100;
		$offset = getPage($page,$perpage);
		$or_where = array('oid'=>$oId,'timestamp'=>array('$lte'=>$ts),"otp"=>"1");
		$gzInfo = $this->Hx_model->getListInfo1('hx_history',$or_where,$offset,$perpage,array("timestamp"=>-1));
		echo json_encode($gzInfo);exit;
	}

	public function hx_history_new($oId=""){
		if(empty($oId)){ echo 0;exit;}
		$page = getCurPage(); $perpage = 100;
		$offset = getPage($page,$perpage);
		$or_where = array('orderid'=>$oId,"isshow"=>true);
		$gzInfo = $this->Hx_model->getListInfo1('hx_history_local',$or_where,$offset,$perpage,array("_id"=>-1));
		echo json_encode($gzInfo);exit;
	}

	public function test(){
		echo time();
		print_r(date('H:i:s',time()));
			$cur_date = new MongoDate(time());
			var_dump($cur_date);
			$cur_date = new MongoDate(time()."000");
			echo ($cur_date);exit;
		$arr = array();
		$arr['target_type'] = "users";
		$arr['target'][] = "";
		$arr['msg']['type'] = "txt";
		$arr['msg']['msg'] = "[预约通知]";
		$arr['from'] = "";
		$arr['ext']['title'] = "门诊预约成功通知";
		$arr['ext']['content'] = "详情";
		$arr['ext']['detail'] = "";
		$arr['ext']['link_text'] = "";
		$arr['ext']['order_id'] = "";

		
	}

	public function test1(){
		$wang = $this->Chat_model->getAccessToken();
		$data = array('username'=>"562c887df154d63c2b8b458f", 'password'=>md5("562c887df154d63c2b8b458fhmjz"),'nickname'=>"千影无痕");
		$check= $this->Chat_model->regUser($wang,$data);
	}



}

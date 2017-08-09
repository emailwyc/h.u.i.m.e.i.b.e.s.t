<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$msg_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/submail/';
require_once($msg_file.'SUBMAILAutoload.php');
/**
 * Msg.php
 */
class Msg extends CI_Controller {
	public $message_configs;

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('msg_model');
		$this->load->model('user_model');
	}

    /** 
     * 发送短信
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function regSend() {
		if(!$this->input->is_ajax_request()){ show_error("请求错误"); }
		$mobile = (string)trim($this->input->post('mobile'));
		if(!preg_match(config_item('global_mobile_format'), $mobile )){ echo 5;exit; }
		//检查用户是否存在
		$type_reg = !empty($_SESSION['aid'])?"weixin":"wap";
		$userinfo = $this->user_model->getUserByMobile($mobile,'patient',$type_reg);
		if(!empty($userinfo)){ echo 6;exit; }
		//检查用户是否满足发送验证码条件
		$info = $this->msg_model->getMobileInfo(array('mobile'=>$mobile,'st'=>1));
		//得到验证码；
		$code = (string)getRandomPass(4,'NUMBER');
		//mobile,code,num,ct,ut,tag,st
		$cur_time = (string)time();
		if(empty($info)){
			$xsend=$this->sendMsg($mobile,$code);
			log_message('error', json_encode($xsend));
			if(isset($xsend['status']) && $xsend['status']=="success"){
			//插入数据库
				$info = array('mobile'=>$mobile,'code'=>$code,'num'=>1,'ct'=>$cur_time,'ut'=>$cur_time,'tag'=>$cur_time,'st'=>1);
				$checkInsert = $this->msg_model->insertMobileMsg($info);
				if($checkInsert){
					echo 1;exit;
				}else{
					echo 4;exit;
				}
			}else{
				echo 4;exit;
			}
		}else{
			$diff_time1 = 7200;
			$diff_time2 = 110;
			$num_limit = 5;
			$diff_sj = $cur_time-$info['tag'];
			$diff_sj1 = $cur_time-$info['ut'];
			if($diff_sj<$diff_time1 && $info['num']>$num_limit){ echo 3;exit; }
			if($diff_sj1<$diff_time2){ echo 2;exit; }
			$xsend=$this->sendMsg($mobile,$code);
			log_message('error', json_encode($xsend));
			if(isset($xsend['status']) && $xsend['status']=="success"){
			//插入数据库
			$update = $diff_sj>$diff_time1 ? array('code'=>$code,'num'=>0,'ut'=>$cur_time,'tag'=>$cur_time):array('code'=>$code,'num'=>$info['num']+1,'ut'=>$cur_time);
				$checkUpdate = $this->msg_model->updateMobileMsg($info['_id'],$update);
				if($checkUpdate){
					echo 1;exit;
				}else{
					echo 4;exit;
				}
			}else{
				echo 4;exit;
			}

		}
		exit;
	}

    /** 
     * 发送短信
     * 
     * @param  null
     * @access public
     * @return void
     */
	private function sendMsg($mobile,$code) {
		$submail=new MESSAGEXsend(config_item('msg_config'));
		$submail->setTo($mobile);
		$submail->SetProject('ztcfN');
		$submail->AddVar('content',$code);
		$xsend=$submail->xsend();
		return $xsend;
	}


}

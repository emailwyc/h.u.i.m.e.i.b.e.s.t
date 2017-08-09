<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户聊天
 */

class Test extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('Jssdk_model');
		$this->load->model('Template_model');
	}


	private function test(){
		print_r($this->Jssdk_model->getSignPackage());
	}

	private function test1(){
		$all = $this->user_model->getInfoAll('patient_family',array());
		foreach($all as $k=>$v){
			$this->user_model->updateRecord("patient_family",array("_id"=>$v['_id']),array('openid'=>@(string)$v['patient']['$id']));
		}
	}

	private function test2(){
		$all = $this->user_model->getInfoAll('doctor',array());
		foreach($all as $k=>$v){
			$update = array();
			if(empty($v['con_num'])){$update['con_num']=0;}
			if(empty($v['reg_num'])){$update['reg_num']=0;}
			if(empty($v['rc_num'])){$update['rc_num']=0;}
			if(empty($v['mul_num'])){$update['mul_num']=0;}
			if(empty($v['starred'])){$update['starred']=0;}
			if(!empty($v['level']) && $v['level']!=3){$update['level']=3;}
			if(!empty($update)){
				$this->user_model->updateRecord("doctor",array("_id"=>$v['_id']),$update);
			}
		}
	}
	private function test3(){
	//	print_r($_SESSION);exit;
		$order = array();
		$ret = $this->Template_model->sendClinicSuccess($order);
		print_r($ret);exit;
	}





}

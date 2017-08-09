<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * GameApi.php
 */

class GameApi extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		//校验ip是否是制定ip-----start-------

		//校验ip是否是制定ip-----end-------
		$this->load->model('Api_model');
		$this->load->model('Coupons_model');
	}

	public function giveCoupons(){
		$post = $this->input->post();
		//校验参数合法性
		if(empty($post['openid']) || empty($post['type']) || empty($post['timestamp']) || empty($post['orderid']) || count($post)>5 ){
			echo $this->Api_model->getErrorStatus(10001);exit;
		}
		//验证签名是否正确
		if(!$this->Api_model->checkSign($post)){
			echo $this->Api_model->getErrorStatus(10002);exit;
		}
		//校验类型是否正确
		$couponsType = $this->config->item('global_coupons_type');
		if(empty($couponsType[$post['type']])){
			echo $this->Api_model->getErrorStatus(10100);exit;
		}
		//校验openid是否正确
		$cur_time = time();
		//检查orderid是否已经存在
		$isExist= $this->Coupons_model->getInfo('user_coupons',array('orderid'=>(string)$post['orderid']));
		if(!empty($isExist)){
			echo $this->Api_model->getErrorStatus(10102);exit;
		}
		$data = array(
				'orderid'=>(string)$post['orderid'],
				'openid'=>(string)$post['openid'],
				'type'=>(string)$post['type'],
				'price'=>(float)$couponsType[$post['type']],
				'start_time'=>$cur_time,
				'end_time'=>($cur_time+30*86400),
				'st'=>'1'
				);
		$check = $this->Coupons_model->insertInfo('user_coupons',$data);
		if($check){
			echo $this->Api_model->getErrorStatus(1,array("orderid"=>(string)$check));exit;
		}else{
			echo $this->Api_model->getErrorStatus(10101);exit;
		}
	}

}

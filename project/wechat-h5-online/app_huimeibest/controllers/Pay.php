<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Doctor.php
 */
class Pay extends CI_Controller {
	public $uid;
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
		$this->uid= $userid;
		$this->load->model('order_model');
		$this->load->model('doctor_model');
	}

    /** 
     * 图文咨询支付
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function consult($oid) {
		if(empty($oid)){ log_message('error',"doctorID not find!");exit;}
		//检测订单合法性
		$order = $this->order_model->getInfo('order',array('_id'=>getMdbId($oid)));
		if(empty($order)){ show_error("订单不存在!");}
		if($order['status']!="新订单"){ show_error("订单状态不正确!");}
		//检测订单医生是否可以下单
		$doctor = $this->doctor_model->getTableByRef('doctor',$order['doctor']);
		if(empty($doctor['service_provided']['consult']['on'])){
			show_error("医生已经该关闭了咨询功能，明天再来吧!");
		}
		$sign = array('oid'=>$check,'type'=>'consult','timestamp'=>time());
		$sign = authcode(json_encode($sign),"ENCODE");
		redirect(config_item("global_wx_payurl")."?sign=".$sign);exit;
	}

    /** 
     * 图文咨询支付
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function clinic($oid) {
		if(empty($oid)){ log_message('error',"doctorID not find!");exit;}
		//检测订单合法性
		$order = $this->order_model->getInfo('order',array('_id'=>getMdbId($oid)));
		if(empty($order)){ show_error("订单不存在!");}
		if($order['status']!="新订单"){ show_error("订单状态不正确!");}
		//检测订单医生是否允许下单
		$doctor = $this->doctor_model->getTableByRef('doctor',$order['doctor']);
		if(empty($doctor['service_provided']['clinic']['on'])){
			show_error("医生已经该关闭了预约功能，明天再来吧!");
		}
		$doctor_timetable = $this->doctor_model->getTableByRef('doctor_timetable',$order['doctor']);
		if(empty($doctor_timetable)){
			show_error("订单日程已经失效，请重新去下单吧!");
		}
		if($doctor_timetable['quantity']>=$doctor['service_provided']['clinic']['quantity']){
			show_error("已预约满，请重新去下单吧!");
		}
		$sign = array('oid'=>$check,'type'=>'clinic','timestamp'=>time());
		$sign = authcode(json_encode($sign),"ENCODE");
		redirect(config_item("global_wx_payurl")."?sign=".$sign);exit;
	}



}

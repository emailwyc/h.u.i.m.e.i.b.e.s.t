<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * CodePay.php
 */
class CodePay extends CI_Controller {
	
    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('order_model');
	}

    /** 
     * 扫码支付
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function index() {
		//下单过程
		$btn = $this->input->post();
		if(isset($btn['btn1'])){
			$price = addslashes(trim($btn['price']));
			$desc = addslashes(trim($btn['desc']));
			if(empty($price) || !is_numeric($price) || $price<=0) { show_error("您还没有输入金额有误,请检查！");	}
			if($price>9999999) { show_error("交易金额过大,请检查！");	}
			$price = number_format($price,2, '.', '');
			$randomtm = getRandomPass(3,'NUMBER');
			$ordernum = date('YmdHis',time())."4".$randomtm;
			$cur_time = time();
			$insertData = array(
				"price"=>$price,
				"desc"=>$desc,
				"seq"=>(string)$ordernum,
				"service"=>'codepay',
				"status"=>0,
				"created_at"=>$cur_time,
				"updated_at"=>$cur_time,
				"pay_at"=>$cur_time
			);
			$check = (string)$this->order_model->insertInfo("order_qrcode",$insertData);
			if($check){
				$sign = array('oid'=>$check,'type'=>'codepay','timestamp'=>$cur_time);
				$sign = authcode(json_encode($sign),"ENCODE");
				redirect(config_item("global_wx_code_payurl")."?sign=".$sign);
				exit;
			}
		}
		$this->load->view('codepay/index',$data);
	}

    /** 
     * 扫码支付订单详情
     * @param  null
     * @access public
     * @return void
     */
	public function details($id="") {
		if(empty($id)){ show_error("参数有误!");}
		//得到医生信息
		$where['_id'] = getMdbId($id);
		$order= $this->Common_model->getInfo('order_qrcode',$where);
		if(empty($order)){ show_error("订单未找到");}
		$data['order'] = $order;
		$this->load->view('codepay/details',$data);
	}




}

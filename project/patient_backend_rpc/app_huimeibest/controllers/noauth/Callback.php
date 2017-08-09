<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$alipay_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'../../libraries/alipay/';
require_once($alipay_dir."lib/alipay_notify.class.php");
require_once($alipay_dir."lib/alipay_rsa.function.php");
require_once($alipay_dir."lib/alipay_core.function.php");
/**
 * Callback.php回调接口
 */
class Callback extends CI_Controller {

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

	public function wechatPay(){
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$data = xmlToArray($xml);
		//签名校验
		if(!isset($data['sign']) || checkSign($data) == FALSE){
			log_message('USERS',"回调：签名错误");exit;
		}else{
			if($data['return_code']=="FAIL"){
				log_message('USERS',$data);exit;
			}elseif($data['result_code']=="FAIL"){
				log_message('USERS',$data);exit;
			}else{
				//校验订单
				$where = array('_id'=>getMdbId($data['out_trade_no']));
				$info = $this->Common_model->getInfo('pat_article_order',$where,array('status'));
				if(empty($info) || $info['status']>=1){ exit; }
				//成功之后的处理
				log_message('USERS',$data);
				$update = array('pay_info'=>array('transaction_id'=>$data['transaction_id'],'total_fee'=>@$data['total_fee']),'status'=>1,'pay_type'=>'wechat','updated_at'=>time());
				$this->Common_model->updateRecord('pat_article_order',$where,$update);
				echo arrayToXml(array('return_code'=>'SUCCESS'));exit;
			}
		}
		
	}

	public function alipayPay(){
		log_message('USERS',$_POST);
		$alipay_config = array(
			'partner'         => config_item('global_alipay_uid'),
			'private_key'     => file_get_contents(config_item('global_alipay_prikey_path')),
			'alipay_public_key'=> config_item('global_alipay_pubkey'),
			'service'         => 'mobile.securitypay.pay',
			'sign_type'       => "RSA",
			'input_charset'   => 'utf-8',
			'cacert'          => config_item('global_alipay_cacert_path'),
			'transport'       => 'http'
		
		);
		$alipayNotify = new AlipayNotify($alipay_config);
		if($alipayNotify->getResponse($_POST['notify_id'])) {
			if($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])) { 
				$where = array('_id'=>getMdbId($_POST['out_trade_no']));
				$info = $this->Common_model->getInfo('pat_article_order',$where,array('status'));
				if($_POST['trade_status'] == 'TRADE_FINISHED') {
					if(!empty($info) && $info['status']<1){
						$update = array('pay_info'=>array('buyer_email'=>@$_POST['buyer_email'],'trade_no'=>@$_POST['trade_no'],'total_fee'=>@$_POST['total_fee']),'status'=>1,'pay_type'=>'alipay','updated_at'=>time());
						$this->Common_model->updateRecord('pat_article_order',$where,$update);
					}
				}
				else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
					//交易成功， //必要时验证total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
					if(!empty($info) && $info['status']<1){
						$update = array('pay_info'=>array('buyer_email'=>@$_POST['buyer_email'],'trade_no'=>@$_POST['trade_no'],'total_fee'=>@$_POST['total_fee']),'status'=>1,'pay_type'=>'alipay','updated_at'=>time());
						$this->Common_model->updateRecord('pat_article_order',$where,$update);
					}
				}
				log_message('USERS',"success");
				echo "success";
			} else {
				log_message('USERS',"sign fail");
				echo "sign fail";
			}
		}else{
			log_message('USERS',"response fail");
			echo "response fail";
		}
	}


}

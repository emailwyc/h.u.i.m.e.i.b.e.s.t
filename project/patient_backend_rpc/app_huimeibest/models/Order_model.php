<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model {
	public $table = "pat_article_order";

	function __construct()
	{
		parent::__construct();
	}
	
	//微信预支付处理
	public function prePayWxchat($order){
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$ct = time();
		$params = array(
			'appid'      => config_item("global_wx_appid"),
			'mch_id'     => config_item("global_wx_mchid"),
			'device_info'=> "WEB",
			'nonce_str'  => getRandomPass(12),
			'body'       => "极致健康-付费文章",
			'detail'     => $order['art_title'],
			'out_trade_no' => (string)$order['_id'],
			'total_fee'  =>(int)(number_format($order['price_pay'],2, '.', '')*100),
			'spbill_create_ip'=> getClientIp(),
			'time_start' => date('YmdHis',$ct),
			'time_expire' =>date('YmdHis',$ct+900),
			'notify_url' =>config_item("global_wx_payurl"),
			'trade_type' => "APP"
		);
		$params['sign'] =$this->wxchatSign($params);
		$response = xmlToArray(postXmlCurl(arrayToXml($params),$url,30));
		log_message('USERS',$response);
		if($response['return_code']!='SUCCESS'){
			display(false,300,$response['return_msg']);
		}else{
			//校验签名
			return $response;
		}
	}


	//微信客户端发起支付所需参数
	public function getWxParameters($params){
		$arr = array(
			'appid'=>$params['appid'],
			'partnerid'=>$params['mch_id'],
			'prepayid'=>$params['prepay_id'],
			'package'=>"Sign=WXPay",
			'noncestr'=>(string)$params['nonce_str'],
			'timestamp'=>(string)time()
			);
		ksort($arr);
		$paramsKV = arrayKeyValue($arr);
		$strParams = implode("&",$paramsKV)."&key=".config_item("global_wx_key");
		$sign = strtoupper(md5($strParams));
		$arr['sign'] = $sign;
		$arr['package1'] = $arr['package'];
		unset($arr['package']);
		return $arr;
	}
	//微信签名
	public function wxchatSign($params){
		ksort($params);
		$paramsKV = arrayKeyValue($params);
		$strParams = implode("&",$paramsKV)."&key=".config_item("global_wx_key");
		return strtoupper(md5($strParams));
	}

	//支付宝预支付处理
	public function prePayAlipay($order){
		$params = array(
				'partner' => config_item("global_alipay_uid"),
				'seller_id' => config_item("global_alipay_seller"),
				'out_trade_no' => (string)$order['_id'],
				'total_fee' => number_format($order['price_pay'],2, '.', ''),
				'subject' => "极致健康-付费文章",
				'body' => $order['art_title'],
				'service' => "mobile.securitypay.pay",
				'_input_charset' => "utf-8",
				'notify_url' => config_item("global_alipay_payurl"),
				'payment_type' => "1",
				'it_b_pay'    =>"30m"
			);
		$data=createLinkstring1($params);
		$rsa_sign = urlencode(rsaSign($data, file_get_contents(config_item("global_alipay_prikey_path"))));
		$data = $data.'&sign='.'"'.$rsa_sign.'"'.'&sign_type='.'"RSA"';
		return $data;
	}



}


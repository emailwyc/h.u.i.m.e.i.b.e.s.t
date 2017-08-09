<?php
class Api_model extends CI_Model{

	private $Secret = "";

	function __construct()
	{
		parent::__construct();
		$this->Secret= $this->config->item('global_api_secret');
	}


	private function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}

	public function checkSign($post) {
		if(empty($post['sign'])){ return false;}
		$clientSign = $post['sign'];
		unset($post['sign']);
		$post['secret'] = $this->Secret;
		ksort($post);
		$str = implode("&",$post);
		$signature = sha1($str);
		if($clientSign==$signature){
			return true;
		}else{
			return false;
		}
	}

	public function getErrorStatus($key,$data=array()){
		$status = array(
				'0'=>"ok",
				'1'=>$data,
				'2'=>$data,
				'10001'=>"Params error!",
				'10002'=>"signature error!",
				'10100'=>"coupons type error!",
				'10101'=>"Failure!",
				'10102'=>"Orders already exist!",
				'10103'=>"not find doctor",
				);
		$temp = array('st'=>$key,'msg'=>$status[$key]);
		return json_encode($temp);
	}

	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

}

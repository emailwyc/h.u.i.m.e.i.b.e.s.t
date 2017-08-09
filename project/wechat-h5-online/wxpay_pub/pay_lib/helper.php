<?php
session_start();
date_default_timezone_set("PRC");
if (! function_exists ( 'getMdbId' )) {
    //getMdbId
    function getMdbId($str){
        try {
            $something = new MongoId($str);
        } catch (MongoException $ex) {
            $something = new MongoId();
        }
        return $something;
    }
}

if (! function_exists ( 'show_error' )) {
    function show_error($str,$st=""){
        redirect('/home/show_error?k='.$str);
    }
}
if (! function_exists ( 'httpRequest' )) {
	function httpRequest($url,$data = NULL) {   
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1); 
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}   
}
//检测用户时候注册
if (! function_exists ( 'checkAuth' )) {
    function checkAuth()
    {   
        if(isset($_SESSION['aid']) && $_SESSION['aid'] && isset($_SESSION['st']) && $_SESSION['st']) {
            return $_SESSION['aid'];
        } else redirect('/register/index');
    }   
}
//跳转
if (! function_exists ( 'redirect' )) {
    function redirect($url) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $url");
        exit;
    }   
}
function authcode($string, $operation = 'DECODE', $key = 'e9qec4676703da00d0f651c8b82728s9', $expiry = 0){
    if($operation == 'DECODE') {
        $string = urldecode($string);
        $string = str_replace('newsnowa','+',$string);
        $string = str_replace('newsnowb','&',$string);
        $string = str_replace('newsnowc','/',$string);
    }
    $ckey_length = 4;
    $key = md5($key ? $key : 'livcmsencryption ');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5("Newsnow"), -$ckey_length)) : ''; 
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = ''; 
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }   
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }   
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }   
    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        $ustr = $keyc.str_replace('=', '', base64_encode($result));
        $ustr = str_replace('+','newsnowa',$ustr);
        $ustr = str_replace('&','newsnowb',$ustr);
        $ustr = str_replace('/','newsnowc',$ustr);
        return $ustr;
    }
}
function wxPayData($Info){
	global $config;
	$result = array();
	$result['body'] = isset($config['order_type'][$Info['service']]) ? $config['order_type'][$Info['service']]:"未知描述";
	$result['out_trade_no'] = (string)$Info['_id'];
	$result['total_fee'] = number_format($Info['price'],2, '.', '')*100;
	$result['notify_url'] = isset($config['order_notify_url'][$Info['service']]) ? $config['order_notify_url'][$Info['service']]:WxPayConf_pub::NOTIFY_URL;
	//失败跳转
	if($Info['service']=="consult"){
		$result['url_fail'] = "/user/service";
		$result['url_success'] = "/consult/question/".$Info['_id'];
	}elseif($Info['service']=="clinic"){
		$result['url_fail'] = "/user/service";
		$result['url_success'] = "/user/clinicser/2/".$Info['_id'];
	}elseif($Info['service']=="phonecall"){
		$result['url_fail'] = "/user/service";
		$result['url_success'] = "/user/pcdetails/".$Info['_id'];
	}elseif($Info['service']=="codepay"){
		$result['url_fail'] = "/CodePay/index";
		$result['url_success'] = "/CodePay/details/".$Info['_id'];
	}else{
		$result['url_fail'] = "/user/service";
		$result['url_success'] = "/user/service";
	}
	//成功跳转
	return $result;
}
function do_post_request($url, $data, $optional_headers = null) {
	$params = array('http' => array(
			  'method' => 'POST',
			  'content' => $data
		   ));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	return $response;
}
function httpGet($url) {
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
function getThumbByOrg($img)
{
	$images = array();
	if(is_array($img)){
		if(!empty($img)){
			foreach($img as $v){
				$temp = @explode("_",$v);
				$temp1 =  @explode(".",@$temp[1]);
				if(empty($temp[1]) || empty($temp1[1])){ $thumb = $v;}else{
					$thumb = $temp[0]."_".$temp1[0]."_thumb.".$temp1[1];
				}
				$images[] = array('origin'=>$v,'thumbnail'=>$thumb);
			}
		}
	}else{
		$temp = @explode("_",$img);
		$temp1 =  @explode(".",@$temp[1]);
		if(!empty($temp[1]) || !empty($temp1[1])){ $thumb = $img;}else{
			$thumb = $temp[0]."_".$temp1[0]."_thumb.".$temp1[1];
		}
		$images = array('origin'=>$v,'thumbnail'=>$thumb);
	}
	return $images;
} 





<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//ini_set('session.gc_maxlifetime', 9999999999999);
//session_start();
//isset($PHPSESSID)?session_id($PHPSESSID):$PHPSESSID = session_id();
//setcookie('PHPSESSID', $PHPSESSID, time()+999999999,"/");
date_default_timezone_set("PRC");
if(!empty($_SERVER['QUERY_STRING'])){ parse_str($_SERVER['QUERY_STRING'], $_GET); }

function checkIsSelfHost() {
	if($_SERVER["REMOTE_ADDR"]=="127.0.0.1"){
		return true;
	}else{
		return false;
	}
}   

/** 
* http 请求 支持get、post
* @param string $url 要请求的地址
* @param $data  
*/
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

function alert($error){
	echo $error;
	exit;
}

if (! function_exists ( 'createFolder' )) {
	/**
	 * 创建文件夹
	 * @param string $path
	 */
	function createFolder($path){
		if(!file_exists($path)){
			createFolder(dirname($path));
			@mkdir($path,0777);
		}
	}
}


if (! function_exists ( 'toAddSlashes' )) {
	/**
	 * 预定义字符：单引号 (')、双引号 (")、反斜杠 (\)、NULL，添加反斜杠
	 * @param var $var
	 */
	function toAddSlashes($var){
		$str='';
		if(!isNullOrEmpty($var)){
			if(!get_magic_quotes_gpc()){
				if(is_array($var)){
					$str=array();
					foreach($var as $key=>$value){
						if(!is_array($value)){
							if(is_numeric($value)){
								$str=$var;
							}else{
								$str[$key]=addslashes($value);
							}
						}else{
							$str[$key]=toAddSlashes($value);
						}
					}
				}else if(is_numeric($var)){
					$str=$var;
				}else{
					$str=addslashes($var);
				}
			}
		}else{
			$str=$var;
		}

		return $str;
	}
}
if (! function_exists ( 'toStripslashes' )) {
	/**
	 * 预定义字符,删除反斜杠
	 * @param var $var
	 */
	function toStripslashes($var){
		$str='';
		if(!isNullOrEmpty($var)){
			//if(!get_magic_quotes_gpc()){
			if(is_array($var)){
				$str=array();
				foreach($var as $key=>$value){
					if(!is_array($value)){
						if(is_numeric($value)){
							$str=$var;
						}else{
							$str[$key]=stripslashes($value);
						}
					}else{
						$str[$key]=toStripslashes($value);
					}
				}
			}else if(is_numeric($var)){
				$str=$var;
			}else{
				$str=stripslashes($var);
			}
			//}
		}else{
			$str=$var;
		}

		return $str;
	}
}

if (! function_exists ( 'toHtmlSpecialChars' )) {
	/**
	 *  html代码 源码输出
	 * @param var $var
	 */
	function toHtmlSpecialChars($var){
		$str='';
		if(!isNullOrEmpty($var)){
				
			if(is_array($var)){
				$str=array();
				foreach($var as $key=>$value){
					if(!is_array($value)){
						$str[$key]=htmlspecialchars($value);
					}else{
						$str[$key]=toHtmlSpecialChars($value);
					}
				}
			}else{
				$str=htmlspecialchars($var);
			}
				
		}else{
			$str=$var;
		}

		return $str;
	}
}

if (! function_exists ( 'toStriForHtmlSpeChar' )) {
	/**
	 *  去掉反斜杠，html代码 源码输出
	 * @param var $var
	 */
	function toStriForHtmlSpeChar($var){
		$str=toHtmlSpecialChars(toStripslashes($var));
		return $str;
	}
}

if (! function_exists ( 'session' )) {
	/**
	 * 设置或获取$_SEESION 的值。$value为空，则获取 值；否则，设置 值
	 * @param string $key
	 * @param string $value
	 * @param array $isArray
	 * @return Array
	 */
	function session($key,$value=NULL,$isArray=FALSE){
		if(isNull($value)){
			$value=@$_SESSION[get_subclass_prefix().$key];
		}else{
			if(!isNullOrEmpty($isArray)){
				if(is_numeric($isArray)||is_string($isArray)){
					$_SESSION[get_subclass_prefix().$key][$isArray]=$value;
				}else if(is_bool($isArray)&&$isArray===TRUE){
					$_SESSION[get_subclass_prefix().$key][]=$value;
				}else{
					$_SESSION[get_subclass_prefix().$key]=$value;
				}
			}else{
				$_SESSION[get_subclass_prefix().$key]=$value;
			}
		}
		
		return $value;
	}
}

if (! function_exists ( 'unset_session' )) {
	function unset_session($key,$key1='',$key2=''){
		if(isNullOrEmpty($key)){
			//do something,请谨慎操作
		}else if(isNullOrEmpty($key1)){
			unset($_SESSION[get_subclass_prefix().$key]);
		}else if(isNullOrEmpty($key2)){
			unset($_SESSION[get_subclass_prefix().$key][$key1]);
		}else{
			unset($_SESSION[get_subclass_prefix().$key][$key1][$key2]);
		}
		
	}
}

if (! function_exists ( 'get_db_prefix' )) {
	function get_db_prefix(){
		$db_prefix=strtolower(config_item('db_prefix'));
		return $db_prefix;
	}
}
if (! function_exists ( 'get_subclass_prefix' )) {
	function get_subclass_prefix(){
		$subclass_prefix=strtolower(config_item('subclass_prefix'));
		return $subclass_prefix;
	}
}

if (! function_exists ( 'setvar' )) {
	/**
	 * 去空格
	 * @param string $var
	 * @return string
	 */
	function setvar($var){
		if(is_string($var)){
			$var=trim($var);
		}
		return $var;
	}
}

if (! function_exists ( 'isNullOrEmpty' )) {
	/**
	 * 判断变量是否为空或NULL
	 * @param mix $var
	 * @return boolean
	 */
	function isNullOrEmpty($var){
		if(is_numeric($var)){
			return FALSE;
		}else if(empty($var)||!isset($var)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
}

if (! function_exists ( 'isNull' )) {
	/**
	 * 判断变量是否为Null
	 * @param mix $var
	 * @return boolean
	 */
	function isNull($var){
		if($var===NULL||!isset($var)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
}

if (! function_exists ( 'isPost' )) {
	/**
	 * 判断是否为post 提交
	 * @return boolean
	 */
	function isPost() {
		$server = $_SERVER;
		$method = $server ['REQUEST_METHOD'];
		if (strtoupper ( $method ) === 'POST') {
			return true;
		} else {
			return false;
		}
	}
}

if (! function_exists ( 'isAppToken' )) {
	/**
	 * 查看token是否一样，如果不同直接返回token错误
	 * @param string $path
	 */
	function isAppToken($PostToken){
		$token = 'klicaetbSOQiS164';
		if($PostToken != $token){
            $redirect = 'app_login/noErrMsge/1';
			redirect ( $redirect );
		}else{
			return true;
		}
	}
}
if (! function_exists ( 'isAppPost' )) {
	/**
	 * 判断是否为post 提交
	 * 如果不是直接返回
	 * @return boolean
	 * //crgken 2015-5 10-10:23
	 */
	function isAppPost() {
		$server = $_SERVER;
		$method = $server ['REQUEST_METHOD'];
		if (strtoupper ( $method ) === 'POST') {
			return ;
		} else {
			return false;
		}
	}
}
if (! function_exists ( 'generate' )) {
	/**
	 * 获取$length长度的随机字符串
	 * @chars 是字符串的备选字符集
	 */
	function generate($length=6,$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'){
		$password = '';  
		for ( $i = 0; $i < $length; $i++ )  {  
			$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}  
		return $password;  
	}
}

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
if (! function_exists ( 'getMdbId1' )) {
	//getMdbId
	function getMdbId1($str){
		try {
			$something = new MongoId($str);
		} catch (MongoException $ex) {
			display(array(),3,"MongoId转换失败,数据有误，请修正！");
		}
		return $something;  
	}
}

/**
 * 添加过滤转义
 * 
 * @param mixed $string 
 * @access public
 * @return void
 */
if (! function_exists ( 'saddslashes' )) {
	function saddslashes($string)
	{
		if(is_array($string))
		{    
			foreach($string as $key => $val)
			{    
				$string[$key] = saddslashes($val);
			}    
		}    
		else 
			$string = addslashes($string);

		return $string;
	}
}

if (! function_exists ( 'getPage' )) {
	function getPage($page,$perpage){
		$page = (int)$page;
		$page = $page < 1 ? 1 : $page;
		$offset = ($page - 1) * $perpage;
		return $offset;
	}
}

if (! function_exists ( 'getCurPage' )) {
	function getCurPage($params){
		$page = isset($params['page']) ? (int)trim($params['page']):1;
		$page = $page < 1 ? 1 : $page;
		return $page;
	}
}
if (! function_exists ( 'unescape' )) {
	function unescape($str)
	{
		$ret = '';
		$len = strlen($str);
		for ($i = 0; $i < $len; $i ++)
		{
			if ($str[$i] == '%' && isset($str[$i+1]) && $str[$i + 1] == 'u')
			{
				$val = hexdec(substr($str, $i + 2, 4));
				if ($val < 0x7f)
					$ret .= chr($val);
				else 
					if ($val < 0x800)
						$ret .= chr(0xc0 | ($val >> 6)) .
						 chr(0x80 | ($val & 0x3f));
					else
						$ret .= chr(0xe0 | ($val >> 12)) .
						 chr(0x80 | (($val >> 6) & 0x3f)) .
						 chr(0x80 | ($val & 0x3f));
				$i += 5;
			} else 
				if ($str[$i] == '%')
				{
					$ret .= urldecode(substr($str, $i, 3));
					$i += 2;
				} else
					$ret .= $str[$i];
		}
		return $ret; 
	}
}
//检查该用户是否是本人
if (! function_exists ( 'checkPatIsSelf' )) {
	function checkPatIsSelf($pid="")
	{
		if(!empty($_SESSION['pid']) && $_SESSION['pid']==$pid) {
			return true;
		} else return false;
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
//得到随机数
if (! function_exists ( 'getRandomPass' )) {
	function getRandomPass($len=6,$format='ALL',$delsame=false) {
		switch($format) { 
		case 'ALL':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; break;
		case 'CHAR':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; break;
		case 'NUMBER':
			$chars='0123456789'; break;
		case 'CAPITAL':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'; break;
		case 'TOLOW':
			$chars='abcdefghijklmnopqrstuvwxyz0123456789';break;
		case 'CHARTOLOW':
				$chars='abcdefghijklmnopqrstuvwxyz';break;
		default :
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; 
			break;
		}
		if ($delsame)
			$chars = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
		mt_srand((double)microtime()*1000000*getmypid()); 
		$password="";
		while(strlen($password)<$len)
			$password.=substr($chars,(mt_rand()%strlen($chars)),1);
		return $password;
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

//得到Id列表
if (! function_exists ( 'getFieldArr' )) {
	function getFieldArr($Info,$field='_id',$st=1) {
		$gzIds = array();
		if(!empty($Info)){
			foreach($Info as $k=>$v){
				if(!empty($v[$field])){
					if($st==1){
						$gzIds[] = getMdbId($v[$field]);
					}else{
						$gzIds[] = $v[$field];
					}
				}
			}
		}
		return $gzIds;
	}
}

//得到唯一Id列表
if (! function_exists ( 'getUniqueFieldArr' )) {
	function getUniqueFieldArr($Info,$field='_id',$st=1) {
		$gzIds = array();
		if(!empty($Info)){
			$Info = array_unique($Info);
			foreach($Info as $k=>$v){
				if(!empty($v[$field])){
					if($st==1){
						$gzIds[] = getMdbId($v[$field]);
					}else{
						$gzIds[] = $v[$field];
					}
				}
			}
		}
		return $gzIds;
	}
}
//得到Id列表
if (! function_exists ( 'getFieldArr1' )) {
	function getFieldArr1($Info,$field1='doctor',$field2='$id',$st=1) {
		$gzIds = array();
		if(!empty($Info)){
			foreach($Info as $k=>$v){
				if($st==1){
					$gzIds[] = $v[$field1][$field2];
				}else{
					$gzIds[] = getMdbId($v[$field1][$field2]);
				}
			}
		}
		return $gzIds;
	}
}

//二维数组排序
if (! function_exists ( 'arrSort' )) {
	function arrSort($gzIds,$docInfo,$field="_id") {
		$doctor = array();
		foreach($docInfo as $k=>$v){
			$doctor[(string)$v['_id']] = $v;
		}
		$doctors = array();
		foreach($gzIds as $v){
			if(!empty($doctor[(string)$v])){
				$doctors[] = $doctor[(string)$v];
			}
		}
		return $doctors;
	}
}

if (! function_exists ( 'ArrKeyFromId' )) {
	function ArrKeyFromId($Info,$key='_id') {
		$gzIds = array();
		if(!empty($Info)){
			foreach($Info as $k=>$v){
				$gzIds[(string)$v[$key]] = $v;
			}
		}
		return $gzIds;
	}
}
if (! function_exists ( 'mk_dir' )) {
	function mk_dir($dir, $mode = 0777)
	{
		if (is_dir($dir) || @mkdir($dir,$mode)) return true;
		if (!mk_dir(dirname($dir),$mode)) return false;
		return @mkdir($dir,$mode);
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
//    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
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

if (! function_exists ( 'http_get_data' )) {
	function http_get_data($url) {  
		$ch = curl_init ();  
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );  
		curl_setopt ( $ch, CURLOPT_URL, $url );  
		ob_start ();  
		curl_exec ( $ch );  
		$return_content = ob_get_contents ();  
		ob_end_clean ();  
		$return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
		return $return_content;  
	}
}

function timeChange12($time) {
	$temp = date("a h:i",strtotime($time));
	return str_replace("pm","下午",str_replace("am","上午",$temp));
} 

function emptyCheck($params,$key_arr) {
	foreach($key_arr as $v){
		if(empty($params[$v])){
			 display(array(),2,$v."参数有误");
		}
	}
}
function getClientHeaders() {
	return array( 'id'=>getHeadersByKey('X-HM-ID'), 'token'=>getHeadersByKey('X-HM-Session-Token'), 'agent'=>getHeadersByKey('X-HM-Endpoint-Agent'), 'version'=>getHeadersByKey('X-HM-App-Version'), 'sign'=>getHeadersByKey('X-HM-Sign'));
} 
function getSessionToken($id) {
	$md5Str = time().(string)($id).getRandomPass(4);
	return  sha1($md5Str);
}
/**
 *  * 取得客户端的ip 
 *   * 
 *    * @access public
 *     * @return void
 *      */
function getClientIp()
{
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else 
	{    
		if(isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
			$ip = $_SERVER['HTTP_CLIENT_IP'];
	        else
				$ip = $_SERVER['REMOTE_ADDR'];
	    }    

    return $ip; 
}

/**
 *   数组键值对拼接
 *   
 *   @access public
 *   @return void
 **/
function arrayKeyValue($arr)
{
	$keyV = array();
	foreach($arr as $k=>$v){
		$keyV[$k] = $k."=".$v;
	}
    return $keyV; 
}

/** 
 *  作用：array转xml
 */
function arrayToXml($arr)
{   
	$xml = "<xml>";
	foreach ($arr as $key=>$val)
	{   
		 if (is_numeric($val))
		 {   
			$xml.="<".$key.">".$val."</".$key.">"; 

		 } else{
			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
		 }   
	}   
	$xml.="</xml>";
	return $xml; 
}

/**
 *  作用：以post方式提交xml到对应的接口url
 */
function postXmlCurl($xml,$url,$second=30) {
	//初始化curl        
	$ch = curl_init();
	//设置超时
	curl_setopt($ch,CURLOPT_TIMEOUT,$second);
	//这里设置代理，如果有的话
	//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
	//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
	//设置header
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	//要求结果为字符串且输出到屏幕上
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	//post提交方式
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	//运行curl
	$data = curl_exec($ch);
	//返回结果
	if($data) {
		curl_close($ch);
		return $data;
	}
	else
	{
		$error = curl_errno($ch);
		log_message('USERS',$error);exit;
		curl_close($ch);
		display(false,101,'网络出现错误，请稍后再试！');	
	}
}

/**
 *  作用：将xml转为array
 */
function xmlToArray($xml) {
	//将XML转为array        
	$array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	return $array_data;
}

function checkSign($data) {   
        $tmpData = $data;
		unset($tmpData['sign']);
		ksort($tmpData);
		$paramsKV = arrayKeyValue($tmpData);
		$strParams = implode("&",$paramsKV)."&key=".config_item("global_wx_key");
		$sign = strtoupper(md5($strParams));
		if ($data['sign'] == $sign) {
			return TRUE;
		}   
        return FALSE;
}
if (! function_exists ( 'sortArrByField' )) {
	function sortArrByField(&$array, $field, $desc = false)
	{
		$fieldArr = array();
		for ($i = 0; $i < count($array); $i++) {
			$fieldArr[$i] = $array[$i][$field];
		}
		$sort = $desc == false ? SORT_ASC : SORT_DESC;
		array_multisort($fieldArr, $sort, $array);
		return $array;
	}
}
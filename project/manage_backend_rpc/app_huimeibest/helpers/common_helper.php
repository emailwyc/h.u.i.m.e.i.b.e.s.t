<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//ini_set('session.gc_maxlifetime', 9999999999999);
session_start();
isset($PHPSESSID)?session_id($PHPSESSID):$PHPSESSID = session_id();
setcookie('PHPSESSID', $PHPSESSID, time()+86400*7,"/");

date_default_timezone_set("PRC");
if(!empty($_SERVER['QUERY_STRING'])){
	parse_str($_SERVER['QUERY_STRING'], $_GET);
}

function checkLogin1(){
	if(config_item('check_login')){
		if(empty($_SESSION['HM']['mobile'])){
			display(array(),401,'请先登陆');
		}//redirect('');  存在session跳转
	}
}

//当前请求控制器&方法 desu
function route() {
	$url = $_SERVER['REQUEST_URI'];
	$controller = array_slice(explode( '/',$url),-2 );
	return  $controller[0].'/'.$controller[1];
}

//判断执行操作权限
function checkUserPower()
{
	if(config_item('check_power')){
		if(!in_array(route(), $_SESSION['HM']['powers'])){
			display(array(),401,'没有权限');
		}}
}

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


if (! function_exists ( 'showErrorMsg' )) {
	/**
	 * 打印错误信息页面
	 * @param string $msg 错误提示信息
	 */
	function showErrorMsg($msg=''){
		if(isNullOrEmpty($msg)){$msg='Error';}
		$module='Error';
		$action='error';
		$url= $module.'/'.$action.'?msg='.$msg;
		redirect ( $url );
	}
}

if (! function_exists ( 'noPower' )) {
	/**
	 * 没有查看权限
	 */
	function noPower() {
		if (isStoreManager()) { 
			$msg='对不起，您无权限操作';
			showErrorMsg($msg);
		}
	}
}
if (! function_exists ( 'checkLogin' )) {
	/**
	 * 判断管理员是否登录
	 */
	function checkLogin() {
		if (! isAdminUserExist ()) { // 登录
			$msg = '请先登录';
			$site_url=site_url () ;
			$redirect = 'admin/admin/dealLogin';
			redirect ( $redirect );
		}
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
	function getCurPage(){
		$page = isset($_REQUEST['p']) ? (int)trim($_REQUEST['p']):1;
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
//检测用户时候注册
if (! function_exists ( 'checkAuth' )) {
	function checkAuth()
	{
		if(isset($_SESSION['aid']) && $_SESSION['aid'] && isset($_SESSION['st']) && $_SESSION['st']) {
			return $_SESSION['aid'];
		} else redirect('/register/index');
	}
}
//检测用户时候注册
function checkAuth1() {
	if(isset($_SESSION['pid']) && $_SESSION['pid']) {
		return $_SESSION['pid'];
	} else return false;
}
function getPatientOpenid() {
	if(isset($_SESSION['aid']) && $_SESSION['aid']) {
		return $_SESSION['aid'];
	} else return false;
}

function checkisScan($scen_id="") {
	if(isset($_SESSION['aid']) && $_SESSION['aid']) {
		return $_SESSION['aid'];
	} else{
		 redirect('/share/subscribe/'.$scen_id);exit;
	};
}
function checkAuth2() {
		if(!empty($_SESSION['aid']) && !empty($_SESSION['st'])  && !empty($_SESSION['pid'])) {
			return array("aid"=>$_SESSION['aid'],"pid"=>$_SESSION['pid']);
		}else return false;
}
function checkAuth3()
{
	if(empty($_SESSION['aid'])){
		redirect('/share/subscribe');exit;
	}
	if(isset($_SESSION['pid']) && $_SESSION['pid'] && isset($_SESSION['st']) && $_SESSION['st']) {
		return $_SESSION['pid'];
	} else{
		$selfUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		redirect('/register/index?redirect='.urlencode($selfUrl));
	}
}
if (! function_exists ( 'checkPatientAuth' )) {
	function checkPatientAuth()
	{
		if(!empty($_SESSION['aid']) && !empty($_SESSION['st'])  && !empty($_SESSION['pid'])) {
			return array("aid"=>$_SESSION['aid'],"pid"=>$_SESSION['pid']);
		} else redirect('/register/index');
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

if (! function_exists ( 'getMongoIds' )) {
	function getMongoIds($Info) {
		$gzIds = array();
		if(!empty($Info)){
			for($i=0; $i<count($Info);$i++){
				$gzIds[] = getMdbId($Info[$i]);
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

if (! function_exists ( 'getThumbByOrg' )) {
	function getThumbByOrg($img)
	{
		$images = array();
		if(is_array($img)){
			if(!empty($img)){
				foreach($img as $v){
					$filetype=substr(strrchr($v,'.'),1); 
					$temp = pathinfo($v);
					if(empty($temp['dirname']) || empty($temp['extension']) || empty($temp['filename'])){ $thumb = $v;}else{
						$thumb = $temp['dirname']."/".$temp['filename']."_thumb.".$temp['extension'];
					}
					$images[] = array('origin'=>$v,'thumbnail'=>$thumb);
				}
			}
		}elseif(!empty($img)){
			$temp = @explode(".",$img);
			if(empty($temp[0]) || empty($temp1[1])){ $thumb = $img;}else{
				$thumb = $temp[0]."_thumb.".$temp1[1];
			}
			$images = array('origin'=>$img,'thumbnail'=>$thumb);
		}else{
			$images = array();
		}
		return $images;
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

if (! function_exists ( 'getSelParams' )) {
	function getSelParams() {
		$dep = isset($_GET['dep']) ? saddslashes(trim($_REQUEST['dep'])):"";
		$hos = isset($_GET['hos']) ? saddslashes(trim($_REQUEST['hos'])):"";
		$kw= isset($_GET['kw']) ? unescape(saddslashes(trim($_REQUEST['kw']))):"";
		$sort = isset($_GET['sort']) ? saddslashes(trim($_REQUEST['sort'])):"mul_num";
		$find = array();
		if(!empty($dep)){
			$tem = @explode("_",$dep);
			if(isset($tem[1]) && $tem[1]=="1"){
				$find['depk'] = "department_id"; $find['depv'] = getMdbId($tem[0]);
			}elseif(isset($tem[1]) && $tem[1]=="2"){
				$find['depk'] = "department_child_id"; $find['depv'] = getMdbId($tem[0]);
			}
		}
		if(!empty($hos)){
			$tem = @explode("_",$hos);
			if(isset($tem[1]) && $tem[1]=="1"){
				$find['host'] = "region"; $find['hosk'] = "region_id"; $find['hosv'] = getMdbId($tem[0]);
			}elseif(isset($tem[1]) && $tem[1]=="2"){
				$find['host'] = "hospital"; $find['hosk'] = "hospital_id"; $find['hosv'] = getMdbId($tem[0]);
			}
		}
		$find['sort'] = $sort; $find['kw']  = $kw;
		$result = array( 'view'=>array('dep'=>$dep,'hos'=>$hos,'sort'=>$sort,'kw'=>$kw), 'find'=>$find);
		return $result;
	} 
}
if (! function_exists ( 'getWhereParams' )) {
	function getWhereParams() {
		$where = array();
		if($_POST){
			foreach($_POST as $k=>$v){
				if($k=='p' || !isset($v)){ continue; }
				$k=saddslashes(trim($k));
				$where[$k] = saddslashes(trim($v));
			}
		}
		return $where;
	} 
}

if (! function_exists ( 'getInsertParams' )) {
	function getInsertParams() {
		$where = array();
		if($_POST){
			foreach($_POST as $k=>$v){
				if($k=='p' || empty($v)){ continue; }
				$k=saddslashes(trim($k));
				$where[$k] = saddslashes(trim($v));
			}
		}
		return $where;
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

function checkIsWxBrowser() {
	if(strpos($_SERVER['HTTP_USER_AGENT'],"MicroMessenger")){
	    return true;
	}else{
		return false;
	}
} 

function timeChange12($time) {
	$temp = date("a h:i",strtotime($time));
	return str_replace("pm","下午",str_replace("am","上午",$temp));
} 

function emptyCheck($arr) {
	foreach($arr as $v){
		if($_REQUEST[$v] == null){
			 display(array(),2,$v."参数有误");
		}
	}
}

//过滤参数
function safeParams($array){
	foreach ($array as $k=>$v){
		if(!is_array($v)){
			$array[$k] = addslashes(trim($v));
		}
		$array[$k] = $v;
	}
	return $array;
}

function password($pwd,$sign){
	return sha1(md5(md5($pwd).$sign));
}

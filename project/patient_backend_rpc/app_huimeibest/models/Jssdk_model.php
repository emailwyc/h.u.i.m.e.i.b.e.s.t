<?php
class Jssdk_model extends CI_Model{
	function __construct()
	{
		parent::__construct();
		$this->appId = $this->config->item('global_wx_appid');
		$this->appSecret = $this->config->item('global_wx_appsecret');
		$this->token = $this->config->item('global_wx_token');;
	}

	private $appId = "";
	private $appSecret = "";
	private $token = "";
	private $table = 'h5_setting';

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

  public function getAccessToken() {
    $where = array( 'sign' => 'weixin');
	$data= $this->mdb->where($where)->get($this->table);
	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
	if(!empty($data)){
		if($data[0]['last_time'] < time()) {
			$res = json_decode($this->httpGet($url));
			if (isset($res->access_token)) {
				$access_token = (string)$res->access_token;
				$data = array('access_token' => $access_token , 'last_time' => (string)(time()+$res->expires_in));
				$this->mdb->where($where)->update($this->table,$data);
			}else{
				log_message('error','获取access_token失败!');
				$access_token = false;
			}
		}else{
		    $access_token = $data[0]['access_token'];
		}
	}else{
		$res = json_decode($this->httpGet($url));
		if (isset($res->access_token)) {
			$access_token = (string)$res->access_token;
			$data = array('sign'=>'weixin','access_token' => $access_token , 'last_time' => (string)(time()+$res->expires_in));
			$this->mdb->where($where)->update($this->table,$data,array('upsert'=>true));
		}else{
			log_message('error','获取1access_token失败!');
			$access_token = false;
		}
	}
	return $access_token;
  }


	public function weixin_get_openid() {
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		if(isset($_GET['code'])){
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->config->item('global_wx_appid')."&secret=".$this->config->item('global_wx_appsecret')."&code=".$_GET['code']."&grant_type=authorization_code";
			$res = (array)json_decode($this->httpGet($url),true);
			//记录
		}else{
			$res = array();
		}
		return $res;
	}

	public function getJsApiTicket() {
		$where = array( 'sign' => 'jsapi_ticket');
		$data= $this->mdb->where($where)->get($this->table);
		if(!empty($data[0])){
			if($data[0]['last_time'] < time()) {
				$token = $this->getAccessToken();
				$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$token."&type=jsapi";
				$res = json_decode($this->httpGet($url));
				if (isset($res->ticket)) {
					$access_token = (string)$res->ticket;
					$data = array('access_token' => $access_token , 'last_time' => (string)(time()+6000));
					$this->mdb->where($where)->update($this->table,$data);
				}else{
					log_message('error','获取jsapi_ticket失败!');
					$access_token = false;
				}
			}else{
				$access_token = $data[0]['access_token'];
			}
		}else{
			$token = $this->getAccessToken();
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$token."&type=jsapi";
			$res = json_decode($this->httpGet($url));
			if (isset($res->ticket)) {
				$access_token = (string)$res->ticket;
				$data = array('sign'=>'jsapi_ticket','access_token' => $access_token , 'last_time' => (string)(time()+6000));
				$this->mdb->where($where)->update($this->table,$data,array('upsert'=>true));
			}else{
				$access_token = false;
			}
		}
		return $access_token;
	}

	public function getSignPackage($url="") {
		$jsapiTicket = $this->getJsApiTicket();
		//$url校验
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		$signature = sha1($string);
		$signPackage = array(
		"appId"     => $this->appId,
		"nonceStr"  => $nonceStr,
		"timestamp" => $timestamp,
		"url"       => $url,
		"signature" => $signature,
		"rawString" => $string
		);

		return json_encode($signPackage); 
	}

	public function sendTemplateMsg($data){
		$token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$token;
		$ok =  httpRequest($url,$data);
		return $ok;
	}

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }


  public function getCode($redirect , $scope="snsapi_userinfo" , $state=1){
    $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId";
	$url .= "&redirect_uri=".$redirect."&response_type=code&scope=snsapi_userinfo&state=".$state."#wechat_redirect";
	return $url;
  }

  public function getCodeBase($redirect , $scope="snsapi_base" , $state=1){
    $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId";
	$url .= "&redirect_uri=".$redirect."&response_type=code&scope=snsapi_base&state=".$state."#wechat_redirect";
	return $url;

  }
  public function getWebAccessToken($code){
	$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appId;
	$url .= "&secret=".$this->appSecret."&code=$code&grant_type=authorization_code";
    $res = json_decode($this->httpGet($url));
	return $res;
  }

  public function getUserInfos($access_token , $open_id){
	$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token;
	$url .= "&openid=".$open_id."&lang=zh_CN";
    $res = json_decode($this->httpGet($url));
	return $res;
  }


  //微信支付
  public function createPayNonceStr($length = 32) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }
  	
	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode)
	{
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	
	/**
	 * 	作用：生成签名
	 */
	public function getSign($Obj)
	{
		foreach ($Obj as $k => $v)
		{
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);
		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		$String = $String."&key=".$this->token;
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}


	/**
	 * 	作用：array转xml
	 */
	function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }


/**
	 * 	作用：将xml转为array
	 */
	public function xmlToArray($xml)
	{		
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}

	
	/**
	 * 	作用：以post方式提交xml到对应的接口url
	 */
	public function postXmlCurl($xml,$url,$second=30)
	{		
        //初始化curl        
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, 30, $second);
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
		curl_close($ch);
		//返回结果
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else 
		{ 
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}

}

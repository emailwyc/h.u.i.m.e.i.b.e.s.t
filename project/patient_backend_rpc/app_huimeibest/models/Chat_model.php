<?php
class Chat_model extends CI_Model{
	private $hx_host = "https://a1.easemob.com/";
	private $appKey= "";
	private $appId= "";
	private $orgname= "";
	private $appname= "";
	private $app= "";
	private $table= "h5_setting";

	function __construct()
	{
		parent::__construct();
		$this->appKey = $this->config->item('global_hx_appkey');
		$this->appId = $this->config->item('global_hx_cli_id');
		$this->appSecret = $this->config->item('global_hx_cli_secret');
		$this->orgname= $this->config->item('global_hx_orgname');
		$this->appname= $this->config->item('global_hx_appname');
	}

  private function httpPost($uri,$data,$header=array()) {
	  if(empty($header)){
		$header = array('Content-Type: application/json',);
	  }
	  $ch = curl_init ();
	  curl_setopt ( $ch, CURLOPT_URL, $uri );
	  curl_setopt ($ch, CURLOPT_HTTPHEADER, $header); 
	  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	  if(!empty($data)){
		  curl_setopt ( $ch, CURLOPT_POST, 1 );
		  curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	  }
	  $return = curl_exec ( $ch );
	  curl_close ( $ch );
	  return trim($return);
  }

  public function getAccessToken() {
    $where = array( 'sign' => 'huanxin');
	$data= $this->mdb->where($where)->get($this->table);
	$url = $this->hx_host.$this->orgname."/".$this->appname."/token";
	$datapost = array("grant_type"=>"client_credentials","client_id"=>$this->appId,"client_secret"=>$this->appSecret);
	if(!empty($data)){
		if($data[0]['last_time'] < time()) {
			$res = $this->httpPost($url,json_encode($datapost));
			$res = json_decode($res);
			if (isset($res->access_token)) {
				$access_token = (string)$res->access_token;
				$data = array('access_token' => $access_token , 'last_time' => (string)(time()+$res->expires_in-300));
				$this->mdb->where($where)->update($this->table,$data);
			}else{
				log_message('error','获取access_token失败!');
				$access_token = false;
			}
		}else{
		    $access_token = $data[0]['access_token'];
		}
	}else{
		$res = $this->httpPost($url,json_encode($datapost));
		$res = json_decode($res);
		if (isset($res->access_token)) {
			$access_token = (string)$res->access_token;
			$data = array('sign'=>'huanxin','access_token' => $access_token , 'last_time' => (string)(time()+$res->expires_in-300),'application'=>(string)$res->application);
			$this->mdb->where($where)->update($this->table,$data,array('upsert'=>true));
		}else{
			log_message('error','获取1access_token失败!');
			$access_token = false;
		}
	}
	return $access_token;
  }

	public function regUser($token,$datapost) {
		$url = $this->hx_host.$this->orgname."/".$this->appname."/users";
		$token_str = 'Authorization: Bearer '.$token.'';
		$header = array('Content-Type: application/json',$token_str);
		$res1 = $this->httpPost($url,json_encode($datapost),$header);
		$res = json_decode($res1);
		if(empty($res->error)){
			return true;
		}else{
			log_message('error',$res1);
			return false;
		}
	}

	//每秒钟只能请求一次
	public function getChatRecord($token) {
		ini_set('mongo.native_long', 1);
		$filePath = config_item("global_storage_hxset");
		$ck_con1 = (int)read_file($filePath);
		$ck_con2 = $ck_con1==1?2:1;
		if (!write_file($filePath, $ck_con2, "w+")){ return false; }
		sleep(1);

		$data= $this->mdb->where(array())->order_by(array('timestamp'=>-1))->limit(1)->get('hx_history');
		if(!empty($data[0]['timestamp'])){
			$cur_time = $data[0]['timestamp'];
		}else{
			$cur_date = date('Y-m-d H:i:00',time()-300);
			$cur_time = strtotime($cur_date)."000";
		}
		$token_str = 'Authorization: Bearer '.$token.'';
		$header = array('Content-Type: application/json',$token_str);
		$RecordStore = array();
		$RecordNo = array("notice_end","notice_clinic","notice_problem","notice_record");
		$jishu = time();
		while(true){
			$jishu1 = time();
			if(($jishu1-$jishu)>=240){ break; }
			$ck_con3 = (int)read_file($filePath);
			if($ck_con3==$ck_con1){ break;}
			$url = $this->hx_host.$this->orgname."/".$this->appname."/chatmessages?ql=select+*+where+timestamp>".$cur_time."&limit=50";
			$res1 = $this->httpPost($url,"",$header);
			$res = json_decode($res1,true);
			if(!empty($res['entities'])){
				foreach($res['entities'] as $v){
					if(!empty($v['payload']['ext']['order_id'])){
						if(!in_array((string)$v['msg_id'],$RecordStore)){
							if(in_array($v['payload']['ext']['msg_type'],$RecordNo)){
								$v['otp'] = "0";
							}else{
								$v['otp'] = "1";
							}
							$v['oid'] = $v['payload']['ext']['order_id'];
							$RecordStore[] = (string)$v['msg_id'];
							@$this->mdb->insert('hx_history',$v);
						}
					}
				}
				$enddata = @end($res['entities']);
				if(!empty($enddata)){
					$cur_time = $enddata['timestamp'];
				}
			}
			sleep(0.3);
		}
		return true;
	}

	//每秒钟只能请求一次
	public function getChatRecords($token,$cursor) {
		$url = $this->hx_host.$this->orgname."/".$this->appname."/chatmessages?limit=500&cursor=".$cursor;
		$token_str = 'Authorization: Bearer '.$token.'';
		$header = array('Content-Type: application/json',$token_str);
		$res1 = $this->httpPost($url,"",$header);
		$res = json_decode($res1,true);
		if(!empty($res['entities'])){ $this->batchInsert($res['entities']); }
		sleep(1);
		if(!empty($res['cursor'])) {
			$this->getChatRecords($token,$cursor);
		}

	}

	public function checkUserOnline($user){
		$url = $this->hx_host.$this->orgname."/".$this->appname."/users/".$user."/status";
		$token = $this->getAccessToken();
		$token_str = 'Authorization: Bearer '.$token.'';
		$header = array('Content-Type: application/json',$token_str);
		$res1 = $this->httpPost($url,"",$header);
		$res = json_decode($res1,true);
		if(!empty($res['data'][$user]) && $res['data'][$user]=="online"){
			return true;
		}else{
			return false;
		}

	}

	//每秒钟只能请求一次
	public function batchInsert($data) {
		set_time_limit(0);
		$this->mdb->batchinsert('hx_history',$data);
	}
	//发送消息接口
	public function sendMsg($token,$datapost) {
		$url = $this->hx_host.$this->orgname."/".$this->appname."/messages";
		$token_str = 'Authorization: Bearer '.$token.'';
		$header = array('Content-Type: application/json',$token_str);
		$res1 = $this->httpPost($url,json_encode($datapost),$header);
		$res = json_decode($res1,true);
		if(empty($res->error)){
			return $res;
		}else{
			log_message('error',$res1);
			return false;
		}
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










  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
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
	
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  public function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    //$data = json_decode(file_get_contents("jsapi_ticket.json"));
	$where = array(
			'Key' => 'jsapi_ticket'
	);
	$this->db->where($where);
	$query = $this->db->get('mx_config');
	$data = $query->result();

    if ($data[0]->Time < time()) {
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
		$where = array('Key' => 'jsapi_ticket');
		$data = array('Value' => $ticket , 'Time' => time() + 7000);
		$this->db->where($where);
		$this->db->update($this->table,$data);
      }
    } else {
		$ticket = $data[0]->Value;
    }

    return $ticket;
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

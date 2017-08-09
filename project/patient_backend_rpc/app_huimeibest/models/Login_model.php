<?php
class Login_model extends CI_Model{
	function __construct()
	{
		parent::__construct();
		$this->appId = $this->config->item('global_wx_appid');
		$this->appSecret = $this->config->item('global_wx_appsecret');
		$this->token = $this->config->item('global_wx_token');;
		$this->load->model('Weixin_model');
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

	public function weixin_get_openid() {
		if(isset($_GET['code'])){
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->config->item('global_wx_appid')."&secret=".$this->config->item('global_wx_appsecret')."&code=".$_GET['code']."&grant_type=authorization_code";
			$res = (array)json_decode($this->httpGet($url),true);
			//记录
		}else{
			$res = array();
		}
		return $res;
	}

	public function weixinLoginCheck($jumpUrl){
		if(@$_GET['auth']=='wx'){
			if(isset($_SESSION['aid']) && $_SESSION['aid']){
				$url = config_item('global_base_url')."/".$jumpUrl;
				header("Location: ".$url);exit;
			}else{
				$info = $this->weixin_get_openid();
				if(!empty($info['openid'])){
					//根据openid得到用户信息
					$userdb = $this->Weixin_model->getWeixinInfo($info['openid']);
					if(!empty($userdb)){
						$_SESSION['aid'] = $userdb[0]['openid'];
						$patient = $this->Weixin_model->getPatientInfo($info['openid']);
						if(!empty($patient)){
							$_SESSION['pid'] = (string)$patient['_id'];
							$_SESSION['st'] = 1;
						}else{
							$_SESSION['st'] = 0;
						}
					}else{
						//插入用户信息调用接口
						$userinfo = $this->Weixin_model->checkUserExist($info['openid']);
						if(!empty($userinfo['openid'])){ $_SESSION['aid'] = $userinfo['openid']; $_SESSION['st'] = 0; }
					}
				}else{
					//刷新本页面
					echo "<script>history.go(-1);</script>";exit;
				}
			}
		   	$url = config_item('global_base_url')."/".$jumpUrl;
		   	header("Location: ".$url);exit;
		}
		return true;
	}

	public function isUserLogin(){
		//判断用户是否登录
		echo 1;exit;
	}

}

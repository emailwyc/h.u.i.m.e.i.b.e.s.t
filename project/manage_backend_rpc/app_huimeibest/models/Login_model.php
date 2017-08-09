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

	//获取用户角色和权限
	public function getPower($mobile)
	{
		$role = $this->Common_model->getInfo('manage_admin',array('mobile'=>$mobile),array('role'));
		if(empty($role['role'])){display(array(),1,'暂时没有分配角色');}
		$roleMId = getMongoIds($role['role']);
		$powersId = $this->getInfoAll('manage_role',array('_id'=>array('$in'=>$roleMId)),null,array('powers','name'));
		if(empty($powersId)){display(array(),1,'user powers empty');}
		$powerList = array();
		foreach($powersId as $k=>$v){
			$powerList = array_merge($powerList,array_merge($v['powers']));
			$roleName[] = $powersId[$k]['name'];
		}
		$powerList = array_values(array_unique($powerList));
		$powerList = getMongoIds($powerList);
		$powers = $this->getInfoAll('manage_power',array('_id'=>array('$in'=>$powerList)),null,null,true);
		if(empty($powers)){display(array(),1,'power empty');}
		return array('role'=>$roleName,'powers'=>$powers);
	}

	public function getInfoAll($table,$where,$sort="",$fields=array(),$idIsStr = false) {
		$info = $this->mdb->where($where)->order_by($sort)->select($fields)->get($table,$idIsStr);
		if(!empty($info)){
			return $info;
		}else{
			return array();
		}
	}
}

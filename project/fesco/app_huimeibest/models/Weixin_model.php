<?php
class Weixin_model extends CI_Model{

	private $table = "patient";

	function __construct() {
		parent::__construct();
        $this->load->helper('Weixin_helper');
	}

	/**
	 * 维信端判断用户是否已经存在 
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function checkUserExist($fromusername){
        //先判断信息是否存在
        $result = $this->getWeixinInfo($fromusername);
        if(empty($result)){
			$tmp_token = $this->Jssdk_model->getAccessToken();
            $info = get_user_info($fromusername, $tmp_token);
			if(!empty($info['openid'])){
				if($info['headimgurl']){
					//上传图片
					$cur_time = time(); $year = date('Ym',$cur_time);
					$fileUrl = "./ui/patient/avatar/".$year;
					$fileUrl1 = config_item('global_base_url')."/ui/patient/avatar/".$year; mk_dir($fileUrl);
					$headimg = substr($info['headimgurl'],0,strlen($info['headimgurl'])-1)."132";
					$headimgInfo = http_get_data($headimg);
					$md5fn = md5($info['openid']).".png";
					$filename = $fileUrl."/".$md5fn;
					$fp= @fopen($filename,"w+");
					$check = fwrite($fp,$headimgInfo);
					if($check){
						$info['headimgurl'] = $fileUrl1."/".$md5fn;
					}
				}
				$data = array('openid'=>$info['openid'],'mobile'=>"",'img'=>$info['headimgurl'],'nickname'=>$info['nickname'],'gender'=>$info['sex'],'regtime' =>(string)time(),'st'=>1,'invite'=>"","eventkey"=>0);
				$this->mdb->insert('user_weixin',$data);
				$data['isnew'] = 1;
				return $data;
			}else{
				return false;
			}
        }else{
			$this->updateWeixinInfo($fromusername,array('st'=>1),$result);
			return $result[0];
		}
	}

	/**
	 * 得到用户相关信息
	 * @param array $where
	 * @param array $field
	 * @return boolean
	 */
	public function getWeixinInfo($openid){
		$where = array('openid'=>(string)$openid);
		$field = array('openid','mobile','nickname');
		$info = $this->mdb->where($where)->select($field)->limit(1)->get('user_weixin');
		return $info;
	}

	public function getPatientInfo($openid){
		$where = array('fid'=>(string)$openid);
		$field = array();
		$info = $this->mdb->where($where)->select($field)->limit(1)->get('patient');
		if(!empty($info)){
			return $info[0];
		}else{
			return $info;
		}
	}

	public function getWeixinInfoAll($openid){
		$where = array('openid'=>(string)$openid);
		$field = array();
		$info = $this->mdb->where($where)->select($field)->limit(1)->get('user_weixin');
		return $info;
	}

	public function updateWeixinInfo($openid,$update,$result){
		$where = array('openid'=>(string)$openid);
		$tmp_token = $this->Jssdk_model->getAccessToken();
		$info = get_user_info($openid, $tmp_token);
		if($info['headimgurl']){
			//上传图片
			if(empty($result['regtime'])){ $cur_time = time(); }else{ $cur_time = $result['regtime']; }
		   	$year = date('Ym',$cur_time);
			$fileUrl = "./ui/patient/avatar/".$year;
			$fileUrl1 = config_item('global_base_url')."/ui/patient/avatar/".$year; mk_dir($fileUrl);
			$headimg = substr($info['headimgurl'],0,strlen($info['headimgurl'])-1)."132";
			$headimgInfo = http_get_data($headimg);
			$md5fn = md5($info['openid']).".png";
			$filename = $fileUrl."/".$md5fn;
			$fp= @fopen($filename,"w+");
			$check = fwrite($fp,$headimgInfo);
			if($check){
				$update['img'] = $fileUrl1."/".$md5fn;
				$update['nickname'] = $info['nickname'];
				$update1 = array();
				$update1['avatar'] = $fileUrl1."/".$md5fn;
				$update1['name'] = $info['nickname'];
			}
		}
		$check = $this->mdb->where($where)->update('user_weixin',$update);
		if(!empty($check)){
			if(!empty($update1)){ $this->mdb->where(array('fid'=>(string)$openid))->update('patient',$update1);}
			return true;
		}else{
			return false;
		}
	}



}

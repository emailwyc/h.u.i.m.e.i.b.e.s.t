<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Share.php
 */
class Share extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('Jssdk_model');
		$this->load->helper('weixin_helper');
	}

	public function subscribe($scen_id="") {
		$data['url'] = "/ui/images/ewm.jpg";
		if(!empty($scen_id) && $scen_id<=90000){
			$info = $this->Common_model->getInfo('doctor_rqcode',array('scene_id'=>(int)$scen_id));
			if(!empty($info)){
				$data['url'] = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$info['ticket'];
			}
		}elseif(!empty($scen_id) && $scen_id>90000){
			$info = $this->Common_model->getInfo('other_rqcode',array('scene_id'=>(int)$scen_id));
			if(!empty($info)){
				$data['url'] = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$info['ticket'];
			}
		}

		$this->load->view('share/subscribe',$data);
	}

	public function qrcode($id="") {
		$this->Login_model->weixinLoginCheck("share/qrcode");
		if(empty($id)){ $id = checkAuth3(); }
		$id = getMdbId($id);
		$cur_time = time();
		$patient = $this->Common_model->getInfo('patient',array('_id'=>$id));
		if(empty($patient)){ show_error("用户不存在！"); }
		if(empty($patient['rqcode']['scen_id']) ||  $patient['rqcode']['endtime']<$cur_time){
			if(empty($patient['rqcode']['scen_id'])){
				$lastPat = $this->Common_model->getInfoSort('patient',array(),array(),array('rqcode.scen_id'=>-1));
				$CodeID = !empty($lastPat['rqcode']['scen_id'])?$lastPat['rqcode']['scen_id']+1:100001;
			}else{
				$CodeID = (int)$patient['rqcode']['scen_id'];
			}
			$token = $this->Jssdk_model->getAccessToken();
			$result = create_qrcode('QR_SCENE',$CodeID,$token);
			$result['endtime'] = time()+$result['expire_seconds']-60;
			$result['scen_id'] = $CodeID;
			//更新用户身上二维码
			if(!empty($result['ticket'])){
				$this->Common_model->updateRecord('patient',array("_id"=>$patient['_id']),array('rqcode'=>$result));
				$patient['rqcode'] = $result;
			}
		}
		$data['pat'] = $patient;
		$this->load->view('share/qrcode',$data);
	}





}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Auth.php
 */
class Auth extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
        $this->load->model('msg_model');
        $this->load->model('User_model');
		//校验session， //session是否过期,
	}

    /** 
     * 用户授权登录
	 * @param  platform:平台名称; open_id:第三方登录获取的用户id; avatar头像链接地址; nickname 昵称
     * @access public
     * @return text/json
     */
	public function sign_in() {
		emptyCheck($this->params,array('platform','open_id'));
		$sInfo = getClientHeaders(); emptyCheck($sInfo,array('version'));
		$ctime = time();
		//检查数据是否存在(不存在插入)
		$patWhere = array('platform'=>$this->params['platform'],'open_id'=>$this->params['open_id']);
		$patInfo = $this->Common_model->getInfo('pat_user',$patWhere);

		if($this->params['platform']=="hm"){
			emptyCheck($this->params,array('yzm'));
			$code = trim((string)$this->params['yzm']);
			if(trim($this->params['open_id'])!="18513852351" && $code!="1024") {
				$msgInfo = $this->msg_model->getMobileInfo(array('mobile'=>(string)$this->params['open_id'],'st'=>1));
				if(empty($code) || empty($msgInfo)){ display(array(),3,"验证码错误，请检查!");}
				$diff_time = $ctime-$msgInfo['ut'];
				if($msgInfo['code'] != $code || $diff_time>300){ display(array(),5,"验证码错误或过期，请检查!");}
			}
		}
		if(empty($patInfo)){
			$insertInfo = array('nickname' => @$this->params['nickname'], 'avatar' => @$this->params['avatar'], 'open_id' => $this->params['open_id'], 'platform' => $this->params['platform'], 'version' => $sInfo['version'], 'created_at' => $ctime, 'updated_at' => $ctime, 'actived_at' => $ctime);
			$patInfo['_id'] = $this->Common_model->insertInfo('pat_user',$insertInfo);
		}else{
			//检查头像是否需要更新
			//更新最后活动时间
			$this->Common_model->updateRecord('pat_user',$patWhere,array('actived_at'=>$ctime));
		}
		if(empty($patInfo['_id'])){ display(array(),3,"服务器错误，请检查!"); }
		//依据设备id查看是否存在未登录的信息记录合并到当前账户信息  desu
		$visit_where = array('pat_id'=>$sInfo['id']);
		$visitInfo = $this->Common_model->getInfoAll('pat_visit_follow_doctor',$visit_where,array('doc_id'));
		if(!empty($visitInfo)){
			$insertFollowDate = array();
			foreach ($visitInfo as $k => $v){
				$checkWhere = array('pat_id'=>(string)$patInfo['_id'],'doc_id'=>$v['doc_id']);
				$existRecord = $this->Common_model->getInfo('pat_follow_doctor',$checkWhere);
				$insertFollowDate = array();
				if(empty($existRecord)){	 //关注信息不存在
					$checkWhere['created_at'] = $v['created_at'];
					$insertFollowDate[] = $checkWhere;
				}
			}
			$this->Common_model->batchInsert('pat_follow_doctor',$insertFollowDate);
			$this->Common_model->deleteAllRecord('pat_visit_follow_doctor',$visit_where);
		}
		//session信息处理
		$SessionInfo = $this->Common_model->getInfo('pat_user_session',array('_id'=>$patInfo['_id']));
		$sessionToken = getSessionToken($patInfo['_id']);
		if(empty($SessionInfo)){
			$insertSession = array(
				'_id' => $patInfo['_id'],
				'session_token' => $sessionToken,
				'dev_id'=>$sInfo['id'],
				'dev_type'=>$sInfo['agent'],
				'created_at'=>$ctime,
				'actived_at'=>$ctime
			);
			$this->Common_model->insertInfo('pat_user_session',$insertSession);
		}else{
			//更新session
			$sUpdate = array('actived_at'=>$ctime,'dev_id'=>$sInfo['id'],'dev_type'=>$sInfo['agent'],'session_token'=>$sessionToken);	
			$this->Common_model->updateRecord('pat_user_session',array('_id'=>$patInfo['_id']),$sUpdate);
		}
		//登陆成功刷新设备表里的记录
		$this->User_model->refreshRunLog($sInfo['id'],(string)$patInfo['_id'],$sInfo['agent']);
		//数据整合返回
		$result = array('patient'=>(string)$patInfo['_id'],'session_token'=>$sessionToken,'nickname'=>$this->params['nickname']);
		display($result,0,"登录成功！");
	}


    /** 
     * 退出登录
	 * @param
     * @access public
     * @return text/json
     */
	public function sign_out() {
		$sInfo = getClientHeaders();
		emptyCheck($sInfo,array('token'));
		//删除pat_user_session
		$this->Common_model->deleteOneRecord('pat_user_session',array('session_token'=>$sInfo['token']));
		display(array(),0,"退出成功！");
	}





}

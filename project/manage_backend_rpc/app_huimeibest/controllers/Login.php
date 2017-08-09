<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Login.php
 */
class Login extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('Login_model');
	}

    /**
     * 用户登录
     */
	public function index() {
		emptyCheck(array('mobile','pwd'));
		$params = safeParams($_POST);
		$checkUser = $this->Common_model->getInfo('manage_admin',array('mobile'=>$params['mobile'],'status'=>1));
		if(empty($checkUser)){display(array(),2,'账户不存在');}
		if($checkUser['pwd'] !== password($params['pwd'],$checkUser['sign'])){	display(array(),3,'请核对密码');}
		$data['sign'] = getRandomPass(46);
		$data['pwd'] = password($params['pwd'],$data['sign'] );
		$data['actived_at'] = time();
		$refreshPwd = $this->Common_model->updateRecord('manage_admin',array('_id'=>$checkUser['_id']),$data);
		//判断用户角色赋予权限填充SESSION信息;
		$_SESSION['HM']['mobile'] = $params['mobile'];
		$_SESSION['HM']['id'] = $checkUser['_id']->{'$id'};
		$_SESSION['HM']['name'] = $checkUser['name'];
		$_SESSION['HM']['power'] = $this->Login_model->getPower($params['mobile']);
		$powers = array();
		foreach ($_SESSION['HM']['power']['powers'] as $k=>$v){
			$powers[] = $v['name'];
		}
		$_SESSION['HM']['powers'] = $powers;
		display($_SESSION['HM']);
	}

	//退出登陆:q

	public function out()
	{
		//删除退出用户session-name  get    用戶名  銷燬
		unset($_SESSION['HM']);
		display(array(),0,'退出成功');
	}











}

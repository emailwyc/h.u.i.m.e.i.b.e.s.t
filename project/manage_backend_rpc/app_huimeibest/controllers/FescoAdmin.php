<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 7/1/16
 * Time: 2:25 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * PatArticle.php
 */
class FescoAdmin extends CI_Controller {

    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    //fesco  后台管理员登陆
    public function index()
    {
        emptyCheck(array('name','pwd'));
        $params = safeParams($_POST);
		if($params['name'] === 'Fescoadmin' && $params['pwd'] === 'Fescoadmin'){
			$_SESSION['fesco'] = 1;
            display(array('login_status'=>1));
        }else{
            display(array(),1,'请输入正确的用户名和密码');
        }
	}

    public function loginOut()
	{
		if(!empty($_SESSION['fesco'])){
			unset($_SESSION['fesco']);
            display(array(),0,"退出成功!");
		}else{
            display(array(),2,"您未登陆，无需退出");
		}
    }
}

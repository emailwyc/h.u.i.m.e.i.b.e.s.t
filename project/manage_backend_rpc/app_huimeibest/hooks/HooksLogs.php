<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *　HooksLogs.php
 */
class HooksLogs{

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		$this->CI = &get_instance(); 
	}

	//得到日志列表
	public function create(){
		if(!empty($_SESSION['HM'])){
			$mongodate= time();
			$route = strtolower($this->CI->uri->segment(1)."/".$this->CI->uri->segment(2));
			$ip =  $this->CI->input->ip_address();
			$global_apidesc = config_item("global_apidesc");
			$class = empty($global_apidesc[$route])?0:$global_apidesc[$route][1];
			$desc= empty($global_apidesc[$route])?$route:$global_apidesc[$route][0];
			$insertData = array(
				'acc' => @$_SESSION['HM']['mobile'],
				'acc_name' => @$_SESSION['HM']['name'],
				'class'=> (int)$class,
				'desc' => $desc,
				'route' => $route,
				'created_at' => $mongodate,
				'ip' => $ip
			);
			$insertId =$this->CI->Common_model->insertInfo('manage_log',$insertData);
		}
	}


}

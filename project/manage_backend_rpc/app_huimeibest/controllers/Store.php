<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$msg_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/file/';
require_once $msg_file.'MKExcel.php';
/**
 * 用户聊天
 */

class Store extends CI_Controller {

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


	//得到活动信息
	private function getActInfo($act) {
		set_time_limit(0);
		$info1 = $this->Common_model->getInfoAll('act_log',array("name"=>"website_que",'act'=>(string)$act),array('_id'=>-1));
		$xls  = new ExportExcel("活动信息统计".$act.".xls", "UTF-8");
		$title = array('活动名称',"活动类型",'电话','姓名','描述','参与时间');
		$xls->addArray($title);
		foreach($info1 as $v){
			$v['act'] = empty($v['act'])?"":$v['act'];
			$hang = array($v['name'],$v['act'],$v['tel'],$v['username'],$v['description'],date('Y-m-d H:i',$v['ct']));
			$xls->addArray($hang);
		}
		echo "success";
	}

}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *Actlog.php
 */
class Actlog extends CI_Controller {

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

	public function getLogList(){
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//得到总分页
		$where = array();
		if(isset($_POST['where']) && !empty($_POST['where'])){
			$where = $_POST['where'];
		}
		$allcount = $this->Common_model->getInfoCount('act_free_log',$where);
		$allpage = ceil($allcount/$perpage);
		//医生文章列表
		$result = $this->Common_model->getListInfo('act_free_log',$where,$offset,$perpage,array("_id"=>-1),array());
		if($result){
			foreach($result as $k=>$v){
				$result[$k]['ct'] = date('Y-m-d H:i:s',$v['ct']);
			}
		}
		
		display(array('page'=>$allpage,'data'=>$result));
	}








}

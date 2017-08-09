<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *　Logs.php
 */
class Logs extends CI_Controller {

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

	//得到日志列表
	public function getList(){
        $page = getCurPage(); $perpage = 10;
        $offset = getPage($page,$perpage);
        //得到总分页
        $where = array();
        if(!empty($_REQUEST['classes'])){ $where['class'] =  (int)$_REQUEST['classes']; }
        $like = !empty($_POST['key']) ? addslashes($_POST['key']):"";
		$allcount = $this->Common_model->getInfoCount('manage_log',$where,array('desc'),$like);// like 统计条数
        //日志列表
		$orderByArray = empty($_POST['orderBy']) ? array('_id'=>-1):array($_POST['orderBy']=>-1);
		$result = $this->Common_model->searchKeysLikes('manage_log',$where,array(),array('desc'),$like,$offset,$perpage, $orderByArray);
		foreach($result as $k=>$v){
			$result[$k]['created_at'] = date('Y-m-d H:i',$v['created_at']);
		}
        display(array('page'=>ceil($allcount/$perpage),'data'=>$result));
	}


}

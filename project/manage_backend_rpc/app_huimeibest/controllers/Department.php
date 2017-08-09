<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * department.php
 */
class Department extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();checkLogin1();checkUserPower();
	}

	//得到一二级别科室(分页)
	public function getDepartment(){
		$page = getCurPage(); $perpage = 30;
		$offset = getPage($page,$perpage);
		$result = $this->Common_model->getListInfo('department',array('tags'=>1),$offset,$perpage,array('tags'=>-1,"order"=>-1),array());
		foreach($result as $k=>$v){
			$result[$k]['child'] = $this->Common_model->getInfoAll('department',array('parent'=>$v['_id']));
		}
		display($result);
	}

	//得到一级别科室
	public function getParent(){
		$page = getCurPage(); $perpage = 200;
		$offset = getPage($page,$perpage);
		$result = $this->Common_model->getListInfo('department',array('tags'=>1),$offset,$perpage,array('tags'=>-1,"order"=>-1),array());
		display($result);
	}

	//得到二级科室
	public function getChild(){
		if(empty($_POST['parent'])){ echo display(array(),2,"参数有误");}
		$page = getCurPage(); $perpage = 200;
		$offset = getPage($page,$perpage);
		$where = array('parent'=>getMdbId($_REQUEST['parent']));
		$result = $this->Common_model->getListInfo('department',array(),$offset,$perpage,array("order"=>-1),array());
		display($result);
	}

	//获取单个科室详情
	public function getDetail(){
		if(empty($_POST['_id'])){ echo display(array(),2,"参数有误");}
		$where = array('_id'=>getMdbId($_REQUEST['_id']));
		$result = $this->Common_model->getInfo('department',$where);
		if($result){
			$result['updated_at'] = date('Y-m-d H:i:s',$v['updated_at']->sec);
			$result['created_at'] = date('Y-m-d H:i:s',$v['created_at']->sec);
		}
		display($result);
	}

	//添加科室
	public function addDepartment(){
		if(empty($_POST['name'])){ display(array(),2,"参数有误");}
		$mongodate= new MongoDate(time());
		$insertData = array(
			"name" => addslashes($_POST['name']),
			"description"=>addslashes($_POST['description']),
			"lcon" => "",
			"status" => 1,
			"order" => 1,
			"created_at" => $mongodate,
			"updated_at" => $mongodate,
		);
		if(!empty($_POST['parent'])){ 
			$insertData['parent'] = getMdbId($_POST['parent']);
		}else{
			$insertData['tags'] = 1;
		}
		$insertId = $this->Common_model->insertInfo('department',$insertData);
		if($insertId){
			display(array('insertId'=>$insertId));
		}else{
			display(array(),3,"插入数据失败");
		}
	}

	//编辑科室
	public function editDepartment(){
		if(empty($_POST['name']) || empty($_POST['_id'])){ display(array(),2,"参数有误");}
		$mongodate= new MongoDate(time());
		$noset = array();
		$setData = array(
			"name" => addslashes($_POST['name']),
			"description"=>addslashes($_POST['description']),
			"updated_at" => $mongodate,
		);
		if(!empty($_POST['parent'])){ 
			$setData['parent'] = getMdbId($_POST['parent']);
		}else{
			$setData['tags'] = 1;;
			$noset['parent'] = 1;
		}
		$update = array('$set'=>$setData);
		if($noset){
			$update['$unset'] = $noset;
		}
		$check = $this->Common_model->updateSetRecord('department',array('_id'=>getMdbId($_POST['_id'])),$update);
		if($check){
			display(array());
		}else{
			display(array(),3,"更新数据失败");
		}
	}








}

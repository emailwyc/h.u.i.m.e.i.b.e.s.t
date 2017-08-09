<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hospital.php
 */
class Hospital extends CI_Controller {

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


    /** 
     * 医生列表
     * 
     * @param  $p
     * @access public
     * @return void
     */
	public function getlist() {
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//得到总分页
		$where = getWhereParams();
		$allcount = $this->Common_model->getInfoCount('hospital',$where);
		$allpage = ceil($allcount/$perpage);
		//医院列表
		$fields = array('name','region_id','region_child_id','address','branches');
		$result = $this->Common_model->getListInfo('hospital',$where,$offset,$perpage,array("_id"=>-1),$fields);
		//处理医院列表
		foreach($result as $k=>$v){
			if($v['region_id']){ $result[$k]['region_id'] = $this->Common_model->getInfo('region',array('_id'=>$v['region_id']),array('name')); }
			if($v['region_child_id']){ $result[$k]['region_child_id'] = $this->Common_model->getInfo('region',array('_id'=>$v['region_child_id']),array('name')); }
		}
		display(array('page'=>$allpage,'data'=>$result));
	}

	//获取某个医院详情
	public function getDetail(){
		if(empty($_REQUEST['_id'])){ echo display(array(),2,"参数有误");}
		$where = array('_id'=>getMdbId($_REQUEST['_id']));
		$result = $this->Common_model->getInfo('hospital',$where);
		if($result){
			if($result['region_id']){ $result['region_id'] = $this->Common_model->getInfo('region',array('_id'=>$result['region_id']),array('name')); }
			if($result['region_child_id']){ $result['region_child_id'] = $this->Common_model->getInfo('region',array('_id'=>$result['region_child_id']),array('name')); }
		}
		display($result);
	}


	//得到医院(所有)
	public function getHospital(){
		$page = getCurPage(); $perpage = 100;
		$offset = getPage($page,$perpage);
		$result = $this->Common_model->getListInfo('hospital',array(),$offset,$perpage,array("_id"=>-1),array('name','_id'),true);
		display($result);
	}

	//添加医院
	public function addHospital(){
		emptyCheck(array('name','region_id','region_child_id','branches'));
		$mongodate= new MongoDate(time());
		$insertData = array(
			"name" => addslashes($_POST['name']),
		    "created_at" => $mongodate,
			"updated_at" => $mongodate,
			"description" => addslashes($_POST['description']),
			"level" => 3,
			"lcon" => "",
			"order" => 1,
			"status" => 1,
			"region_id" => getMdbId($_POST['region_id']),
			"region_child_id" => getMdbId($_POST['region_child_id']),
			"address" => addslashes($_POST['address']),
			"branches" => $_POST['branches'],
			"rule" => addslashes($_POST['rule'])	
		);
		$insertId = $this->Common_model->insertInfo('hospital',$insertData);
		if($insertId){
			display(array('insertId'=>$insertId));
		}else{
			display(array(),3,"插入数据失败");
		}
	}

	//编辑医院
	public function editHospital(){
		emptyCheck(array('_id','name','region_id','region_child_id','branches'));
		$mongodate= new MongoDate(time());
		$insertData = array(
			"name" => addslashes($_POST['name']),
			"updated_at" => $mongodate,
			"description" => addslashes($_POST['description']),
			"region_id" => getMdbId($_POST['region_id']),
			"region_child_id" => getMdbId($_POST['region_child_id']),
			"address" => addslashes($_POST['address']),
			"branches" => $_POST['branches'],
			"rule" => addslashes($_POST['rule'])	
		);
		$check = $this->Common_model->updateRecord('hospital',array('_id'=>getMdbId($_POST['_id'])),$insertData);
		if($check){ display(array()); }else{ display(array(),3,"更新数据失败"); }
	}





}

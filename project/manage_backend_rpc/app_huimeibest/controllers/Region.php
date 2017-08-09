<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Region.php
 */
class Region extends CI_Controller {

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

	//得到一级地区
	public function getRegion(){
		$page = getCurPage(); $perpage = 100;
		$offset = getPage($page,$perpage);
		$result = $this->Common_model->getListInfo('region',array('parent'=>array('$exists'=>0)),$offset,$perpage,array("_id"=>1),array());
		display($result);
	}

	//得到二级地区
	public function getRegionChild(){
		$page = getCurPage(); $perpage = 100;
		$offset = getPage($page,$perpage);
		if(empty($_POST['parent'])){ echo display(array(),2,"参数有误");}
		$where = array('parent'=>array('$exists'=>1),'parent'=>getMdbId($_REQUEST['parent']));
		$result = $this->Common_model->getListInfo('region',$where,$offset,$perpage,array("_id"=>1),array());
		display($result);
	}

	//得到一二级区域
	public function getRegionAndChild(){
		$page = getCurPage(); $perpage = 50;
		$offset = getPage($page,$perpage);
		$allcount = $this->Common_model->getInfoCount('region',array('level'=>1));
		$allpage = ceil($allcount/$perpage);
		$result = $this->Common_model->getListInfo('region',array('level'=>1),$offset,$perpage,array("weight"=>-1),array());
		foreach($result as $k=>$v){
			$result[$k]['child'] = $this->Common_model->getInfoAll('region',array('parent'=>$v['_id']));
		}
		display(array('page'=>$allpage,'data'=>$result));
	}


	//根据id得到区域详情
	public function getDetail(){
		if(empty($_REQUEST['_id'])){ echo display(array(),2,"参数有误");}
		$where = array('_id'=>getMdbId($_REQUEST['_id']));
		$result = $this->Common_model->getInfo('region',$where);
		if($result['parent']){
			$result['parent'] = $this->Common_model->getInfo('region',array('_id'=>$result['parent']));
		}
		display($result);
	}

	//添加地区
	public function addRegion(){
		emptyCheck(array('name'));
		$insertData = array(
			"name" => addslashes($_POST['name']),
			"weight" => 1,
		);
		if(!empty($_POST['parent'])){ 
			$insertData['parent'] = getMdbId($_POST['parent']);
			$insertData['level'] = 2;
		}else{
			$insertData['level'] = 1;
		}
		$insertId = $this->Common_model->insertInfo('region',$insertData);
		if($insertId){ display(array('insertId'=>$insertId)); }else{ display(array(),3,"插入数据失败"); }
	}

	//编辑地区
	public function editRegion(){
		emptyCheck(array('_id','name'));
		$noset = array();
		$setData = array( "name" => addslashes($_POST['name']));
		if(!empty($_POST['parent'])){ 
			$setData['parent'] = getMdbId($_POST['parent']);
			$setData['level'] = 2;
		}else{
			$setData['level'] = 1;;
			$noset['parent'] = 1;
		}
		$update = array('$set'=>$setData);
		if($noset){ $update['$unset'] = $noset; }
		$check = $this->Common_model->updateSetRecord('region',array('_id'=>getMdbId($_POST['_id'])),$update);
		if($check){ display(array()); }else{ display(array(),3,"更新数据失败"); }
	}












}

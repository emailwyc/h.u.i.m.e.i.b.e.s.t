<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Article.php
 */
class Article extends CI_Controller {

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

	//得到某个医生的文章
	public function getDoctorArticle(){
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//得到总分页
		$where = array('doctor'=>getMdbId($_REQUEST['doctor']));
		$allcount = $this->Common_model->getInfoCount('doctor_article',$where);
		$allpage = ceil($allcount/$perpage);
		//医生文章列表
		$result = $this->Common_model->getListInfo('doctor_article',$where,$offset,$perpage,array("_id"=>-1),array());
		display(array('page'=>$allpage,'data'=>$result));
	}

	//添加医生文章
	public function addDoctorArticle(){
		emptyCheck(array('title','posted_date','doctor'));
		$mongodate= new MongoDate(time());
		$insertData = array(
			"title" => addslashes($_POST['title']),
			"doctor" =>getMdbId($_POST['doctor']),
			"posted_date" => addslashes($_POST['posted_date']),
			"link_url" => addslashes($_POST['link_url']),
			"image_url" => addslashes($_POST['image_url']),
			"description" => addslashes($_POST['description']),
			"created_at" => $mongodate,
			"updated_at" => $mongodate
		);
		$insertId = $this->Common_model->insertInfo('doctor_article',$insertData);
		if($insertId){
			display(array('insertId'=>$insertId));
		}else{
			display(array(),3,"插入数据失败");
		}
	}

	//编辑精品文章
	public function editDoctorArticle(){
		emptyCheck(array('_id','title','posted_date'));
		$mongodate= new MongoDate(time());
		$insertData = array(
			"title" => addslashes($_POST['title']),
			"posted_date" => addslashes($_POST['posted_date']),
			"link_url" => addslashes($_POST['link_url']),
			"image_url" => addslashes($_POST['image_url']),
			"description" => addslashes($_POST['description']),
			"updated_at" => $mongodate
		);
		$check = $this->Common_model->updateRecord('doctor_article',array('_id'=>getMdbId($_POST['_id'])),$insertData);
		if($check){ display(array()); }else{ display(array(),3,"更新数据失败"); }
	}

	//得到所有文章列表
	public function getList(){
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//得到总分页
		$where = array();
		$allcount = $this->Common_model->getInfoCount('article_hot',$where);
		$allpage = ceil($allcount/$perpage);
		//医生文章列表
		$fields = array('title','posted_date','link_url','image_url','description');
		$result = $this->Common_model->getListInfo('article_hot',$where,$offset,$perpage,array("_id"=>-1),$fields);
		display(array('page'=>$allpage,'data'=>$result));
	}

	//添加精品文章
	public function addArticleHot(){
		emptyCheck(array('title','posted_date'));
		$mongodate= new MongoDate(time());
		$insertData = array(
			"title" => addslashes($_POST['title']),
			"posted_date" => addslashes($_POST['posted_date']),
			"link_url" => addslashes($_POST['link_url']),
			"image_url" => addslashes($_POST['image_url']),
			"description" => addslashes($_POST['description']),
			"created_at" => $mongodate,
			"updated_at" => $mongodate
		);
		$insertId = $this->Common_model->insertInfo('article_hot',$insertData);
		if($insertId){
			display(array('insertId'=>$insertId));
		}else{
			display(array(),3,"插入数据失败");
		}
	}

	//编辑精品文章
	public function editArticleHot(){
		emptyCheck(array('_id','title','posted_date'));
		$mongodate= new MongoDate(time());
		$insertData = array(
			"title" => addslashes($_POST['title']),
			"posted_date" => addslashes($_POST['posted_date']),
			"link_url" => addslashes($_POST['link_url']),
			"image_url" => addslashes($_POST['image_url']),
			"description" => addslashes($_POST['description']),
			"updated_at" => $mongodate
		);
		$check = $this->Common_model->updateRecord('article_hot',array('_id'=>getMdbId($_POST['_id'])),$insertData);
		if($check){ display(array()); }else{ display(array(),3,"更新数据失败"); }
	}












}

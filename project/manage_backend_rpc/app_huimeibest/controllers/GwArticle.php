<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * GwArticle.php
 */
class GwArticle extends CI_Controller {

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

	//得到文章列表
	public function getList(){
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//得到总分页
		$where = array();
		if(!empty($_REQUEST['class'])){ $where['class'] =  (int)$_REQUEST['class']; }
		$allcount = $this->Common_model->getInfoCount('website_article',$where);
		$allpage = ceil($allcount/$perpage);
		//文章列表
		$fields = array('_id','title','description','icon','class','like_num','comment_num','created_at','order','author');
		$result = $this->Common_model->getListInfo('website_article',$where,$offset,$perpage,array('order'=>-1,"_id"=>-1),$fields);
		if($result){
			foreach($result as $k=>$v){
				$result[$k]['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
			}
		}
		display(array('page'=>$allpage,'data'=>$result));
	}

	//得到文章详情
	public function getDetails(){
		if(empty($_POST['_id'])){ echo display(array(),2,"参数有误");}
		$where = array('_id'=>getMdbId($_REQUEST['_id']));
		$result = $this->Common_model->getInfo('website_article',$where);
		if($result){
			$result['body'] =(($result['body']));
			$result['updated_at'] = date('Y-m-d H:i:s',$result['updated_at']);
			$result['created_at'] = date('Y-m-d H:i:s',$result['created_at']);
		}else{
			display(array(),3,"未找到文章");
		}
		display($result);
	}

	//添加文章
	public function addArticle(){
		emptyCheck(array('title','icon','class'));
		$mongodate= time();
		$insertData = array(
			"title" => addslashes($_POST['title']),
			"description" => addslashes($_POST['description']),
			"icon" => addslashes($_POST['icon']),
			"body" =>($_POST['body']),
			"author" =>(string)$_POST['author'],
			"class" => (int)addslashes($_POST['class']),
			"order" => (int)addslashes($_POST['order']),
			"like_num" =>0,
			"comment_num" =>0,
			"likes" => array(),
			"created_at" => $mongodate,
			"updated_at" => $mongodate
		);
		$insertId = $this->Common_model->insertInfo('website_article',$insertData);
		if($insertId){
			display(array('insertId'=>$insertId));
		}else{
			display(array(),3,"插入数据失败");
		}
	}

	//编辑文章
	public function editArticle(){
		emptyCheck(array('_id','title','icon'));
		$mongodate= time();
		$insertData = array(
			"title" => addslashes($_POST['title']),
			"description" => addslashes($_POST['description']),
			"icon" => addslashes($_POST['icon']),
			"body" =>($_POST['body']),
			"author" =>(string)$_POST['author'],
			"class" => (int)addslashes($_POST['class']),
			"order" => (int)addslashes($_POST['order']),
			"updated_at" => $mongodate
		);
		$check = $this->Common_model->updateRecord('website_article',array('_id'=>getMdbId($_POST['_id'])),$insertData);
		if($check){ display(array()); }else{ display(array(),3,"更新数据失败"); }
	}

	//删除文章
	public function delArticle(){
		emptyCheck(array('_id'));
		$insertId = $this->Common_model->deleteOneRecord('website_article',array('_id'=>getMdbId($_POST['_id'])));
		if($insertId){
			display(array(),0,"删除成功!");
		}else{
			display(array(),3,"删除失败!");
		}
	}













}

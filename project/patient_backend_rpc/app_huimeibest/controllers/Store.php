<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$msg_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/file/';
require_once $msg_file.'MKExcel.php';

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

	private function createArt() {
		$wang = file_get_contents("http://h5test.huimeibest.com:8087/static/article.json");
		$article_arr = json_decode((string)$wang,true);
		exit;
		foreach($article_arr as $k=>$v){
			if($v['author']=="海外"){
				$classes=1;
			}elseif($v['author']=="精选"){
				$classes=3;
			}elseif($v['author']=="魏以桢"){
				$classes=2;
			}else{
				continue;
			}
			$icon = "http://h5test.huimeibest.com:8087/ui/images/article/".$v['_id']['oid'].".jpeg";
			$info = array(
					'title'=>addslashes($v['title']),
					'description'=>$v['title'],
					'icon'=>$icon,
					'link_url'=>addslashes($v['url']),
					'body'=>"",
					'classes'=>$classes,
					'type'=>2,
					'like_num'=>0,
					'comment_num'=>0,
					'created_at'=>time(),
					'updated_at'=>time(),
					'likes'=>array()
				);

			$this->Common_model->insertInfo('pat_article',$info);
		}
	}

	private function createArt1() {
		$wang = file_get_contents("http://h5test.huimeibest.com:8087/static/article_dingxiangyuan.json");
		$article_arr = json_decode((string)$wang,true);
		foreach($article_arr as $k=>$v){
			$icon = "http://h5test.huimeibest.com:8087/ui/images/article/".$v['_id']['$oid'].".jpeg";
			$info = array(
					'title'=>addslashes($v['title']),
					'description'=>$v['title'],
					'icon'=>$icon,
					'author'=>$v['author'],
					'link_url'=>addslashes($v['url']),
					'body'=>"",
					'classes'=>(int)$v['classes'],
					'type'=>2,
					'like_num'=>0,
					'read_num'=>0,
					'comment_num'=>0,
					'pubdate'=>(string)date('Y-m-d H:i:s',strtotime($v['publish_date']['$date'])),
					'created_at'=>time(),
					'updated_at'=>time(),
					'likes'=>array(),
					'reads'=>array(),
					'status'=>1,
				);
				print_r($info);exit;
			$this->Common_model->insertInfo('pat_article',$info);
		}
	}

	private function updateArt() {
		$article_arr = $this->Common_model->getInfoAll('pat_article',array());
		foreach($article_arr as $k=>$v){
			$update= array(
				'$unset'=>array('class'=>"")
				);
			$this->Common_model->updateSetRecord('pat_article',array('_id'=>$v['_id']),$update);
		}
		echo 11;exit;
	}


}

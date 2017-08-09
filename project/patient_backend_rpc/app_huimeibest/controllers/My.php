<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * My.php
 */
class My extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		//校验session， //session是否过期,
		$this->userid = (string)$this->Common_model->checkLogin();
	}

    /** 
     * 我的文章订单
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function articleOrder() {
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('status'=>1,'userId'=>$this->userid);
		$result = $this->Common_model->getListInfo('pat_article_order',$where,$offset,$perpage,array("_id"=>-1),array(),true);
		display($result,0,'ok','list');
	}

    /** 
     * 我的收藏
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function articleCollect() {
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('user_id'=>$this->userid);
		$result = $this->Common_model->getListInfo('pat_article_collect',$where,$offset,$perpage,array("_id"=>-1),array(),true);
		$artIds = getFieldArr($result,'article_id',1);
		$artInfo = ArrKeyFromId($this->Common_model->getInfoAll('pat_article',array('_id'=>array('$in'=>$artIds)),"",array('read_num','pubdate','like_num','comment_num','title','icon','price','likes'),true));
		//是否有阅读权限
		$where_access = array('userId'=>$this->userid,'status'=>1,'artId'=>array('$in'=>getFieldArr($result,'article_id',0)));
		$accessArtList = $this->Common_model->getInfoAll('pat_article_order',$where_access,"",array('_id','artId'),true);
		$accessArt = getFieldArr($accessArtList,'artId',0);

		foreach($result as $k=>$v){
			$result[$k]['isdel'] = isset($artInfo[$v['article_id']])?false:true;
			if(isset($artInfo[$v['article_id']])){
				$artInfo[$v['article_id']]['is_likes'] = in_array((string)$this->userid,$artInfo[$v['article_id']]['likes'])?true:false;
				if(isset($artInfo[$v['article_id']]['price']) && $artInfo[$v['article_id']]['price']>0){
					$artInfo[$v['article_id']]['is_vip'] = true;
					$artInfo[$v['article_id']]['read_access'] = isset($accessArt) && in_array($v['article_id'],$accessArt)?true:false;//是否可以阅读
				}else{
					$artInfo[$v['article_id']]['is_vip'] = false;
					$artInfo[$v['article_id']]['read_access'] = true;
				}
				unset($artInfo[$v['article_id']]['price']); unset($artInfo[$v['article_id']]['likes']);
				$result[$k]['article'] = $artInfo[$v['article_id']];
			}else{
				$result[$k]['article'] = (object)array();
			}
		}
		display($result,0,'ok','list');
	}


	/**
	 * 我收到的评论
	 */
	public function comment()
	{
		//params ready
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$pat_id = (string)$this->Common_model->checkLogin();
		//comment me
		$where = array('pid'=>$pat_id);
		$patCommentBody = $this->Common_model->getListInfo('pat_comment',$where,$offset,$perpage,array('_id'=>-1),array(),true);
		$patCommentBody = ArrKeyFromId($patCommentBody,'body_id');
		//owen comment
		$owenWhere = array('uid'=>$pat_id);
		$patCommentIds = $this->Common_model->getListInfo('pat_article_comment',$owenWhere,$offset,$perpage,array(),array('_id'),true);
		$patCommentIds = getFieldArr($patCommentIds,'_id',0);
		//owen body comment
		$owenBodyComment = $this->Common_model->getListInfo('pat_comment',array('comment_id'=>array('$in'=>$patCommentIds)),$offset,$perpage,array(),array(),true);
		$owenBodyComment = ArrKeyFromId($owenBodyComment,'body_id');
		$body = array_values(array_merge($patCommentBody,$owenBodyComment));
		$body = sortArrByField($body,'created_at',true);
		display($body,0,'ok','list');
	}

	public function commentCopy()
	{
		//和我相关的评论
		$patComment = $this->Common_model->getInfoAll('pat_article_comment',array(),array(),array(),true);
		//获取相关文章
		$ArtId1 = getFieldArr($patComment,'article_id',1);
		$commentArticleId = array_values(array_unique(array_merge($ArtId1)));
		$patCommentArticleWhere = array('_id'=>array('$in'=>$commentArticleId));
		$commentFiler = array('icon','title');
		$commentArticleInfo = $this->Common_model->getInfoAll('pat_article',$patCommentArticleWhere,array(),$commentFiler,true);
		$commentArticleInfo = ArrKeyFromId($commentArticleInfo);
		//评论相关用户信息
		$body = array();
		if(!empty($patComment)){
			//print_r($patComments);print_r($patComments);
			foreach ($patComment as $k=>$v){
				if(!empty($v['body'])){
					for($i=0;$i<count($v['body']);$i++){
						$v['body'][$i]['article_id']= $v['article_id'];
						$v['body'][$i]['comment_id']= $v['_id'];
						$v['body'][$i]['body_id']= $v['body'][$i]['_id'];
						$body[] = $v['body'][$i];
					}
				}
			}
		}
		$patIds = array();
		for($i=0;$i<count($body);$i++){
			$patIds[$i]= getMdbId($body[$i]['uid']);
		}
		$patIds = array_values(array_unique($patIds));
		$patientWhere = array('_id'=>array('$in'=>$patIds));
		$patientFiler = array('avatar');
		$patientInfo = $this->Common_model->getInfoAll('pat_user',$patientWhere,array(),$patientFiler,true);
		$patientInfo = ArrKeyFromId($patientInfo);
		//数据处理
		for($i=0;$i<count($body);$i++){
			if(array_key_exists($body[$i]['article_id'],$commentArticleInfo)){
				$body[$i] = array_merge($body[$i],$commentArticleInfo[$body[$i]['article_id']]);
				$body[$i]['article_isset'] = true;
				$body[$i]['article_title'] = empty($body[$i]['title'])?'':$body[$i]['title'];
				$body[$i]['article_icon'] = empty($body[$i]['icon'])?'':$body[$i]['icon'];
				unset($body[$i]['_id'],$body[$i]['title'],$body[$i]['icon']);
			}else{
				$body[$i]['article_isset'] = false;
				$body[$i]['article_title'] = '';
				$body[$i]['article_icon'] = '';
			}
			if(array_key_exists($body[$i]['uid'],$patientInfo)){
				$body[$i] = array_merge($body[$i],$patientInfo[$body[$i]['uid']]);
				$body[$i]['patient_avatar'] = empty($body[$i]['avatar'])?'':$body[$i]['avatar'];
				unset($body[$i]['avatar'],$body[$i]['_id']);
			}else{
				$body[$i]['patient_avatar'] = '';
			}
		}
		$this->Common_model->batchInsert('pat_comment',$body);echo 1;

	}
	
	public function comment1()
	{
		//和我相关的评论
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$pat_id = (string)$this->Common_model->checkLogin();
		$patComment = $this->Common_model->getListInfo('pat_article_comment',array(),$offset,$perpage,array(),array('body'),true);
		//我评论的评论
		$patCommentsWhere = array('body.uid'=>$pat_id);
		$patComments = $this->Common_model->getListInfo('pat_article_comment',$patCommentsWhere,$offset,$perpage,array("_id"=>-1),array(),true);
		//获取相关文章
		$ArtId1 = getFieldArr($patComment,'article_id',1);
		$ArtId2 = getFieldArr($patComments,'article_id',1);
		$commentArticleId = array_values(array_unique(array_merge($ArtId1,$ArtId2)));
		$patCommentArticleWhere = array('_id'=>array('$in'=>$commentArticleId));
		$commentFiler = array('icon','title');
		$commentArticleInfo = $this->Common_model->getInfoAll('pat_article',$patCommentArticleWhere,array(),$commentFiler,true);
		$commentArticleInfo = ArrKeyFromId($commentArticleInfo);
		//评论相关用户信息
		$body = array();
		if(!empty($patComment) && !empty($patComments)){
			//print_r($patComments);print_r($patComments);
			foreach ($patComment as $k=>$v){
				if(!empty($v['body'])){
					for($i=0;$i<count($v['body']);$i++){
						$v['body'][$i]['article_id']= $v['article_id'];
						$v['body'][$i]['comment_id']= $v['_id'];
						$v['body'][$i]['body_id']= $v['body'][$i]['_id'];
						$body[] = $v['body'][$i];
					}
				}
			}
			foreach ($patComments as $k=>$v){
				if(!empty($v['body'])){
					for($i=0;$i<count($v['body']);$i++){
						$v['body'][$i]['article_id']= $v['article_id'];
						$v['body'][$i]['comment_id']= $v['_id'];
						$v['body'][$i]['body_id']= $v['body'][$i]['_id'];
						$body[] = $v['body'][$i];
					}
				}
			}
		}
		$patIds = array();
		for($i=0;$i<count($body);$i++){
			$patIds[$i]= getMdbId($body[$i]['uid']);
		}
		$patIds = array_values(array_unique($patIds));
		$patientWhere = array('_id'=>array('$in'=>$patIds));
		$patientFiler = array('avatar');
		$patientInfo = $this->Common_model->getInfoAll('pat_user',$patientWhere,array(),$patientFiler,true);
		$patientInfo = ArrKeyFromId($patientInfo);
		//数据处理
		for($i=0;$i<count($body);$i++){
			if(array_key_exists($body[$i]['article_id'],$commentArticleInfo)){
				$body[$i] = array_merge($body[$i],$commentArticleInfo[$body[$i]['article_id']]);
				$body[$i]['article_isset'] = true;
				$body[$i]['article_title'] = empty($body[$i]['title'])?'':$body[$i]['title'];
				$body[$i]['article_icon'] = empty($body[$i]['icon'])?'':$body[$i]['icon'];
				unset($body[$i]['_id'],$body[$i]['title'],$body[$i]['icon']);
			}else{
				$body[$i]['article_isset'] = false;
				$body[$i]['article_title'] = '';
				$body[$i]['article_icon'] = '';
			}
			if(array_key_exists($body[$i]['uid'],$patientInfo)){
				$body[$i] = array_merge($body[$i],$patientInfo[$body[$i]['uid']]);
				$body[$i]['patient_avatar'] = empty($body[$i]['avatar'])?'':$body[$i]['avatar'];
				unset($body[$i]['avatar']);
			}else{
				$body[$i]['patient_avatar'] = '';
			}
		}
		$this->Common_model->benchInsert($body);
		$body = sortArrByField($body,'created_at',true);
		display($body,0,'ok','list');
	}
}

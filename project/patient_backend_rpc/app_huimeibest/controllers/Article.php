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
		parent::__construct();
		//校验session， //session是否过期,
		//$this->Common_model->checkLogin();
	}

    /** 
     * 文章列表
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function getList() {
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('status'=>1,'pubdate'=>array('$lte'=>time()));
		if(!empty($this->params['classes'])){ $where['classes'] = $this->params['classes']; }
		if(!empty($this->params['doctor'])){ $where['doctor'] = $this->params['doctor']; }
		$sort = !empty($this->params['sort'])?$this->params['sort']:array("pubdate"=>-1);
		$fields = array('_id','title','description','type','comment_num','classes','created_at','class','link_url','icon','like_num','read_num','pubdate','author','doctor','tags','price');
		$result = $this->Common_model->getListInfo('pat_article',$where,$offset,$perpage,$sort,$fields,true);
		//是否点赞
		$PatId = (string)$this->Common_model->getPatId();
		if(!empty($PatId)){
			$where['_id']['$in'] = getFieldArr($result,'_id',1); $where_read = $where;
			//点赞文章
			$where['likes'] = $PatId;
			$likesArtList = $this->Common_model->getInfoAll('pat_article',$where,"",array('_id'),true);
			$likesArt = getFieldArr($likesArtList,'_id',0);
			//阅读文章
			$where_read['reads'] = $PatId;
			$readsArtList = $this->Common_model->getInfoAll('pat_article',$where_read,"",array('_id'),true);
			$readsArt = getFieldArr($readsArtList,'_id',0);
			//阅读权限
			$where_access = array('userId'=>$PatId,'status'=>1,'artId'=>array('$in'=>getFieldArr($result,'_id',0)));
			$accessArtList = $this->Common_model->getInfoAll('pat_article_order',$where_access,"",array('_id','artId'),true);
			$accessArt = getFieldArr($accessArtList,'artId',0);
		}
		foreach($result as $k=>$v){
			if(!empty($v['tags'])){
				for ($i=0; $i<count($v['tags']); $i++){
					$tags[$i] = getMdbId($v['tags'][$i]);
				}
				$result[$k]['tags'] =  $this->Common_model->getInfoAll('pat_article_tags',array("_id"=>array('$in'=>$tags)),null,array(),true);
			}
			$result[$k]['is_likes'] = isset($likesArt) && in_array($v['_id'],$likesArt)?true:false;
			$result[$k]['is_reads'] = isset($readsArt) && in_array($v['_id'],$readsArt)?true:false;
			if(isset($v['price']) && $v['price']>0){
				$result[$k]['is_vip'] = true;
				$result[$k]['read_access'] = isset($accessArt) && in_array($v['_id'],$accessArt)?true:false;//是否可以阅读
			}else{
				$result[$k]['is_vip'] = false;
				$result[$k]['read_access'] = true;
			}
			$result[$k]['price'] = (string)$result[$k]['price'];
		}
		display($result,0,'ok','list');
	}

    /** 
     * 得到文章详情
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function getDetails() {
		emptyCheck($this->params,array('_id'));
		$where = array('_id'=>getMdbId($this->params['_id']));
		$fields = array('_id','title','description','body','type','like_num','comment_num','likes','created_at','updated_at','classes','link_url','icon','read_num','reads','pubdate','author','doctor','tags','price',);
		$result = $this->Common_model->getInfo('pat_article',$where,$fields,true);
		if(!empty($result)){
			$PatId = $this->Common_model->getPatId();
			$result['is_likes'] = in_array((string)$PatId,$result['likes'])?true:false;
			$result['is_reads'] = in_array((string)$PatId,$result['reads'])?true:false;
			$result['is_vip'] = isset($result['price']) && $result['price']>0 ?true:false;
			$result['price'] = (string)$result['price'];
			if($result['is_vip']){
				$order = $this->Common_model->getInfo('pat_article_order',array('artId'=>$result['_id'],'userId'=>(string)$PatId,'status'=>1),array('_id'),true);
				$result['read_access'] = !empty($order)?true:false;
			}else{
				$result['read_access'] = true;
			}
			$collect = $this->Common_model->getInfo('pat_article_collect',array('user_id'=>(string)$PatId,'article_id'=>(string)$result['_id']),array('_id'),true);
			$result['is_collect'] = !empty($collect)?true:false;
			if(!empty($result['tags'])){
				for($i=0; $i<count($result['tags']); $i++){
					$tags[$i] = getMdbId($result['tags'][$i]);
				}
				$result['tags'] =  $this->Common_model->getInfoAll('pat_article_tags',array("_id"=>array('$in'=>$tags)),null,array(),true);
			}
		}else{
			display(false,-1,'文章不存在！');//负数状态码，表示数据不存在
		}
		display($result);
	}

    /** 
     * 得到评论列表
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function getCommentList() {
		emptyCheck($this->params,array('article_id'));
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('status'=>1,'article_id'=>$this->params['article_id']);
		$result = $this->Common_model->getListInfo('pat_article_comment',$where,$offset,$perpage,array("_id"=>-1),array(),true);
		//$patIds = getFieldArr($result,'pat_id',1);
		//$patInfos = $this->Common_model->getInfoAll('pat_user',array('_id'=>array('$in'=>$patIds)),'',array('nickname','avatar'),true);
		//$patInfos= ArrKeyFromId($patInfos);
		display($result,0,'ok','list');
	}

    /** 
     * 用户写评论
	 * @params 文章id，评论id，内容，评论者id，接受者id，接受者角色，接受者昵称,commentType评论类型1:评论文章，2回复评论
     * @access public
     * @return text/json
     */
	public function sendComment() {
		$pat_id = (string)$this->Common_model->checkLogin();
		emptyCheck($this->params,array('user_id','article_id','content'));
		//检查参数
		if(mb_strlen($this->params['content'])>200){ display(array(),1,'语言要简练哟');};
		if($pat_id!=$this->params['user_id']){ display(array(),3,"评论者不是本人，评论失败!"); }
		$artInfo = $this->Common_model->getInfo('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array('_id'));
		if(empty($artInfo)){ display(array(),3,"未找到文章，评论失败!"); }
		$patInfo = $this->Common_model->getInfo('pat_user',array('_id'=>getMdbId($pat_id)),array('nickname','avatar'),true);
		if(empty($patInfo)){ display(array(),3,"未找到患者，评论失败!"); }
		$stamptime = time();
		$insertInfo = array( 
			"article_id" => (string)$artInfo['_id'],
			"uid" => $pat_id, 
			"name" => $patInfo['nickname'], 
			"avatar" => $patInfo['avatar'],
			"role" => "pat_user",
			"content" => $this->params['content'],
			"body" => array(), 
			"created_at" => $stamptime,
			"updated_at" => $stamptime, 
			"status" => 1
		);
		$insertId = $this->Common_model->insertInfo('pat_article_comment',$insertInfo);
		if($insertId){
			$this->Common_model->updateSetRecord('pat_article',array('_id'=>$artInfo['_id']),array('$inc'=>array('comment_num'=>1)));
			$insertInfo['_id'] = (string)$insertId;
			display($insertInfo,0,"评论成功!");
		}else{
			display(array(),3,"评论失败!"); 
		}

	}

    /** 
     * 用户写评论
	 * @params 文章id，评论id，内容，评论者id，接受者id，接受者角色，接受者昵称,commentType评论类型1:评论文章，2回复评论
     * @access public
     * @return text/json
     */
	public function sendChildComment() {
		$pat_id = (string)$this->Common_model->checkLogin();
		emptyCheck($this->params,array('user_id','article_id','content','comment_id','pid','p_role'));
		//检查参数
		if(mb_strlen($this->params['content'])>200){ display(array(),1,'语言要简练哟');};
		if($pat_id!=$this->params['user_id']){ display(array(),3,"评论者不是本人，评论失败!"); }
		$artInfo = $this->Common_model->getInfo('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array('_id','title','icon'));
		if(empty($artInfo)){ display(array(),3,"未找到文章，评论失败!"); }
		$patInfo = $this->Common_model->getInfo('pat_user',array('_id'=>getMdbId($pat_id)),array('nickname','avatar'),true);
		if(empty($patInfo)){ display(array(),3,"未找到患者，评论失败!"); }
		$stamptime = time();
		$commentInfo = $this->Common_model->getInfo('pat_article_comment',array('_id'=>getMdbId($this->params['comment_id'])),array(),true);
		if(count($commentInfo['body'])>=50){ display(array(),3,"回复数量已超过上限!"); }
		if(empty($commentInfo)){ display(array(),3,"未找到父级评论!"); }
		if(!in_array($this->params['p_role'],array('pat_user','doctor'))){ display(array(),3,"角色有误!");}
		$insertInfo = array( 
			"_id" => (string)new MongoId(),
			"uid" => $pat_id, 
			"pid" => $this->params['pid'],
			"u_role" => "pat_user", 
			"p_role" => $this->params['p_role'],
			"u_name" => $patInfo['nickname'], 
			"p_name" => $this->params['p_name'],
			"content" => $this->params['content'],
			"created_at" => $stamptime,
		);
		$insertId = $this->Common_model->updateSetRecord('pat_article_comment',array('_id'=>getMdbId($this->params['comment_id'])),array('$addToSet'=>array('body'=>$insertInfo),'$set'=>array('updated_at'=>$stamptime)));
		//add the comment info for comment list
		$commentInsert = array(
			'uid'=>$pat_id,
			'pid'=> $this->params['pid'],
			'u_role'=>'pat_user',
			'p_role'=> $this->params['p_role'],
			'u_name'=>$patInfo['nickname'],
			'p_name'=> $this->params['p_name'],
			'content'=>$this->params['content'],
			'created_at'=> $stamptime,
			'article_id'=>$this->params['article_id'],
			'comment_id'=> $this->params['comment_id'],
			'body_id'=>$insertInfo['_id'],
			'article_isset'=> empty($artInfo)?false:true,
			'article_title'=>empty($artInfo)?'':$artInfo['title'],
			'article_icon'=> empty($artInfo)?'':$artInfo['icon'],
			'patient_avatar'=> empty($patInfo['avatar'])?'':$patInfo['avatar'],
		);
		$this->Common_model->insertInfo('pat_comment',$commentInsert);
		if($insertId['nModified']){
			if($this->config->item('open_push')){
				//通过id推送被评论人，和顶级评论人
				$alertMessage = empty($patInfo['nickname'])?'有人回复了您的评论！':$patInfo['nickname'].'回复了您的评论！';
				$extras = array('comment_id'=>$insertInfo['_id'],'type'=>'comment');
				$alias = array_unique(array($commentInfo['uid'],$this->params['pid']));
				if(!empty($alias)){
					$this->sendPush($alertMessage,$extras,$alias);
				}
			}
			display($insertInfo,0,"评论成功!");
		}else{ display(array(),3,"评论失败!"); }

	}


    /** 
     * 删除评论
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function delComment() {
		$pat_id = (string)$this->Common_model->checkLogin();
		emptyCheck($this->params,array('comment_id'));
		if(empty($this->params['body_id'])){
			//删除一级评论
			$comment = $this->Common_model->getInfo('pat_article_comment',array('_id'=>getMdbId($this->params['comment_id'])));
			if(empty($comment) || (string)$pat_id!=$comment['uid']){ display(array(),3,"删除失败!"); }
			$this->Common_model->updateRecord('pat_article_comment',array('_id'=>getMdbId($this->params['comment_id'])),array('status'=>0));
			if($comment['status']==1){ $this->Common_model->updateSetRecord('pat_article',array('_id'=>getMdbId($comment['article_id'])),array('$inc'=>array('comment_num'=>-1))); }
		}else{
			//删除二级评论
			$comment = $this->Common_model->getInfo('pat_article_comment',array('_id'=>getMdbId($this->params['comment_id']),'body._id'=>$this->params['body_id']));
			if(empty($comment)){ display(array(),3,"该记录不存在或已删除!"); }
			$this->Common_model->updateSetRecord('pat_article_comment',array('_id'=>getMdbId($this->params['comment_id'])),array('$pull'=>array('body'=>array('_id'=>$this->params['body_id']))));
		}
		display(TRUE,0,"删除成功!");
	}

    /** 
     * 文章点赞
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function likes() {
		$pat_id = $this->Common_model->checkLogin();
		emptyCheck($this->params,array('article_id'));
		$w = $this->Common_model->updateSetRecord('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array('$addToSet'=>array('likes'=>(string)$pat_id)));
		if(!empty($w['nModified'])){
			$this->Common_model->updateSetRecord('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array('$inc'=>array('like_num'=>1)));
			display(true,0,"点赞成功!");
		}else{
			display(FALSE,3,"重复点赞!");
		}
	}

    /** 
     * 文章阅读量
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function read() {
		$pat_id = $this->Common_model->checkLogin();
		emptyCheck($this->params,array('article_id'));
		$w = $this->Common_model->updateSetRecord('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array('$addToSet'=>array('reads'=>(string)$pat_id)));
		if(!empty($w['nModified'])){
			$this->Common_model->updateSetRecord('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array('$inc'=>array('read_num'=>1)));
			display(true,0,"成功!");
		}else{
			display(FALSE,3,"重复阅读!");
		}
	}

	/**
	 * 快速搜索标签（name搜索关键字，默认tags5条，不够title补）
	 */
	public function tagLikes()
	{
		emptyCheck($this->params,array('name'));
		$tags = $this->Common_model->searchLikes('pat_article_tags',array('name'),5,'name',$this->params['name'],true,array(),null,null);
		foreach($tags as $k => $v){
			$tags[$k]['type'] = 'tag';
		}
		if(count($tags)<5){
			$limit = 5-count($tags);
			$titles = $this->Common_model->searchLikes('pat_article',array('title','_id'),$limit,'title',$this->params['name'],true,array(),null,null);
			foreach($titles as $k => $v){
				$titles[$k]['name'] = $v['title'];
				$titles[$k]['type'] = 'article';
				unset($titles[$k]['title']);
			}
		}
		$result = array_merge($tags,$titles);
		if(empty($result)){ display(array(),0,'暂无数据','list');}
		display($result);
	}

	/**
	 * 获取对应标签文章列表
	 */
	public function tagLists()
	{
		emptyCheck($this->params,array('_id'));
		//获取对应tag下的文章
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('tags'=>$this->params['_id']);
		$result = $this->Common_model->getListInfo('pat_article',$where,$offset,$perpage,array("_id"=>-1),array(),true);
		//是否点赞
		$PatId = (string)$this->Common_model->getPatId();
		if(!empty($PatId)){
			$where_read = $where;
			//点赞文章
			$where['likes'] = $PatId;
			$sort = array('_id');
			$likesArtList = $this->Common_model->getListInfo('pat_article',$where,$offset,$perpage,$sort,array('_id'),true);
			$likesArt = getFieldArr($likesArtList,'_id',0);
			//阅读文章
			$where_read['reads'] = $PatId;
			$readsArtList = $this->Common_model->getListInfo('pat_article',$where_read,$offset,$perpage,$sort,array('_id'),true);
			$readsArt = getFieldArr($readsArtList,'_id',0);
		}
		foreach($result as $k=>$v){
			$result[$k]['is_likes'] = isset($likesArt) && in_array($v['_id'],$likesArt)?true:false;
			$result[$k]['is_reads'] = isset($readsArt) && in_array($v['_id'],$readsArt)?true:false;
			$result[$k]['is_vip'] = isset($result[$k]['price']) && $result[$k]['price']>0 ?true:false;
			$result[$k]['price'] = (string)$result[$k]['price'];
			if($result[$k]['is_vip']){
				$order = $this->Common_model->getInfo('pat_article_order',array('artId'=>$result[$k]['_id'],'userId'=>(string)$PatId,'status'=>1),array('_id'),true);
				$result[$k]['read_access'] = !empty($order)?true:false;
			}else{
				$result[$k]['read_access'] = true;
			}
			$tags = array();
			for ($i=0; $i<count($v['tags']); $i++){
				$tags[$i] = getMdbId($v['tags'][$i]);
			}
			$result[$k]['tags'] = empty($v['tags'])?array():$result[$k]['tags'] = $this->Common_model->getInfoAll('pat_article_tags',array("_id"=>array('$in'=>$tags)),null,array(),true);
		}
		display($result);
	}

	/**
	 * 搜索
	 */
	public function relateSearch()
	{
		emptyCheck($this->params,array('name'));
		//相关tags
		$where = array('status'=>1,'pubdate'=>array('$lte'=>time()));
		$result['tags'] = $this->Common_model->searchLikes('pat_article_tags',array('name'),5,'name',
			$this->params['name'],true,array(),null,null);
		//相关文章
		$fields = array('_id','title','description','type','comment_num','classes','created_at','class','link_url','icon','like_num','read_num','pubdate','author','doctor','tags','price');
		$result['articles'] = $this->Common_model->searchLikes('pat_article',$fields,3,'title',$this->params['name'],true,$where,array('_id'),null);
		//点赞文章
		$where_read = $where;
		$pat_id = (string)$this->Common_model->getPatId();
		$where['likes'] = (string)$pat_id;
		$likesArtList = $this->Common_model->getListInfo('pat_article', $where, null, null, array('_id'), array('_id'),
			true);
		$likesArt = getFieldArr($likesArtList, '_id', 0);
		//阅读文章
		$where_read['reads'] = (string)$pat_id;
		$readsArtList = $this->Common_model->getListInfo('pat_article', $where_read, null, array('_id'), null, array('_id'), true);
		$readsArt = getFieldArr($readsArtList, '_id', 0);
		foreach ($result['articles'] as $k => $v){
			$result['articles'][$k]['is_likes'] = isset($likesArt) && in_array($v['_id'],$likesArt)?true:false;
			$result['articles'][$k]['is_reads'] = isset($readsArt) && in_array($v['_id'],$readsArt)?true:false;
			$result['articles'][$k]['is_vip'] = isset($result['articles'][$k]['price']) && $result['articles'][$k]['price']>0 ?true:false;
			$result['articles'][$k]['price'] = (string)$result['articles'][$k]['price'];
			if($result['articles'][$k]['is_vip']){
				$order = $this->Common_model->getInfo('pat_article_order',array('artId'=>$result['articles'][$k]['_id'],'userId'=>(string)$pat_id,'status'=>1),array('_id'),true);
				$result['articles'][$k]['read_access'] = !empty($order)?true:false;
			}else{
				$result['articles'][$k]['read_access'] = true;
			}
			$tags = array();
			if(count($v['tags'])>0){
				for ($i=0; $i<count($v['tags']); $i++){
					$tags[$i] = getMdbId($v['tags'][$i]);
				}
			}
			$result['articles'][$k]['tags'] = empty($v['tags'])?array():$result['articles'][$k]['tags'] = $this->Common_model->getInfoAll('pat_article_tags',array("_id"=>array('$in'=>$tags)),null,array(),true);
		}
		if(empty($result)){ display(array(),0,'暂无数据','list');}
		display($result);
	}

	//更多内容
	public function other()
	{
		$pat_id = (string)$this->Common_model->getPatId();
		emptyCheck($this->params,array('name','type'));
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('status'=>1,'pubdate'=>array('$lte'=>time()));
		//相关tags
		if($this->params['type'] == 'tag'){
			$result['tags'] = $this->Common_model->searchLikes('pat_article_tags',array('name'),$perpage,'name',$this->params['name'],true,array(),null,$offset);
		}
		if($this->params['type'] == 'article'){
			$fields = array('_id','title','description','type','comment_num','classes','created_at','class','link_url','icon','like_num','read_num','pubdate','author','doctor','tags','price');
			$article = $this->Common_model->searchLikes('pat_article',$fields,$perpage,'title',
				$this->params['name'],true,$where,null,$offset);
			//点赞文章
			$where_read = $where;
			$where['likes'] = (string)$pat_id;
			$likesArtList = $this->Common_model->getListInfo('pat_article', $where, null, null, array('_id'), array('_id'),true);
			$likesArt = getFieldArr($likesArtList, '_id', 0);
			//阅读文章
			$where_read['reads'] = (string)$pat_id;
			$readsArtList = $this->Common_model->getListInfo('pat_article', $where_read, null, array('_id'), null, array('_id'), true);
			$readsArt = getFieldArr($readsArtList, '_id', 0);
			foreach ($article as $k => $v){
				$article[$k]['is_likes'] = isset($likesArt) && in_array($v['_id'],$likesArt)?true:false;
				$article[$k]['is_reads'] = isset($readsArt) && in_array($v['_id'],$readsArt)?true:false;
				$article[$k]['is_vip'] = isset($article[$k]['price']) && $article[$k]['price']>0 ?true:false;
				$article[$k]['price'] = (string)$article[$k]['price'];
				if($article[$k]['is_vip']){
					$order = $this->Common_model->getInfo('pat_article_order',array('artId'=>$article[$k]['_id'],'userId'=>$pat_id,'status'=>1),array('_id'),true);
					$article[$k]['read_access'] = !empty($order)?true:false;
				}else{
					$article[$k]['read_access'] = true;
				}
				$tags = array();
				if(empty($v['tags'])){$article[$k]['tags'] = $tags;}
				for ($i=0; $i<count($v['tags']); $i++){
					$tags[$i] = getMdbId($v['tags'][$i]);
				}
				$article[$k]['tags'] =  $this->Common_model->getInfoAll('pat_article_tags',array("_id"=>array('$in'=>$tags)),null,array(),true);
			}
			$result['articles'] = $article;
		}
		display($result);
	}

	//消息推送  1:推送被顶级评论用户  2：子级评论推送主评论人，和当前被评论人
	public function sendPush($alertMessage,$extras,$alias)
	{
		$jPush_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/JPush/';
		require_once($jPush_dir."JPush.php");
		$app_key = $this->config->item('jg_app_key');
		$master_secret = $this->config->item('jg_master_secret');
		// 初始化
		$client = new JPush($app_key, $master_secret,$jPush_dir.'/jpush.log');
		$device = $client->device();
		$aliasUser = array();
		for ($i=0;$i<count($alias);$i++){
			$checkAlias = $device->getAliasDevices($alias[$i], $platform=null);
			if(!empty($checkAlias->data->registration_ids)){
				$aliasUser[] = $alias[$i];
			}
		}
		//针对 用户推送消息方法封装
		if(!empty($aliasUser)){
			$result = $client->push()
				->setPlatform('all')
				->addIosNotification($alertMessage, 'iOS sound', '+1', true, 'iOS category', $extras)
				->addAndroidNotification($alertMessage, null, 1, $extras)
				->setOptions($sendno=null, $time_to_live=null, $override_msg_id=null, $apns_production=true, $big_push_duration=null)
				->addAlias($aliasUser)
				->send();
		}
	}


	/**
	 * 收藏文章
	 * @params lookDoc
	 * @access public
	 * @return text/json
	 */
	public function collecting() {
		$pat_id = (string)$this->Common_model->checkLogin();
		emptyCheck($this->params,array('article_id','types'));
		if($this->params['types']=="1"){
			$result = $this->Common_model->getInfo('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array('title'),true);
			if(empty($result)){ display(false,2,"未找到该文章!");}
			$artInfo = $this->Common_model->getInfo('pat_article_collect',array('article_id'=>$this->params['article_id'],'user_id'=>$pat_id),array('_id'),true);
			if(empty($artInfo)){
				$stamptime = time();
				$insertInfo = array(
					"article_id" => (string)$this->params['article_id'],
					"user_id" => $pat_id,
					"title" => $result['title'],
					"created_at" => $stamptime,
					"updated_at" => $stamptime,
					"status" => 1
				);
				$insertId = $this->Common_model->insertInfo('pat_article_collect',$insertInfo);
				display(true,0,"收藏成功!");
			}else{
				display(false,3,"已经收藏!");
			}
		}elseif($this->params['types']=="2"){
			$this->Common_model->deleteOneRecord('pat_article_collect',array('article_id'=>$this->params['article_id'],'user_id'=>$pat_id));
			display(true,0,"已取消收藏!");
		}else{
			display(false,4,"类型不正确!");
		}
	}

	/**
	 *通过评论id获取评论列表
	 */
	public function getCommentByCommentId() {
		emptyCheck($this->params,array('comment_id'));
		$page = getCurPage($this->params); $perpage = 20;
		$offset = getPage($page,$perpage);
		$where = array('_id'=>getMdbId($this->params['comment_id']));
		$result = $this->Common_model->getInfo('pat_article_comment',$where,$offset,$perpage,array("_id"=>-1),array(),true);
		display($result);
	}
}

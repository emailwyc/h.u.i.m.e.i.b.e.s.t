<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * PatArticle.php
 */
class PatArticle extends CI_Controller {

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
        $like = '';
        if(!empty($_REQUEST['classes'])){ $where['classes'] =  (int)$_REQUEST['classes']; }
        if(!empty($_POST['key'])){ $like = addslashes($_POST['key']);}
        $where['status'] = array('$ne'=>3);
        $allcount = $this->Common_model->getInfoCount('pat_article',$where,array('title','description'),$like);// like 统计条数
        $allpage = ceil($allcount/$perpage);
        //文章列表
        $fields = array('_id','title','description','icon','link_url','classes','type','like_num','comment_num','created_at','updated_at','read_num','pubdate','status','author','push_message','order', 'price');

		$orderByArray = array('order'=>-1,'updated_at'=>-1);
		if ($_POST['orderBy'] == 'publish'){
			unset($orderByArray['updated_at']);
			$orderByArray['pubdate'] = -1;

		}else if($_POST['orderBy'] == 'update'){
			//do nothing;
		}else{
			//do nothing;
		}

		$result = $this->Common_model->searchKeysLikes('pat_article',$where,$fields,array('title','description'),$like,$offset,$perpage, $orderByArray);
        if($result){
            foreach($result as $k=>$v){
                $result[$k]['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
                $result[$k]['pubdate'] = date('Y-m-d H:i:s',$v['pubdate']);
                $result[$k]['updated_at'] = date('Y-m-d H:i:s', $v['updated_at']);
            }
        }
        display(array('page'=>$allpage,'data'=>$result));
    }


    //得到文章详情
	public function getDetailsM(){
		emptyCheck(array('_id'));$params = safeParams($_POST);
		$where = array('_id'=>getMdbId($params['_id']));
		$result = $this->Common_model->getInfo('pat_article',$where);
		if($result){
			$result['updated_at'] = date('Y-m-d H:i:s',$result['updated_at']);
			$result['created_at'] = date('Y-m-d H:i:s',$result['created_at']);
			$result['pubdate'] = date('Y-m-d H:i:s',$result['pubdate']);
			$tags = array();
			if(count($result['tags'])>0){
				for ($i=0; $i<count($result['tags']); $i++){
					$tags[$i] = getMdbId($result['tags'][$i]);
				}
			}
			$result['tags'] = empty($result['tags'])?array():$result['tags'] = $this->Common_model->getInfoAll('pat_article_tags',array("_id"=>array('$in'=>$tags)),null,array(),true);
		}
		display($result);
	}

	//得到文章详情
	public function getDetails(){
		emptyCheck(array('_id'));$params = safeParams($_POST);
		$where = array('_id'=>getMdbId($params['_id']));
		$result = $this->Common_model->getInfo('pat_article',$where);
		if($result){
			$result['updated_at'] = date('Y-m-d H:i:s',$result['updated_at']);
			$result['created_at'] = date('Y-m-d H:i:s',$result['created_at']);
			$result['pubdate'] = date('Y-m-d H:i:s',$result['pubdate']);
			$tags = array();
			if(count($result['tags'])>0){
				for ($i=0; $i<count($result['tags']); $i++){
					$tags[$i] = getMdbId($result['tags'][$i]);
				}
			}
			$result['tags'] = empty($result['tags'])?array():$result['tags'] = $this->Common_model->getInfoAll('pat_article_tags',array("_id"=>array('$in'=>$tags)),null,array(),true);
			if($result['price']>0){
				if(empty($params['userid'])) {
					unset($result['body']);	
				}else{
					$order = $this->Common_model->getInfo('pat_article_order',array('artId'=>$params['_id'],'userId'=>$params['userid'],'status'=>1));
					if(empty($order)){ unset($result['body']); }
				}
			}
			
		}
		display($result);
	}

	//添加文章
	public function addArticle(){
		emptyCheck(array('title','icon','classes','status','pubdate','push_message','type','order','price'));
		$params = safeParams($_POST);
		//添加tags 不存在即添加 返回文章添加标签的所有id
		/*$tags_id = array();
		if(isset($params['tags'])){
			$this->load->model('Patarticle_model');// 测试环境需要大写
			$tags_info = $this->Patarticle_model->addTags($params['tags']);
			$tags_id = getStr($tags_info,'_id');
		}*/
		$mongodate= time();
		$insertData = array(
			'title' => $params['title'],
			'description' => $params['description'],
			'icon' => addslashes($params['icon']),
			'link_url' => addslashes($params['link_url']),
			'body' =>(string)$params['body'],
			'classes' =>(int)($params['classes']),
			'type' =>(int)($params['type']),
			'like_num' =>0,
			'read_num' =>0,
			'comment_num' =>0,
			'likes' => array(),
			'reads' => array(),
			'pubdate' =>strtotime($params['pubdate']),
			'created_at' => $mongodate,
			'updated_at' => $mongodate,
			'status'=>(int)$params['status'],
			'author'=>(string)$params['author'],
			'push_message'=>$params['push_message'],
			'doctor'=>(string)$params['doctor'],
			'tags'=>(array)$params['tags'],
			'price'=>(float)number_format($params['price'],2, '.', ''),
			'free_content'=>(string)$params['free_content'],
			'order'=>(int)$params['order']
		);
		$insertId =$this->Common_model->insertInfo('pat_article',$insertData);
		if($insertId){
			//配置文件中设置开关 是否开启推送
			if($this->config->item('open_push') && $params['push_message'] == 'true'){
				$this->doPush($params['title'],$insertId,'article');
			}
			display(array('insertId'=>$insertId));
		}else{
			display(array(),3,'插入数据失败');
		}
	}

	//编辑文章
	public function editArticle(){
		emptyCheck(array('_id','title','icon','classes','status','pubdate','order'));
		$params = safeParams($_POST);
		/*$tags_id = array();
		if(isset($params['tags'])){
			$this->load->model('Patarticle_model');
			$tags_info = $this->Patarticle_model->addTags($params['tags']);
			$tags_id = getStr($tags_info,'_id');
		}*/
		$mongodate= time();
		$insertData = array(
			'title' => $params['title'],
			'description' => $params['description'],
			'icon' => addslashes($params['icon']),
			'link_url' => addslashes($params['link_url']),
			'body' =>(string)$params['body'],
			'classes' =>(int)($params['classes']),
			'type' =>(int)($params['type']),
			'updated_at' => $mongodate,
			'status'=>(int)$params['status'],
			'author'=>(string)$params['author'],
			'like_num' =>(int)($params['like_num']),
			'read_num' =>(int)($params['read_num']),
			'pubdate' =>strtotime($params['pubdate']),
			'push_message'=>$params['push_message'],
			'doctor'=>(string)$params['doctor'],
			'tags'=>(array)$params['tags'],
			'price'=>(float)number_format($params['price'],2, '.', ''),
			'free_content'=>(string)$params['free_content'],
			'order'=>(int)$params['order']
		);
		$check = $this->Common_model->updateRecord('pat_article',array('_id'=>getMdbId($params['_id'])),$insertData);
		if($check){
			//配置文件中设置开关 是否开启推送
			if($this->config->item('open_push') && $params['push_message'] == 'true'){
				$this->doPush(addslashes($params['title']),$params['_id'],'article');
			}
			display(array()); }else{ display(array(),3,"更新数据失败"); }
	}

	//删除文章
	public function delArticle(){
		emptyCheck(array('_id'));
		$insertId = $this->Common_model->updateRecord('pat_article',array('_id'=>getMdbId($_POST['_id'])),array('status'=>3));
		if($insertId){
			display(array(),0,"删除成功!");
		}else{
			display(array(),3,"删除失败!");
		}
	}

	//得到评论列表
	public function getCommentList(){
		emptyCheck(array('article_id'));
		$page = getCurPage($this->params); $perpage = 20;
		$offset = getPage($page,$perpage);
		$where = array('status'=>1,'article_id'=>$_REQUEST['article_id']);
		$allcount = $this->Common_model->getInfoCount('pat_article_comment',$where);
		$allpage = ceil($allcount/$perpage);
		$result = $this->Common_model->getListInfo('pat_article_comment',$where,$offset,$perpage,array("_id"=>-1),array());
		$patIds = getFieldArr($result,'uid',1);
		$patInfos = $this->Common_model->getInfoAll('pat_user',array('_id'=>array('$in'=>$patIds)),'',array('nickname','avatar'),true);
		$patInfos= ArrKeyFromId($patInfos);
        foreach($result as $k=>$v){
            $result[$k]['patient'] = !empty($patInfos[$v['uid']])?$patInfos[$v['uid']]:(object)array();
            $result[$k]['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
		}
		display(array('page'=>$allpage,'data'=>$result));


	}

	//删除评论
	public function delComment(){
		emptyCheck(array('_id'));
		$insertId = $this->Common_model->deleteOneRecord('pat_article_comment',array('_id'=>getMdbId($_POST['_id'])));
		if($insertId){
			display(array(),0,"删除评论成功!");
		}else{
			display(array(),3,"删除评论失败!");
		}
	}

	//消息推送
	public function doPush($alertMessage,$insertId,$type)
	{
		$push = $this->jPush($alertMessage,(string)$insertId,null,null,array('_id'=>(string)$insertId,'type'=>$type));
		if(!$push){display(array(),403,'推送失败');}
	}

	/**
	 * 极光推送
	 * @param $alertMessage		提示消息
	 * @param $content			消息内容
	 * @param $msg_title		推送标题
	 * @param $type				消息类型
	 * @param array $extras		附加消息
	 * @return array|object
	 */
	private function jPush($alertMessage,$content,$msg_title,$type,$extras=array())
	{
		require_once("src/JPush/JPush.php");
		$app_key = $this->config->item('jg_app_key');
		$master_secret = $this->config->item('jg_master_secret');
		// 初始化
		$client = new JPush($app_key, $master_secret,'./src/JPush/jpush.log');
		// 简单推送示例
		$result = $client->push()
			->setPlatform('all')   //推送平台ios android
			->setAudience('all')	//推送用户
			->addIosNotification($alertMessage, 'iOS sound', '+1', true, 'iOS category', $extras)
			->addAndroidNotification($alertMessage, null, 1, $extras)
			->send();
		return $result;
	}
}

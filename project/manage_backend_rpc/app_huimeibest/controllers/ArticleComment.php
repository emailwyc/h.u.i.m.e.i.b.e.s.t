<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 6/12/16
 * Time: 1:51 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User.php
 */
class ArticleComment extends CI_Controller {

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

    //后台添加评论
    public function add()
    {
        emptyCheck(array('article_id','content','uid','nickname','avatar','role','content','comment_type'));
        $params = safeParams($_POST);
        $artInfo = $this->Common_model->getInfo('pat_article',array('_id'=>getMdbId($params['article_id'])),array('_id'));
        if(empty($artInfo)){ display(array(),3,"未找到文章，评论失败!"); }
        $stamptime = time();
        if($params['comment_type']=="1"){
            $insertInfo = array(
                "article_id" => (string)$artInfo['_id'],
                "uid" => $params['uid'],
                "name" => $params['nickname'],
                "avatar" => $params['avatar'],
                "role" => $params['role'],
                "content" => $params['content'],
                "body" => array(),
                "created_at" => $stamptime,
                "updated_at" => $stamptime,
                "status" => 1
            );
            $insertId = $this->Common_model->insertInfo('pat_article_comment',$insertInfo);
            if($insertId){
                $this->Common_model->updateSetRecord('pat_article',array('_id'=>$artInfo['_id']),array('$inc'=>array('comment_num'=>1)));
                $insertInfo['_id'] = (string)$insertId;
                display(array(),0,"评论成功!");
            }else{
                display(array(),3,"评论失败!");
            }
        }elseif($params['comment_type']=="2"){
            emptyCheck(array('comment_id','pid','p_role','p_name'));
            $commentInfo = $this->Common_model->getInfo('pat_article_comment',array('_id'=>getMdbId($params['comment_id'])),array(),true);
            if(count($commentInfo['body'])>=50){ display(array(),3,"回复数量已超过上限!"); }
            if(empty($commentInfo)){ display(array(),3,"未找到父级评论!"); }
            if(!in_array($params['p_role'],array('pat_user','doctor'))){ display(array(),3,"角色有误!");}
            $date = array(
                "_id" => (string)new MongoId(),
                "uid" => $params['uid'],
                "u_name" => $params['nickname'],
                "u_role" => $params['u_role'],
                "pid" => $params['pid'],
                "p_name" => $params['p_name'],
                "p_role" => $params['p_role'],
                "content" => $params['content'],
                "created_at" => $stamptime,
            );
            $insertId = $this->Common_model->updateSetRecord('pat_article_comment',array('_id'=>getMdbId($params['comment_id'])),array('$addToSet'=>array('body'=>$date)));
            if($insertId['nModified']){
                $this->Common_model->updateSetRecord('pat_article',array('_id'=>$artInfo['_id']),array('$inc'=>array('comment_num'=>1)));
                display($date,0,"评论成功!");
            }else{ display(array(),3,"评论失败!"); }

        }else{
            display(array(),3,"评论类型错误!");
        }
    }

    //后台评论列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $where = array('status'=>1);
        $allcount = $this->Common_model->getInfoCount('pat_article_comment',$where);
        $allpage = ceil($allcount/$perpage);
        $ArticleComments = $this->Common_model->getListInfo('pat_article_comment',$where,$offset,$perpage,array('_id'=>-1));
        foreach ($ArticleComments as $key => $val){
             $pat_user = $this->Common_model->getInfo('pat_user',array('_id'=>getMdbId($val['uid'])),array
             ('nickname','avatar'));
            if(!empty($pat_user)){
                $ArticleComments[$key]['nickname']  = $pat_user['nickname'];
                $ArticleComments[$key]['avatar']  = $pat_user['avatar'];
            }
            $ArticleComments[$key]['comment_num'] = count($ArticleComments[$key]['body']);
        }
        display(array('page'=>$allpage,'data'=>$ArticleComments));
    }

    //删除评论
    public function del()
    {
        if(empty($_REQUEST)){display(array(),403,'选择目标数据');}
        @$id = getMdbId(addslashes(trim($_REQUEST['id'])));
        @$body_id = addslashes(trim($_REQUEST['body_id']));
        if(empty($body_id)){
            $comment = $this->Common_model->getInfo('pat_article_comment',array('_id'=>$id));
            if(empty($comment)){ display(array(),3,"数据不存在!"); }
            $this->Common_model->updateRecord('pat_article_comment',array('_id'=>$id),array('status'=>0));
            if($comment['status']==1){ $this->Common_model->updateSetRecord('pat_article',array('_id'=>getMdbId($comment['article_id'])),array('$inc'=>array('comment_num'=>-1))); }
        }else{
            $comment = $this->Common_model->getInfo('pat_article_comment',array('_id'=>$id,'body._id'=>$body_id));
            if(empty($comment)){ display(array(),3,"该记录不存在或已删除!"); }
            $this->Common_model->updateSetRecord('pat_article_comment',array('_id'=>$id),array('$pull'=>array('body'=>array('_id'=>$body_id))));
        }
        display(array());
    }

    //信息修改
    public function update()
    {
        if(empty($_POST['id'])){display(array(),403,'选择目标数据');}
        $id = getMdbId(addslashes(trim($_POST['id'])));
        emptyCheck(array('content'));
        $updateDate['content'] = addslashes(trim($_POST['content']));
        if(!empty($_POST['status'])){
            $updateDate['status'] = addslashes(trim($_POST['status']));
        }
        $updateRe = $this->Common_model->updateRecord('pat_article_comment',array('_id'=>$id),$updateDate);
        if($updateRe){
            display(array());
        }else{
            display(array(),1,'修改失败');
        }
    }

    //单个评论详情
    public function one()
    {
        if(empty($_REQUEST['id'])){display(array(),403,'选择目标数据');}
        $id = getMdbId(addslashes(trim($_REQUEST['id'])));
        $comment = $this->Common_model->getInfo('pat_article_comment',array("_id"=>$id));
        $comment['comment_num'] = count($comment['body']);
        if($comment){
            display(array($comment));
        }else{
            display(array(),403,'数据不存在');
        }
    }

}

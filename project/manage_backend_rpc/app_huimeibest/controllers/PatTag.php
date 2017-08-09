<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 7/7/16
 * Time: 10:09 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * PatArticle.php
 */
class PatTag extends CI_Controller {

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

    //获取一级标签分类
    public function getFirstTagSort()
    {
        $result = $this->Common_model->getInfoAll('pat_article_tags',array('status'=>1));
        display($result);
    }
    //标签模糊搜索
    public function likeSearch()//导入数据库时需要在文章标签中关联分类
    {
        emptyCheck(array('name','type'));
        $params = safeParams($_POST);
        $where = array('type'=>(int)$params['type'],'status'=>1);
        $result = $this->Common_model->searchLikes('pat_article_tags',array(),'name',$params['name'],$where);
        display($result);
    }

    //添加标签
    public function add()
    {
        //post提交 标签名字
        //数据结构 name，type（1为疾病标签，2为症状标签）,status正常使用状态为1
        emptyCheck(array('name','type'));
        $params = safeParams($_POST);
        $insertData = array(
            'name' => $params['name'],
            'type' => (int)$params['type'],
            'status' => empty($params['status'])?1:(int)$params['status']
        );
        $where = array('name'=>$insertData['name'],'type'=>$insertData['type']);
        $patTag = $this->Common_model->getInfo('pat_article_tags',$where);
        if($patTag){display(array(),3,'插入失败数据已存在！');}
        $powerId = $this->Common_model->insertInfo('pat_article_tags',$insertData);
        if(!$powerId){display(array(),3,'插入失败');}
        display(array());
    }

    //标签列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $allcount = $this->Common_model->getInfoCount('pat_article_tags',array());
        $allpage = ceil($allcount/$perpage);
        $where = array('status'=>1);
        $params = safeParams($_POST);
        if(isset($params['type'])){
            $where['type'] = (int)$params['type'];
        }
        if(isset($params['name'])){
            $where['name'] = $params['name'];
        }
        $result = $this->Common_model->getListInfo('pat_article_tags',$where,$offset,$perpage);
        if(empty($result)){display(array(),403,'暂无数据');}
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除标签
    public function del()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $delRe = $this->Common_model->deleteOneRecord('pat_article_tags',array("_id"=>getMdbId($params['id'])));
        if($delRe){
            display(array());
        }else{
            display(array(),3,'操作失败');
        }
    }

    //修改标签数据
    public function update()
    {
        emptyCheck(array('id','name','type'));
        $params = safeParams($_POST);
        $id = getMdbId($params['id']);
        $updateData = array(
            'name' => $params['name'],
            'type' => (int)$params['type'],
            'status' => empty($params['status'])?1:(int)$params['status']
        );
        $updateRe = $this->Common_model->updateRecord('pat_article_tags',array('_id'=>$id),$updateData);
        if($updateRe){display(array());}
        else{display(array(),1,'修改失败');}
    }

    //获取单个标签
    public function one()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $result = $this->Common_model->getInfo('pat_article_tags',array('_id'=>getMdbId($params['id'])));
        display($result);
    }
}
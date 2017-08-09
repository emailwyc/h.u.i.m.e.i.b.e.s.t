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
class User extends CI_Controller {

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

    //后台添加用户
    public function add()
    {
        emptyCheck(array('name','pwd','mobile'));
        $date['name'] = addslashes(trim($_POST['name']));//name 创建唯一索引
        $date['mobile'] = addslashes(trim($_POST['mobile']));
        $check = $this->Common_model->getInfo('user',array("mobile"=>$date['mobile'],'from'=>array('$exists'=>0)));
        if($check){ display(array(),3,"该手机号已经存在"); }
        $mongodate= new MongoDate(time());
        $salt = uniqid();
        $date['pwd'] = sha1(md5(substr($date['mobile'],5)).$salt);
        $date['created_at'] = $mongodate;
        $date['updated_at'] = $mongodate;
        $date['actived_at'] = $mongodate;
        $doAdd = $this->Common_model->insertInfo('user',$date);
        if($doAdd){
            display(array());
        }else{
            display(array(),0,'添加失败');
        }
    }

    //微信关注用戶列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $where = array();
        $allcount = $this->Common_model->getInfoCount('user_weixin',$where);
        $allpage = ceil($allcount/$perpage);
        @$st = (int)$_REQUEST['st'];
        $where = empty($st)?array() : array('st'=>$st);
        $files = array('gender','img','mobile','nickname','regtime','st');
        $result = $this->Common_model->getListInfo('user_weixin',$where,$offset,$perpage,array('_id'=>-1),$files);
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除用户
    public function del()
    {
        if(empty($_GET['id'])){display(array(),403,'参数不正确');}
        $id = getMdbId(addslashes(trim($_GET['id'])));
        $delRe = $this->Common_model->deleteOneRecord('user_weixin',array("_id"=>$id));
        if($delRe){
            display(array());
        }else{
            display(array(),1,'操作失败');
        }
    }

    //信息修改
    public function update()
    {
        if(empty($_POST['id'])){display(array(),403,'参数不正确');}
        $id = getMdbId(addslashes(trim($_POST['id'])));
        emptyCheck(array('nickname','mobile'));
        @$updateDate['nickname'] = addslashes(trim($_POST['nickname']));
        @$updateDate['mobile'] = addslashes(trim($_POST['mobile']));
        $updateRe = $this->Common_model->updateRecord('user_weixin',array('_id'=>$id),$updateDate);
        if($updateRe){
            display(array());
        }else{
            display(array(),1,'修改失败');
        }
    }

    //单个用户信息
    public function one()
    {
        if(empty($_GET['id'])){display(array(),403,'参数不正确');}
        $id = getMdbId(addslashes(trim($_GET['id'])));
        $files = array('gender','img','mobile','nickname','regtime','st');
        $user = $this->Common_model->getInfo('user_weixin',array("_id"=>$id),$files);
        if($user){
            display(array($user));
        }else{
            display(array(),1,'用户不存在');
        }
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 7/1/16
 * Time: 2:31 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * PatArticle.php
 */
class FescoUser extends CI_Controller {

    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		if(empty($_SESSION['fesco'])){
			display(array(),403,'授权失败，请登陆');
		}
    }

    //后台添加用户
    public function add()
    {
        emptyCheck(array('name','mobile','ticket_overplus'));
        $params = safeParams($_POST);
        $check = $this->Common_model->getInfo('fesco_user',array("mobile"=>$params['mobile']));
        if($check){ display(array(),3,"该手机号已经存在"); }
        $mongodate= time();
        $date = array(
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'ticket_total' => (int)$params['ticket_overplus'],
            'ticket_overplus' => (int)$params['ticket_overplus'],
            'created_at' => $mongodate,
            'status' => 1,
        );
        $doAdd = $this->Common_model->insertInfo('fesco_user',$date);
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
        $allcount = $this->Common_model->getInfoCount('fesco_user',$where);
        $allpage = ceil($allcount/$perpage);
        $files = array('name','mobile','ticket_total','ticket_overplus','created_at','status');
        $result = $this->Common_model->getListInfo('fesco_user',$where,$offset,$perpage,array('_id'=>-1),$files);
        foreach ($result as $key=>$val){
            $result[$key]['ticket_consume'] = $val['ticket_total']-$val['ticket_overplus'];
        }
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除用户
    public function del()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $id = getMdbId(addslashes(trim($params['id'])));
        $delRe = $this->Common_model->updateRecord('fesco_user',array('_id'=>$id),array("status"=>0));
        if($delRe){
            display(array());
        }else{
            display(array(),1,'操作失败');
        }
    }

    //信息修改
    public function update()
    {
        emptyCheck(array('name','mobile','ticket_overplus','id','status'));
        $params = safeParams($_POST);
        $id = getMdbId($params['id']);
        $userInfo = $this->Common_model->getInfo('fesco_user',array('_id'=>$id));
        $updateDate = array(
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'ticket_total' => (int)$userInfo['ticket_total']+($params['ticket_overplus']-$userInfo['ticket_overplus']),
            'ticket_overplus' => (int)$params['ticket_overplus'],
            'status' => (int)$params['status']
        );
        $updateRe = $this->Common_model->updateRecord('fesco_user',array('_id'=>$id),$updateDate);
        if($updateRe){
            display(array());
        }else{
            display(array(),1,'修改失败');
        }
    }

    //单个用户信息
    public function one()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $id = getMdbId($params['id']);
        $user = $this->Common_model->getInfo('fesco_user',array("_id"=>$id));
        if(empty($user)){
            display(array(),1,'用户不存在');
        }display($user);
	}

    public function orderList()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $where = array('from'=>'fesco');
        $allcount = $this->Common_model->getInfoCount('order',$where);
        $allpage = ceil($allcount/$perpage);
        $files = array();
		$result = $this->Common_model->getListInfo('order',$where,$offset,$perpage,array('_id'=>-1),$files);
		foreach($result as $k=>$v){
			$result[$k]['doctor'] = $this->Common_model->getInfo('doctor',array('_id'=>$v['doctor']['$id']),array('name','hospital','assistant'));
		}
        display(array('page'=>$allpage,'data'=>$result));
    }
}

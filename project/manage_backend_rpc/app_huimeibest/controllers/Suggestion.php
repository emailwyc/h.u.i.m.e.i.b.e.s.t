<?php

/**
 * Created by PhpStorm.
 * User: wangwei
 * Date: 16/8/16
 * Time: 下午2:34
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Suggestion extends CI_Controller{


    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
    public function __construct(){
        parent::__construct();
    //    checkLogin1();checkUserPower();
    }

    public function getList(){

        //计算分页
        $perpage = 20;
        $page = getCurPage();
        
        $offset = getPage($page, $perpage);
        $where = array();

        $allcount = $this->Common_model->getInfoCount('suggestions', $where);
        $allpage = ceil($allcount/$perpage);

        //意见反馈列表
        //$sort = array("state" => 1, "last_update_time" => 1);
        $sort = array("state" => 1);
        $result = $this->Common_model->getListInfo('suggestions', $where, $offset, $perpage, $sort, array(), true);

        foreach ($result as &$item) {
            $item['submit_at'] = date('Y-m-d H:i:s', $item['submit_at']);
            $item['last_update_time'] = date('Y-m-d H:i:s', $item['last_update_time']);
        }
        
        display(array('page'=>$allpage,'data'=>$result));
        
    }

    public function saveNote(){
        $this->updateSuggestion(0);
   }

    public function submitNote(){
        $this->updateSuggestion(1);
    }
    
    
    private function updateSuggestion($state){
        $sId = $_POST['_id'];
        $note = $_POST['note'];
        if (empty($sId)){
            display(array(), 2, '反馈Id不能为空');
        }

        $lastUpdateTime = time();

        $update = array('note' => $note, 'last_update_time' => $lastUpdateTime);
        $update['state'] = $state;
        
        $res = $this->Common_model->updateRecord('suggestions', array('_id'=>getMdbId1($sId)), $update);
        if ($res) {

            $update['_id'] = $sId;
            
            display($update, 0, '更新成功');
        }else{
            display(array(), 1, '更新失败');
        } 
    }
    
}

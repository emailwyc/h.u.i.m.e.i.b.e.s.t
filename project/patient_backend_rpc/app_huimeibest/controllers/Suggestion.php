<?php
/**
 * Created by PhpStorm.
 * User: wangwei
 * Date: 16/8/18
 * Time: 上午10:02
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Suggestion extends CI_Controller {

    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
    public function __construct(){
        parent::__construct();

        //校验session， /
        $this->userid = (string)$this->Common_model->checkLogin();
    }


    public function submit(){

        //Check params
        emptyCheck($this->params, array("content"));

        if(mb_strlen($this->params["content"]) > 200){
            display(array(), 1, '意见反馈内容不能超过200字');
        };

        if(mb_strlen($this->params["contactInfo"]) > 100){
            display(array(), 2, '联系方式不能超过50字');
        }

        //Save the suggestion
        $content = $this->params["content"];
        $contactInfo = (string)$this->params["contactInfo"];
        $timestamp = time();
        $uid = $this->userid;
        
        $userInfo = $this->Common_model->getInfo('pat_user', array('_id'=>getMdbId($uid)), array('nickname', 'platform'), true);
        if(empty($userInfo)){
            display(array(), 3, "没有此用户!"); 
        }

        if(!$this->checkFrequency($uid)){
            display(array(), 4, "提交反馈过于频繁");
        }

        $suggestion = array(
            "uid" => $uid,
            "nickname" => $userInfo["nickname"],
            "platform" => $userInfo["platform"],
            "content" => $content,
            "contactInfo" => $contactInfo,
            "submit_at" => $timestamp,
            "state" => "0", //未处理
            "note" => "",
            "last_update_time" => $timestamp
        );

        $insertId = $this->Common_model->insertInfo('suggestions', $suggestion);
        if($insertId){
            $suggestion['_id'] = (string)$insertId;
            display($suggestion, 0, "提交成功!");
        }else{
            display(array(), -1, "提交失败!");
        }
    }

    
    private function checkFrequency($uid)
    {
        $twentyHoursBefore = time() - 3600 * 24;
        $where = array( 
            "submit_at" => array('$gte' => $twentyHoursBefore),
            'uid' => $uid
        );

        $todaySubmitCount = $this->Common_model->getInfoCount('suggestions', $where);
        
        if($todaySubmitCount >= 10){
            return false;
        }

        return true;
    }
}
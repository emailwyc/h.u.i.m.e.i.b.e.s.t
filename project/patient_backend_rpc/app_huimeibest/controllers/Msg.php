<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$msg_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/submail/';
require_once($msg_file.'SUBMAILAutoload.php');
/**
 * Msg.php
 */
class Msg extends CI_Controller {
    public $message_configs;

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
    public function __construct(){
        parent::__construct();
        $this->load->model('msg_model');
        $this->load->model('user_model');
    }

    /** 
     * 发送短信
     * 
     * @param  null
     * @access public
     * @return void
     */
    public function regSend() {
		$patId = $this->Common_model->getPatId();
		if($patId){ display(array(),1,'您已登陆，无需验证！');	}
		emptyCheck($this->params,array('mobile'));
        if(!preg_match(config_item('global_mobile_format'), $this->params['mobile'])){ display(array(),2,'手机号码格式错误，请修正！');}
        //检查用户是否满足发送验证码条件
        $info = $this->msg_model->getMobileInfo(array('mobile'=>$this->params['mobile'],'st'=>1));
        $code = (string)getRandomPass(4,'NUMBER');
        $cur_time = (string)time();
        log_message('USERS', $code);
		if(empty($info)){
            $xsend=$this->sendMsg($this->params['mobile'],$code);
            if(isset($xsend['status']) && $xsend['status']=="success"){
                $info = array('mobile'=>$this->params['mobile'],'code'=>$code,'num'=>1,'ct'=>$cur_time,'ut'=>$cur_time,'tag'=>$cur_time,'st'=>1);
                $checkInsert = $this->msg_model->insertMobileMsg($info);
				if($checkInsert){
					display(true,0,'发送成功');
                }else{
					display(false,4,'发送失败,请重新发送!');
                }
            }else{
				display(false,4,'发送失败,请重新发送!');
            }
        }else{
            $diff_time1 = 7200;
            $diff_time2 = 110;
            $num_limit = 5;
            $diff_sj = $cur_time-$info['tag'];
			$diff_sj1 = $cur_time-$info['ut'];
			$timeDiff = $diff_time2-$diff_sj1+1;
			$err3Str = "操作太快，客观请".$timeDiff."秒后再试";
            if($diff_sj<$diff_time1 && $info['num']>$num_limit){ display(array(),3,'操作频繁，请稍后再试！'); }
            if($diff_sj1<$diff_time2){ display(array(),3,$err3Str); }
			$xsend=$this->sendMsg($this->params['mobile'],$code);
            if(isset($xsend['status']) && $xsend['status']=="success"){
            //插入数据库
            $update = $diff_sj>$diff_time1 ? array('code'=>$code,'num'=>0,'ut'=>$cur_time,'tag'=>$cur_time):array('code'=>$code,'num'=>$info['num']+1,'ut'=>$cur_time);
                $checkUpdate = $this->msg_model->updateMobileMsg($info['_id'],$update);
                if($checkUpdate){
					display(true,0,'发送成功');
                }else{
					display(false,4,'发送失败,请重新发送!');
                }
            }else{
				display(false,4,'发送失败,请重新发送!');
            }

        }
    }

    /** 
     * 发送短信
     * 
     * @param  null
     * @access public
     * @return void
     */
    private function sendMsg($mobile,$code) {
        $submail=new MESSAGEXsend(config_item('msg_config'));
        $submail->setTo($mobile);
        $submail->SetProject('ztcfN');
        $submail->AddVar('content',$code);
        $xsend=$submail->xsend();
        return $xsend;
	}

    /** 
     * 系统消息列表
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function sysMsgList() {
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('pubdate'=>array('$lte'=>time()));
		$sort = array("_id"=>-1);
		$fields = array();
		$result = $this->Common_model->getListInfo('pat_system_msg',$where,$offset,$perpage,$sort,$fields,true);
		//是否点赞
		display($result,0,'ok','list');
	}

    //消息提示
    public function msgPrompt()
    {
        $result = array('existence_new_sys_msg'=>false,'existence_new_comment'=>false);
        $where = !empty($this->params['system_id'])?array('_id'=>array('$gt'=>getMdbId($this->params['system_id']))):array();
		$where['isPush'] =1;
		$msgInfo = $this->Common_model->getInfo('pat_system_msg',$where);
        $result['existence_new_sys_msg'] = empty($msgInfo)?false:true;
        if(!empty($this->params['comment_id'])){
            $result['existence_new_comment'] = $this->existNewComment($this->params['comment_id']);
		}else{
			$pat_id = (string)$this->Common_model->getPatId();
			if(empty($pat_id)){return false;}
				$newCommentInfo = $this->Common_model->getInfo('pat_article_comment',array('status'=>1,'body.pid'=>$pat_id));
			$result['existence_new_comment'] = empty($newCommentInfo)?false:true;

		}
        display($result);
    }

    private function existNewComment($comment)
    {
        $pat_id = (string)$this->Common_model->getPatId();
        if(empty($pat_id)){return false;}
        $newCommentInfo = $this->Common_model->getInfo('pat_article_comment',array('status'=>1,'body._id'=>array('$gt'=>$comment),'body.uid'=>$pat_id));
		log_message('users',$newCommentInfo);
        return  empty($newCommentInfo)?false:true;

    }

}

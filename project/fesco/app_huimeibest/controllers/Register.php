<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Doctor.php
 */
class Register extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('msg_model');
		$this->load->model('weixin_model');
		$this->load->model('Chat_model');
	}

    /** 
     * 注册页面 
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function index() {
		$postInfo = $this->input->post();
		$data['msg'] = "";
		$data['mobile'] = "";
		$data['st'] = 0;
		if(isset($postInfo['btn1'])){
			if(empty($postInfo['sjh']) || empty($postInfo['yzm'])){
				$data['msg'] = "提交参数有误，请检查！";
			}else{
				$data['mobile'] = trim($postInfo['sjh']);
				//验证手机号码格式
				if(!preg_match(config_item('global_mobile_format'), $postInfo['sjh'])){
					$data['msg'] = "手机号码格式错误，请检查！";
				}
			}

			if(empty($data['msg'])){
				//检测验证码是否正确
				$msgInfo = $this->msg_model->getMobileInfo(array('mobile'=>(string)$postInfo['sjh']));
				if(empty($msgInfo)){
					$data['msg'] = "验证码错误，重新发送验证码试试看吧！";
				}else{
					$code = trim((string)$postInfo['yzm']);
					$diff_time = time()-$msgInfo['ut'];
					if($msgInfo['code'] != $code || $diff_time>300){
						$data['msg'] = "验证码错误或过期，请修正！";
					}else{
						//验证手机号是否注册
						$result = $this->Common_model->getInfo('fesco_user',array('mobile'=>$postInfo['sjh'],'status'=>1));
						//插入数据
						$cur_date = new MongoDate(time());
						if(!empty($result)){
							$checkui= $this->user_model->getInfo('user',array('mobile'=>(string)($postInfo['sjh']),'from'=>"fesco"));
							$userinfo= $this->user_model->getInfo('patient',array('fid'=>(string)$result['_id'],'from'=>'fesco'));
							$info = array('mobile'=>(string)trim($postInfo['sjh']),'password'=>"","salt"=>"","actived_at"=>$cur_date,"created_at"=>$cur_date,"updated_at"=>$cur_date,'from'=>"fesco");
							if(empty($checkui['_id'])){
								$userid = $this->user_model->insertUserInfo($info);
							}else{ $userid = $checkui['_id']; }
							if($userid){
								if(empty($userinfo)){
									$sex = "male";
									$trainRef = MongoDBRef::create("user", $userid);
									$info = array("_uref"=>$trainRef,"created_at"=>$cur_date,"updated_at"=>$cur_date,"name"=>$result['name'],'mobile'=>(string)trim($postInfo['sjh']),"avatar"=>"",'age'=>0,"gender"=>$sex,"from"=>"fesco","fid"=>(string)$result['_id']);
									$pId = (string)$this->user_model->insertInfo("patient",$info);
								}else{
									$pId=(string)$userinfo["_id"];
								}
								if($pId){
									$_SESSION['from']="fesco";
									$_SESSION['aid']=(string)$result['_id'];
									$_SESSION['pid']=$pId;
									$_SESSION['st']=1;
								}else{
									show_error("注册失败！请重新注册！");
								}
								//$wang = $this->Chat_model->getAccessToken();
								//$data = array('username'=>(string)$pId, 'password'=>md5($pId."hmjz"),'nickname'=>$result['nickname']);
								//$regcheck= $this->Chat_model->regUser($wang,$data);
							}else{
								show_error("注册失败！请重新注册！");
							}
							redirect($postInfo['from11']);
						}else{
							show_error("该手机号未授权！");
						}
					}
				}
			}
		}
		if(!isset($btn['from11'])){
			if($_GET['redirect']){ $data['from']= urldecode($_GET['redirect']); }else{ $data['from'] = "/"; }
		}else{
			$data['from'] = $postInfo['from11'];
		}
		if(checkAuth2()){ redirect($data['from']); }
		if($aid = getPatientOpenid()){
			if(checkAuth1()){
				$_SESSION['st']=1;
				redirect($data['from']);
			}else{
				$info = $this->Common_model->getInfo('patient',array('fid'=>$aid));
				if($info['mobile']){
					$_SESSION['pid']=(string)$info['_id'];
					$_SESSION['st']=1;
					echo "<script>history.go(-1);</script>";exit;
				}
			}
		}
		$this->load->view('register/index',$data);
	}


}

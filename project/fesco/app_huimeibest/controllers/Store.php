<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$msg_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/file/';
require_once $msg_file.'MKExcel.php';
/**
 * 用户聊天
 */

class Store extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('Jssdk_model');
		$this->load->helper('weixin_helper');
	}

	private function CreateDoctorCode() {
		$token = $this->Jssdk_model->getAccessToken();
		$cur_time=time();
		for($i=1;$i<=2000;$i++){
			$CodeID = $i;
			$codeInfo = $this->Common_model->getInfo('doctor_rqcode',array('scene_id'=>$CodeID));
			if(empty($codeInfo)){
				$result = create_qrcode('QR_LIMIT_SCENE',$CodeID,$token);
				if($result){
					$info = array('scene_id'=>$CodeID,'ticket'=>(string)$result['ticket'],'url'=>(string)$result['url'],'timestamp'=>$cur_time);
					$this->Common_model->insertInfo('doctor_rqcode',$info);
				}
			}
		}
		echo "success";
	}

	public function CreateOtherCode() {
		$token = $this->Jssdk_model->getAccessToken();
		$cur_time=time();
		$xls = new ExportExcel("永久二维码.xls", "UTF-8");
		$title = array('编号','二维码地址','生成日期','用途');
		$xls->addArray($title);
		for($i=90087;$i<=90087;$i++){
			$CodeID = $i;
			$codeInfo = $this->Common_model->getInfo('other_rqcode',array('scene_id'=>$CodeID));
			if(empty($codeInfo)){
				$result = create_qrcode('QR_LIMIT_SCENE',$CodeID,$token);
				if($result){
					$info = array('scene_id'=>$CodeID,'ticket'=>(string)$result['ticket'],'url'=>(string)$result['url'],'timestamp'=>$cur_time,"act"=>"","remark"=>"");
					$this->Common_model->insertInfo('other_rqcode',$info);
					$tempUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$info['ticket']."";
					$hang = array($info['scene_id'],$tempUrl,date('Y-m-d H:i:s',$info['timestamp']),"");
					$xls->addArray($hang);
				}
			}else{
				$tempUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$codeInfo['ticket']."";
				$hang = array($codeInfo['scene_id'],$tempUrl,date('Y-m-d H:i:s',$codeInfo['timestamp']),"");
				$xls->addArray($hang);
			}
		}
		echo "success";
	}

	//生成活动码
	private function CreateActCode() {
		set_time_limit(0);
		$cur_time = time();
		$start_time = strtotime('2016-03-07 00:00:00');
		$end_time = strtotime('2016-03-16 00:00:00');
		$i = 1;
		$xls = new ExportExcel("义诊活动兑换码.xls", "UTF-8");
		$title = array('编号','活动名称','兑换码','开始日期','结束日期','生成时间','使用状态');
		while(true){
			$code = getRandomPass(4,'CAPITAL');
			$codeInfo = $this->Common_model->getInfo('act_code',array('aid'=>$i,'code'=>$code));
			if(!empty($codeInfo)){
				continue; 
			}else{
				$info = array('aid'=>$i,'code'=>(string)$code,'act'=>"义诊活动",'st'=>1,'start_time'=>$start_time,'end_time'=>$end_time,'add_time'=>$cur_time);
				$this->Common_model->insertInfo('act_code',$info);
				$isuse = $info['st']==1?"未使用":"已使用";
				$hang = array($info['aid'],$info['act'],$info['code'],date('Y-m-d',$info['start_time']),date('Y-m-d',$info['end_time']),date('Y-m-d',$info['add_time']),$isuse);
				$xls->addArray($hang);
			}
			if($i>=20){break;}
			$i++;
		}
		echo "success";
	}

	//生成兑换码
	private function CreateCodeCoup() {
		set_time_limit(0);
		$cur_time = time();
		$start_time = strtotime('2016-01-09 00:00:00');
		$end_time = strtotime('2016-02-10 00:00:00');
		$i = 1;
		while(true){
			$code = getRandomPass(7,'NUMBER');
			$codeInfo = $this->Common_model->getInfo('user_exchange',array('code'=>$code));
			if(!empty($codeInfo)){ continue; }else{
				$info = array('aid'=>$i,'code'=>(string)$code,'type'=>2,'price'=>100,'status'=>1,'act'=>1,'remarks'=>"狮子会活动",'start_time'=>$start_time,'end_time'=>$end_time,'add_time'=>$cur_time,'from'=>"");
				$this->Common_model->insertInfo('user_exchange',$info);
			}
			if($i>=1000){break;}
			$i++;
		}
		echo "success";
	}
	//得到兑换码
	private function getCodeCoup() {
		set_time_limit(0);
		$info = $this->Common_model->getInfoAll('user_exchange',array('act'=>1),array('aid'=>1));
		$xls = new ExportExcel("狮子会兑换码.xls", "UTF-8");
		$title = array('编号','兑换码','开始日期','结束日期','使用状态','类型','备注','优惠券价格');
		$xls->addArray($title);
		foreach($info as $v){
			$use_st = $v['status']==1?"未使用":"已使用";
			$hang = array($v['aid'],$v['code'],date('Y-m-d H:i:s',$v['start_time']),date('Y-m-d H:i:s',$v['end_time']),$use_st,'预约挂号',$v['remarks'],$v['price']);
			$xls->addArray($hang);
		}
		$title1 = array("总数",count($info));
		$xls->addArray($title1);
		echo "success";
	}

	//统计邀请人数
	public function getInvi($date) {
		set_time_limit(0);
		$time11 =(string)strtotime($date);
		if(!$time11){ show_error("时间格式错误");}
		$info = $this->Common_model->getInfoAll('patient',array('rqcode'=>array('$exists'=>1)),array('rqcode.scene_id'=>1));
		$xls = new ExportExcel($date."号之前邀请人数统计.xls", "UTF-8");
		$title = array('编号','昵称','手机号码','邀请总人数','邀请净人数','邀请后取消人数');
		$xls->addArray($title);
		foreach($info as $v){
			$count = $this->Common_model->getInfoCount('user_weixin',array('regtime'=>array('$lte'=>$time11),'eventkey'=>(int)$v['rqcode']['scen_id'],'st'=>1));
			$count1 = $this->Common_model->getInfoCount('user_weixin',array('regtime'=>array('$lte'=>$time11),'eventkey'=>(int)$v['rqcode']['scen_id']));
			if($v['mobile']=="18010021635" && $date>"2016-02-03"){ $count = 16;	$count1 = 17;	}
			$quxiao = $count1 - $count;
			$hang = array($v['rqcode']['scen_id'],$v['name'],$v['mobile'],$count1,$count,$quxiao);
			$xls->addArray($hang);
		}
		$title1 = array("总数",count($info));
		$xls->addArray($title1);
		echo "success";
	}

	//统计邀请人数
	public function getCodeCount($code,$start="2013-01-01",$end="2018-01-01") {
		set_time_limit(0);
		$code = (int)$code;
		$start = (string)strtotime($start);
		$end = (string)strtotime($end);
		$info = $this->Common_model->getInfoCount('user_weixin',array('regtime'=>array('$gte'=>$start,'$lte'=>$end),'eventkey'=>$code,'st'=>1));
		$info1 = $this->Common_model->getInfoCount('user_weixin',array('regtime'=>array('$gte'=>$start,'$lte'=>$end),'eventkey'=>$code));
		$info2 = $info1-$info;
		echo "总关注人数：".$info1."<br/>";
		echo "净关注人数：".$info."<br/>";
		echo "取消关注人数：".$info2;
	}

	//统计一段时间内的订单
	private function getOrderInfo() {
		set_time_limit(0);
		$sT = new MongoDate(strtotime("2016-01-01 00:00:00"));
		$eT = new MongoDate(strtotime("2016-03-01 00:00:00"));
		$info = $this->Common_model->getInfoAll('order',array('status'=>array('$ne'=>"新订单"),'created_at'=>array('$gte'=>$sT,'$lte'=>$eT)),array('created_at'=>1));
		$xls  = new ExportExcel("订单统计.xls", "UTF-8");
		$title = array('订单类型','订单状态','应付金额','实付金额','下单日期','患者姓名','年龄','性别','患病信息','微信昵称','邀请人微信');
		$xls->addArray($title);
		foreach($info as $v){
			$patInfo= $this->Common_model->getInfo('user_weixin',array('openid'=>$v['fid']));
			if(!empty($patInfo['eventkey'])){
				$invInfo = $this->Common_model->getInfo('patient',array('rqcode.scen_id'=>(int)$patInfo['eventkey']));
				if(!empty($invInfo)){
					$invName = $invInfo['name'];
				}else{
					$invName = $patInfo['eventkey'];
				}
			}else{
				$invName = isset($patInfo['eventkey'])?$patInfo['eventkey']:"未通过渠道";
			}
			$odTime = date('Y-m-d',$v['created_at']->sec);
			$hang = array($v['service'],$v['status'],$v['price_ori'],$v['price'],$odTime,$v['name'],$v['age'],$v['gender'],$v['message'],$patInfo['nickname'],$invName);
			$xls->addArray($hang);
		}
		echo "success";
	}

	//更新用户信息
	private function updatePatInfo() {
		set_time_limit(0);
		$info1 = $this->Common_model->getInfoAll('user_weixin',array('mobile'=>array('$ne'=>""),"st"=>1),array('_id'=>1));
        $this->load->model('Jssdk_model');
        $this->load->model('Weixin_model');
		$tmp_token = $this->Jssdk_model->getAccessToken();
		foreach($info1 as $v){
			if(empty($v['regtime'])) continue;
			$year = date('Ym',$v['regtime']);
			$fileUrl = "./ui/patient/avatar/".$year;
			$fileUrl1 = config_item('global_base_url')."/ui/patient/avatar/".$year;
			mk_dir($fileUrl);
			$info = get_user_info($v['openid'], $tmp_token);
			if(!empty($info['nickname']) && !empty($info['headimgurl'])){
				$headimg = substr($info['headimgurl'],0,strlen($info['headimgurl'])-1)."132";
				$headimgInfo = http_get_data($headimg);
				$md5fn = md5($v['openid']).".png";
				$filename = $fileUrl."/".$md5fn;
				$fp= @fopen($filename,"w+");
				$check = fwrite($fp,$headimgInfo);
				if($check){
					$savedir = $fileUrl1."/".$md5fn;
					$this->Common_model->updateRecord('patient',array('fid'=>$v['openid']),array('name'=>$info['nickname'],'avatar'=>$savedir));
					$this->Common_model->updateRecord('user_weixin',array('openid'=>$v['openid']),array('nickname'=>$info['nickname'],'img'=>$savedir));
				}

			}
			
		}
		echo "success";
	}

	//得到活动信息
	public function getActInfo() {
		set_time_limit(0);
		$info1 = $this->Common_model->getInfoAll('act_log',array("name"=>"freeclinic"),array('_id'=>-1));
		$xls  = new ExportExcel("活动信息统计.xls", "UTF-8");
		$title = array('活动名称','性别','年龄','疾病','备注','电话','微信','参与时间');
		$xls->addArray($title);
		foreach($info1 as $v){
			$hang = array($v['name'],$v['sex'],$v['age'],$v['disease'],$v['explain'],$v['tel'],$v['weixin'],date('Y-m-d H:i',$v['ct']));
			$xls->addArray($hang);
		}
		echo "success";
	}

	//得到活动信息
	public function freeInfo() {
		set_time_limit(0);
		$curtime = time();
		$curdate = (string)date('Y-m-d',$curtime);
		$info = $this->Common_model->getListInfo('act_free',array('startT'=>array('$lte'=>$curdate),'endT'=>array('$gte'=>$curdate)),0,20,"",array("regnum",'doctor'));
		$result = array();
		if($info){
			foreach($info as $k=>$v){
				$docInfo = $this->Common_model->getInfo('doctor',array('_id'=>$v['doctor']),array("name"));
				$count = $this->Common_model->getInfoCount('order',array('ext.actid'=>$v['_id']));
				if(!empty($docInfo)){
					$v['docname'] = $docInfo['name'];
					$v['count'] = $count;
					unset($v['_id'],$v['doctor']);
					$result[] = $v;
				}
			}
		}
		echo "<pre>";
		print_r($result);exit;
	}

	//得到活动参与信息
	public function getActInfo1($start,$end) {
		set_time_limit(0);
		$start = (string)$start;
		$end= (string)addslashes($end);
		$info1 = $this->Common_model->getInfoAll('act_free',array("startT"=>array('$gte'=>$start,'$lte'=>$end)),array('_id'=>1));
		$xls  = new ExportExcel("活动信息统计(以患者下单为维度).xls", "UTF-8");
		$title = array('患者','接待医生','下单时间','订单状态',"就诊信息","诊前问题",'聊天总次数','聊天记录内容（可能不全）');
		$xls->addArray($title);
		foreach($info1 as $v){
			$w= $this->Common_model->getInfoAll('order',array('ext.actid'=>$v['_id']));
			if(!empty($w)){
				foreach($w as $value){
					$pat= $this->Common_model->getTableByRef('patient',$value['patient']);
					$doc= $this->Common_model->getTableByRef('doctor',$value['doctor']);
					$chat = $this->Common_model->getListInfo("hx_history",array('oid'=>(string)$value['_id']),0,200,array('timestamp'=>1),array("payload"));
					$value['question'] = !empty($value['question'])?implode("\n",$value['question']):"";
					$chatInfo = "";
					if($chat){
						foreach($chat as $j){
							if($j['payload']['bodies'][0]['type']=="audio"){
								$chatInfo .= $j['payload']['ext']['nickname']." :: ".$j['payload']['bodies'][0]['url']."  \n";
							}elseif($j['payload']['bodies'][0]['type']=="img"){
								$chatInfo .= $j['payload']['ext']['nickname']." :: ".$j['payload']['bodies'][0]['url']."  \n";
							}else{
								$chatInfo .= $j['payload']['ext']['nickname']." :: ".$j['payload']['bodies'][0]['msg']."  \n";
							}
						}
					}
					if($pat && $doc){
						$hang = array($pat['name'],$doc['name'],date('Y-m-d',$value['created_at']->sec),$value['status'],$value['message'],$value['question'],count($chat),($chatInfo));
						$xls->addArray($hang);
					}
						
				}
			}
		}
		echo "success";
	}

}

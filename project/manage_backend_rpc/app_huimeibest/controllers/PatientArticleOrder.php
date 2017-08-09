<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Order.php
 */
class PatientArticleOrder extends CI_Controller {

	/**
	 * 构造方法
	 *
	 * @param  null
	 * @access public
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		checkLogin1();checkUserPower(); //权限验证
	}

	//订单列表
	public function all()
	{
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		$where = array();
		$params = safeParams($_POST);
		$where['status']['$in'] = empty($params['status'])? array(0,1):array($params['status']);
		if(!empty($params['artId'])){ $where['artId'] = $params['artId']; }
		if(!empty($params['userId'])){ $where['userId'] = $params['userId']; }
		$allcount = $this->Common_model->getInfoCount('pat_article_order',$where);
		$allpage = ceil($allcount/$perpage);
		$result = $this->Common_model->getListInfo('pat_article_order',$where,$offset,$perpage,array('created_at'=>-1),array('artId','userId','art_title','classes','price_ori','price_pay','created_at','prepay','status','pay_info'),true);
		//获取相关用户信息
		$patientId = array_values(array_unique(getFieldArr($result,'userId')));
		$patientInfo = $this->Common_model->getInfoAll('pat_user',array('_id'=>array('$in'=>$patientId)),'',array
		('nickname'),true);
		$patientInfo = ArrKeyFromId($patientInfo);
		foreach ($result as $k=>$v){
			$result[$k]['patient'] = $patientInfo[$v['userId']];
			$result[$k]['created_at'] = date("Y-m-d H:i:s",$v['created_at']);
			//处理支付宝，微信支付金额单位
			if($result[$k]['prepay']['type'] == 'wechat'){
				$result[$k]['price_ori'] = number_format(100.00 * $v['price_ori'],2,'.',',');
				$result[$k]['price_pay'] = number_format(100.00 * $v['price_pay'],2,'.',',');
			}
			if(!array_key_exists('pay_info',$result[$k] )){
				$result[$k]['pay_info']['transaction_id'] = '未生成订单';
			}
		}
		display(array('page'=>$allpage,'data'=>$result));
	}

	//文章标题模糊搜索
	public function articleLikeSearch()
	{
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		$params = safeParams($_POST);
		$aimKeys = array_keys($params);
		$likesValue = $params[$aimKeys[1]];
		if($aimKeys[1] == 'userId'){
			$patUserInfo = $this->Common_model->searchKeysLikes('pat_user',array(),array('nickname'),array('nickname'),$likesValue,$offset,$perpage,'');
			foreach ($patUserInfo as $k=>$v){
				$patUserInfo[$k]['title'] = $v['nickname'];
			}
			display(array('data'=>$patUserInfo));
		}
		$patientTitle = $this->Common_model->searchKeysLikes('pat_article',array(),array('title'),$aimKeys,$likesValue,$offset,$perpage,'');
		display(array('data'=>$patientTitle));
	}












}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$alipay_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/alipay/';
require_once($alipay_dir."lib/alipay_notify.class.php");
require_once($alipay_dir."lib/alipay_rsa.function.php");
require_once($alipay_dir."lib/alipay_core.function.php");
/**
 * Order.php
 */
class Order extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		$this->load->model('order_model');
		//校验session， //session是否过期,
		$this->userid = (string)$this->Common_model->checkLogin();
	}

    /** 
     * 文章下订单
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function article() {
		emptyCheck($this->params,array('article_id'));
		//获取文章
		$artInfo = $this->Common_model->getInfo('pat_article',array('_id'=>getMdbId($this->params['article_id'])),array(),true);
		//校验文章
		if(empty($artInfo)){ display(false,-1,'文章不存在！');}
		if($artInfo['price']<=0){ display(false,2,'该文章为免费文章,不需要支付！');}
		$order = $this->Common_model->getInfo('pat_article_order',array('artId'=>$artInfo['_id'],'userId'=>(string)$this->userid,'status'=>1),array('_id'),true);
		if(!empty($order)){ display(false,3,'您已经购买过该文章！');}
		//没有购买过该付费文章，进行下单
		$ct = time();
		$artInfo['title'] = empty($artInfo['title'])?"付费文章":$artInfo['title'];
		$insertInfo = array( 
			"artId" => $artInfo['_id'],
			"userId" => $this->userid, 
			"classes"=>$artInfo['classes'],
			"art_title"=>$artInfo['title'],
			"price_ori" => (string)$artInfo['price'], 
			"price_pay" => (string)$artInfo['price'],
			"created_at" => $ct,
			"updated_at" => $ct, 
			"pay_type" => "",
			"status" => 0
		);

		$insertId = $this->Common_model->insertInfo('pat_article_order',$insertInfo);
		if($insertId){
			display(array('_id'=>(string)$insertId,'price_pay'=>(string)$artInfo['price']),0,"下单成功!");
		}else{
			display(false,4,"下单失败!"); 
		}
	}


    /** 
     * 预支付接口
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function prePayment() {
		emptyCheck($this->params,array('type','order_id'));
		//支付校验
		$order = $this->Common_model->getInfo('pat_article_order',array('_id'=>getMdbId($this->params['order_id']),'userId'=>(string)$this->userid,'status'=>0),array(),true);
		if(empty($order)){ display(false,2,'该订单不符合支付条件！');}

		$com_order = $this->Common_model->getInfo('pat_article_order',array('artId'=>$order['artId'],'userId'=>(string)$this->userid,'status'=>1),array(),true);
		if(!empty($com_order)){ display(false,2,'该文章已购买过,无需重复购买！');}

		//发起预支付接口 
		if($this->params['type']=="wechat"){
			$data = $this->order_model->prePayWxchat($order);
			if(isset($data['prepay_id'])){
				$update = array('prepay'=>array('type'=>'wechat','prepay_id'=>$data['prepay_id']),'updated_at'=>time());
				$this->Common_model->updateRecord('pat_article_order',array('_id'=>getMdbId($this->params['order_id'])),$update);
			}
			//生成客户端所需数据以及给客户端签名
			$data = $this->order_model->getWxParameters($data);
		}elseif($this->params['type']=="alipay"){
			$update = array('prepay'=>array('type'=>'alipay','prepay_id'=>""),'updated_at'=>time());
			$this->Common_model->updateRecord('pat_article_order',array('_id'=>getMdbId($this->params['order_id'])),$update);
			$data = $this->order_model->prePayAlipay($order);
		}else{
			display(false,3,'支付类型错误！');	
		}
		display(array($this->params['type']=>$data),0,'预支付成功！');	
	}


}

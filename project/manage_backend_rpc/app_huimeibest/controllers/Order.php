<?php
defined('BASEPATH') OR exit('No direct script access allowed');
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
		checkLogin1();
		checkUserPower(); //权限验证
	}

    /** 
     * 得到医生订单信息(聚合查询)
     * 
     * @param  $p;$st;$et;
     * @access public
     * @return void
     */
	public function getDoctorInfo() {
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//聚合查询
		$where = array();
		$where['status']['$nin'] = array('新订单');
		if(!empty($REQUEST['start'])){ $where['pay_at']['$gte'] = (string)strtotime($_REQUEST['start']); }
		if(!empty($REQUEST['end'])){ $where['pay_at']['$lte'] = (string)strtotime($_REQUEST['end']); }
		$pipeline= array(
			array('$project'=>array('doctor'=>1,'price'=>1,'pay_at'=>1,'service'=>1)),
			array('$match'=>$where),
			array('$group'  =>array(
				'_id'     =>array('doctor'=>'$doctor'),
				'priceSum'=>array('$sum'=>'$price'),
				'count'   =>array('$sum'=>1),
				'service'   =>array('$push'=>'$service'),
				'service_price' =>array('$push'=>'$price'),
			)),
			array('$sort'=>array('priceSum'=>-1)),
			array('$skip'=>$offset),
			array('$limit'=>$perpage),
		);
		$options = array();
		$result = $this->Common_model->aggregate('order',$pipeline,$options);
		//处理医生分组订单列表
		$fields = array('starred','_uref','avatar','hospital','name','title','department','position','freeze','service_provided');
		foreach($result as $k=>$v){
			if($v['_id']['doctor']){ $result[$k]['doctor'] = $this->Common_model->getInfo('doctor',array('_id'=>$v['_id']['doctor']['$id']),$fields); }
			$result[$k]['services'] = array('consult'=>array('num'=>0,'price'=>0),'phonecall'=>array('num'=>0,'price'=>0));
			foreach($v['service'] as $i=>$j){
				if($j=='consult'){
					$result[$k]['services']['consult']['num']++;
					$result[$k]['services']['consult']['price']+=$v['service_price'][$i];
				}elseif($j=='phonecall'){
					$result[$k]['services']['phonecall']['num']++;
					$result[$k]['services']['phonecall']['price']+=$v['service_price'][$i];
				}
			}
			unset($result[$k]['service'],$result[$k]['service_price']);
		}
		display($result);
	}

    /** 
     * 得到医生订单信息数量(分组查询)
     * 
     * @param  $p;$st;$et;
     * @access public
     * @return void
     */
	public function getDoctorInfoCount() {
		//分组查询
		$where = array();
		$where['status']['$nin'] = array('新订单');
		if(!empty($REQUEST['start'])){ $where['pay_at']['$gte'] = (string)strtotime($_REQUEST['start']); }
		if(!empty($REQUEST['end'])){ $where['pay_at']['$lte'] = (string)strtotime($_REQUEST['end']); }
		$keys = array('doctor'=>1);
		$initial = array('count'=>0);
		$reduce = "function (obj,prev){}";
		$options = array('condition'=>$where);
		$result = $this->Common_model->getGroupInfo('order', $keys, $initial, $reduce,$options);
		$count = !empty($result['keys'])?$result['keys']:1;
		$page = ceil($count/20);
		display(array('page'=>$page));
	}

    /** 
     * 得到订单详情
     * 
     * @param  $order
     * @access public
     * @return void
     */
	public function getOrderInfo() {
		if(empty($_REQUEST['_id'])){ echo display(array(),2,"参数有误");}
		$where = array('_id'=>getMdbId($_REQUEST['_id']));
		$result = $this->Common_model->getInfo('order',$where);
		if($result){
			//$result['user'] = $this->Common_model->getInfo('user',array('_id'=>$result['_uref']['$id']),array('mobile','actived_at'));
			$result['pay_at'] = date('Y-m-d H:i:s',$result['pay_at']);
			$result['schedule'] = date('Y-m-d H:i:s',$result['schedule']->sec);
			$result['doctor'] = $this->Common_model->getInfo('doctor',array('_id'=>$result['doctor']['$id']),array('name','hospital','assistant'));
			if($result['doctor']){
				$result['assistant'] = $this->Common_model->getInfo('doctor_assistant',array('_id'=>@$result['doctor']['assistant']));
			}
			$result['private'] = $this->Common_model->getInfo('doctor_private',array('_uref'=>$result['_uref']));
		}
		display($result);
	}

	//订单列表
	public function all()
	{
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//聚合查询
		$where = array();
		@$status = $_REQUEST['status'];
		$where['status']['$nin'] = empty($status)? array('新订单','已完成','未支付','取消支付'):array($status);
		if(!empty($REQUEST['start'])){ $where['pay_at']['$gte'] = (string)strtotime($_REQUEST['start']); }
		if(!empty($REQUEST['end'])){ $where['pay_at']['$lte'] = (string)strtotime($_REQUEST['end']); }
		$pipeline= array(
			array('$match'=>$where),//where 条件不起作用？？？
			array('$skip'=>$offset),
			array('$limit'=>$perpage),
		);
		$options = array();
		$result = $this->Common_model->aggregate('order',$pipeline,$options);
		display($result);
	}













}

<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model {
	public $table = "order";

	function __construct()
	{
		parent::__construct();
	}
	
	public function getOrderStatus($class,$info){
		$st = array( "新订单"=>"新订单", "等待支付"=>"等待支付", "付款进行中"=>"付款进行中", "付款失败"=>"付款失败", "已支付"=>"待就诊", "待就诊"=>"待就诊", "待转诊"=>"待转诊", "已转诊"=>"已转诊", "已完成"=>"已完成", "已取消"=>"已取消", "已退款"=>"已退款");

		$st1 = array( "新订单"=>"新订单", "等待支付"=>"等待支付", "付款进行中"=>"付款进行中", "付款失败"=>"付款失败", "已支付"=>"咨询中", "已完成"=>"已完成", "已取消"=>"已取消", "已退款"=>"已退款");

		$st2 = array( "新订单"=>"新订单","待就诊"=>"待就诊","等待支付"=>"等待支付", "付款进行中"=>"付款进行中", "付款失败"=>"付款失败", "已支付"=>"已支付", "已完成"=>"已完成", "已取消"=>"已取消", "已退款"=>"已退款");

		if($class=='clinic'){
			return !empty($st[$info])?$st[$info]:$info;
		}elseif($class=='consult'){
			return !empty($st1[$info])?$st1[$info]:$info;
		}elseif($class=='phonecall'){
			return !empty($st2[$info])?$st2[$info]:$info;
		}else{
			$ststr = "无";
		}
	}
	
	public function insertInfo($table,$info){
		$check = $this->mdb->insert($table,$info);
		if(!empty($check)){
			return $check;
		}else{
			return false;
		}
	}

	public function getInfo($table,$where) {
		$info = $this->mdb->where($where)->limit(1)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			array();
		}
	}

	public function getInfoAll($table,$where) {
		$info = $this->mdb->where($where)->get($table);
		if(!empty($info)){
			return $info;
		}else{
			array();
		}
	}

	public function getInfoCount($table,$where) {
		$info = $this->mdb->where($where)->count($table);
		return $info;
	}


	public function updateRecord($table,$where,$update) {
		//add cache
		$check = $this->mdb->where($where)->update($table,$update);
		if(!empty($check)){
			return true;
		}else{
			return false;
		}
	}

	public function getListInfo($table,$where,$offset,$perpage=20,$sort="",$field=array()){
		//add cache
		$info = $this->mdb->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get($table);
		return $info;
	}



}


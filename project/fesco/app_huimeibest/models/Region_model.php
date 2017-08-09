<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Region_Model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	public function getFirstArea($field,$limit=300){
		//add cache
		$where = array('level'=>1);
		$order = array("weight"=>1);
		$info = $this->mdb->where($where)->order_by($order)->select($field)->limit($limit)->get('region');
		return $info;
	}

}


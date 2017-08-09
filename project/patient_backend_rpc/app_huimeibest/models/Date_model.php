<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Date_model extends CI_Model {
	public $ct;
	public $wday;
	public $week;

	function __construct()
	{
		$cur_time = time();
		$this->ct = $cur_time;
		$temp = getdate($cur_time);
		$this->wday = $temp['wday'];
		$this->week = array( '0'=>"周日", '1'=>"周一", '2'=>"周二", '3'=>"周三", '4'=>"周四", '5'=>"周五", '6'=>"周六");
		$this->week1 = array( '7'=>"周日", '1'=>"周一", '2'=>"周二", '3'=>"周三", '4'=>"周四", '5'=>"周五", '6'=>"周六");
		parent::__construct();
	}

	public function getSelTime($format = "m月d日",$num=3){
		$sTime = $this->getThisWeekMondayTime();
		$result = array();
		for($i=1;$i<=$num;$i++){
			$tempeTime = $sTime+518400;
			$result[$i] = date($format,$sTime)." - ".date($format,$tempeTime);
			$sTime+=604800;
		}
		return $result;
	}

	public function getClientClander($num=3){
		$result = array();
		$sTime = $this->getThisWeekMondayTime();
		for($i=1;$i<=$num;$i++){
			$result[$i]['riqi'] = $this->getWeekDate($sTime);
			$result[$i]['list'] = $this->getDateArrList($sTime);
			$sTime+=604800;
		}
		return $result;
	}

	public function getSelEndDate($num=3){
		$monday = $this->wday==0?7:$this->wday;
		$temp = $this->ct+(($num-1)*7+(7-$monday))*86400;
		$result = date('Y-m-d',$temp);
		return $result;
	}

	public function getWeekMondayTime(){
		$monday = $this->wday==0?6:$this->wday-1;
		$monday = $monday+1;
		return strtotime("-$monday days");
	}	   

	public function getWeekTime($date,$format=1){
		$timestamp = strtotime($date);
		$dateInfo = getdate($timestamp);
		$result = array();
		if($format==1){
			$week = $dateInfo['wday']==0?6:$dateInfo['wday']-1;
			if($week>=5){
				$result['start'] = date('Y-m-d',$timestamp);
				$result['end'] = date('Y-m-d',($timestamp+86400*(11-$week)));
			}else{
				$result['start'] = date('Y-m-d',($timestamp-(86400*$week)));
				$result['end'] = date('Y-m-d',($timestamp+86400*(4-$week)));
			}
			return $result;
		}
	}	   

	private function getDateArrList($sTime,$days=7){
		$result = array();
		for($i=1;$i<=$days;$i++){
			$temDate = date('Ymd',$sTime);
			$result['morn'][$temDate."1"] = 1;
			$result['after'][$temDate."2"] = 1;
			$sTime += 86400;
		}
		return $result;
	}

	private function getWeekDate($sTime,$days=7){
		$result = array();
		for($i=1;$i<=$days;$i++){
			$result[$i]['week'] = $this->week[date('w',$sTime)];
			$result[$i]['date'] = date('m.d',$sTime);
			$sTime += 86400;
		}
		return $result;
	}

	private function getThisWeekMondayTime(){
		$monday = $this->wday==0?6:$this->wday-1;
		return strtotime("-$monday days");
	}	   
	



}


<?php
$msg_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/submail/';
require_once($msg_file.'SUBMAILAutoload.php');
class Message_model extends CI_Model{

	function __construct()
	{
		parent::__construct();
	}

	public function sendMsgFromPc($data) {
		$submail=new MESSAGEXsend(config_item('msg_config'));
		$submail->setTo($data['mobile']);
		$submail->SetProject('YJ3PU4');
		$submail->AddVar('name',$data['name']);
		$submail->AddVar('hospital',$data['hospital']);
		$submail->AddVar('dep',$data['dep']);
		$submail->AddVar('doctor',$data['doctor']);
		$submail->AddVar('time',$data['time']);
		$submail->AddVar('hours',$data['hours']);
		$xsend=$submail->xsend();
		return $xsend;
	}

	public function sendMsgFromCl($data) {
		$submail=new MESSAGEXsend(config_item('msg_config'));
		$submail->setTo($data['mobile']);
		$submail->SetProject('aUCax1');
		$submail->AddVar('name',$data['name']);
		$submail->AddVar('location',$data['location']);
		$submail->AddVar('doctor',$data['doctor']);
		$submail->AddVar('time',$data['time']);
		$submail->AddVar('hours',$data['hours']);
		$xsend=$submail->xsend();
		return $xsend;
	}

	public function sendMsgFromCl1($data) {
		$submail=new MESSAGEXsend(config_item('msg_config'));
		$submail->setTo($data['mobile']);
		$submail->SetProject('bg3gG4');
		$submail->AddVar('assistant',$data['assistant']);
		$submail->AddVar('patient',$data['patient']);
		$submail->AddVar('mobile',$data['show_mobile']);
		$submail->AddVar('hospital',$data['hospital']);
		$submail->AddVar('doctor',$data['doctor']);
		$submail->AddVar('datetime',$data['datetime']);
		$submail->AddVar('serial_number',$data['serial_number']);
		$xsend=$submail->xsend();
		return $xsend;
	}
}

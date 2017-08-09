<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$oss_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/oss/';
require_once($oss_file.'autoload.php');

$phpqrcode_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/phpqrcode/';
require_once($phpqrcode_file.'phpqrcode.php');

use OSS\OssClient;
use OSS\Core\OssException;
/**
 * Doctor.php
 */
class Doctor extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
		checkLogin1();checkUserPower();
	}

    /** 
     * 医生列表
     * 
     * @param  $status page dep hos kw sort
     * @access public
     * @return void
     */
	public function getlist() {
		$page = getCurPage(); $perpage = 20;
		$offset = getPage($page,$perpage);
		//得到总分页
		$fields = array('starred','_uref','updated_at','avatar','hospital','name','title','department','position','freeze','reg_num','region_id','region_child_id','assistant','actived_at','service_provided');
		$where = getWhereParams();
		if(!empty($where)){
			$searchK = array_keys($where);
			$searchV = addslashes(trim($_POST["$searchK[0]"]));
			$allcount = $this->Common_model->getInfoCount('doctor',array(),$searchK,$searchV);
			$allpage = ceil($allcount/$perpage);
			//医生列表
			$result = $this->Common_model->searchKeysLikes('doctor',array(),$fields,$searchK,$searchV,$offset,$perpage,array("_id"=>-1));
		}else{
			$allcount = $this->Common_model->getInfoCount('doctor',array());
			$allpage = ceil($allcount/$perpage);
			$result = $this->Common_model->getListInfo('doctor',$where,$offset,$perpage,array("_id"=>-1),$fields);
		}
		//处理医生列表
		$assistantId = getFieldArr($result,'assistant',0);
		$assistant = $this->Common_model->getInfoAll('doctor_assistant',array('_id'=>array('$in'=>$assistantId))); 
		$assistant= ArrKeyFromId($assistant);
		foreach($result as $k=>$v){
			$result[$k]['assistant'] = !empty($assistant[(string)$v['assistant']])?$assistant[(string)$v['assistant']]:"";
			if($v['region_id']){ $result[$k]['region_id'] = $this->Common_model->getInfo('region',array('_id'=>$v['region_id'])); }
			if($v['region_child_id']){ $result[$k]['region_child_id'] = $this->Common_model->getInfo('region',array('_id'=>$v['region_child_id'])); }
			$result[$k]['articels'] = $this->Common_model->getInfoCount('doctor_article',array('doctor'=>$v['_id'])); 
			$result[$k]['user'] = $this->Common_model->getInfo('user',array('_id'=>$v['_uref']['$id']),array('mobile','created_at','updated_at','actived_at')); 
			if($result[$k]['user']['actived_at']) $result[$k]['user']['actived_at'] = date('Y-m-d H:i:s',$result[$k]['user']['actived_at']->sec);
		}
		display(array('page'=>$allpage,'data'=>$result));
	}

	//得到医助
	public function getassistant(){
		$page = getCurPage(); $perpage = 50;
		$offset = getPage($page,$perpage);
		//列表
		$result = $this->Common_model->getListInfo('doctor_assistant',array(),$offset,$perpage,array("_id"=>-1),array());
		display($result);
	}

	//获取医生详情
	public function getDoctorDetail(){
		if(empty($_POST['_id'])){ display(array(),2,"参数有误");}
		$where = array('_id'=>getMdbId1($_REQUEST['_id']));
		$result = $this->Common_model->getInfo('doctor',$where);
		if($result){

            if (!$result['qrcode_url']){
                //如果医生还没有生成二维码,则生成二维码并将二维码的url保存到数据库中。
                $result = $this->saveDoctorQrcode($result);
            }

			if($result['assistant']){ $result['assistant'] = $this->Common_model->getInfo('doctor_assistant',array('_id'=>$result['assistant'])); }
			if($result['region_id']){ $result['region_id'] = $this->Common_model->getInfo('region',array('_id'=>$result['region_id'])); }
			if($result['region_child_id']){ $result['region_child_id'] = $this->Common_model->getInfo('region',array('_id'=>$result['region_child_id'])); }
			if($result['department_id']){ $result['department_id'] = $this->Common_model->getInfo('department',array('_id'=>$result['department_id']),array('name')); }
			if($result['department_child_id']){ $result['department_child_id'] = $this->Common_model->getInfo('department',array('_id'=>$result['department_child_id']),array('name')); }
			$result['articels'] = $this->Common_model->getInfoCount('doctor_article',array('doctor'=>$result['_id'])); 
			$result['user'] = $this->Common_model->getInfo('user',array('_id'=>$result['_uref']['$id']),array('mobile','created_at','updated_at','actived_at')); 
			$result['actived_at'] = date('Y-m-d H:i:s',$result['user']['actived_at']->sec);
			$result['updated_at'] = date('Y-m-d H:i:s',$result['updated_at']->sec);
			$result['created_at'] = date('Y-m-d H:i:s',$result['created_at']->sec);
		}

		display($result);
	}


	public function regenerateQrcode(){
		
        if(empty($_POST['_id'])){ display(array(),2,"参数有误");}
        $where = array('_id'=>getMdbId1($_REQUEST['_id']));
        $result = $this->Common_model->getInfo('doctor',$where);

        if($result) {
            $result = $this->saveDoctorQrcode($result);

            display(array('url'=>$result['qrcode_url']));
        }else{
            display(array(),-1,"无此医生");
        }
    }


	private function saveDoctorQrcode($doctor) {

        $doctorId = $doctor['_id'];
        $encoded_doctor_id = authcode($doctorId,'ENCODE');
        $doctorUrl = 'http://'.$_SERVER['SERVER_NAME'].'/scan/follow?version=1.0&doctorId='.$encoded_doctor_id;

        $dirName = FCPATH.'data/doctor_qr/';
        $imageName = $dirName.$doctorId.'.png';

        if(!is_readable($dirName)){
            mkdir($dirName);
        }

        if (is_writable($dirName)){

            //再次check文件夹可写,才生成二维码。
            QRcode::png($doctorUrl, $imageName);

            $doctor['qrcode_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/data/doctor_qr/'.$doctorId.'.png';
            log_message('info', 'create doctor qrcodemage at'. $doctor['qrcode_url']);

            $this->Common_model->updateRecord('doctor',array('_id'=>$doctor['_id']),$doctor);
		}

        return $doctor;
    }

	//添加医生
	public function addDoctor(){
		$userData = $this->sign_user();
		$doctorData = $this->sign_doctor();
		//插入数据user
		$userId= $this->Common_model->insertInfo('user',$userData);
		if(empty($userId)){ display(array(),3,"插入数据失败,用户已存在");}
		//插入数据doctor
		$doctorData['_uref'] =  MongoDBRef::create("user", getMdbId1($userId));
		$doctorId = $this->Common_model->insertInfo('doctor',$doctorData);
		//更新医生二维码doctor_rqcode
		if($doctorData['scene_id']){
			$doctor_ref =  MongoDBRef::create("doctor", getMdbId1($doctorId));
			$this->Common_model->updateRecord('doctor_rqcode',array('scene_id'=>(int)$doctorData['scene_id']),array('doctor'=>$doctor_ref));
		}
		display(array('insertId'=>$doctorId));
	}

	//编辑医生
	public function editDoctor(){
		emptyCheck(array('_id','mobile','name','freeze','assistant','avatar','hospital','hospital_id','region_id','department','position','title','department_id'));
		$mongodate= new MongoDate(time());
		$mobile = addslashes(trim($_POST['mobile']));
		if(strlen($mobile)<8){ display(array(),3,"手机格式错误"); }
		//检查是否存在该医生
		$doctorInfo = $this->Common_model->getInfo('doctor',array('_id'=>getMdbId1(addslashes(trim($_POST['_id'])))));
		if(empty($doctorInfo)){ display(array(),3,"未找到该医生，请检查!"); }
		//检查user，
		$userInfo = $this->Common_model->getTableByRef('user',$doctorInfo['_uref']);
		if(empty($userInfo)){ display(array(),3,"未找到该用户，请检查!"); }
		//检查手机号码是否修改
		if($mobile!=$userInfo['mobile']){
			//检查手机号是否存在
			$checkMobile = $this->Common_model->getInfo('user',array('mobile'=>$mobile,'_id'=>array('$ne'=>$userInfo['_id'])));
			if(empty($checkMobile)){ display(array(),3,"手机号已经存在,请检查!"); }
			//更新user表
			$salt = $userInfo['salt'];
			$passworld = sha1(md5(substr($mobile,5)).$salt);
			$userUpdate = array(
				"mobile" => $mobile,
				"password" => $passworld,
				"updated_at" => $mongodate
			);
			$this->Common_model->updateRecord('user',array('_id'=>$userInfo['_id']),$userUpdate);
		}
		//更新doctor
		$doctorUpdate= array(
			"name" => addslashes($_POST['name']),
			"freeze" => addslashes($_POST['freeze']),
			"assistant" => getMdbId1(trim($_POST['assistant'])),
			"avatar" => addslashes($_POST['avatar']),
			"hospital" => addslashes($_POST['hospital']),
			"hospital_id" => getMdbId1(trim($_POST['hospital_id'])),
			"region_id" => getMdbId1(trim($_POST['region_id'])),
			"region_child_id" => getMdbId1(trim($_POST['region_child_id'])),
			"department" => addslashes($_POST['department']),
			"department_id" => getMdbId1(trim($_POST['department_id'])),
			"department_child_id" => getMdbId1(trim($_POST['department_child_id'])),
			"position" => addslashes($_POST['position']),
			"title" => addslashes($_POST['title']),
			"speciality" => addslashes($_POST['speciality']),
			"description" => addslashes($_POST['description']),
			"mul_num" => (int)$_POST['mul_num'],
			"updated_at" => $mongodate,
		);
		$this->Common_model->updateRecord('doctor',array('_id'=>$doctorInfo['_id']),$doctorUpdate);
		display(array());
	}

	//注册user
	private function sign_user(){
		emptyCheck(array('mobile'));
		//检查手机号是否存在
		$mobile = addslashes(trim($_POST['mobile']));
		$check = $this->Common_model->getInfo('user',array("mobile"=>$mobile,'from'=>array('$exists'=>0)));
		if($check){ display(array(),3,"该手机号已经存在"); }
		$mongodate= new MongoDate(time());
		if(strlen($mobile)<8){ display(array(),3,"手机格式错误"); }
		$salt = uniqid();
		$passworld = sha1(md5(substr($mobile,5)).$salt);
		return array(
			"mobile" => $mobile,
			"password" => $passworld,
			"salt" => $salt,
		    "created_at" => $mongodate,
			"updated_at" => $mongodate,
			"actived_at" => $mongodate
		);
	}

	//注册doctor
	private function sign_doctor(){
		emptyCheck(array('mobile','name','freeze','assistant','avatar','hospital','hospital_id','region_id','department','position','title','department_id','department_child_id'));
		$mongodate= new MongoDate(time());
		$insertData = array(
			"name" => addslashes($_POST['name']),
			"freeze" => addslashes($_POST['freeze']),
			"assistant" => getMdbId1(trim($_POST['assistant'])),
			"avatar" => addslashes($_POST['avatar']),
			"hospital" => addslashes($_POST['hospital']),
			"hospital_id" => getMdbId1(trim($_POST['hospital_id'])),
			"region_id" => getMdbId1(trim($_POST['region_id'])),
			"region_child_id" => getMdbId1(trim($_POST['region_child_id'])),
			"department" => addslashes($_POST['department']),
			"department_id" => getMdbId1(trim($_POST['department_id'])),
			"department_child_id" => getMdbId1(trim($_POST['department_child_id'])),
			"position" => addslashes($_POST['position']),
			"title" => addslashes($_POST['title']),
			"speciality" => addslashes($_POST['speciality']),
			"description" => addslashes($_POST['description']),
			"service_provided" => json_decode('{ "consult" => { "on" => false, "price" => 200 }, "phonecall" => { "on" => true, "price_05" => -1, "price_10" => -1, "price_15" => -1, "price_20" => -1, "minutes_min" => 10000 }, "clinic" => { "on" => false } }'),
			"starred" => 0,
			"con_num" => 0,
			"mul_num" => (int)$_POST['mul_num'],
			"rc_num" => 0,
			"reg_num" => 0,
			"level" => 3,
			"comment" => array('star'=>0,'num'=>0,'per'=>100),
			"created_at" => $mongodate,
			"updated_at" => $mongodate,
			"scene_id" => 0
		);
		$sceneInfo = $this->Common_model->getInfoSort('doctor_rqcode',array('doctor'=>array('$exists'=>0)),array('scene_id'),array('scene_id'=>1));
		if($sceneInfo){
			$insertData['scene_id'] = $sceneInfo['scene_id'];
		}
		return $insertData;
	}

	//注册avatar
	public function sign_avatar(){
		$data = $this->input->post();
		$cur_time = time();
		if(empty($data['msg']) || empty($data['type'])){ display(array(),3,"参数错误!"); }
		$accessKeyId = "uLmwkyi2tLw0pj7L"; ;
		$accessKeySecret = "DnNH0hXvDV2zqlf9HaCNrNpLwOBXIb";
		$endpoint = "oss-cn-beijing-internal.aliyuncs.com";
		try {
			$ossClient = new OssClient( $accessKeyId, $accessKeySecret, $endpoint);
			//保存到本地文件
			$filename = empty($data['dataname']) ? md5($cur_time.getRandomPass(4)).$data['type']:$data['filename'].$data['type'];
			$streamFile = @base64_decode(urldecode($data['msg']));
			$temp_file = "/tmp/".$filename;
			$hder = "Content-Type: image/".$data['type']; header($hder);
			$file = fopen($temp_file,"w");//打开文件准备写入
			fwrite($file,$streamFile);//写入 fclose($file);//关闭
			//存储到OSS
			header("Content-type: text/html; charset=utf-8");
			$osspath = "avatar/".$filename;
			$ossClient->uploadFile('hm-img', $osspath, $temp_file);
		} catch (OssException $e) {
			display(array(),3,$e->getMessage());
		}
		$OssPath = 'http://hm-img.huimeibest.com/'.$osspath."@!256";
		
		display(array('path'=>$OssPath));
		
	}


	//医生姓名搜索
	public function searchName()
	{
		emptyCheck(array('name'));
		$result = $this->Common_model->searchLikes('doctor',array('name'),'name',$_POST['name']);
		display($result);
	}

	//验证添加用户手机号
	public function checkPhone()
	{
		$params = safeParams($_POST);
		$result = $this->Common_model->getInfo('user',array('mobile'=>$params['mobile']));
		if(empty($result)){display(array(),0,'手机号可用');}
		else{display(array(),404,'手机号已存在');}
	}

}

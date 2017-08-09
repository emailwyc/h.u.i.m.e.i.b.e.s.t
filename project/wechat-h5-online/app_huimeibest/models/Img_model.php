<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Img_model extends CI_Model {
	
	private $img_type =  array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1,'GIF'=>1,'JPG'=>1,'PNG'=>1,'BMP'=>1,'JPEG'=>1);
	function __construct()
	{
		parent::__construct();
		$this->load->library('image_lib');
	}

	//dir,size,msg,type,quality,isthumb
	public function ImgUploadOne($Info){
		$error = array('st'=>0,'msg'=>"");
		if(empty($Info['msg']) || empty($Info['type'])){ $error['st'] = 2; $error['msg'] = "参数错误！"; return $error; }
		$cur_time = time();
		$month = date('Ym',$cur_time);
		$streamFile = @base64_decode(urldecode($Info['msg']));
		if(empty($streamFile)){ $error['st'] = 2; $error['msg'] = "图片参数错误！"; return $error; }
		if(!isset($this->img_type[$Info['type']])){ $error['st'] = 3; $error['msg'] = "图片格式错误！"; return $error; }
		$md5fn = md5($cur_time.getRandomPass(4)).".".$Info['type'];
		$temp_file = "/tmp/".$md5fn;
		$hder = "Content-Type: image/".$Info['type']; header($hder);
		$file = fopen($temp_file,"w");//打开文件准备写入
		fwrite($file,$streamFile);//写入 fclose($file);//关闭
		header("Content-type: text/html; charset=utf-8");
		$im = @getimagesize($temp_file); $img_size=@ceil(filesize($temp_file)/1048576);
		if($im == false || $img_size>=5){ $error['st'] = 6; $error['msg'] = "上传图片不符合规则或已损坏！！"; return $error;	}
		//处理图片
		$dbfiles = './ui/patient/'.$Info['dir'].'/'.$month."/";
		mk_dir($dbfiles);
		$configLarge1 = $this->getPara($Info);
		$configLarge1['source_image'] = $temp_file;
		$configLarge1['new_image'] = $dbfiles.$md5fn;
		$this->image_lib->initialize($configLarge1);
		if(!$this->image_lib->resize()){ $error['st'] = 4; $error['msg'] = $this->image_lib->display_errors(); return $error; }
		$error['st'] = 1; $error['msg'] = config_item('global_base_url').'/ui/patient/'.$Info['dir'].'/'.$month."/".$md5fn;;
		return $error;
	}

	private function getPara($Info,$isThumb=""){
		$configLarge1 = array();
		$configLarge1['image_library'] = 'gd2';
		$configLarge1['create_thumb'] = 'TRUE';
		$configLarge1['maintain_ratio'] = TRUE; //保持图片比例
		$configLarge1['master_dim'] = 'auto';
		$configLarge1['quality'] = empty($Info['quality'])?"80%":$Info['quality'];
		$configLarge1['width'] = $Info['size']['x'];
		$configLarge1['height'] = $Info['size']['y'];;
		$configLarge1['dynamic_output'] = FALSE;
		$configLarge1['thumb_marker'] = $isThumb;
		return $configLarge1;
	}

	//单张base64位图片上传
	public function ConsultImgUploadOne($Info){
		//$data = array('msg'=>"asdf",'type'=>'txt','remarks'=>"png",'orderid'=>'5678f11db7ef6acd118b45ef','doctor'=>'55f95ab283cdf8575d62dd87');
		$error = array('st'=>0,'msg'=>"");
		if(empty($Info['msg']) || empty($Info['remarks'])){ $error['st'] = 2; $error['msg'] = "参数错误！"; return $error; }
		$cur_time = time();
		$createFn = $cur_time.getRandomPass(4);
		$month = date('Ym',$cur_time);
		$streamFile = @base64_decode(urldecode($Info['msg']));//图片流
		if(empty($streamFile)){ $error['st'] = 2; $error['msg'] = "参数错误！"; return $error; }
		//写入图片
		$img_type_all = array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1,'GIF'=>1,'JPG'=>1,'PNG'=>1,'BMP'=>1,'JPEG'=>1);
		if(!isset($img_type_all[$Info['remarks']])){ $error['st'] = 3; $error['msg'] = "图片格式错误！"; return $error; }
		$md5filename = md5($createFn);
		$md5fn = $md5filename.".".$Info['remarks'];
		//保存成文件格式
		$temp_file = "/tmp/".$md5fn;
		$hder = "Content-Type: image/".$Info['remarks'];
		header($hder);
		$file = fopen($temp_file,"w");//打开文件准备写入
		fwrite($file,$streamFile);//写入
		fclose($file);//关闭
		header("Content-type: text/html; charset=utf-8");
		//判断是否是真正图片
		$im = @getimagesize($temp_file);
		$img_size=@ceil(filesize($temp_file)/1048576);
		if($im == false || $img_size>=5){
			$error['st'] = 6; $error['msg'] = "上传图片不符合规则或已损坏！！"; return $error;	
		}
		//处理图片
		$dbfile = config_item('global_base_url').'/ui/patient/consult/'.$month."/".$md5fn;
		$dbfiles = './ui/patient/consult/'.$month."/";
		mk_dir($dbfiles);
		$conf = $this->getParams1();
		$conf['source_image'] = $temp_file;
		$conf['new_image'] = $dbfiles.$md5fn;
		$this->image_lib->initialize($conf);
		if(!$this->image_lib->resize()){
			$error['st'] = 4; $error['msg'] = $this->image_lib->display_errors();//处理图片出现错误
		   	return $error;
		}
		$conf = $this->getParams2();
		$conf['source_image'] = $temp_file;
		$conf['new_image'] = $dbfiles.$md5fn;
		$dbfile_thumb = config_item('global_base_url').'/ui/patient/consult/'.$month."/".$md5filename.$conf['thumb_marker'].".".$Info['remarks'];
		$this->image_lib->initialize($conf);
		$this->image_lib->resize();


		$imgInfo = @getimagesize($dbfiles.$md5fn);
		$error['size']['x'] = @$imgInfo[0];
		$error['size']['y'] = @$imgInfo[1];
		$error['st'] = 1; $error['msg'] = $dbfile;
		$error['thumb'] = $dbfile_thumb;
		return $error;
	}
	//单张base64位图片上传
	public function patientImgUploadOne($Info){
		$error = array('st'=>0,'msg'=>"");
		if(empty($Info['img_info']) || empty($Info['img_type'])){ $error['st'] = 2; $error['msg'] = "参数错误！"; return $error; }
		$cur_time = time();
		$createFn = $cur_time.getRandomPass(4);
		$month = date('Ym',$cur_time);
		$streamFile = @base64_decode(urldecode($Info['img_info']));//图片流
		if(empty($streamFile)){ $error['st'] = 2; $error['msg'] = "参数错误！"; return $error; }
		//写入图片
		$img_type_all = array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1,'GIF'=>1,'JPG'=>1,'PNG'=>1,'BMP'=>1,'JPEG'=>1);
		if(!isset($img_type_all[$Info['img_type']])){ $error['st'] = 3; $error['msg'] = "图片格式错误！"; return $error; }
		$md5filename = md5($createFn);
		$md5fn = $md5filename.".".$Info['img_type'];
		//保存成文件格式
		$temp_file = "/tmp/".$md5fn;
		$hder = "Content-Type: image/".$Info['img_type'];
		header($hder);
		$file = fopen($temp_file,"w");//打开文件准备写入
		fwrite($file,$streamFile);//写入
		fclose($file);//关闭
		header("Content-type: text/html; charset=utf-8");
		//判断是否是真正图片
		$im = @getimagesize($temp_file);
		$img_size=@ceil(filesize($temp_file)/1048576);
		if($im == false || $img_size>=5){
			$error['st'] = 6; $error['msg'] = "上传图片不符合规则或已损坏！！"; return $error;	
		}
		//处理图片
		$dbfile = config_item('global_base_url').'/ui/patient/uploads/'.$month."/".$md5fn;
		$dbfiles = './ui/patient/uploads/'.$month."/";
		mk_dir($dbfiles);
		$conf = $this->getParams();
		$conf['conf1']['source_image'] = $temp_file;
		$conf['conf1']['new_image'] = $dbfiles.$md5fn;
		$this->image_lib->initialize($conf['conf1']);
		if(!$this->image_lib->resize()){
			$error['st'] = 4; $error['msg'] = $this->image_lib->display_errors();//处理图片出现错误
		   	return $error;
		}
		$conf['conf2']['source_image'] = $temp_file;
		$conf['conf2']['new_image'] = $dbfiles.$md5fn;
		$this->image_lib->initialize($conf['conf2']);
		$this->image_lib->resize();
		$error['st'] = 1; $error['msg'] = $dbfile;
		return $error;
	}
	private function getParams1(){
		$configLarge1 = array();
		$configLarge1['image_library'] = 'gd2';
		$configLarge1['create_thumb'] = 'TRUE';
		$configLarge1['maintain_ratio'] = TRUE; //保持图片比例
		$configLarge1['master_dim'] = 'auto';
		$configLarge1['quality'] = '60%';
		$configLarge1['width'] = 800;
		$configLarge1['height'] = 800;
		$configLarge1['dynamic_output'] = FALSE;
		$configLarge1['thumb_marker'] = "";
		return $configLarge1;
	}

	private function getParams2(){
		$configLarge1 = array();
		$configLarge1['image_library'] = 'gd2';
		$configLarge1['create_thumb'] = 'TRUE';
		$configLarge1['maintain_ratio'] = TRUE; //保持图片比例
		$configLarge1['master_dim'] = 'auto';
		$configLarge1['quality'] = '50%';
		$configLarge1['width'] = 100;
		$configLarge1['height'] = 100;
		$configLarge1['dynamic_output'] = FALSE;
		$configLarge1['thumb_marker'] = "_thumb";
		return $configLarge1;
	}

	private function getParams(){
		$configLarge1 = array();
		$configLarge1['image_library'] = 'gd2';
		$configLarge1['create_thumb'] = 'TRUE';
		$configLarge1['maintain_ratio'] = TRUE; //保持图片比例
		$configLarge1['master_dim'] = 'auto';
		$configLarge1['quality'] = '80%';
		$configLarge1['width'] = 600;
		$configLarge1['height'] = 800;
		$configLarge1['dynamic_output'] = FALSE;
		$configLarge1['thumb_marker'] = "";

		$configLarge = array();
		$configLarge['image_library'] = 'gd2';
		$configLarge['create_thumb'] = TRUE;
		$configLarge['maintain_ratio'] = TRUE; //保持图片比例
		$configLarge['master_dim'] = 'auto';
		$configLarge['quality'] = '50%';
		$configLarge['width'] = 180;
		$configLarge['height'] = 180;
		$configLarge['dynamic_output'] = FALSE;
		$configLarge['thumb_marker'] = "_thumb";
		return array('conf1'=>$configLarge1,'conf2'=>$configLarge);
	}
	
	public function patientImgUpload(){
		$random4 = getRandomPass(4);
		$cur_time = time();
		$createFn = $cur_time.$random4;
		$month = date('Ym',$cur_time);
		$dbfile = config_item('global_base_url').'/ui/patient/uploads/'.$month."/";
		$dbfiles = './ui/patient/uploads/'.$month."/";
        $config['upload_path']      = '/tmp';
        $config['allowed_types']    = 'gif|jpg|png|bmp|jpeg|GIF|JPG|PNG|BMP|JPEG';
        $config['file_name']		= $createFn;
        $config['max_size']         = 20480;
        $config['max_width']        = 0;
        $config['max_height']       = 0;
		mk_dir($config['upload_path']);
		$this->load->library('upload', $config);

		$configLarge1 = array();
		$configLarge1['image_library'] = 'gd2';
		$configLarge1['source_image'] = '';
		$configLarge1['create_thumb'] = 'TRUE';
		$configLarge1['maintain_ratio'] = TRUE; //保持图片比例
		$configLarge1['master_dim'] = 'auto';
		$configLarge1['quality'] = '80%';
		$configLarge1['width'] = 600;
		$configLarge1['height'] = 800;

		$configLarge = array();
		$configLarge['image_library'] = 'gd2';
		$configLarge['new_image'] = "";
		$configLarge['source_image'] = '';
		$configLarge['create_thumb'] = TRUE;
		$configLarge['maintain_ratio'] = TRUE; //保持图片比例
		$configLarge['master_dim'] = 'auto';
		$configLarge['width'] = 180;
		$configLarge['height'] = 180;
		$md5fn = md5($createFn);
		$arr_image = array();
		for($i = 0; $i < 15; $i++) {
			$temkey = 'file'.$i;
			$md5name = $md5fn."_".$i;
			$config['file_name'] = $md5name;
			$this->upload->initialize($config);
			if(empty($_FILES[$temkey]['size']) || @$_FILES[$temkey]['error']!=0){ continue;}
			
			$upload = $this->upload->do_upload($temkey);
			$error = array('error' => $this->upload->display_errors());

			if($upload === FALSE) continue;
			$data = $this->upload->data();//返回上传文件的所有相关信息的数组$
			if($data['is_image'] == 1) {
				$arr_image[] = $dbfile.$data['file_name'];
				$configLarge1['source_image'] = $data['full_path'];
				$configLarge1['new_image'] = $dbfiles.$data['file_name'];
				$configLarge1['dynamic_output'] = FALSE;
				$configLarge1['thumb_marker'] = "";
				$this->image_lib->initialize($configLarge1);
				$this->image_lib->resize();

				$configLarge['source_image'] = $data['full_path'];
				$configLarge['new_image'] = $dbfiles.$data['file_name'];
				$configLarge['dynamic_output'] = FALSE;
				$configLarge['thumb_marker'] = "_thumb";
				$this->image_lib->initialize($configLarge);
				$this->image_lib->resize();
			}
		}
		return $arr_image;
	}

	private function imgAngle($data){
		$configAngle = array();
		$configAngle['image_library'] = 'NetPBM';
		$configAngle['library_path'] = '/usr/bin/';
		$configAngle['source_image'] = $data['full_path'];
		$imgInfoAll = exif_read_data($data['full_path'],0,1);
		if(!empty($imgInfoAll['IFD0']['Orientation'])){
			$Orientation = $imgInfoAll['IFD0']['Orientation'];
			if($Orientation==8){
				$configAngle['rotation_angle'] = 90;
			}elseif($Orientation==6){
				$configAngle['rotation_angle'] = 270;
			}elseif($Orientation==3){
				$configAngle['rotation_angle'] = 180;
			}else{
				$configAngle['rotation_angle'] = 0;
			}
			if($configAngle['rotation_angle']>0){
				$this->image_lib->initialize($configAngle);
				$wang = $this->image_lib->rotate();
				echo $this->image_lib->display_errors();
				print_r($wang);exit;
			}
		}
	
	}

	

}


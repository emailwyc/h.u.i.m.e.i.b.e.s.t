<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$oss_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/oss/';
require_once($oss_file.'autoload.php');
use OSS\OssClient;
use OSS\Core\OssException;
/**
 * Util.php
 */
class Util extends CI_Controller {

    /** 
     * 构造方法
     * 
     * @param  null
     * @access public
     * @return void
     */
	public function __construct(){
		parent::__construct();
	}

	//
	public function ossUploadFile(){
		$data = $this->input->post();
		$cur_time = time();
		if(empty($data['msg']) || empty($data['class'])){ display(array(),3,"参数错误!"); }
		$classArr = array('avatar','doctor_article_icon','pat_article_icon','site_article_icon');
		if(!in_array($data['class'],$classArr)){ display(array(),3,"类型错误!"); }
		$accessKeyId = "uLmwkyi2tLw0pj7L"; ;
		$accessKeySecret = "DnNH0hXvDV2zqlf9HaCNrNpLwOBXIb";
		$endpoint = "oss-cn-beijing-internal.aliyuncs.com";
		try {
			$ossClient = new OssClient( $accessKeyId, $accessKeySecret, $endpoint);
			//保存到本地文件
			$filename = empty($data['filename']) ? md5($cur_time.getRandomPass(4)):$data['filename'];
			$streamFile = @base64_decode(urldecode($data['msg']));
			$temp_file = "/tmp/".$filename;
			$hder = "Content-Type: image/".$data['type']; header($hder);
			$file = fopen($temp_file,"w");//打开文件准备写入
			fwrite($file,$streamFile);//写入 fclose($file);//关闭
			//存储到OSS
			header("Content-type: text/html; charset=utf-8");
			$osspath = $data['class']."/".$filename;
			$ossClient->uploadFile('hm-img', $osspath, $temp_file);
		} catch (OssException $e) {
			display(array(),3,$e->getMessage());
		}
		$OssPath = 'http://hm-img.huimeibest.com/'.$osspath;
		display(array('path'=>$OssPath));
		
	}

    /** 
     * create doctor rqcode
     * 
     * @param  $status page dep hos kw sort
     * @access public
     * @return void
     */
	public function createDoctorRqcode(){
	//pass	
	}










}

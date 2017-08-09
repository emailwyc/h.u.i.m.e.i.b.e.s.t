<?php
defined('BASEPATH') OR exit('No direct script access allowed');
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
		//校验session， //session是否过期,
		//$this->Common_model->checkLogin();
	}

    /** 
     * 得到医生详情
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function getDetails() {
		$useInfo = $this->userOrVisitor();
		$pat_id = $useInfo['pat_id'];
		emptyCheck($this->params,array('_id'));
		$where = array('_id'=>getMdbId($this->params['_id']));
		$fields = array('_id','name','avatar','hospital','department','position','speciality','description');
		$result = $this->Common_model->getInfo('doctor',$where,$fields,true);
		if(empty($result)){ display(false,3,'医生不存在');}
		if(!empty($result)){
			//$pat_id = $this->Common_model->getPatId();
			if($pat_id){ $followInfo= $this->Common_model->getInfo($useInfo['table'],array('pat_id'=>$pat_id,'doc_id'=>$this->params['_id']));}
			$result['is_follow'] = !empty($followInfo) ? 1:0;
		}
		display($result,0,'ok','object');
	}

    /** 
     * 关注医生
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function follow() {
		$useInfo = $this->userOrVisitor();
		$pat_id = $useInfo['pat_id'];
		emptyCheck($this->params,array('doctor','status'));
		$where = array('pat_id'=>$pat_id,'doc_id'=>$this->params['doctor']);
		$where1 = array('_id'=>getMdbId($this->params['doctor']));
		$info = $this->Common_model->getInfo($useInfo['table'],$where);
		if($this->params['status']=="1"){//关注
			if($info){ display(false,3,"重复关注!"); }
			$insertInfo = array('pat_id'=>$pat_id,'doc_id'=>$this->params['doctor'],'created_at'=>time());
			$this->Common_model->insertInfo($useInfo['table'],$insertInfo);
			$this->Common_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
			display(true,0,"关注成功!");
		}elseif($this->params['status']==2){//取消
			if(!$info){ display(false,3,"已经取消!"); }
			$this->Common_model->deleteOneRecord($useInfo['table'],$where);
			$this->Common_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>-1,'mul_num'=>-1)));
			display(true,0,"取消成功!");	
		}else{
			display(false,2,"参数错误!");
		}
	}

    /** 
     * 扫码关注医生
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function scanFollow() {
		$useInfo = $this->userOrVisitor();
		$pat_id = $useInfo['pat_id'];
		emptyCheck($this->params,array('code'));
		$doc_id = authcode($this->params['code'],'DECODE');
		if(empty($doc_id)){ display(false,3,"参数错误!");}
		$where = array('pat_id'=>$pat_id,'doc_id'=>$doc_id);
		$where1 = array('_id'=>getMdbId($doc_id));
		$doctorInfo = $this->Common_model->getInfo('doctor',$where1);
		if(empty($doctorInfo)){display(false,3,'解析错误！');}
		$info = $this->Common_model->getInfo($useInfo['table'],$where);
		if($info){ display(array('doc_id'=>$doc_id),0,"关注成功!"); }
		$insertInfo = array('pat_id'=>$pat_id,'doc_id'=>$doc_id,'created_at'=>time());
		$this->Common_model->insertInfo($useInfo['table'],$insertInfo);
		$this->Common_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
		display(array('doc_id'=>$doc_id),0,"关注成功!");
	}

    /** 
     * 得到关注医生列表
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function followList() {
		$useInfo = $this->userOrVisitor();
		$pat_id = $useInfo['pat_id'];
		$page = getCurPage($this->params); $perpage = 10;
		$offset = getPage($page,$perpage);
		$where = array('pat_id'=>$pat_id);
		$result = $this->Common_model->getListInfo($useInfo['table'],$where,$offset,$perpage,array("_id"=>-1),array(),true);
		$docIds = getFieldArr($result,'doc_id',1);
		$docInfos = $this->Common_model->getInfoAll('doctor',array('_id'=>array('$in'=>$docIds)),'',array('name','hospital','position','speciality','avatar'),true);
		$docInfos= ArrKeyFromId($docInfos);
        foreach($result as $k=>$v){
            $result[$k]['doctor'] = !empty($docInfos[$v['doc_id']])?$docInfos[$v['doc_id']]:(object)array();
		}
		display($result,0,'ok','list');
	}

    /** 
     * 得到关注医生数量
	 * @params lookDoc
     * @access public
     * @return text/json
     */
	public function followCount() {
		$useInfo = $this->userOrVisitor();
		$pat_id = $useInfo['pat_id'];
		$where = array('pat_id'=>$pat_id);
		$result = $this->Common_model->getInfoCount($useInfo['table'],$where);
		display($result);
	}

	//判断游客和用户   desu
	public function userOrVisitor()
	{
		$sInfo = getClientHeaders();
		if(empty($sInfo['token'])){ //visitor
			if(empty($sInfo['id'])){display(false,3,"操作失败!");}
			$result['pat_id'] =  $sInfo['id'];//设备id  入参设备id必传
			$result['table'] =  'pat_visit_follow_doctor';//数据集合
		}else{
			$result['pat_id'] = (string)$this->Common_model->checkLogin();
			$result['table'] =  'pat_follow_doctor';//数据集合
		}
		return $result;
	}






}

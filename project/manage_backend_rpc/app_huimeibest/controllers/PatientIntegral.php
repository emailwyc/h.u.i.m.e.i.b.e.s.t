<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 8/23/16
 * Time: 3:01 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *patientIntegral.php
 */
class PatientIntegral extends CI_Controller
{

    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();checkLogin1();checkUserPower();
        $this->load->driver('cache', array('adapter' => 'apc'));
    }

    //添加integral
    public function add()
    {
        //post提交 integral名字
        emptyCheck(array('patient_id','number'));
        $params = safeParams($_POST);
        //patient exists
        $patientInfo = $this->Common_model->getInfo('pat_user',array('_id'=>getMdbId($params['patient_id'])));
        if(empty($patientInfo)){display(false,3,'patient user not find!');}
        //today  pat_integral sum
        $integralConfig = $this->Common_model->getInfo('pat_integral_config',array(),array('per_max_integral'));
        if((int)$params['number']>0 && $this->sumIntegral($params['patient_id'])>$integralConfig['per_max_integral']){
            display(false,3,'More than the upper limit of the day');
        }
        $stampTime = time();
        $insertInfo = array(
            'patient_id' => $params['patient_id'],
            'number' => (int)$params['number'],
            'source' => (string)$params['source'],
            'nickname' => $patientInfo['nickname'],
            'status'=> 1,
            'created_at' => $stampTime,);
        $insertRecord = $this->Common_model->insertInfo('pat_integral',$insertInfo);
        //update patient_user integral
        if($insertRecord){
            $this->Common_model->updateSetRecord('pat_user',array('_id'=>$patientInfo['_id']),array('$inc'=>array('integral'=>$insertInfo['number'])));
        }
        display();
    }

    //integral列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $allcount = $this->Common_model->getInfoCount('pat_integral',array());
        $allpage = ceil($allcount/$perpage);
        $integralConfig = $this->Common_model->getInfo('pat_integral_config',array());
        $startTime = time()-$integralConfig['usable_date'];
        $where['created_at'] = array('$gt'=>$startTime);
        $params = safeParams($_POST);
        if(isset($params['patient_id'])){  $where['patient_id'] = $params['patient_id'];}
        $result = $this->Common_model->getListInfo('pat_integral',$where,$offset,$perpage,array(),array(),false);
        if(empty($result)){display(array(),403,'暂无数据');}
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除integral
    public function del()
    {
        emptyCheck(array('_id'));
        $params = safeParams($_POST);
        $info = $this->Common_model->getInfo('pat_integral',array("_id"=>getMdbId($params['_id'])));
        if(empty($info)){   display(false,3,'integral recode not find!');}
        $delRe = $this->Common_model->deleteOneRecord('pat_integral',array("_id"=>$info['_id']));
        if($delRe){ display(array());
        }else{ display(array(),3,'操作失败');}
    }

    //修改integral数据
    public function update()
    {
        emptyCheck(array('_id'));
        $params = safeParams($_POST);
        $id = getMdbId($params['_id']);
        unset($params['_id']);
        $updateRe = $this->Common_model->updateRecord('pat_integral',array('_id'=>$id),$params);
        if($updateRe){  display(array());}else{   display(array(),1,'修改失败');}
    }

    //获取单个integral
    public function one()
    {
        emptyCheck(array('_id'));
        $params = safeParams($_POST);
        $id = getMdbId($params['_id']);
        $result = $this->Common_model->getInfo('pat_integral',array('_id'=>$id));
        if($result){ display($result);}else{ display(array(),3,'empty integral!');}
    }

    //integral config
    public function Config()
    {
        $result = $this->Common_model->getInfo('pat_integral_config',array());
        display($result);
    }

    //update pat_integral_config
    public function updateConfig()
    {
        emptyCheck(array('_id'));
        $params = safeParams($_POST);
        $result = $this->Common_model->updateRecord('pat_integral_config',array('_id'=>getMdbId($params['_id'])),$params);
        display();
    }

    //today time
    private function dateWhere()
    {
        $t = time(); //params date 2016-08-01
        $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        return array('start'=>$start,'end'=>$end);
    }

    //today  pat_integral sum
    private function sumIntegral($patient_id)
    {
        $dateWhere = $this->dateWhere();
        $where['created_at']['$gte'] = $dateWhere['start'];
        $where['created_at']['$lte'] = $dateWhere['end'];
        $where['patient_id'] = $patient_id;
        $pipeline= array(
            array('$match'=>$where),
            array('$group'  =>array(
                '_id'=>array('patient_id'=>'$patient_id'),
                'integralsum'=>array('$sum'=>'$number')
            )),
        );
        $options = array();
        $result = $this->Common_model->aggregate('pat_integral',$pipeline,$options);
        return $result[0]['integralsum'];
    }
}

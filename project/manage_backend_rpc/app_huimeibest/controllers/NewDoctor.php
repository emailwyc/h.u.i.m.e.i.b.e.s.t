<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$msg_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'../libraries/file/';
require_once $msg_file.'MKExcel.php';

/**
 * Login.php
 */
class newDoctor extends CI_Controller
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
    }

    public static $databaseTabelName = 'doctor_bak';

    /**
     * 用户登录
     */
    public function getlist()
    {
        $page = getCurPage();
        $perpage = 20;
        $offset = getPage($page, $perpage);
        $where = getWhereParams();
        $allcount = $this->Common_model->getInfoCount($this::$databaseTabelName,$where);
        $allpage = ceil($allcount/$perpage);
        $fields = array();
        $result = $this->Common_model->getListInfo($this::$databaseTabelName, $where, $offset, $perpage, "", $fields);
        display(array('page'=>$allpage,'data'=>$result));
    }
    
    public function getAll()
    {
        $where = getWhereParams();
        $result = $this->Common_model->getInfoAll($this::$databaseTabelName, $where);
        display($result);
    }

    public function getHospitalStatistics()
    {
        $pipeline = array(
            array(
                '$group' => array(
                    '_id' => '$hospital',
                    'value' => array(
                        '$sum' => 1
                    )
                )

            ),
            array(
                '$sort' => array(
                    'value' => -1
                )
            )
        );

        $result = $this->mdb->aggregate($this::$databaseTabelName, $pipeline);
        $array = array();
        foreach($result['result'] as $v){
            $t = array();
            $t['name'] = $v['_id'];
            $t['value'] = $v['value'];
            $array[] = $t;
        }
        display($array);
    }

    //获取医生详情
    public function getDoctorDetail(){
        emptyCheck(array('doctor'));
        $params= safeParams($_POST);
        $result = $this->Common_model->getInfo($this::$databaseTabelName, array('_id' => getMdbId1($params['doctor'])));
        if($result['d1']){ $result['d1_id'] = $this->Common_model->getInfo('department',array('name'=>$result['d1']),array('_id')); }
        display($result);
    }

    public function add()
    {
        emptyCheck(array('name'));
        $mongodate = new MongoDate(time());
        $insertData = array(
            "name" => $this->getPost('name'),
            "sex" => $this->getPost('sex'),
            "age" => $this->getPost('age'),
            "hospital" => $this->getPost('hospital'),
            "department" => $this->getPost('department'),
            "position" => $this->getPost('position'),
            "title" => $this->getPost('title'),
            "mobile" => $this->getPost('mobile'),
            "joinDate" => $this->getPost('joinDate'),
            "isSubscriptions" => $this->getPost('isSubscriptions'),
            "subscriptionsDate" => $this->getPost('subscriptionsDate'),
            "fans" => $this->getPost('fans'),
            "assistantName" => $this->getPost('assistantName'),
            "d1" => $this->getPost('d1'),
            "d2" => $this->getPost('d2'),
            "speciality" => $this->getPost('speciality'),
            "description" => $this->getPost('description'),
            "offlineOrderPrice" => $this->getPost('offlineOrderPrice'),
            "offlineOrderCount" => $this->getPost('offlineOrderCount'),
            "offlineIncome" => $this->getPost('offlineIncome'),
            "type" => $this->getPost('type', '0'),
            "level" => $this->getPost('level', '0')
        );
        $doctorId = $this->Common_model->insertInfo($this::$databaseTabelName, $insertData);
        display(array('insertId' => $doctorId));
    }

    public function update()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $doctorInfo = $this->Common_model->getInfo($this::$databaseTabelName, array('_id' => getMdbId($params['id'])));
        if (empty($doctorInfo)) {display(array(), 3, "未找到该医生，请检查!");}
        unset($params['id']);
        $result = $this->Common_model->updateRecord($this::$databaseTabelName, array('_id' => getMdbId($params['id'])), $params);
        display(array(), $result ? 0 : -1, $result ? "ok" : "更新失败");
    }

    public function del()
    {
        $id = $this->getPost('id');
        $doctorInfo = $this->Common_model->getInfo($this::$databaseTabelName, array('_id' => getMdbId1($id)));
        if (empty($doctorInfo)) {
            display(array(), 3, "未找到该医生，请检查!");
        }

        $result = $this->mdb->where($doctorInfo)->delete($this::$databaseTabelName);
        display(array(), $result ? 0 : -1, $result ? "ok" : "删除失败");
    }


    public function getPost($key, $value = "")
    {
        return isset($_POST[$key]) ? addslashes($_POST[$key]) : $value;
	}
	
	/*
	 *拓展医生下载
	 *params:void
	 *return: file download
	 */
    public function download()
    {
		$title = array('_id','name','sex','age','hospital','department','position','title','mobile','joinDate','isSubscriptions','subscriptionsDate','fans','assistantName','d1','d2','speciality','offlineOrderPrice','offlineOrderCount','offlineIncome','type');
		$result = $this->Common_model->getInfoAll($this::$databaseTabelName, array(),'',$title);
		$filename= "拓展医生".date('Ymd',time()).".xls";
		$xls = new ExportExcel($filename, "UTF-8");
		$xls->addArray($title);
		foreach($result as $k=>$v){
			$hang = array((string)$v['_id'],$v['name'],$v['sex'],$v['age'],$v['hospital'],$v['department'],$v['position'],$v['title'],$v['mobile'],$v['joinDate'],$v['isSubscriptions'],$v['subscriptionsDate'],$v['fans'],$v['assistantName'],$v['d1'],$v['d2'],$v['speciality'],$v['offlineOrderPrice'],$v['offlineOrderCount'],$v['offlineIncome'],$v['type']);
			$xls->addArray($hang);
		}
    }


}

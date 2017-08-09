<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Product.php
 */
class Product extends CI_Controller {

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

    //得到产品列表
    public function getList(){
        $page = getCurPage(); $perpage = 10;
        $offset = getPage($page,$perpage);
        //得到总分页
        $where = array('status'=>1);
        if(!empty($_REQUEST['classes'])){ $where['classes'] =  (int)$_REQUEST['classes']; }
        $like = !empty($_POST['key']) ? addslashes($_POST['key']):"";
		$allcount = $this->Common_model->getInfoCount('product',$where,array('title'),$like);// like 统计条数
        //产品列表
		$orderByArray = empty($_POST['orderBy']) ? array('_id'=>-1):array($_POST['orderBy']=>-1);
		$result = $this->Common_model->searchKeysLikes('product',$where,array(),array('title'),$like,$offset,$perpage, $orderByArray);
        if($result){
            foreach($result as $k=>$v){
                $result[$k]['created_at'] = date('Y-m-d',$v['created_at']);
                $result[$k]['time_start'] = empty($v['time_start'])?"无":date('Y-m-d',$v['time_start']);
                $result[$k]['time_end'] =   empty($v['time_end'])?"无":date('Y-m-d', $v['time_end']);
            }
        }
        display(array('page'=>ceil($allcount/$perpage),'data'=>$result));
	}


	public function getDetailsM(){
		emptyCheck(array('_id'));$params = safeParams($_POST);
		$where = array('_id'=>getMdbId($params['_id']));
		$result = $this->Common_model->getInfo('product',$where);
		if($result){
			$result['time_start'] = empty($result['time_start'])?"":date('Y-m-d',$result['time_start']);
			$result['time_end'] = empty($result['time_end'])?"":date('Y-m-d',$result['time_end']);
		}
		display($result);
	}

	//添加产品
	public function create(){
		emptyCheck(array('title','desc','classes','price'));
		$params = safeParams($_POST);
		$mongodate= time();
		$insertData = array(
			'title' => $params['title'],
			'desc' => $params['desc'],
			'price'=> (string)number_format($params['price'],2, '.', ''),
			'time_start' => (int)strtotime($params['time_start']),
			'time_end' => (int)strtotime($params['time_end']),
			'classes' =>(int)($params['classes']),
			'status' => 1,
			'created_at' => $mongodate,
			'updated_at' => $mongodate
		);
		$insertId =$this->Common_model->insertInfo('product',$insertData);
		if($insertId){
			display(array('insertId'=>$insertId));
		}else{
			display(array(),3,'插入数据失败');
		}
	}

	//编辑产品
	public function edit(){
		emptyCheck(array('_id','title','desc','classes','price'));
		$params = safeParams($_POST);
		$mongodate= time();
		$insertData = array(
			'title' => $params['title'],
			'desc' => $params['desc'],
			'price'=> (string)number_format($params['price'],2, '.', ''),
			'time_start' => (int)strtotime($params['time_start']),
			'time_end' => (int)strtotime($params['time_end']),
			'classes' =>(int)($params['classes']),
			'updated_at' => $mongodate
		);
		$check = $this->Common_model->updateRecord('product',array('_id'=>getMdbId($params['_id'])),$insertData);
		if($check){ display(array()); }else{ display(array(),3,"更新数据失败"); }
	}

	//删除产品
	public function delete(){
		emptyCheck(array('_id'));
		$insertId = $this->Common_model->updateRecord('product',array('_id'=>getMdbId($_POST['_id'])),array('status'=>0));
		if($insertId){
			display(array(),0,"删除成功!");
		}else{
			display(array(),3,"删除失败!");
		}
	}

}

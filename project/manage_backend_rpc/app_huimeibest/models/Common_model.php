<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	public function getListInfo($table,$where,$offset,$perpage=20,$sort="",$field=array(),$idStr){
		//add cache
		$info = $this->mdb->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get($table,$idStr);
		return $info;
	}
	public function getInfoCount($table,$where,$aimFields=array(),$likeVal='') {
		for ($i=0 ;$i<count($aimFields);$i++){
			$this->mdb->likes($aimFields[$i],$likeVal);
		}
		$info = $this->mdb->where($where)->count($table);
		return $info;
	}
	public function getTableByRef($table,$ref) {
		//add cache
		$info = $this->mdb->getRef($table,$ref);
		return $info;
	}
	public function getInfoAll($table,$where,$sort="",$fields=array(),$idIsStr = false) {
		$info = $this->mdb->where($where)->order_by($sort)->select($fields)->get($table,$idIsStr);
		if(!empty($info)){
			return $info;
		}else{
			return array();
		}
	}
	public function getInfo($table,$where,$fields=array()) {
		$info = $this->mdb->where($where)->select($fields)->limit(1)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			return array();
		}
	}

	public function getInfoSort($table,$where,$fields=array(),$sort="") {
		$info = $this->mdb->where($where)->order_by($sort)->select($fields)->limit(1)->get($table);
		if(!empty($info)){
			return $info[0];
		}else{
			return array();
		}
	}
	public function updateSetRecord($table,$where,$update) {
		//add cache
		$check = $this->mdb->where($where)->updateset($table,$update);
		if(!empty($check)){
			return true;
		}else{
			return false;
		}
	}
	public function updateRecord($table,$where,$update) {
		//add cache
		$check = $this->mdb->where($where)->update($table,$update);
		if(!empty($check)){
			return true;
		}else{
			return false;
		}
	}
	public function insertInfo($table,$info){
		$check = $this->mdb->insert($table,$info);
		if(!empty($check)){
			return $check;
		}else{
			return false;
		}
	}

	public function deleteOneRecord($table,$where) {
		$check = $this->mdb->where($where)->delete($table);
		return $check;
	}

   /** 
   * 得到table分组信息
    */
    public function getGroupInfo($table, $keys, $initial, $reduce, $options =array()){
		$Info = $this->mdb->group($table,$keys,$initial,$reduce,$options);
        if($Info)
            return $Info;
        else{
            return array();
        }
	}

   /** 
   * 聚合查询
    */
    public function aggregate($table,$pipeline,$options =array()){
		$Info = $this->mdb->aggregate($table,$pipeline,$options);
        if($Info['ok'])
            return $Info['result'];
        else{
            return array();
        }
    }

	//批量插入
	public function batchInsert($table,$date)
	{
		$result = $this->mdb->batchinsert($table,$date);
		if($result){return true;}
		else{return false;}
	}
	//模糊查询
	public function searchLikes($table,$fields=array(),$aimField,$likeVal,$where)
	{
		$info = $this->mdb->likes($aimField,$likeVal)->select($fields)->limit(10)->where($where)->get($table);
		return $info;
	}

	//多字段模糊查询
	public function searchKeysLikes($table,$where,$fields,$aimFields,$likeVal,$offset,$perpage=20,$sort="")
	{
		if(empty($aimFields)){
			return false;
		}
		for ($i=0;$i<count($aimFields);$i++){
			$this->mdb->likes($aimFields[$i],$likeVal);
		}
		$info = $this->mdb->where($where)->order_by($sort)->select($fields)->limit($perpage)->offset($offset)->get($table);
		return $info;
	}
	
	/**
	 * 极光推送
	 * @param $alertMessage		提示消息
	 * @param $content			消息内容
	 * @param $msg_title		推送标题
	 * @param $type				消息类型
	 * @param array $extras		附加消息
	 * @return array|object
	 */
	public function jPush($alertMessage,$extras=array())
	{
		require_once("src/JPush/JPush.php");
		$app_key = $this->config->item('jg_app_key');
		$master_secret = $this->config->item('jg_master_secret');
		$client = new JPush($app_key, $master_secret,'./src/JPush/jpush.log');
		$result = $client->push()
			->setPlatform('all')   //推送平台ios android
			->setAudience('all')	//推送用户
			->addIosNotification($alertMessage, 'iOS sound', '+1', true, 'iOS category', $extras)
			->addAndroidNotification($alertMessage, null, 1, $extras)
			->send();
		return $result;
	}
}


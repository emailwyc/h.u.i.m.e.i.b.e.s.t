<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	public function getListInfo($table,$where,$offset,$perpage=20,$sort="",$field=array(),$idIsStr = false){
		//add cache
		$info = $this->mdb->where($where)->order_by($sort)->select($field)->limit($perpage)->offset($offset)->get($table,$idIsStr);
		return $info;
	}
	public function getInfoCount($table,$where) {
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
	public function getInfo($table,$where,$fields=array(),$idIsStr=false) {
		$info = $this->mdb->where($where)->select($fields)->limit(1)->get($table,$idIsStr);
		if(!empty($info)){
			return $info[0];
		}else{
			return array();
		}
	}

	public function getInfoSort($table,$where,$fields=array(),$sort="",$idIsStr = false) {
		$info = $this->mdb->where($where)->order_by($sort)->select($fields)->limit(1)->get($table,$idIsStr);
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
			return $check;
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

	//删除多条
	public function deleteAllRecord($table,$where)
	{
		$check = $this->mdb->where($where)->delete_all($table);
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

   /** 
	* 验证Session
    */
	public function checkLogin(){
		//得到Session
		$sInfo = getClientHeaders();
		if(empty($sInfo['token'])){ display(array(),403,"授权失败，请登录!"); }
		$ctime = time();
		//db查找Session
		$patInfo = $this->Common_model->getInfo('pat_user_session',array('session_token'=>$sInfo['token']));
		if(empty($patInfo)){
			//重新登录
			display(array(),403,"授权失败，请重新登录!");
		}else{
			if($this->config->item("is_login_expired")){
				$expiredTime = $ctime-($this->config->item("is_login_expired_time")*86400);
				if($patInfo['actived_at']<$expiredTime){
					 display(array(),402,"客官您已长时间未操作，请重新登录!");
				}
			}
			//更新返回
			$this->updateRecord('pat_user_session',array('_id'=>$patInfo['_id']),array('actived_at'=>$ctime));
			$this->updateRecord('pat_user',array('_id'=>$patInfo['_id']),array('actived_at'=>$ctime));
			return $patInfo['_id'];
		}

	}

   /** 
	* 得到用户id
    */
	public function getPatId(){
		//得到Session
		$sInfo = getClientHeaders();
		if(empty($sInfo['token'])){ return false; }
			$ctime = time();
		//db查找Session
		$patInfo = $this->Common_model->getInfo('pat_user_session',array('session_token'=>$sInfo['token']));
		if(empty($patInfo)){
			return false;
		}else{
			return $patInfo['_id'];
		}

	}

	//多字段模糊查询  desu
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

	//模糊查询
	public function searchLikes($table,$fields=array(),$perpage,$aimField,$likeVal,$idIsStr = false,$where,$sort,$offset)
	{
		$info = $this->mdb->likes($aimField,$likeVal)->select($fields)->where($where)->limit($perpage)->offset($offset)->order_by($sort)->get($table,$idIsStr);
		return $info;
	}

	//批量插入
	public function batchInsert($table,$date)
	{
		if(!empty($date)){
			$result = $this->mdb->batchinsert($table,$date);
			if($result){return true;}
			else{return false;}
		}
	}

}


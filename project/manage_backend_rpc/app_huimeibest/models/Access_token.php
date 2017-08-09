<?php 
class Access_token extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	private $appid = 'wxfa6e857e77c9fe1e';
	private $appsecret = '440450a73c3c3d8b22fdfb953aa30217';
	private $table = 'mx_config';

	/*获取新的TOKEN*/
	public function mkToken(){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
        $res = httpRequest($url);
        $result = json_decode($res, true);
        $access_token = $result["access_token"];
        return $access_token;
	}
	/*外部调用TOKEN*/
	public function getToken(){
		$where = array(
			'Key' => 'Token_Time'
			);
		$res = $this->selectToken($where);

		if($res['0']['Value'] < time() - 7000){
			$access_token = $this->mkToken();
			$this->newToken($access_token);
		}else{
			$where = array(
				'Key' => 'Token'
				);
			$res = $this->selectToken($where);
			$access_token = $res['0']['Value'];
		}
		return $access_token;
	}
	//查找数据库中的access_token和Token_Time
	public function selectToken($where){
		$this->db->where($where);
		$this->db->select('Value');
		$query = $this->db->get('mx_config');
		$res = $query->result_array();
		return $res;
	}
	//更新数据库中的access_token
	public function newToken($access_token){
		$where = array(
			'Key' => 'Token'
			);
		$data = array(
			'Value' => $access_token
			);
		$this->db->where($where);
		$this->db->update($this->table,$data);
		$where1 = array(
			'Key' => 'Token_Time'
			);
		$data1 = array(
			'Value' => time()
			);
		$this->db->where($where1);
		$this->db->update($this->table,$data1);
	}
}
?>

<?php
class WxMenu extends CI_Controller{
	private $appId = "";
	function __construct(){
		parent::__construct();
		$this->appId = $this->config->item('global_wx_appid');
		$this->load->model('jssdk_model');
		$this->load->helper('weixin_helper');
	}
	private function index3(){
		$this->deleteMenu();
		$token = $this->jssdk_model->getAccessToken();
		$data = '{
			     	"button":[
				      	{
				               "type":"view",
				               "name":"找明医",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/home/index?auth=wx",$this->appId,'homeindex').'"
				       	},
						{
				           "name":"文章&活动",
				           "sub_button":[
								{	
								   "type":"view",
								   "name":"义诊活动",
								   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/act/doclist?auth=wx",$this->appId,'actdoclist').'"
								},
								{	
								   "type":"view",
								   "name":"订阅文章",
								   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/act/article?auth=wx",$this->appId,'actarticle').'"
								},
								{	
								   "type":"view",
								   "name":"精品文章",
								   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/act/artwell?auth=wx",$this->appId,'actartwell').'"
								},
								{
								   "type":"view",
								   "name":"近期活动",
								   "url":"http://mp.weixin.qq.com/s?__biz=MzI1MjA2NTMwMQ==&mid=2652401152&idx=1&sn=0cea7fe1e3f076dddfcf5a4b777d308a&scene=0"
								}
							]
						},
						{
				           "name":"我",
				           "sub_button":[
								{	
								   "type":"view",
								   "name":"我的医生",
								   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/doctor?auth=wx",$this->appId,'userdoctor').'"
								},
								{
								   "type":"view",
								   "name":"就诊人列表",
								   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/contact?auth=wx",$this->appId,'usercontact').'"
								},
								{
								   "type":"view",
								   "name":"就诊记录",
								   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/service?auth=wx",$this->appId,'usercontact').'"
								},
								{
								   "type":"click",
								   "name":"客服热线",
								   "key":"about_csphone"
								},
								{
								   "type":"view",
								   "name":"邀请好友",
								   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/share/qrcode?auth=wx",$this->appId,'sharecode').'"
								}
							]
						}
					] 
		}';
		$ok = create_menu($data,$token);	 
		var_dump($ok);	
	}
	private function index2(){
		$this->deleteMenu();
		$token = $this->jssdk_model->getAccessToken();
		$data = '{
			     	"button":[
				      	{
				               "type":"view",
				               "name":"找明医",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/home/index?auth=wx",$this->appId,'homeindex').'"
				       	},
				      	{
				               "type":"view",
				               "name":"消息",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/service?auth=wx",$this->appId,'userservice').'"
				       	},
						{
				           "name":"我的",
				           "sub_button":[
				            {	
				               "type":"view",
				               "name":"我的医生",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/doctor?auth=wx",$this->appId,'userdoctor').'"
				            },
				            {
				               "type":"view",
				               "name":"家庭联系人",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/contact?auth=wx",$this->appId,'usercontact').'"
				            }]
				       	}] 
		}';
		$ok = create_menu($data,$token);	 
		var_dump($ok);	
	}
	private function index1(){
		$this->deleteMenu();
		$token = $this->jssdk_model->getAccessToken();
		$data = '{
			     	"button":[
				      	{
				               "type":"view",
				               "name":"找明医",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/home/index?auth=wx",$this->appId,'homeindex').'"
				       	},
				      	{
				               "type":"view",
				               "name":"消息",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/service?auth=wx",$this->appId,'userservice').'"
				       	},
						{
				           "name":"我的",
				           "sub_button":[
				            {	
				               "type":"view",
				               "name":"我的医生",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/doctor?auth=wx",$this->appId,'userdoctor').'"
				            },
				            {
				               "type":"view",
				               "name":"家庭联系人",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/contact?auth=wx",$this->appId,'usercontact').'"
				            },
				            {	
				               "type":"view",
				               "name":"用户微信群",
				               "url":"http://h5test.huimeibest.com/about/wxgroup"
				            },
				            {
				               "type":"view",
				               "name":"企业合作",
				               "url":"http://h5test.huimeibest.com/about/join"
				            },
				            {	
				               "type":"view",
				               "name":"客服热线",
				               "url":"http://h5test.huimeibest.com/about/csphone"
				            }]
				       	}] 
		}';
		$ok = create_menu($data,$token);	 
		var_dump($ok);	
	}
	private function index(){
		$wang = weixin_redirect_uri($this->config->item('global_base_url')."/user/service?auth=wx",$this->appId,'userservice');
		$this->deleteMenu();
		$token = $this->jssdk_model->getAccessToken();
		$data = '{
			     	"button":[
				      	{
				               "type":"view",
				               "name":"找医生",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/home/index?auth=wx",$this->appId,'homeindex').'"
				       	},
						{
				           "name":"我的",
				           "sub_button":[
				           {	
				               "type":"view",
				               "name":"我的咨询",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/service?auth=wx",$this->appId,'userservice').'"
				            },
				            {	
				               "type":"view",
				               "name":"我的医生",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/doctor?auth=wx",$this->appId,'userdoctor').'"
				            },
				            {
				               "type":"view",
				               "name":"家庭联系人",
							   "url":"'.weixin_redirect_uri($this->config->item('global_base_url')."/user/contact?auth=wx",$this->appId,'usercontact').'"
				            }]
				       	},
						{
				           "name":"更多",
				           "sub_button":[
				           {	
				               "type":"view",
				               "name":"用户微信群",
				               "url":"http://h5test.huimeibest.com/about/wxgroup"
				            },
				            {	
				               "type":"view",
				               "name":"客服热线",
				               "url":"http://h5test.huimeibest.com/about/csphone"
				            },
				            {
				               "type":"view",
				               "name":"企业合作",
				               "url":"http://h5test.huimeibest.com/about/join"
				            }]
				       	}] 
		}';
		$ok = create_menu($data,$token);	 
		var_dump($ok);	
	}
	//删除菜单
	private function deleteMenu()
	{
		$token = $this->jssdk_model->getAccessToken();
		delete_menu($token);
	}
	private function ok()
	{
		$token = $this->jssdk_model->getAccessToken();
		echo $token;
		
	}
}
?>

<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("PRC");
//header("Content-type: text/html; charset=utf-8");

if(!function_exists('create_qrcode')){
    /* 生成二维码 */
    function create_qrcode($scene_type, $scene_id,$token) {
        switch($scene_type)
        {
            case 'QR_LIMIT_STR_SCENE': //永久字符串
                $data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": '.$scene_id.'}}}';
                break;
            case 'QR_LIMIT_SCENE': //永久
                $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
                break;
            case 'QR_SCENE':       //临时
                $data = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
                break;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$token;
        $res = httpRequest($url, $data);
        $result = json_decode($res, true);
		if(isset($result["ticket"])){
			return $result;
			//"https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($result["ticket"]);
		}else{
			return false;
		}
    }
}

if(!function_exists('get_user_info')){
    /*
    获取用户基本信息
    */
    function get_user_info($openid,$token)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$openid."&lang=zh_CN";
        $res = httpRequest($url);
        return json_decode($res, true);
    }
}

if(!function_exists('create_menu')){
    /*
    创建菜单
    */
    function create_menu($data,$token)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$token;
        $res = httpRequest($url, $data);
        return json_decode($res, true);
    }
}
if(!function_exists('delete_menu')){
    /*
    删除菜单
    */
    function delete_menu($token)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$token;
        httpRequest($url);
    }
}
if(!function_exists('send_custom_message')){
    /*
    发送客服消息
    */
    function send_custom_message($touser, $type, $data,$token)
    {
        $msg = array('touser' =>$touser);
        switch($type)
        {
            case 'text':
                $msg['msgtype'] = 'text';
                $msg['text']    = array('content'=> urlencode($data));
                break;
            case 'news':
                $msg['msgtype'] = 'news';
                $msg['news']    = array('articles'=>$data);
                break;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$token;
        return httpRequest($url, urldecode(json_encode($msg)));
    }
}

if(!function_exists('create_group')){
    /*
    创建分组
    */
    function create_group($name,$token)
    {
        $data = '{"group": {"name": "'.$name.'"}}';
        $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=".$token;
        $res = httpRequest($url, $data);
        return json_decode($res, true);
    }
}

if(!function_exists('update_group')){
    /*
    移动用户分组
    */
    function update_group($openid, $to_groupid,$token)
    {
        $data = '{"openid":"'.$openid.'","to_groupid":'.$to_groupid.'}';
        $url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=".$token;
        $res = httpRequest($url, $data);
        return json_decode($res, true);
    }
}

if(!function_exists('upload_media')){
    /*
    上传多媒体文件
    */
    function upload_media($type, $file,$token)
    {
        $data = array("media"  => "@".dirname(__FILE__).'\\'.$file);
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$token."&type=".$type;
        $res = httpRequest($url, $data);
        return json_decode($res, true);
    }
}
    /*
    *卡券使用接口
    */
 if(!function_exists('card_white')){
    /*
    定义调试白名单
    */
    function card_white($data,$token)
    {
        $url = "https://api.weixin.qq.com/card/testwhitelist/set?access_token=".$token;
        $res = httpRequest($url, $data);
        return json_decode($res, true);
    }
}

if(!function_exists('weixin_redirect_uri')){
    function weixin_redirect_uri($uri,$appid,$stat) {
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($uri)."&response_type=code&scope=snsapi_base&state=".$stat."#wechat_redirect";
		return $url;
    }
}


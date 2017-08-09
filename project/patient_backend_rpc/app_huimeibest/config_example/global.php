<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//维信相关配置
$config['global_wx_appid'] = "wx8a4d255ba2325887";
$config['global_wx_appsecret'] = "0e8cfd6fe71cdbef0a62dc6b8f2c3b1b";
$config['global_wx_token'] = "huimeibesttest11";
$config['global_wx_payurl'] = "http://h5test.huimeibest.com/wxpay_pub/WebPay/js_api_call.php";
$config['global_wx_pubts'] = "";
$config['global_wx_template1'] = "GlpT0GEBJmhe5zmiywcO4IevnKysFS01B0FHMFojyIY";//预约挂号成功模板id
$config['global_wx_template2'] = "eP-5YmWKArkbQKS2ELA4SpS7m07BN9gs261ghz27JcI";//预约挂号成功模板id
$config['global_wx_template3'] = "0rXuc_OqgQVDnaWKNSFVv0DVGd8pjqQ-FZv9D6fwOKA";//invi
$config['global_wx_template4'] = "oEpkLbQT1jCXMdYiV4RTenLCZxyq79vIZj11dsGfn7g";//医生推送给患者消息
$config['global_wx_template5'] = "d2yFL9C4T-NG59BOXzwASavhn4uoDfcjJ2fsDcWvg1k";//电话订单推送
//环信配置
$config['global_hx_appkey'] = "110102018872160#doctor-staging";
$config['global_hx_orgname'] = "110102018872160";
$config['global_hx_appname'] = "doctor-staging";
$config['global_hx_cli_id'] = "YXA6EhWFQGanEeWW63-U852OBw";
$config['global_hx_cli_secret'] = "YXA6oFmC75zj470b15DQNnHizBvVxMo";
//短信配置
$config['msg_config']['appid'] = '10503';
$config['msg_config']['appkey'] = 'a235d82d1be5703dd1503e0cea4bd875';
$config['msg_config']['sign_type'] = 'normal';
//global
$config['global_version'] = '1.1.1';//版本号
$config['global_doc_secret'] = 'e139d12d1be57e3dd15p3e4ce34bd975';//医生端
$config['global_doc_url'] = 'http://api-staging.huimeibest.com/hooks/order/';//医生端
$config['global_doc_act'] = '561c9c78f154d653238b456b';


$config['global_api_secret'] = 'e139d82d1be57w3dd15p3e4cea4bd875';
$config['global_api_openip'] = array();

$config['global_base_url'] = "http://h5test.huimeibest.com";
$config['global_cron_clikey'] = "hm613536";
$config['global_mobile_format'] = "/^1[34578]\d{9}$/";
$config['global_sort_weight'] = "/^1[34578]\d{9}$/";
$config['global_path_storage'] = "./app_huimeibest/storage/";
$config['global_storage_hxset'] = $config['global_path_storage']."hx_auth.txt";
$config['global_coupons_type'] = array('1'=>1,'2'=>2,'3'=>3,'4'=>5,'5'=>8,'6'=>10,'7'=>20,'8'=>30,'9'=>50,'10'=>80);
$config['global_noMsg'] = 0;

<?php
/**
 * JS_API支付demo
 * ====================================================
 * 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
 * 成功调起支付需要三个步骤：
 * 步骤1：网页授权获取用户openid
 * 步骤2：使用统一支付接口，获取prepay_id
 * 步骤3：使用jsapi调起支付
*/
header("Content-Type: text/html; charset=utf-8");
require_once ('../pay_lib/config.php');
require_once '../pay_lib/Mdb.php';
require_once '../pay_lib/helper.php';
require_once '../pay_lib/log.php';
include_once("../WxPayPubHelper/WxPayPubHelper.php");
//使用jsapi接口
//=========步骤1：网页授权获取用户openid============
//通过code获得openid
$jsApi = new JsApi_pub();
if (!isset($_GET['code'])) {
	//触发微信返回code码
	$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
	$url = $jsApi->createOauthUrlForCode($baseUrl);
	Header("Location: $url");exit;
}else {
	//获取code码，以获取openid
	$code = $_GET['code'];
	$jsApi->setCode($code);
	$openid = (string)trim($jsApi->getOpenid());
}
$cur_time = time();
$sign = $_GET['sign'];
$sign = urldecode($sign);
if(empty($sign)){ show_error("参数错误!"); }
$paramJson = authcode($sign,$operation = 'DECODE');
$params = @json_decode($paramJson,true);
if(empty($params['oid']) || empty($params['type']) || empty($params['timestamp'])){ show_error("参数错误!"); }
if(($params['timestamp']+4)<$cur_time){
    echo "<script type=\"text/javascript\">";
    echo "window.history.go(-2);";
    echo "</script>";exit;
}
$userOpenid = (string)$openid;
if(empty($userOpenid)){ show_error("授权失败,请退出重新支付!"); }
$Mdb = new Mdb();
//得到订单
$obj_id = getMdbId($params['oid']);
$orderInfo = $Mdb->where(array('_id'=>$obj_id))->limit(1)->get("order_qrcode");
if(!empty($orderInfo)){
	$orderInfo = $orderInfo[0];
}else{
	show_error("失效的订单,请检查!");
}
if($orderInfo['status']!=0 || $orderInfo['service']!=$params['type']){ show_error("订单不符合规则!"); }
//更新订单

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//根据订单数据，得到微信参数（）；
$data = wxPayData($orderInfo);
if((int)$data['total_fee']<=0){
	show_error("金额错误!");
}

	
	//=========步骤2：使用统一支付接口，获取prepay_id============
	//使用统一支付接口
	$unifiedOrder = new UnifiedOrder_pub();
	
	//设置统一支付接口参数
	//设置必填参数
	//appid已填,商户无需重复填写
	//mch_id已填,商户无需重复填写
	//noncestr已填,商户无需重复填写
	//spbill_create_ip已填,商户无需重复填写
	//sign已填,商户无需重复填写
	$unifiedOrder->setParameter("openid","$openid");//商品描述
	$unifiedOrder->setParameter("body",$data['body']);//商品描述
	//自定义订单号，此处仅作举例
	$timeStamp = time();
	$jsTime = ($timeStamp+4)*1000;
	$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
	$unifiedOrder->setParameter("out_trade_no",$data['out_trade_no']);//商户订单号 
	$unifiedOrder->setParameter("total_fee",$data['total_fee']);//总金额
	$unifiedOrder->setParameter("notify_url",$data['notify_url']);//通知地址 
	$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
	//非必填参数，商户可根据实际情况选填
	//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
	//$unifiedOrder->setParameter("device_info","XXXX");//设备号 
	//$unifiedOrder->setParameter("attach","XXXX");//附加数据 
	$unifiedOrder->setParameter("time_start",date("YmdHis",time()));//交易起始时间
	$unifiedOrder->setParameter("time_expire",date("YmdHis", time()+290));//交易结束时间 
	//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
	//$unifiedOrder->setParameter("openid","XXXX");//用户标识
	//$unifiedOrder->setParameter("product_id",$data['out_trade_no']);//商品ID

	$prepay_id = $unifiedOrder->getPrepayId();
	//=========步骤3：使用jsapi调起支付============
	$jsApi->setPrepayId($prepay_id);

	$jsApiParameters = $jsApi->getParameters();
	//echo $jsApiParameters;
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <title>微信安全支付</title>
    <script src="/ui/js/jquery-1.12.0.min.js"></script>
	<script type="text/javascript">
		var timestamp = new Date().getTime();
		var reqtime = <?php echo $jsTime;?>;
		var order_id = "<?php echo $obj_id;?>";
		//调用微信JS api 支付
		function jsApiCall() {
			WeixinJSBridge.invoke(
				'getBrandWCPayRequest',
				<?php echo $jsApiParameters; ?>,
				function(res){
					WeixinJSBridge.log(res.err_msg);
					//alert(res.err_code+res.err_desc+res.err_msg);
					switch (true) {
						// 用户取消
						case /\:cancel$/i.test(res.err_msg) :
							ajax(order_id,1);
							break;
						case /\:(|ok)$/i.test(res.err_msg):
							window.location.href="<?=@$data['url_success']?>";
							//自己买，支付完成的回调地址	/service/topic/fudai/song_order.php?payType=*&orderType=wo
							//送给TA，支付完成的回调地址	/service/topic/fudai/song_order.php
							//TA给我买，支付完成的回调地址	/service/topic/fudai/mai.php?id
	//						if( /\/Payment\/payOrder\/\?sign/i.test(backUrl) ){
	//							window.location.href='/Payment/success/?orderId=<?//=$orderId?>//';
	//						}else{
	//							window.location.href='/Order/payResults/?base_pay_type_id=2&order_price=<?//=$price?>//&id=<?//=$orderId?>//';
	//						}
							//window.location.href='/Address/paySuccess/?payOrderId=<?=$orderId?>';

							break;
						// fail　发送失败
						case /\:fail$/i.test(res.err_msg) :
						default:
							ajax(order_id,2);
							break;
					}
				}
			);
		}

		function callpay() {
			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			    }else if (document.attachEvent){
			        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
			        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			    }
			}else{
			    jsApiCall();
			}
		}
		window.onload = function(){
			callpay();
		};
		function ajax(id,num){ $.ajax({ type: "post", url: "/Json/codepayFail", dataType: "json",	data:{"order_id":id,"st":num}, success: function (data){ window.history.go(-1); }, error:function(msg){ window.history.go(-1); } }); }
	</script>
</head>
<body>
	</br></br></br></br>
	<div align="center">
	</div>
</body>
</html>

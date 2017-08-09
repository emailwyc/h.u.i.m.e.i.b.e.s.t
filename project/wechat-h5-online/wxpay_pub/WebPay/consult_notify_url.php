<?php
/**
 * 通用通知接口demo
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 * 
 * 这里举例使用log文件形式记录回调信息。
*/
	require_once ('../pay_lib/config.php');
	require_once '../pay_lib/Mdb.php';
	require_once '../pay_lib/helper.php';
	include_once("./log_.php");
	include_once("../WxPayPubHelper/WxPayPubHelper.php");
	$cur_time = time();
	$log_ = new Log_();
	$log_name= "../logs/".date('Y-m-d').'.log';

    //使用通用通知接口
	$notify = new Notify_pub();

	//存储微信的回调
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	
	$notify->saveData($xml);
	
	//验证签名，并回应微信。
	//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
	//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
	//尽可能提高通知的成功率，但微信不保证通知最终能成功。
	if($notify->checkSign() == FALSE){
		$notify->setReturnParameter("return_code","FAIL");//返回状态码
		$notify->setReturnParameter("return_msg","签名失败");//返回信息
	}else{
		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	}
	
	//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
	
	//以log文件形式记录回调信息
	$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

	if($notify->checkSign() == TRUE)
	{
		if ($notify->data["return_code"] == "FAIL") {
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
		}
		elseif($notify->data["result_code"] == "FAIL"){
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
		}
		else{
			$Mdb = new Mdb();
			//此处应该更新一下订单状态，商户自行增删操作
			$rData = $notify->getData();
			$log_->log_result($log_name,"【orderInfo】:\n".json_encode($rData)."\n");
			if(!empty($rData['out_trade_no'])){
				$returnXml = $notify->returnXml();
				//得到数据 
				$where = array('_id'=>getMdbId($rData['out_trade_no']));
				$info = $Mdb->where($where)->limit(1)->get('order');
				if(!empty($info)){
					$info = $info[0];
					if($info['service']=="consult" && $info['status']=="新订单"){
						$cur_date = new MongoDate($cur_time);
						$updateOrder = array('$set'=>array('status'=>"已支付",'updated_at'=>$cur_date,'pay_at'=>(string)$cur_time));
						$check = $Mdb->where($where)->updateset('order',$updateOrder);
						if($check){
							echo $returnXml; 
							$url = $config['global_base_url']."/Callback/consult_pay";
							$postdata = http_build_query(array( "orderId" =>(string)$info['_id']));
							$order_data = do_post_request($url, $postdata);
							$log_->log_result($log_name,(string)$order_data);
						}
						exit;

					}
				}

				//校验订单

				//更新订单
			}
			
		}
		
		//商户自行增加处理流程,
		//例如：更新订单状态
		//例如：数据库操作
		//例如：推送支付完成信息
	}
?>

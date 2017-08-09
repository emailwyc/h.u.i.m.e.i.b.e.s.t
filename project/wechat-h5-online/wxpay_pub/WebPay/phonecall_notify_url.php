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
		} else{
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
					if($info['service']=="phonecall" && $info['status']=="新订单"){
						//处理订单
						$cur_date = new MongoDate($cur_time);
						$updateOrder = array('$set'=>array('status'=>"已支付",'updated_at'=>$cur_date));
						$check = $Mdb->where($where)->updateset('order',$updateOrder);
						if($check){
						   	echo $returnXml;
							$url = $config['global_base_url']."/Callback/phonecall_pay";
							$postdata = http_build_query(array( "orderId" =>(string)$info['_id']));
							$order_data = do_post_request($url, $postdata);
							$log_->log_result($log_name,(string)$order_data);
						}
						exit;
						/*	
						//处理医生日程
						$where2 = array('_id'=>@$info['doctor_timetable']['$id']);
						$key_up = "showtimes.".$info['hours'];
						$update2= array('$inc'=>array('quantity'=>1),'$set'=>array($key_up=>0));
						$Mdb->where($where2)->updateset('doctor_timetable',$update2);
						//处理医生关注
						$where1 = array('_id'=>@$info['doctor']['$id']);
						$update1= array('$inc'=>array('phonecall_num'=>1,'mul_num'=>2,'rc_num'=>1));
						$Mdb->where($where1)->updateset('doctor',$update1);
						$order_id = $rData['out_trade_no'];
						$order_id_md5 = md5($order_id.'huimei123456');
						$ts_url = $config['global_doc_url'].$order_id."/".$order_id_md5;
						$tuisong = httpGet($ts_url);
						//推送消息
						$docInfo= $Mdb->getRef('doctor',$info['doctor']);
						$postdata = array();
						$postdata['mobile'] = $info['mobile'];
						$postdata['name'] = $info['name'];
						$postdata['hospital'] = @$docInfo['hospital'];
						$postdata['dep'] = @$docInfo['department'];
						$postdata['doctor'] = @$docInfo['name'];
						$postdata['time'] = (string)date('Y-m-d H:i',$info['schedule']->sec);
						$postdata['hours'] = $info['hours'];
						$postdata['type'] = "phonecall";
						$postdata = http_build_query($postdata);
						$url = $config['global_base_url']."/Callback/msg_push";
						$order_data = do_post_request($url, $postdata);
						*/
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

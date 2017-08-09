<?php
class Weixin extends CI_Controller
{
	private $appId = "";
    private $FromUserName = '';
	private $ToUserName   = '';
	private $custom_message ="";
	private $wx_scan = "欢迎关注【找明医】！\n找明医平台汇聚知名三甲医院副主任级别以上专家，提供图文、电话多种问诊方式。你可以<a href='http://h5.huimeibest.com'>点击“找明医”</a>根据自己的疾病状况选择医院、医生或拨打热线: 400-068-6895 进行快约。\n有问题也可直接留言，我们会尽快为您解答！";
	private $wx_scan1 = "感谢您关注找明医\n客服热线: 400-068-6895";
	public function __construct()
	{
		parent::__construct();
		$this->appId = $this->config->item('global_wx_appid');
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		$this->appId = $this->config->item('global_wx_appid');
        $this->load->model('Jssdk_model');
        $this->load->model('Weixin_model');
		$this->load->model('Template_model');
	}
	public function message()
	{
		log_message('error',json_encode($_GET));
        if (!isset($_GET['echostr'])) {
            $this->responseMsg();
        }else{
            $this->valid();
        }


	}
    //验证签名

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->config->item('global_wx_token');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
			log_message('error',$echoStr);
        }
		exit;
    }

    //响应消息
    public function responseMsg()
    {
        //$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = file_get_contents('php://input');
		log_message('error',$postStr);
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$RX_TYPE = trim($postObj->MsgType);
            $this->FromUserName = (string)$postObj->FromUserName;
            $this->ToUserName = (string)$postObj->ToUserName;
            //检测用户是否已存在数据库中
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case 'location':
					echo "success";exit;
                    $result = $this->receiveLocation($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
				default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
			if(!empty($this->custom_message)){
				$token = $this->Jssdk_model->getAccessToken();
				@send_custom_message($this->FromUserName, 'text', $this->custom_message,$token);
			}
			echo $result;
			exit;
        }else {
			log_message('error',"weixin send data error!");
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
				$content = $this->wx_scan;
				$check = $this->Weixin_model->checkUserExist($this->FromUserName);
				if(!$check){ log_message('error', "wx用户信息存储失败！");}
				$cur_time = time();
				$end_time = $cur_time+2592000;

                if(!empty($object->EventKey)){ 
					$codeID = (int)str_replace("qrscene_","",$object->EventKey);
					if(!empty($check['isnew'])){
						$this->Common_model->updateRecord('user_weixin',array('openid'=>(string)$this->FromUserName),array('eventkey'=>$codeID));
					}
					if($codeID<=90000){
						//查找CodeId对应医生
						$doctor = $this->careDoctor($codeID,0);
						if($doctor){
							$this->custom_message = "您已关注".$doctor['name']."医生，如需了解更多医生详情，请点击该医生简介。如有问题可拨打电话: 400-068-6895";
							//推送医生消息
							$data = array();
							$data['Title'] = "您已关注".$doctor['name']."医生";
							$data['Description'] ="您已关注".$doctor['name']."医生，点击进入医生详情";
							$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/wx_push360x200.png";
							$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/doctor/details/".$doctor['_id']."?auth=wx",$this->appId,'doctordetails');
							$content = array(); $content[] = $data;
						}
					}

					if((int)$codeID==90085){
						$data = array();
						$data['Title'] = "感恩医生 贺卡制作";
						$data['Description'] ="";
						$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/hd2/ysj.png";
						$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/act/day330?auth=wx",$this->appId,'actday330');
						$content = array(); $content[] = $data;
					}elseif((int)$codeID==90086){
						$data = array();
						$data['Title'] = "【找明医】义诊公益活动第三期";
						$data['Description'] ="严肃对待医疗  温暖对待生命";
						$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/act/act_free_push.png";
						$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/act/doclist?auth=wx",$this->appId,'actdoclist');
						$content = array(); $content[] = $data;
					}elseif((int)$codeID==90087){
						$data = array();
						$data['Title'] = "【找明医】义诊公益活动第三期";
						$data['Description'] ="严肃对待医疗  温暖对待生命";
						$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/act/act_free_push.png";
						$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/act/doclist?auth=wx",$this->appId,'actdoclist');
						$content = array(); $content[] = $data;
					}
				}
				//扫描事件则进行checkCodeID方法
				/*
                if(!empty($object->EventKey)){ 
					$codeID = (int)str_replace("qrscene_","",$object->EventKey);
					if($codeID<=90000){
						//查找CodeId对应医生
						$doctor = $this->careDoctor($codeID,0);
						if($doctor){
							$this->custom_message = "您已关注".$doctor['name']."医生，如需了解更多医生详情，请点击该医生简介。如有问题可拨打电话: 400-068-6895";
							//推送医生消息
							$data = array();
							$data['Title'] = "您已关注".$doctor['name']."医生";
							$data['Description'] ="您已关注".$doctor['name']."医生，点击进入医生详情";
							$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/wx_push360x200.png";
							$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/doctor/details/".$doctor['_id']."?auth=wx",$this->appId,'doctordetails');
							$content = array(); $content[] = $data;
						}
					}elseif($codeID>100000){
						//邀请处理();
						if($check['isnew']==1){
							//fa song you hui quan
							$beInvi = $this->Common_model->getInfo('patient',array('rqcode.scen_id'=>$codeID));
							if($beInvi){
								$insertData3 = array('openid'=>$beInvi['fid'],'type'=>1,'price'=>10,'start_time'=>$cur_time,'end_time'=>$end_time,'st'=>1,'from'=>2,'remark'=>'邀请获得');
								$this->Common_model->insertInfo('user_coupons',$insertData3);
								//推送消息
								$postdata = array();
								$postdata['fid'] = $beInvi['fid'];
								$postdata['beinvi'] = $check['nickname'];
								$postdata['url'] = $cou_url;
								$postdata['end_time'] = (string)date('Y-m-d ',$end_time);
								$ret = $this->Template_model->sendCouponsByInvi($postdata);
							}


						}
					}
				}
				*/
				//新用户赠送优惠券
				/*
				if($check['isnew']==1 || $this->FromUserName=="oV5DDvj93ABuHfj5-ihKqEYbdBck"){
					$cou_url = weixin_redirect_uri($this->config->item('global_base_url')."/user/coupons?auth=wx",$this->appId,'usercoupons');
					$this->custom_message = $this->wx_scan;
					$content = "感谢您的关注，找明医赠送首次关注亲情优惠礼包，可<a href='".$cou_url."'>点击链接查看</a>";

					$insertData1 = array('openid'=>$this->FromUserName,'type'=>2,'price'=>10,'start_time'=>$cur_time,'end_time'=>$end_time,'st'=>1,'from'=>1,'remark'=>'首次关注赠送');
					$this->Common_model->insertInfo('user_coupons',$insertData1);
					$insertData2 = array('openid'=>$this->FromUserName,'type'=>3,'price'=>30,'start_time'=>$cur_time,'end_time'=>$end_time,'st'=>1,'from'=>1,'remark'=>'首次关注赠送');
					$this->Common_model->insertInfo('user_coupons',$insertData2);
				}
				*/
                break;
			case "unsubscribe":
				$this->Common_model->updateRecord('user_weixin',array('openid'=>$this->FromUserName),array('st'=>0));
                $content = "";
                break;
            case "SCAN":
				$content = $this->wx_scan1;
                if(!empty($object->EventKey)){ 
					$codeID = $object->EventKey;
					log_message("error",$codeID."qqqqqq");
					//查找CodeId对应医生
					if($codeID<=90000){
						$doctor = $this->careDoctor($codeID,1);
						if($doctor){
							$this->custom_message = "您已关注".$doctor['name']."医生，如需了解更多医生详情，请点击该医生简介。如有问题可拨打电话: 400-068-6895";
							//推送医生消息
							$data = array();
							$data['Title'] = "您已关注".$doctor['name']."医生";
							$data['Description'] ="您已关注".$doctor['name']."医生，点击进入医生详情";
							$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/wx_push360x200.png";
							$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/doctor/details/".$doctor['_id']."?auth=wx",$this->appId,'doctordetails');
							$content = array(); $content[] = $data;
						}
					}
					if(((int)$codeID)==90085){
						$data = array();
						$data['Title'] = "感恩医生 贺卡制作";
						$data['Description'] ="";
						$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/hd2/ysj.png";
						$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/act/day330?auth=wx",$this->appId,'actday330');
						$content = array(); $content[] = $data;
					}elseif((int)$codeID==90086){
						$data = array();
						$data['Title'] = "【找明医】义诊公益活动第三期";
						$data['Description'] ="严肃对待医疗  温暖对待生命";
						$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/act/act_free_push.png";
						$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/act/doclist?auth=wx",$this->appId,'actdoclist');
						$content = array(); $content[] = $data;
					}elseif((int)$codeID==90087){
						$data = array();
						$data['Title'] = "【找明医】义诊公益活动第三期";
						$data['Description'] ="严肃对待医疗  温暖对待生命";
						$data['PicUrl'] = $this->config->item('global_base_url')."/ui/images/act/act_free_push.png";
						$data['Url'] = weixin_redirect_uri($this->config->item('global_base_url')."/act/doclist?auth=wx",$this->appId,'actdoclist');
						$content = array(); $content[] = $data;
					}
                }
                break;
            case "LOCATION":
                $content = '获取地理信息成功--X:'.$object->Location_X.'--Y:'.$object->Location_Y;
                break;
            case "CLICK":
				if(@$object->EventKey=="about_csphone"){ 
					//$content = "      哪种医疗服务更适合我？\n\n1、预约加号是直接向平台专家申请加号，可以优先获得线下面诊的机会，适合希望得到专家临床诊断和治疗的患者；\n2、电话咨询能与平台专家直接对话，获得专家意见，适合异地、不方便当面问诊及病情不清，需要与专家充分语言沟通的患者；\n3、图文咨询是通过“图片+文字”的方式进行在线问诊，适合异地、不方便当面问诊及已有初步诊断，寻求更高级别专家进一步诊疗意见的患者。\n客服热线: 400-068-6895";
					$content = "【找明医】官方客服热线 400-068-6895，您可以电话咨询或者直接留言，我们会尽快为您解答!";
				}else{
					$content = "欢迎关注找名医!\n客服热线: 400-068-6895";
				}
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
		}
        if(is_array($content)){
            $result = $this->transmitNews($object, $content);
        }elseif($content==""){
			$result = "success";
		}else{
            $result = $this->transmitText($object, $content);
		}

        return $result;
	}

	private function careDoctor($codeID,$type){
		$codeID = (int)$codeID;
		$timestamp = time();
		$doctor = $this->Common_model->getInfo('doctor',array('scene_id'=>$codeID));
		if($doctor){
			$info = array('openid'=>$this->FromUserName,'scene_id'=>$codeID,'doctor_id'=>$doctor['_id'],'type'=>$type,'timestamp'=>$timestamp);
			$this->Common_model->insertInfo('user_scan',$info);
			//自动关注	
			$where = array('tid'=>(string)$doctor['_id'],"openid"=>$this->FromUserName);
			$where1 = array('_id'=>$doctor['_id']);
			$careInfo = $this->Common_model->getInfo('doctor_fans',$where);
			if(empty($careInfo)){
				$info = array("tid"=>(string)$doctor['_id'],"openid"=>$this->FromUserName,"ct"=>(string)$timestamp,"st"=>"1",'from'=>"wx_scan");
				$this->Common_model->insertInfo('doctor_fans',$info);
				@$this->Common_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
			}else{
				$info = array("st"=>"1",'ct'=>(string)$timestamp);
				$this->Common_model->updateRecord('doctor_fans',$where,$info);
			}
			if(isset($careInfo['st']) && $careInfo['st']!="1"){
				@$this->Common_model->updateSetRecord('doctor',$where1,array('$inc'=>array('starred'=>1,'mul_num'=>1)));
			}
			//推送客服消息内容
		}
		return $doctor;

	}

	//20150429
    //接收地理位置消息
	//将用户的位置信息更新到后台，方便菜单选择不同的附近（附近场地，附近群。。。）
    private function receiveLocation($object)
    {
        //$content = '经度：'.$object->Location_Y.'纬度：'.$object->Location_X;
        //$result = $this->transmitText($object,$content);
        /*$content = array();
        $content[] = array('Title' => '周边场地','Description' => '距离您1000m以内的所有场地','PicUrl' => base_url('/newstyle/images/zhoubianshangjia.jpg'),'Url' => site_url('mx/nearPlace/'.$object->Location_X.'/'.$object->Location_Y));
        $result = $this->transmitNews($object,$content);*/
		$update_data = array(
		   'LBSX' => $object->Location_X,
		   'LBSY' => $object->Location_Y
		);
		$this->db->where('OpenID', $this->FromUserName);
		$this->db->update('mx_user', $update_data);
		$content = '更新位置成功';
		$result = $this->transmitText($object,$content);
        return $result;
    }

    //接收文本消息
    private function receiveText($object)
	{
		//接收文本消息处理
        $keyword = trim($object->Content);
		$content = "您的问题已经收到，我们的客服会尽快回复，请耐心等待！\n客服电话: 400-068-6895";
		$cur_date = date('Ymd',time());
		if($keyword=="猴年大吉" && $cur_date<=20160214){
			$content="恭喜您成功参与“把名医带回家”活动，获奖名单以及奖品使用方式将在正月十五元宵节公布\n\n一等奖：\n与中国顶级三甲医院名医一对一交流，价值2000元，操作简单，只需要记住4000686895 服务电话。\n二等奖：\n找明医平台健康券，价值500元。\n三等奖：\n免费 400-068-6895 进行健康咨询和导诊建议，价值200元\n\n中奖率：百分之百\n截止时间：2016-2-14";
			$this->Common_model->updateRecord('user_weixin',array('openid'=>$this->FromUserName,'act.monkey_year'=>array('$exists'=>0)),array('act.monkey_year'=>$cur_date));
			$result = $this->transmitText($object, $content);
		}elseif($keyword=="我的邀请"){
			$content="邀请净人数：";
			$UserInfo = $this->Common_model->getInfo('patient',array('fid'=>$this->FromUserName));
			if(!empty($UserInfo['rqcode']['scen_id'])){
				$count = $this->Common_model->getInfoCount('user_weixin',array('eventkey'=>(int)$UserInfo['rqcode']['scen_id'],'st'=>1));
			}else{
				$count = 0;
			}
			$content .=$count;
			$result = $this->transmitText($object, $content);
		}elseif($keyword=="张涛义诊"){
			$content="欢迎您参与张涛义诊报名，请您点击 <a href='http://h5.huimeibest.com/static/freeclinic'>【患者基本信息填写】</a> 完成义诊患者基本信息填写，便于医师提前了解您的基本情况。 您填写完成并提交后，我们的客服人员将在第一时间联系您， 安排您在方便时间就诊。在此感谢您对找明医的支持与信任。";
			$result = $this->transmitText($object, $content);
		}elseif($keyword=="讲座"){
			$content="欢迎您参与本次活动，请您点击 <a href='http://lxi.me/qo3xj'>【在线讲座】</a> 完成相应信息勾选，便于【找明医】来找寻您期望的相应科室专家来进行讲座。 本次讲座活动举行之前，我们的客服人员将在第一时间联系您， 安排您来参与本次活动。在此感谢您对找明医的支持与信任。";
			$this->Common_model->insertInfo('act_log',array('name'=>'zxjz','fid'=>$this->FromUserName,'ct'=>time()));
			$result = $this->transmitText($object, $content);
		}else{
			$result = $this->transmitText1($object);
		}
        return $result;
	}

    //消息转发至多客服
    private function transmitText1($object)
    {
        $xmlTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
					<MsgType><![CDATA[transfer_customer_service]]></MsgType>
                </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $item_str = "";
        foreach ($newsArray as $item){
        $item_str.= "<item>
						<Title><![CDATA[".$item['Title']."]]></Title>
						<Description><![CDATA[".$item['Description']."]]></Description>
						<PicUrl><![CDATA[".$item['PicUrl']."]]></PicUrl>
						<Url><![CDATA[".$item['Url']."]]></Url>
					</item>";
        }
        $result= "<xml>
                    <ToUserName><![CDATA[".$object->FromUserName."]]></ToUserName>
                    <FromUserName><![CDATA[".$object->ToUserName."]]></FromUserName>
                    <CreateTime>".time()."</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>".count($newsArray)."</ArticleCount>
                    <Articles>
                    $item_str</Articles>
                </xml>";
        return $result;
    }
    /**
    *根据OpenID查出用户昵称
    */
    public function getNiCheng()
    {
        $where = array('OpenID' => $this->FromUserName);
        $select = array('NiCheng');
        $this->Mx_model->table = 'mx_user';
        $result = $this->Mx_model->getData($where,$select,1);
        return $result['0']['NiCheng'];
    }


	/**
    *返回用户所有信息
    *@param $fromusername 用户的OpenID
    */
    public function getUser($fromusername = NULL)
    {
        //先判断信息是否存在
        $where = array(
            'OpenID' => $fromusername
            );
        $this->Mx_model->table = 'mx_user';
        $result = $this->Mx_model->getData($where,'',1);

		if(empty($result))
		{
			return null;
		}else
		{
			return $result;
		}
    }

	/**
    *获取教练信息并存入数据库
    *@param $fromusername 用户的OpenID
    */
    public function checkJiaoLian($fromusername = NULL)
    {
        //先判断信息是否存在
        $where = array(
            'OpenID' => $fromusername
            );
        $this->Mx_model->table = 'mx_user';
        $result = $this->Mx_model->getData($where,'',1);
		var_dump($result);
		if(!empty($result)){
			$where_jiaolian = array(
				'UserID' => $result[0]['ID']
				);
			$this->Mx_model->table = 'mx_jiao_lian';
			$result_jiaolian = $this->Mx_model->getData($where_jiaolian,'',1);
			if(empty($result_jiaolian)){
				$data_jiaolian = array(
					'UserID' => $result[0]['ID']
				);
				$this->Mx_model->addData($data_jiaolian);
			}
			
		}
    }
    /**
    *获取教练信息并存入数据库
    *@param $fromusername 用户的OpenID
    */
    public function SelectJiaoLian($UserID = NULL)
    {
        //先判断信息是否存在
        $where = array(
            'UserID' => $UserID
            );
        $this->Mx_model->table = 'mx_jiao_lian';
        $result = $this->Mx_model->getData($where,'',1);
        if(!isNullOrEmpty($result)){
            return $result[0]['ID'];
        }else{
            return false;
        }
    }
    /**
    *求两个已知经纬度之间的距离,单位为米
    *@param $lng1 用户所处位置的经度
    *@param $lat1 用户所处位置的纬度
    *@return float 距离，单位米
    **/
    public function getDistance($lat1, $lng1)
    {
        //从数据库取出店的经纬度
        $this->Mx_model->table = 'mx_dian';
        $select = array('ID','DianMing','LBSX','LBSY');
        $result = $this->Mx_model->getData('',$select,'','');

        $earthRadius = 6367000;
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;
        $roundDianInfo = array();
        foreach ($result as $value) {
            $lat2 = ($value['LBSX'] * pi() ) / 180;
            $lng2 = ($value['LBSY'] * pi() ) / 180;

            $calcLongitude = $lng2 - $lng1;
            $calcLatitude = $lat2 - $lat1;
            $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  
            $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
            $calculatedDistance = round($earthRadius * $stepTwo);
            if($calculatedDistance > 1000){continue;}
            $info = array('DianID' => $value['ID'],'Distance' => $calculatedDistance,'DianMing' => $value['DianMing']);
            array_push($roundDianInfo, $info);
        }
        $this->orderDistance($roundDianInfo);
        //return $roundDianInfo;
    }
    /**
    *将周边医院按照距离由近到远排序
    *@param roundDianInfo 店铺数组
    */
    public function orderDistance($roundDianInfo)
    {
        $num = count($roundDianInfo);
        for ($i=0; $i < $num-1; $i++) { 
            for ($j=0; $j < $num-$i-1 ; $j++) { 
                if($roundDianInfo[$j]['Distance']>$roundDianInfo[$j+1]['Distance']){
                    $kong = $roundDianInfo[$j+1];
                    $roundDianInfo[$j+1] = $roundDianInfo[$j];
                    $roundDianInfo[$j] = $kong;
                }
            }
        }
        //print_r($roundDianInfo);
        $data['info'] = $roundDianInfo;
        $data['userid'] = $this->FromUserName;
        $this->load->view('store/dlist',$data);
    }
        /**
    *接收返回的微信信息code 再通过接口，得到当前用户的openID
    *@param 
    */
        public function view(){
            $get=$this->input->get();
            $code = $get['code'];
            $state = $get['state'];
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxfa6e857e77c9fe1e&secret=440450a73c3c3d8b22fdfb953aa30217&code='.$code.'&grant_type=authorization_code';
            $res = httpRequest($url);
            $json = json_decode($res, true);
            $openid = $json['openid'];
            $user = $this->getUser($openid);

            $userID = $user[0]['ID'];
            switch ($state) {
                case 'xly':
                    # code...
                    redirect ( '/camp/listCamp/0/'.$userID );
                    break;
                case 'grxx':
                    # code...
                    redirect ( 'person/index/'.$userID );
                    break;
                case 'wxj':
                    # code...
                    redirect ( '/mx/changdi/1/-/'.$userID.'/1' );
                    break;
                case 'sy':
                    # code...
                    redirect ( '/mx/changdi/1/-/'.$userID.'/2' );
                case 'qb':
                    # code...
                    redirect ( '/mx/changdi/1/-/'.$userID );
                case 'wdyd':
                    # code...
                    redirect ( '/order/listOrder/'.$userID );
                default:
                    # code...
                    break;
            }
            
    }
}
?>

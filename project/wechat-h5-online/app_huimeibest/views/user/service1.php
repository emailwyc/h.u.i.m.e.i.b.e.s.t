<?php $this->load->view('common/header');?>
<title>我的咨询</title> 
<script type="text/javascript" src="/ui/js/strophe-custom-2.0.0.js"></script>
<script type="text/javascript" src="/ui/js/json2.js"></script>
<script type="text/javascript" src="/ui/js/easemob.im-1.0.5.js"></script>
</head>  
<body>
  <!-- 医生-搜索 -->
  <!-- 医生-搜索 end -->
  <!-- 我的服务-列表 -->
  <div class="wdfw-list">

<?php if(!empty($info)){?>
<?php foreach($info as $k=>$v){?>
    <div class="item" data-id="<?=@$v['_id']?>">
	  <a href="
	  <?php if($v['service']=="clinic"){?>
		  <?php if($v['status']=="新订单" || $v['status']=="等待支付" || $v['status']=="付款进行中" || $v['status']=="付款失败"){?>
			javascript:void(0);
		  <?php }else{?>
		  /user/clinicser/2/<?=@$v['_id']?>
		  <?php }?>
	  <?php }else{?>
		  <?php if($v['status']=="新订单" || $v['status']=="等待支付" || $v['status']=="付款进行中" || $v['status']=="付款失败"){?>
			javascript:void(0);
		  <?php }elseif($v['status']=="已支付"){
			  echo "/user/chat/".$v['_id'];
		  }else{
			  echo "/user/chat/".$v['_id'];
		  }?>
	  <?php }?>
	  ">
        <div class="tx">
          <img src="<?=@$v['doc_avatar']?>" alt="" />
		  <div class="dian"></div>
        </div>
        <div class="con">
          <div class="cfix">
            <div class="fl xm fs14"><?=@$v['doc_name']?></div>
            <div class="fr ch5"><?=@$v['tm'];?></div>
          </div>
          <div class="zt <?php if($v['service']=="clinic"){ echo "gh"; }elseif($v['service']=="consult"){ echo "zx";}?>">［<?=@$v['stat']?>］</div>
        </div>
      </a>
    </div>
<?php }}else{?>
<!-- 无搜索结果 -->
<div class="wssjg pt35 tc fs15">
	<img src="/ui/images/wssjg.png" alt="" />
	<p class="ch6">您还没有新的服务！</p>
</div>
<!-- 无搜索结果 end -->
<?php }?>

  </div>
  <div class="loading">
    <div></div>
  </div>
  <!-- 我的服务-列表 end -->
<script type="text/javascript">
var user="<?=@$hx_user?>";//患者账号
var pwd="<?=@$hx_pass?>";//患者密码
var appKey="<?=@$hx_appk?>";//环信

$(function(){
	var conn = null;
	conn = new Easemob.im.Connection();
	conn.init({
		wait:'60',//非必填，连接超时，默认：60，单位seconds
		onOpened : function() {
			conn.setPresence();
			sendform();
		},
		onClosed : function() {
			//处理登出事件
		},
		onTextMessage : function(message) {
			var from = message.from;//消息的发送者
			var messageContent = message.data;//文本消息体
			var msgtype = message.ext["msg_type"];
		    var orderid = message.ext["order_id"];
			$(".wdfw-list .item").each(function(){
				if($(this).attr("data-id")==orderid){
					if(msgtype=="notice_end"){
					    $(this).find(".zt").text("[ 图文咨询-已完成 ]");
						$(this).find(".dian").show();
						var con=$(this);
						$(this).remove();
						$(".wdfw-list").prepend(con);
					} else{
						$(this).find(".zt").text("[ 图文咨询-文字回复 ]");
						$(this).find(".dian").show();
						var con=$(this);
						$(this).remove();
						$(".wdfw-list").prepend(con);
					}
					return false;
				}
				
			})
		},
		
		onPictureMessage : function(message) {
			var filename = message.filename;//文件名称，带文件扩展名
			var from = message.from;//文件的发送者
			var mestype = message.type;//消息发送的类型是群组消息还是个人消息
			var contactDivId = from;
			var options = message;
        	var orderid = message.ext["order_id"];

			$(".wdfw-list .item").each(function(){
				if($(this).attr("data-id")==orderid){
					$(this).find(".zt").text("[ 图文咨询-图片回复 ]");
					$(this).find(".dian").show();
					var con=$(this);
					$(this).remove();
					$(".wdfw-list").prepend(con);
				}
			})
		},
		//收到联系人信息的回调方法
		onRoster : function (message){
		},
		onError : function(e) {
			//异常处理
			$.alert(e.msg);
		}
	});
	$(function() {
		conn.open({
			user : user,
			pwd : pwd,
			appKey : appKey
		});
	});
});
var loadnum=10;
var page=2;
var isajax = 0;
$(document).ready(function(){
	$(window).scroll(function(){	
		if($(".wssjg").length<=0) {
			if(document.body.clientHeight == document.body.scrollHeight-document.body.scrollTop) {       
				if(loadnum<10) {
				} else {	
					if(isajax==0){	
						$.ajax({
							type: "get",
							url: "/user/service?p="+page,
							dataType: "json",	
							beforeSend: function(){
								$(".loading").show();
								isajax = 1;
							},
							success: function (data){
								loadnum=0;
								setTimeout(function(){
									$(".loading").hide();
									var str="";
									var ysurl="/doctor/details/";
									page+=1;
									for(i in data)
									{
										str+="<div class=\"item\" data-id=\""+data[i].doctor.$id.$id+"\"><a href=\"";
										if(data[i].service=="clinic"){
											if(data[i].status=="新订单"||data[i].status=="等待支付"||data[i].status=="付款进行中"||data[i].status=="付款失败"){
												str+="javascript:void(0)";
											}
											else{
												str+="/user/clinicser/2/"+data[i]._id.$id;
											}
										}
										else{
											if(data[i].status=="新订单"||data[i].status=="等待支付"||data[i].status=="付款进行中"||data[i].status=="付款失败"){
												str+="javascript:void(0)";
											}
											else{
												if(data[i].status=="已支付"){
													str+="/user/chat/"+data[i]._id.$id;
												}
												else{
													str+="/user/chat/"+data[i]._id.$id;
												}
											}
										}
										str+="\"><div class=\"tx\"><img src=\""+data[i].doc_avatar+"\" alt=\"\" /><div class=\"dian\"></div></div><div class=\"con\"><div class=\"cfix\"><div class=\"fl xm fs14\">"+data[i].doc_name+"</div><div class=\"fr ch5\">"+data[i].tm+"</div></div>";
										if(data[i].service=="clinic"){
											str+="<div class=\"zt gh\">"+data[i].stat+"</div>";
										}
										else{
											if(data[i].service=="consult"){
												str+="<div class=\"zt zx\">"+data[i].stat+"</div>";
											}
										}
										str+="</div></a></div>";
										loadnum+=1;	
									}
									$(".wdfw-list").append(str);
									isajax = 0;
								},500)
							}
						});
					}
				}
			}
		}
	});
});
</script>
<?php $this->load->view('common/footer');?>

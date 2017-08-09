<?php $this->load->view('common/header');?>
<title>与<?=@$doctor['name']?>聊天中</title>
<script type="text/javascript" src="/ui/js/strophe-custom-2.0.0.js?v=1.1"></script>
<script type="text/javascript" src="/ui/js/json2.js?v=1.1"></script>
<script type="text/javascript" src="/ui/js/easemob.im-1.0.5.js?v=1.1"></script>
<script type="text/javascript">  
var user="<?=@$patient['_id'];?>";//患者账号
var pwd="<?=@md5($patient['_id']."hmjz")?>";//患者密码
var appKey="<?=@$appkey?>";//环信
var to="<?=@$doctor['_id']?>";//医生账号
var timestamp="<?=@$timestamp?>";//服务器当前时间戳
var fileInputId="fileInputId";

var pat_name = "<?=@$patient['name']?>";
var pat_avatar = "<?=@$patient['avatar']?>";
var doc_name = "<?=@$doctor['name']?>";
var doc_avatar = "<?=@$doctor['avatar']?>";
var issends = "<?=@$issends?>";
var order_id="<?=@$order['_id']?>";
var time="<?=@date("m月d日H时",@$order['pay_at']);?>";
var iscom="<?=@$order['iscom']?>";

var message=<?=@json_encode($order['question']);?>;

var isajax = 0;
if("<?=@$order['ext']['type']?>"!="义诊"){
var newnum=10-<?=@$historyNum?>;
}else{
var newnum="max";
$(function(){
	$(".hz-info .name em,.hz-info .name img").hide();
});
}

var myScroll;
var losttime = new Array();
$(function(){
	$.adddiv("loading");
    $.ajax({
		type: "get",
		url: "/chat/hx_history/"+order_id+"/"+timestamp+"?p=1",
		dataType: "json",	
		beforeSend: function(){
			isajax = 1;
		},
		success: function (data1){
			var sf;
			var tx;
			var msg;
			$(".loading").hide();
			for(i in data1){
				losttime[i]=data1[i].payload.ext.msg_time;
				if(data1[i].from==user){
					sf="hz";
				}
				else{
					sf="ys";
				}
				if(data1[i].payload.bodies[0].type=="img"){
					msg=[data1[i].payload.bodies[0].url,data1[i].payload.bodies[0].thumb];
					ltadd(sf,"img",msg,"top2");
				}else{
				if(data1[i].payload.bodies[0].type=="txt"){
					msg=data1[i].payload.bodies[0].msg;	
					ltadd(sf,"txt",msg,"top2");
				}else{
				if(data1[i].payload.bodies[0].type=="audio"){
					if(!data1[i].hasOwnProperty("video")){
						$.ajax({
							type: "post",
							url: "/Json/videoExchange",
							dataType: "json",
							data:{"msgid":data1[i].msg_id,"url":data1[i].payload.bodies[0].url},	
							beforeSend: function(){
								
							},
							success: function (data2){
								if(data2.st==0){	
									msg=[data2.msg,data1[i].payload.bodies[0].length];
									ltadd(sf,"audio",msg,"top2");
								}else{
									$.alert(data2.msg)
								}
							}
						})
					}else{
						msg=[data1[i].video.url,data1[i].payload.bodies[0].length];
						ltadd(sf,"audio",msg,"top2");
					}
					
				}
				}
				}
			}
			isajax = 0;
		}
	})	
})
$(window).load(function(){
	$.deletediv("loading");
	if(newnum=="max"){
		$('.text-add textarea').attr("placeholder","");
	}else{
	if(newnum<=0){
		$('.text-add textarea').attr("placeholder","您已经没有回复次数了，请重新咨询");
	}else{
		$('.text-add textarea').attr("placeholder","您还可以回复"+newnum+"条信息");
	}
	}
	$(".lt-start").html("<div class=\"left\"></div><div class=\"center fs14\">咨询起始于"+time+"，48小时内有效</div><div class=\"right\"></div>");
	if(issends=="0"){
		if(iscom=="0"){
			$("body").append("<div class=\"lt-end bb\"><div class=\"left\"></div><div class=\"center fs14\"><a href=\"/doctor/comment/"+order_id+"\">本次咨询已结束,请评论</a></div><div class=\"right\"></div></div>");
		}else{
			$("body").append("<div class=\"lt-end bb\"><div class=\"left\"></div><div class=\"center fs14\">本次咨询已结束,已评论</div><div class=\"right\"></div></div>");
		}
		$(".lt-input").hide();
	}
	myScroll= new IScroll("#wrapper", {
		mouseWheel: true,
		scrollbars: true,
		click: true
	});
	myScroll.on("scrollEnd",function(){
	})
	
	var conn = null;
	conn = new Easemob.im.Connection();
	conn.init({
		wait:'60',//非必填，连接超时，默认：60，单位seconds
		onOpened : function() {
			conn.setPresence();
			
		},
		onClosed : function() {
			//处理登出事件
		},
		onTextMessage : function(message) {
			var from = message.from;//消息的发送者
			var messageContent = message.data;//文本消息体
            var msgtype = message.ext["msg_type"];
			var msgtime = message.ext["msg_time"]||0;
			var panduan;
			if(to==from){
				for(var i=0;i<losttime.length;i++)
				{
					if(losttime[i] == msgtime){
						panduan="no";
					}
				}
				if(panduan!="no"){
					if(msgtype=="notice_end"){
						$("body").append("<div class=\"lt-end bb\"><div class=\"left\"></div><div class=\"center fs14\"><a href=\"/doctor/comment/"+order_id+"\">本次咨询已结束,请评论</a></div><div class=\"right\"></div></div>");
						$(".lt-input").hide();
					}
					else{
						ltadd("ys","txt",messageContent,"down");
					}
				}
			}
		},
		
		onPictureMessage : function(message) {
			var filename = message.filename;//文件名称，带文件扩展名
			var from = message.from;//文件的发送者
			var mestype = message.type;//消息发送的类型是群组消息还是个人消息
			var contactDivId = from;
			var options = message;
			var msgtime = message.ext["msg_time"]||0;
			var panduan;
			// 图片消息下载成功后的处理逻辑
			if(to==from){
				for(var i=0;i<losttime.length;i++)
				{
					if(losttime[i] == msgtime){
						panduan="no";
					}
				}
				if(panduan!="no"){
					options.onFileDownloadComplete = function(response, xhr) {
						var objectURL = window.webkitURL.createObjectURL(response);
						img = document.createElement("img");		
						img.onload = function(e) {
						img.onload = null;
						window.webkitURL.revokeObjectURL(img.src);
					};
					img.onerror = function() {
						img.onerror = null;
						if (typeof FileReader == 'undefined') {
							img.alter = "当前浏览器不支持blob方式";
							return;
						}
						img.onerror = function() {
							img.alter = "当前浏览器不支持blob方式";
						};
						var reader = new FileReader();
						reader.onload = function(event) {
							img.src = this.result;
						};
						reader.readAsDataURL(response);
					}
					img.src = objectURL;
					var pic_real_width = options.width;
					var picmsg=[options.url,img.src];
					ltadd("ys","img",picmsg,"down");
				}
			};
			options.onFileDownloadError = function(e) {
				$.alert("下载图片失败")
			};
			Easemob.im.Helper.download(options);
			}
		},
		onAudioMessage : function(message) {
			var filename = message.filename;
			var filetype = message.filetype;
			var from = message.from;
			var mestype = message.type;//消息发送的类型是群组消息还是个人消息
			var contactDivId = from;
			var length=message.length;

			if(to==from){
				$.ajax({
					type: "post",
					url: "/Json/videoExchange",
					dataType: "json",
					data:{"msgid":message.id,"url":message.url},	
					beforeSend: function(){
						
					},
					success: function (data){
						if(data.st==0){
							msg=[data.msg,length];
							ltadd("ys","audio",msg,"down");
						}else{
							$.alert(data.msg)
						}
					}
				})
			}
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
	var clock;
	$(".text-add textarea").focus(function() {
		window.activeobj=this;
		this.clock=setInterval(function(){
			var height=activeobj.scrollHeight;
			if(height>30&&height<90){
				activeobj.style.height=height;
				activeobj.style.overflowY="hidden";
			}
			if(height>=90){
				activeobj.style.height="90";
				activeobj.style.overflowY="scroll";
			}
		},10);
	});
	$(".text-add textarea").blur(function() {
		clearInterval(clock);
	})
    $(".text-add textarea").on('keydown',function(){
        var e = window.event || arguments.callee.caller.arguments[0];
		var msg = $('.text-add textarea').val();
		if(msg.length>300)
		{
			$.alert("病情描述请少于300字")
		}
	})
	$(".send").on('click',function(){
		if(newnum>=1||newnum=="max"){
			var msg = $('.text-add textarea').val();
			if(msg.length==0)
			{
				$.alert("发送内容不能为空，请重新输入")
			}
			else
			{
				sendText(msg);
				$('.text-add textarea').val("");
			}
		}else{
			$.alert("您已经没有回复次数了，请重新咨询");
		}
	})
	$("#fileInputId").on("change",function(){
		if(newnum>=1||newnum=="max"){
			sendPic();
		}else{
			$.alert("您已经没有回复次数了，请重新咨询");
		}
	})
	function sendText(msg) {
		var msg_time=new Date().getTime();
		var options = {
			to : to,
			msg : msg,
			type : "chat",
			ext:{"nickname":pat_name,"avatar":pat_avatar,"order_id":order_id,"msg_time":msg_time}
		};
		conn.sendTextMessage(options);
		ltadd("hz","txt",msg,"down");
		if(newnum=="max"){
			$('.text-add textarea').attr("placeholder","");
		}else{
			newnum=newnum-1;
			if(newnum<=0){
				$('.text-add textarea').attr("placeholder","您已经没有回复次数了，请重新咨询");
			}else{
				$('.text-add textarea').attr("placeholder","您还可以回复"+newnum+"条信息");
			}
		}
	};
	function sendPic() {
		var msg_time=new Date().getTime();
		var fileObj = Easemob.im.Helper.getFileUrl(fileInputId);
		if (fileObj.url == null || fileObj.url == '') {
			return;
		}
		var filetype = fileObj.filetype;
		var filename = fileObj.filename;
		if (filetype in  { "jpg" : true, "gif" : true, "png" : true,"jpeg":true }) {
			var opt = {
				fileInputId : fileInputId,
				to : to,
				onFileUploadError : function(error) {
					$.alert(error.msg)
				},
				onFileUploadComplete : function(data) {
					var picmsg=[fileObj.url,fileObj.url];
					ltadd("hz","img",picmsg,"down");
				},
				ext:{"nickname":pat_name,"avatar":pat_avatar,"order_id":order_id,"msg_time":msg_time}
			};
			conn.sendPicture(opt);
			if(newnum=="max"){
				$('.text-add textarea').attr("placeholder","");
			}else{
				newnum=newnum-1;
				if(newnum<=0){
					$('.text-add textarea').attr("placeholder","您已经没有回复次数了，请重新咨询");
				}else{
					$('.text-add textarea').attr("placeholder","您还可以回复"+newnum+"条信息");
				}
			}
			return;
		}
		$.alert("不支持此图片类型" + filetype);
	};
    $(document).on("click",".img-fd",function(){
		var img1 = new Image();
		img1.src=$(this).attr("data-img");
		img1.onload = function () {
			$(".img-pop").append("<img src=\""+img1.src+"\"/>").show(); 
			var h1=$(".img-pop").height()-20;
			var w1=$(".img-pop").width()-20;
			var hw1=h1/w1;
			var hw2=this.height/this.width;
			if(hw1<=hw2){
				$(".img-pop img").height(h1).width(h1/hw2)	
			}
			else{
				$(".img-pop img").width(w1).height(w1*hw2)
			}
			
			$(".img-pop img").css({"padding-top":($(".img-pop").height()-$(".img-pop img").height())/2,"padding-left":($(".img-pop").width()-$(".img-pop img").width())/2})
		}
		
	})
	$(".img-pop").click(function(){
		$(this).find("img").remove();
		$(this).hide();
	})
	var audiotime;
	var	audio=document.createElement("audio");
	audio.load();
	audio.addEventListener("pause",function(){
		if (audio.currentTime == audio.duration) {
			audio.pause();
			audio.currentTime=0;
			$(".yy.cur").removeClass("cur");
		}
	}, false);
    $(document).on("click",".yy",function(){
		if($(this).hasClass("cur")){
			if(audio.pause){
				audio.src=$(this).attr("data-yy");
				audio.play();
				$(this).addClass("cur");
				audiotime=setInterval(function(){						
					$(".yy.cur").find("em").text(parseInt(audio.currentTime)+1);
				},10)
			}else{
				clearInterval(audiotime);
				audio.pause();
				audio.currentTime=0;
				$(".yy.cur").find("em").text($(".yy.cur").attr("data-time"));
				$(".yy.cur").removeClass("cur");
			}	
		}else{
			clearInterval(audiotime);
			audio.src=$(this).attr("data-yy");
			audio.play();
			$(".yy.cur").find("em").text($(".yy.cur").attr("data-time"));
			$(".yy.cur").removeClass("cur");
			$(this).addClass("cur");
			audiotime=setInterval(function(){							
				$(".yy.cur").find("em").text(parseInt(audio.currentTime)+1);
			},10)
		}
	})
});

function ltadd(user,type,msg,fx){
	var str;
	var lr;
	var con;
	var height;
	var avatar;
	//判断用户
	if(user=="ys"){
		lr="fl";
		avatar=doc_avatar;
	}else{
		lr="fr";
		avatar=pat_avatar;
	}
	//判断消息类型
	if(type=="img"){
		con="<div class=\"tp\"><img class=\"img-fd\" data-img=\""+msg[0]+"\"  src=\""+msg[0]+"\" /></div>";
	}else{
	if(type=="txt"){
		con="<div class=\"zx\"><p>"+msg+"</p></div>";
	}else{
	if(type=="audio"){	
		con="<div class=\"yy\" data-yy=\""+msg[0]+"\" data-time=\""+msg[1]+"\"><p><span></span><em>"+msg[1]+"</em></p></div>";
	}
	}
	}
	str="<div class=\"item "+user+"\"><div class=\"tx\">";
	if(user=="ys"){
		str+="<a href=\"/doctor/details/"+to+"/\"><img src=\""+avatar+"\" alt=\"\" /></a>";
	}else{
		str+="<img src=\""+avatar+"\" alt=\"\" />";
	}
	str+="</div><div class=\"xx cfix\"><div class=\"rq "+lr+"\">"+con+"</div><div class=\"arrow\"></div></div></div>";
	if(fx=="down"){
		$(".lt-content").append(str);
	}else{
		$(".hz-info").after(str);	
	}
	
	if(type=="img"){
	    var image1 = new Image();
	    image1.src=msg[0];
	    image1.onload = function () {
			myScroll.refresh();
		    if(fx=="top"){
				height=0;
			}else{
				height=myScroll.maxScrollY;
			}
		 	myScroll.scrollTo(0,height,500);
	    }
	}
	else{
		myScroll.refresh();
		if(fx=="top"){
				height=0;
		}else{
			height=myScroll.maxScrollY;
		}
		myScroll.scrollTo(0,height,500);
	}
}
</script>
  
<style>
html{height:100%; overflow:hidden;} 
#wrapper{position: absolute;z-index: 1;left: 0;width: 100%; bottom:5.1rem;overflow: hidden;top:0; z-index:1;}
</style>  
</head>
<body>
  <!-- 聊天-内容 -->
  <div id="wrapper">
    <div id="scroller">
      <div class="lt-content">
        <div class="lt-start"></div>
        <div class="hz-info">
		<div class="name"><span class="fs14"><?=@$order['name']?></span><em class="ch6"><?=@$order['age']?>岁</em><img src="/ui/images/sex-<?php if($order['gender']=="male"){ echo "nan";}else{ echo "nv";}?>.png" alt="" /></div>
          <div class="tit">病情描述:</div>
		  <div class="con lh15 ch7"><?=@$order['message']?></div>
		  <?php if(!empty($images)):?>
			  <div class="tit">图片信息:</div>
			  <div class="img cfix">
			  <?php foreach($images as $v):?>
				<div><img src="<?=@$v['thumbnail']?>" alt="" /></div>
			  <?php endforeach;?>
			  </div>
		  <?php endif;?>

		  <?php if(!empty($order['question'])):?>
          <div class="tit">咨询问题:</div>
		  <div class="con lh15 fs13 ch7">
			  <?php foreach($order['question'] as $k=>$v):?>
			  <p><?=@$k+1;?>.<?=@$v?></p>
			  <?php endforeach;?>
		  </div>
		  <?php endif;?>
        </div> 
      </div>  
    </div>
  </div>    
  <!-- 聊天-内容 end -->
  <!-- 聊天-输入 -->
  <div class="lt-input bb">
    <div class="img-add"><input id="fileInputId"  type="file" capture="camera" accept="image/*"></div>
    <div class="text-add"><textarea id="text" type="text" maxlength="300"></textarea></div>
    <div class="send fs16 tc">发送</div>
  </div>
  <!-- 聊天-输入 end -->
  <div class="img-pop"></div>
  <div class="loading">
    <div></div>
  </div>
<?php $this->load->view('common/footer');?>

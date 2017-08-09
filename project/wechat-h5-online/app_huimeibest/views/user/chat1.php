<?php $this->load->view('common/header');?>
<title>与<?=@$doctor['name']?>聊天中</title>
<script type="text/javascript" src="/ui/js/strophe-custom-2.0.0.js"></script>
<script type="text/javascript" src="/ui/js/json2.js"></script>
<script type="text/javascript" src="/ui/js/easemob.im-1.0.5.js"></script>
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
var newnum=10-<?=@$historyNum?>;

var myScroll;
var losttime = new Array();
$(function(){　 　 
	$(".loading").show();
    $.ajax({
		type: "get",
		url: "/chat/hx_history_new/"+order_id,
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
				losttime[i]=data1[i].msg_id;
				if(data1[i].sender=="doctor"){
					sf="ys";
				}
				else{
					sf="hz";
				}
				if(data1[i].type=="img"){
					msg=[data1[i].ext.url,data1[i].msg];
					ltadd(sf,"img",msg,"top2");
				}else{
				if(data1[i].type=="txt"){
					msg=data1[i].msg;	
					ltadd(sf,"txt",msg,"top2");
				}else{
				if(data1[i].type=="voice"){
					msg=[data1[i].msg,data1[i].ext.lenth];
					ltadd(sf,"audio",msg,"top2");
				}
				}
				}
			}
			isajax = 0;
		}
	})	
})
$(window).load(function(){
	if(newnum<=0){
		$('.text-add textarea').attr("placeholder","您已经没有回复次数了，请重新咨询");
	}else{
		$('.text-add textarea').attr("placeholder","您还可以回复"+newnum+"条信息");
	}
	$(".loading").hide();
	$(".lt-start").html("<div class=\"left\"></div><div class=\"center fs14\">咨询起始于"+time+"，48小时内有效</div><div class=\"right\"></div>");
	if(issends=="0"){
		if(iscom=="0"){
			$("body").append("<div class=\"lt-end bb\"><div class=\"left\"></div><div class=\"center fs14\"><a href=\"/doctor/comment/"+order_id+"\">本次咨询已结束,请评论</a></div><div class=\"right\"></div></div>");
		}else{
			$("body").append("<div class=\"lt-end bb\"><div class=\"left\"></div><div class=\"center fs14\">本次咨询已结束,已评论</div><div class=\"right\"></div></div>");
		}
		$(".lt-input").hide();
	}
	
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
			var msg=message.ext;
			var msgfrom = message.from;//消息的发送者
			var msgcon = msg.msg_content;//文本消息体
            var msgtype = msg.msg_type;
			var msgtime = msg.msg_id||0;
			var panduan="1";
			if(to==msgfrom){
				for(var i=0;i<losttime.length;i++)
				{
					if(losttime[i] == msgtime){
						panduan="no";
					}
				}
				if(panduan!="no"){
					var type={
						"notice_text": function(){
							ltadd("ys","txt",msgcon,"down");
						},
						"notice_image": function(){
							var picmsg=[msgcon.url,msgcon.url_thumb];
							ltadd("ys","img",picmsg,"down");
						},
						"notice_voice": function(){
							var voicemsg=[msgcon.url_mp3,msgcon.duration];
							ltadd("ys","audio",voicemsg,"down");
						},
						"notice_end": function(){
							$("body").append("<div class=\"lt-end bb\"><div class=\"left\"></div><div class=\"center fs14\"><a href=\"/doctor/comment/"+order_id+"\">本次咨询已结束,请评论</a></div><div class=\"right\"></div></div>")
							$(".lt-input").hide();
						}
					};
					if(type[msgtype]){
						type[msgtype]();
					}
				}
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
		if(newnum>=1){
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
		if(newnum>=1){
			sendPic();
		}else{
			$.alert("您已经没有回复次数了，请重新咨询");
		}
	})
	function sendText(msg) {
		var data = {'msg':msg,'type':'txt','remarks':"txt",'orderid':order_id,'doctor':to};
		$.ajax({
			type: "post",
			url: "/Json/consultMsg",
			async: false,
			data:data,
			dataType: "json", 
			beforeSend: function(){
				$.adddiv("loading"); 
			},
			success: function (data){
				$.deletediv("loading");
				if(data.st==1){
					var options = {
						to : to,
						msg : msg,
						type : "chat",
						ext:{"nickname":pat_name,"avatar":pat_avatar,"order_id":order_id,"msg_type":"notice_txt","msg_content":msg}
					};
					conn.sendTextMessage(options);
					ltadd("hz","txt",msg,"down");
					newnum=newnum-1;
					if(newnum<=0){
						$('.text-add textarea').attr("placeholder","您已经没有回复次数了，请重新咨询");
					}else{
						$('.text-add textarea').attr("placeholder","您还可以回复"+newnum+"条信息");
					}
				}else{
					$.alert(data.msg);
				}
				
			}
		})
		
	};
	function sendPic() {
	    var f=$("#fileInputId");
        if(f.val()=="") {
			 $.alert("请上传图片");return false;
		} else {
			if(!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG|JPEG)$/.test(f.val())) {
			  $.alert("图片类型必须是.gif,jpeg,jpg,png中的一种");
			  return false;
			}
        }
		var canvas = document.createElement("canvas");
		var img = new Image();
		img.onload=function(){
			var width=800;
			var height=800;
			var bor = img.width > img.height ? 1 : 0;
			if(bor){ 
				if(img.width > width){ 
					var target_w = width; 
					var target_h = parseInt(width/img.width*img.height); 
				}else{ 
					var target_w = img.width; 
					var target_h = img.height; 
				} 
			}else{ 
				if(img.height > height){ 
					var target_w = parseInt(height/img.height*img.width); 
					var target_h = height; 	
				}else{ 
					var target_w = img.width; 
					var target_h = img.height; 
				} 
			} 
			canvas.width = target_w; 
			canvas.height = target_h; 
			canvas.getContext("2d").drawImage(img,0,0,target_w,target_h);
			var imgData = canvas.toDataURL();
			imgData = imgData.replace('data:image/png;base64,','')
			var imagedata =  encodeURIComponent(imgData);
			var data = {'msg':imagedata,'type':'img','remarks':"png",'orderid':order_id,'doctor':to};
			$.ajax({
				type: "post",
				url: "/Json/consultMsg",
				async: false,
				data:data,
				dataType: "json", 
				beforeSend: function(){
					$.adddiv("loading"); 
				},
				success: function (data){
					$.deletediv("loading"); 
					if(data.st==1){
						var img=data.msg;
						var thumb=img.replace('.png','_thumb.png');
						var imgw=data.size.x;
						var imgh=data.size.y;
						var picmsg={"height":imgh,"url":img,"url_thumb":thumb,"width":imgw};
						var picmsg2=[img,thumb];
						var options = {
							to : to,
							msg : "",
							type : "chat",
							ext:{"nickname":pat_name,"avatar":pat_avatar,"order_id":order_id,"msg_type":"notice_image","msg_content":picmsg}
						};
						conn.sendTextMessage(options);
						ltadd("hz","img",picmsg2,"down");
						newnum=newnum-1;
						if(newnum<=0){
							$('.text-add textarea').attr("placeholder","您已经没有回复次数了，请重新咨询");
						}else{
							$('.text-add textarea').attr("placeholder","您还可以回复"+newnum+"条信息");
						}
					}else{		
						
						$.alert(data.msg)
					}
				}
			})
		}
		if(typeof FileReader != 'undefined'){  
			var reader = new FileReader();
			reader.readAsDataURL(f.prop('files')[0]); 
			reader.onload=function(e){ 
				 img.src=e.target.result; 
			};
		}else{			
			if (window.navigator.userAgent.indexOf("MSIE") >= 1) {
				 this.select();
				 img.src=document.selection.createRange().text;	
			} 
		}
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
		con="<div class=\"tp\"><img class=\"img-fd\" data-img=\""+msg[0]+"\"  src=\""+msg[1]+"\" /></div>";
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

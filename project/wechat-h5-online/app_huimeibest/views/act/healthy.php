<?php $this->load->view('common/header');?>
<title>晒家乡美景，赢健康大礼</title>
<style>
.loading{ background:rgba(0,0,0,0.5);}
html{ background:url(/ui/images/hd1/bg.jpg); background-size:100% auto;}
.hd1-head{ padding-bottom:1rem;}
.hd1-body .img{ border:.2rem solid #e60012; margin:0 5.5rem; min-height:12.5rem; position:relative; background:#fcf4e7;}
.hd1-body .img .left{ position:absolute; left:-1.1rem; width:2rem; top:.5rem;}
.hd1-body .img .right{ position:absolute; right:-1.1rem; width:2rem; top:.5rem;}
.hd1-body .tit{ padding:.75rem 0;}
.hd1-body .tit span{ background:url(/ui/images/hd1/body-tit.png) no-repeat center; background-size:100% 100%; width:2.25rem; line-height:2.25rem; display:inline-block; color:#fbf3e7;}
.hd1-body .con{ border-top:1px solid #e60012; padding:.5rem 0; display:inline-block; color:#e60012; font-weight:bold;}
.hd1-body .btn{ padding:2rem 0;}
.hd1-body .btn div{ display:block;background:url(/ui/images/hd1/btn.png) no-repeat; width:20rem; line-height:3rem; margin:0 auto;  color:#fbf3e7; position:relative; background-size:100% 100%; height:4.25rem;}
.hd1-body .btn span{ display:inline-block; background:url(/ui/images/hd1/body-btn.png) no-repeat; background-size:100% 100%; width:1.5rem; height:1.2rem; vertical-align:middle; margin-right:1rem;}
.hd1-body .btn em{ vertical-align:middle;}
.hd1-body .btn input{ width:20rem; height:4.25rem; overflow: hidden; opacity: 0; display: block; position:absolute; left:0; top:0;}
.hd1-text{ background:#e10819; color:#fbf3e7; padding:2.25rem 3.75rem;}
.hd1-text .tit{ height:1.3rem; display:box; display:-webkit-box;}
.hd1-text .tit .center{ padding:0 1rem; line-height:1.3rem; font-weight:bold;}
.hd1-text .tit .left{ background:url(/ui/images/hd1/text-tit-l.png) no-repeat right; background-size:auto 80%; box-flex:1; -webkit-box-flex:1;}
.hd1-text .tit .right{ background:url(/ui/images/hd1/text-tit-r.png) no-repeat left; background-size:auto 80%; box-flex:1; -webkit-box-flex:1;}
.hd1-text .con{ padding:.5rem 0 2rem;}
.pop{ position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.50);}
</style>
<script type="text/javascript">  
var word=["湖光山色","满园春色","赏心悦目","山清水秀","景色怡人","风和日丽","地大物博","别有洞天","锦绣河山","鸟语花香","如花似锦","诗情画意","五彩缤纷","秀色可餐","春暖花开","金碧辉煌","美轮美奂","水阔山高","世外桃源","锦绣山河"];
var fximg;
$(function(){
	$(".hd1-body .btn1 #fileInputId").on("change",function(){
		var f=$(this);
        if(f.val()=="") {
			 $.alert("请上传图片");return false;
		} else {
			if(!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG|JPEG)$/.test(f.val())) {
			  $.alert("图片类型必须是gif,jpeg,jpg,png中的一种");
			  return false;
			}
        }
		$.adddiv("loading");
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
			imgData = imgData.replace('data:image/png;base64,','');
			var imagedata =  encodeURIComponent(imgData);
			var img_data={"msg" : imagedata,"type" : "png"}
			$.ajax({
				type: "post",
				url: "/act/healthyImg",
				async: false,
				data: img_data,
				dataType: "json", 
				beforeSend: function(){ 
				},
				success: function (data){
					if(data.st==1){
						$.ajax({
							type: "get",
							url: "/act/healthyJson/img",
							async: false,
							dataType: "json"
						});
						var img2 = new Image();
						img2.onload=function(){	
							$(".hd1-body .center img").attr("src",img2.src);
							word=word[Math.floor((Math.random())*word.length)];
							for(var i=0;i<4;i++){
								$(".hd1-body .tit span").eq(i).text(word.substring(i,(i+1)));
							}
							$(".hd1-body .btn").removeClass("btn1").addClass("btn2");
							$(".hd1-body .btn span").css("background-image","url(/ui/images/hd1/body-btn2.png)");
							$(".hd1-body .btn em").text("分享");
							$("#fileInputId").hide();
							fximg=img2.src;
							$(".hd1-body .btn div").click(function(){
								$("body").append("<div class=\"pop pop1\"><img src=\"/ui/images/hd1/fx.png\" width=\"100%\" alt=\"\" /></div>");
							});
							wx.ready(function () {  
								wx.onMenuShareAppMessage({
									title: '晒家乡美景，赢健康大礼（我的家乡美景标签是"'+word+'"，你也来试试）', // 分享标题
									desc: '', // 分享描述
									imgUrl: fximg, // 分享图标
									success: function () { 
										$.ajax({
											type: "get",
											url: "/act/healthyJson/share/1",
											async: false,
											dataType: "json"
										});
										$(".pop1").remove();
										$("body").append("<div class=\"pop pop2\"><img src=\"/ui/images/hd1/ewm.png\" width=\"100%\" alt=\"\" /></div>");
									},
									cancel: function () {
										
									}
								}); 
								wx.onMenuShareTimeline({
									title: '晒家乡美景，赢健康大礼（我的家乡美景标签是"'+word+'"，你也来试试）', // 分享标题
									imgUrl: fximg, // 分享图标
									success: function () { 
										$.ajax({
											type: "get",
											url: "/act/healthyJson/share/2",
											async: false,
											dataType: "json"
										});
										$(".pop1").remove();
										$("body").append("<div class=\"pop pop2\"><img src=\"/ui/images/hd1/ewm.png\" width=\"100%\" alt=\"\" /></div>");
									},
									cancel: function () { 
									}
								});  
							});   
							$.deletediv("loading"); 
						}
						img2.src=data.msg;
					}else{		
						$.alert(data.msg);
						$.deletediv("loading"); 
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
	});		
});
wx.ready(function () {  
	wx.onMenuShareAppMessage({
		title: '晒家乡美景，赢健康大礼', // 分享标题
		desc: '', // 分享描述
		imgUrl: weburl+'/ui/images/logo.png', // 分享图标
		success: function () { 
			$.ajax({
				type: "get",
				url: "/act/healthyJson/share/3",
				async: false,
				dataType: "json"
			});
			$("body").append("<div class=\"pop pop2\"><img src=\"/ui/images/hd1/ewm.png\" width=\"100%\" alt=\"\" /></div>");
		},
		cancel: function () {
			
		}
	}); 
	wx.onMenuShareTimeline({
		title: '晒家乡美景，赢健康大礼', // 分享标题
		imgUrl: weburl+'/ui/images/logo.png', // 分享图标
		success: function () { 
			$.ajax({
				type: "get",
				url: "/act/healthyJson/share/4",
				async: false,
				dataType: "json"
			});
			$("body").append("<div class=\"pop pop2\"><img src=\"/ui/images/hd1/ewm.png\" width=\"100%\" alt=\"\" /></div>");
		},
		cancel: function () { 
		}
	});  
});   
</script> 

</head>  
<body>
<div class="hd1-head">
  <img src="/ui/images/hd1/head.png" width="100%" alt="" />
</div>
<div class="hd1-body tc">
  <div class="img">
    <div class="left"><img src="/ui/images/hd1/body-line.png" width="100%" alt="" /></div>
    <div class="center"><img src="/ui/images/hd1/mr.png" width="100%" alt="" /></div>
    <div class="right"><img src="/ui/images/hd1/body-line.png" width="100%" alt="" /></div>
  </div>
  <div class="tit"><span>幸</span><span>福</span><span>短</span><span>语</span></div>
  <div class="con lh15"><p><span class="fs16">找明医携手中国顶级名医</span></p><p>为您家乡人的健康保驾护航</p></div>
  <div class="btn btn1">
    <div>
      <span></span><em>拍照</em>
      <input id="fileInputId"  type="file" capture="camera" accept="image/*" />
    </div>
  </div>
</div>
<div class="hd1-text">
  <div class="tit">
    <div class="left"></div>
    <div class="center fs13">参加方式</div>
    <div class="right"></div>
  </div>
  <div class="con lh15">
    <p>1.点击拍照，上传体现家乡美的图片;*</p>
    <p>2.分享到朋友圈，关注【找明医】服务号;</p>
    <p>3.活动截止日期：2016年2月14日;</p>
    <p>4.中奖名单将于元宵节公布在【找明医】服务号上，敬请期待;</p>
    <p>5.中奖率百分之百。</p>
    
  </div>
  <div class="tit">
    <div class="left"></div>
    <div class="center fs13">奖品设置</div>
    <div class="right"></div>
  </div>
  <div class="con lh15">
    <p>一等奖：与中国顶级三甲医院名医一对一交流，价值2000元，操作简单，只需要记住4000686895 服务电话;</p>
    <p>二等奖：【找明医】平台500元健康券;</p>
    <p>三等奖：4000686895电话健康咨询和导诊建议，价值200元。</p>
  </div>
  <div class="con lh15">
    <p class="fs10">*禁止上传国家法律法规禁止的图片，对于因违规产生不良影响者，我公司将保留追究其法律责任的权利。</p>
  </div>
</div>
<?php $this->load->view('common/footer');?>
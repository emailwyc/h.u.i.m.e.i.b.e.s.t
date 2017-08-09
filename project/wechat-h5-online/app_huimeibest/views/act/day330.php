<?php $this->load->view('common/header');?>
<title> 找明医</title>
<script>
$(document).ready(function(){
	$(".gjysj-btn").click(function(){
		if($(".gjysj-text input").val()==""){
			$.alert("请输入医生姓名");
			return false;
		}
		img($(".gjysj-text input").val());
	})
	$(".gjysj-pop div").click(function(){
		$(".gjysj-pop").hide();
		$(".gjysj-text input").val("");
	})
});
function img(text) {
	$.adddiv("loading");
	var canvas =document.createElement("canvas");
	var img = new Image();
	img.onload=function(){
		var width=720;
		var height=1200;
		canvas.width = width*0.75; 
		canvas.height = height*0.75; 
		var cxt=canvas.getContext("2d");
		cxt.drawImage(img,0,0,width*0.75,height*0.75); 
        cxt.fillStyle = "#bd907d";  
        cxt.font = "bold 60px Arial";  
        cxt.textBaseline = "top";  
        cxt.fillText(text, 175, 75);
		var imgData = canvas.toDataURL();
		imgData = imgData.replace('data:image/png;base64,','');
		var imagedata =  encodeURIComponent(imgData);
		var img_data={"msg" : imagedata,"type" : "png"}
		$.ajax({
			type: "post",
			url: "/act/day330Img",
			async: false,
			data: img_data,
			dataType: "json", 
			beforeSend: function(){ 
			},
			success: function (data){
				var img2 = new Image();
				img2.onload=function(){
					$(".gjysj-pop").show();
					document.getElementById('canvasImg').src = img2.src;
					wx.ready(function () {  
						wx.onMenuShareAppMessage({
							title: '感恩医生 贺卡制作', // 分享标题
							desc: '', // 分享描述
							imgUrl:weburl+'/ui/images/hd2/ysj2.png', // 分享图标
							success: function () { 
							},
							cancel: function () {
								
							}
						}); 
						wx.onMenuShareTimeline({
							title: '感恩医生 贺卡制作', // 分享标题
							imgUrl: weburl+'/ui/images/hd2/ysj2.png', // 分享图标
							success: function () { 
							},
							cancel: function () { 
							}
						});  
					});   
					$.deletediv("loading"); 
				}
				img2.src=data.msg;
			}
		})
	}
	img.src="/ui/images/hd2/imgbg.jpg";
}
wx.ready(function () {  
	wx.onMenuShareAppMessage({
		title: '感恩医生 贺卡制作', // 分享标题
		desc: '', // 分享描述
		imgUrl: weburl+'/ui/images/hd2/ysj2.png', // 分享图标
		success: function () { 
		},
		cancel: function () {
			
		}
	}); 
	wx.onMenuShareTimeline({
		title: '感恩医生 贺卡制作', // 分享标题
		imgUrl: weburl+'/ui/images/hd2/ysj2.png', // 分享图标
		success: function () { 
		},
		cancel: function () { 
		}
	});  
});   
</script>
<style>
html{ background:url(/ui/images/hd2/bg.jpg) no-repeat top #ede4d6; background-size:100% auto; }
.gjysj{ position:fixed; left:0; bottom:0; width:100%;}
.gjysj-text,.gjysj-btn{ border-radius:.5rem; overflow:hidden; margin:0 auto 1.5rem; width:25rem;}
.gjysj-text input{ width:25rem; line-height:4rem; height:4rem; text-align:center; font-size:1.8rem;}
.gjysj-btn div{ width:25rem; line-height:4rem; height:4rem; text-align:center; font-size:1.8rem; background:#ecaa80; color:#fff;}
.gjysj-foot{ color:#bd907d; text-align:center; padding:5rem 0 2rem;}
.gjysj-foot img{ width:2rem; height:2rem; padding-right:.5rem;}
.gjysj-pop{ display:none; background:rgba(0,0,0,.5); position:fixed; z-index:100; width:100%; height:100%; left:0; top:0; text-align:center;}
.gjysj-pop img{ width:27rem; height:45rem; margin:4rem 0 2rem;}
.gjysj-pop p{ color:#fff;line-height:1.5;}
.gjysj-pop div{ background:url(/ui/images/hd2/circle---delete.png) no-repeat; background-size:100% 100%; width:2rem; height:2rem; position:absolute; top:.5rem; right:.5rem;}
</style>
</head>  
<body>
<div class="gjysj">
  <div class="gjysj-text">
    <input type="text" maxlength="3" placeholder="请输入想感谢的医生名字" />
  </div>
  <div class="gjysj-btn">
    <div>确认</div>
  </div>
  <div class="gjysj-foot">
    <img src="/ui/images/logo.png" alt="" />找明医
  </div>
  <div class="gjysj-pop">
    <img id="canvasImg" />
    <p>长按图片保存到手机</p>
    <p>感谢医生 从你我分享做起</p>
    <div></div>
  </div>
</div>
<?php $this->load->view('common/footer');?>
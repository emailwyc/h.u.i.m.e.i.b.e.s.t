/*公有JS*/
$.extend({
	//提示框
	alert:function(txt){
		if(!$(".alert-pop").is(":animated,:visible")){
			if($(".alert-pop").length==0){
				$("body").append("<div class=\"alert-pop\"><span>"+txt+"</span></div>");
			}
			$(".alert-pop").css("margin-top","0rem").fadeIn({duration:300,queue: false}).animate({marginTop:"-.5rem"},{duration:300,queue: false}).find("span").text(txt);
			setTimeout(function(){$(".alert-pop").fadeOut({duration:300,queue: false}).animate({marginTop:"0rem"},{duration:300,queue: false})},1000);
		}else{
			return false;
		}
	},
	//添加元素
	adddiv:function(type){
		if(type=="loading"){
			if($(".loading").length==0){
				$("body").append("<div class=\"loading\"><div></div></div>");
			}
			return false;
		}
		if(type=="mask"){
		  if($(".mask").length==0){
			  $("body").append("<div class=\"mask\"></div>");
			}
			return false;
		}
	},
	//删除元素
	deletediv:function(type){
		$("."+type).remove();
	}
});
//百度统计
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?ad626d7ffc8c7de82382bd831c68e887";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();

var myScroll;
var isajax = 0;
$(function(){
	//页面滚动初始化
	if($("#wrapper").length>0){
		myScroll= new IScroll("#wrapper", {
			mouseWheel: true,
			scrollbars: true,
			click: true
		}); 
	};
	//单选按钮
	$(".default").click(function(){
		if($(".btn-checkbox").hasClass("cur"))
		{
			$(".btn-checkbox").removeClass("cur");
			$("#default").attr("value","0");
		}
		else
		{
			$(".btn-checkbox").addClass("cur");
			$("#default").attr("value","1");
		}
	});
	//添加优惠券
	$(".yhq-add .btn").click(function(){
		var text=$(".yhq-add .text").val();
		$.ajax({
			type: "post",
			url: "/Json/CodeExchange",
			async: false,
			data:{"code":text},
			dataType: "json", 
			beforeSend: function(){
				isajax = 1;
			},
			success: function (data){
				setTimeout(function(){
					if(data.st==0){
						$.alert(data.msg);
						page=1;
						loadnum=10;
						isajax = 0;
						$(".nolist").hide();
						$(".yhq-list2").empty();
						myScroll.refresh();	
						ajaxlist();
					}else{
						$.alert(data.msg)
					}
				},100);
			}
		})
		$(".mask").click(function(){
			$(".yhq-list").remove();
			$.deletediv("mask");
		})
	})
});
$(window).load(function(){
	//页面滚动初始化
	if(myScroll!=undefined){
		myScroll.refresh();
	}
})
//微信分享
$(document).ready(function(){
	var url = location.href.split('#').toString();  
	$.ajax({  
		type : "POST",  
		url : "/json/getclientsign",  
		dataType : "json",  
		async : false,  
		data:{"url":url},  
		success : function(data) {
			wx.config({  
				debug: false,  
				appId: data.appId,  
				timestamp: data.timestamp,  
				nonceStr: data.nonceStr,  
				signature: data.signature,          
				jsApiList: [  
					'onMenuShareTimeline',
					'onMenuShareAppMessage',
					'getNetworkType'  
				]  
			});  
		},  
		error: function(res) {  
		},  
	});
})
wx.ready(function () {  
	wx.onMenuShareAppMessage({
		title: '[找明医]为您提供品质医疗服务', // 分享标题
		desc: '汇聚三甲医院知名专家,提供快速便捷就医通道,实现与专家一对一交流', // 分享描述
		imgUrl: weburl+'/ui/images/logo.png', // 分享图标
		success: function () { 
		},
		cancel: function () {
		}
	});  
	wx.onMenuShareTimeline({
		title: '汇聚三甲医院知名专家,提供快速便捷就医通道,实现与专家一对一交流[找明医]', // 分享标题
		imgUrl: weburl+'/ui/images/logo.png', // 分享图标
		success: function () { 
		},
		cancel: function () { 
		}
	});
	wx.getNetworkType({
		success: function (res) {
			$zmy['network'] = res.networkType; // 返回网络类型2g，3g，4g，wifi
		}
	});
	
});
//请求多数列表   
function ajaxlist(ajaxurl,ajaxdata,myScroll,p,type){
	page=p;
	if(isajax == 0){
		$.ajax({
			type: "get",
			url: ajaxurl,
			dataType: "json",	
			beforeSend: function(){
				$.adddiv("loading");
				isajax = 1;
			},
			success: function (data){
				loadnum=0;
				setTimeout(function(){
					$.deletediv("loading");
					isajax = 0;	
					if(data.length==0&&page==1){
						$(".nolist").show();
						return false;
					}
					var str="";
					page+=1;
					for(i in data)
					{
						str+=ajaxdata(data,type);
						loadnum+=1;	
					}
					$(".ajaxlist").append(str);
					myScroll.refresh();		
				},100)
			}
		});
	}
}
function ajaxdata(data,type){
	var str="";
	if(type==1){
		var ysurl="/doctor/details/";
		var zxurl="/doctor/details/";
		var ghurl="/doctor/details/";
    	str+="<div class=\"item\"><a href=\""+ysurl+data[i]._id.$id+"\" class=\"top\"><div class=\"left tc\"><img class=\"tx\" src=\""+data[i].avatar+"\" onerror=\"this.onerror=null;this.src='/ui/images/doctor.jpg'\" alt=\"\" />";
		str+="<img class=\"jb\" src=\"/ui/images/sanjia.png\" alt=\"三甲\" />";
		str+="<div class=\"gz fs10 c6 lh15\">关注 "+data[i].starred+"</div><div class=\"myd fs10 lh15\">满意度 "+data[i].comment.per+"%</div></div><div class=\"right\"><p class=\"xm cfix\"><span class=\"fs15 c19\">"+data[i].name+"</span><em class=\"c3\">"+data[i].position+"</em></p><p class=\"dw fs14 c19\">"+data[i].hospital+"</p><p class=\"ks ch6 lh15\">"+data[i].department+"</p><p class=\"sc ch6 lh15\">擅长："+data[i].speciality+"</p></div></a><div class=\"bot lh15 fs13 cfix\">";
		if('phonecall' in data[i].service_provided) {
			if(data[i].isph=="1"){
				str+="<div class=\"dhzx\"><a href=\""+zxurl+data[i]._id.$id+"\" class=\"cb1\"><img src=\"/ui/images/ico3-3.png\" alt=\"\" /><span> <em>未满</span></a></div>";
			}else{
				str+="<div class=\"dhzx\"><a href=\"javascript:void(0);\" class=\"ch4\"><img src=\"/ui/images/ico3-31.png\" alt=\"\" /><span> <em>已满</em></span></a></div>";
			}
		} else {
			str+="<div class=\"dhzx\"><a href=\"javascript:void(0);\" class=\"ch4\"><img src=\"/ui/images/ico3-31.png\" alt=\"\" /><span> <em>已满</em></span></a></div>";
		}

		str+="</div></div>";
		return str;	
		return false;
	}
	if(type==2){
		str+="<div class=\"tit cfix\"><div class=\"fl fs13\">"+data[i].p_name;
		if(data[i].p_gender=="male"){
			str+="先生";
		}else{
			str+="女士";
		}
		str+="</div><div class=\"fr star\">";
		for(j=1;j<=5;j++){
			if(j<=data[i].star){
				str+="<span class=\"cur\"></span>";
			}
			else{
				str+="<span></span>";
			}
		}
		str+="</div></div><div class=\"con lh15\">"+data[i].msg+"</div><div class=\"con2 lh15 cfix\">";
		if(data[i].service=="consult"){
			str+="<div class=\"fl twzx\">图文咨询</div>";
		}else{
		if(data[i].service=="phonecall"){
			str+="<div class=\"fl dhzx\">电话咨询</div>";
		}else{
			str+="<div class=\"fl yygh\">预约加号</div>";
		}
		}
		str+="<div class=\"fr c9\">"+data[i].tm+"</div></div>";
		return str;	
		return false;
	}
	if(type==3){
		str+="<div class=\"item";
		if(data[i].st==1&&data[i].outtime==1){
			if(data[i].type==1){
				str+=" red ";
			}
			if(data[i].type==2){
				str+=" yellow ";
			}
			if(data[i].type==3){
				str+=" green ";
			}
			if(data[i].type==4){
				str+=" blue ";
			}
		}
		str+="\"><div class=\"top cfix\"><div class=\"fl\"><img src=\"/ui/images/yhq-logo.png\" /></div><div class=\"fr tr\"><div class=\"fs20\">"+data[i].price+"元</div><p class=\"lh20\">";
			if(data[i].type==1){
				str+="通用";
			}
			if(data[i].type==2){
				str+="图文咨询";
			}
			if(data[i].type==3){
				str+="预约加号";
			}
			if(data[i].type==4){
				str+="电话咨询";
			}
		str+="优惠券</p></div></div><div class=\"bot lh15 cfix\"><div class=\"fl\"><div><img src=\"/ui/images/ico6-1.png\" />"+data[i].remark+"</div><p><img src=\"/ui/images/ico6-2.png\" />有效期至"+data[i].end_time+"</p></div><div class=\"fr\">";
		if(data[i].st==1&&data[i].outtime==1){
		str+="可使用";
		}else{
			if(data[i].st!=1){
				str+="已使用";
			}else{
				str+="已过期";
			}
		}
		str+="</div></div></div>";
		return str;	
		return false;
	}	
	if(type==4||type==5||type==6){
		if(type==4){
			var url="/user/chat/";
		}else{
		if(type==5){
			var url="/user/clinicser/2/";
		}
		else{
			var url="/user/pcdetails/";
		}
		}
		str+="<div class=\"item\"><a href=\""+url+data[i]._id.$id+"\" class=\"tit cfix\"><span class=\"fl\"><em class=\"name fs14 c19\">"+data[i].doc_name+"</em><em class=\"state";
		if(data[i].status=="已完成"){
			str+=" end";
		}
		str+="\">［"+data[i].stat+"］</em>";
		if(type==4&&data[i].nomsg=="1"){
			str+="<em class=\"dian\"></em>";
		}
		str+="</span><span class=\"fr ch4\">"+data[i].tm+"</span></a><div class=\"con\">"+data[i].message+"</div><div class=\"btn tr\">";
		if(data[i].status=="已完成"){
			if(data[i].iscom=="1"){
				str+="<a href=\"javascript:void(0)\">已评论</a>";
			}
			else{
				str+="<a href=\"/doctor/comment/"+data[i]._id.$id+"\" class=\"btn-2\">未评论</a>";
			}
		}
		else{
			str+="<a href=\"javascript:void(0)\">去评论</a>";
		}
		if(data[i].status=="已完成"){
			str+="<a href=\"/doctor/details/"+data[i].doctor.$id.$id+"\" class=\"btn-1\">再次咨询</a>";
		}
		else{
			str+="<a href=\"javascript:void(0);\">再次咨询</a>";
		}
		str+="</div></div>";
		return str;	
		return false;
	}
	if(type==7){
		str+="<a class=\"item\" href=\""+data[i].link_url+"\"><img src=\""+data[i].image_url+"\"><div><span>"+data[i].title+"</span><em>"+data[i].description+"</em><i class=\"tr\">"+data[i].posted_date+"</i></div></a>";
		return str;	
		return false;
	}
	if(type==8){
		str+="<div><a href=\"/user/editpat/"+data[i]._id.$id+"\"><p class=\"cfix xm fs14\"><span class=\"left\">"+data[i].name+"</span><span class=\"right\">";
		if(data[i].gender=="male"){
			str+="<img src=\"/ui/images/sex-nan.png\" />";
		}else{
			str+="<img src=\"/ui/images/sex-nv.png\" />";
		}
		if(data[i].isdefault){
			str+="<em class=\"mr\">[默认]</em>";
		}
		str+="</span></p><p class=\"cfix ch7 fs13 lh15\"><span class=\"left\">身份证</span><span class=\"right\">"+data[i].idcard+"</span></p><p class=\"cfix ch7 fs13 lh15\"><span class=\"left\">手&nbsp;&nbsp;&nbsp;&nbsp;机</span><span class=\"right\">"+data[i].mobile+"</span></p></a></div>";
		return str;	
		return false;
	}
}
//请求导航列表
function ajaxnav(type,back_url,hos,sort,kw,dep){	
	if(type=="ks"){
		var txt=dep;
	}else{
		var txt=hos;
	}
	if(txt==""){
		$(".tuijian").addClass("cur").siblings().removeClass("cur");
	}else{
		$("."+dep).addClass("cur").siblings().removeClass("cur");
	}
	if($(".fl-box1 .cur").length==0){
		$(".fl-box1 li").eq(0).addClass("cur").siblings().removeClass("cur");
	}
	var myScroll1= new IScroll(".fl-box1", {
		probeType: 3,
		mouseWheel: true,
		click: true
	});
	var myScroll2= new IScroll(".fl-box2", {
		mouseWheel: true,
		click: true
	});	
	flcur();
	myScroll1.on("scroll",function(){
		flcur();
	});
	var text=$(".fl-box1 .cur").attr("data-id");
	ajaxnav2(type,text,myScroll2);
	$(".fl-box1 li").click(function(){
		$(this).addClass("cur").siblings().removeClass("cur");
		$(".fl-box1-pop").hide();
		var text=$(this).attr("data-id");
		ajaxnav2(type,text,myScroll2);
	})
	$(document).on("click",".fl-box2 li",function(){
		var flcurid2=$(this).attr("data-id")||"";
		var urls=back_url+"?";
		if(type=="ks"){
			if(flcurid2=="tuijian"){ 
				location.href=urls+"sort="+sort+"&kw="+kw;
			}else{
				location.href=urls+"dep="+flcurid2+"&sort="+sort+"&kw="+kw;
			}
		}else{
			if(flcurid2=="tuijian"){ 
				location.href=urls+"dep="+dep+"&sort="+sort+"&kw="+kw;
			}else{
				location.href=urls+"dep="+dep+"&hos="+flcurid2+"&sort="+sort+"&kw="+kw;
			}
		}
	})
}
function ajaxnav2(type,text,myScroll2){
	var name="";
	if(text=="tuijian"){
		if(type=="ks"){
			name="全部科室";
		}else{
			name="全部医院";
		}
	}else{ 
		name="不限";
	}
	if(type=="ks"){
		url="/json/getdep/"
	}else{
		url="/json/gethos/";
	}
	$.ajax({
		type: "get",
		url: url+text,
		dataType: "json",	
		beforeSend: function(){
			$.adddiv("loading");  
		},
		success: function (data){
			var str="";
			setTimeout(function(){
				$.deletediv("loading");
				if(type=="yy"){
					if(text!="tuijian"){
						text = text+"_1";
					}else{
						text = "tuijian";
					}
				}
				str+="<li data-id=\""+text+"\">"+name+"</li>"
				for(i in data){
					str+="<li data-id=\""+data[i]._id.$id+"_2\">"+data[i].name+"</li>";
				}
				$(".fl-box2 ul").html(str);
				myScroll2.refresh();
			},100);
		}
	})
}
function flcur(){
	var flcurtop=$(".fl-box1 .cur").offset().top;
	var flcurid=$(".fl-box1 .cur").attr("data-id");
	var flcurtext=$(".fl-box1 .cur").text();
	if(flcurtop<=$(".fl-tit").height()){
		$(".fl-box1-pop").show().css({"top":$(".fl-tit").height(),"bottom":"auto"}).attr("data-id",flcurid).text(flcurtext);
	}else if(flcurtop>=$(window).height()-40){
		$(".fl-box1-pop").show().css({"top":"auto","bottom":"0px"}).attr("data-id",flcurid).text(flcurtext);
	}else{
		$(".fl-box1-pop").hide();
	}
}
//请求优惠券列表
function ajaxcoupons(type,money){
	$("body").append("<div class=\"yhq-list bb zi10 fs13 tc\"><div class=\"list\"></div><div class=\"btn\"><a href=\"javascript:void(0)\">暂不使用优惠券</a></div></div>");
	var yhqnum=0;
	$.ajax({
		type: "get",
		url: "/Json/getcoupons/"+type,
		async: false,
		dataType: "json", 
		beforeSend: function(){
		},
		success: function (data){
			yhqnum=0;
			$(".hint").hide();
			var str="";
			for(i in data)
			{
				str+="<a href=\"javascript:void(0)\" data-id=\""+data[i]._id.$id+"\" data-money=\""+data[i].price+"\" data-time=\""+data[i].end_time+"\" ><div class=\"";
				if(data[i].type==1){
					str+=" red ";
				}
				if(data[i].type==2){
					str+=" yellow ";
				}
				if(data[i].type==3){
					str+=" green ";
				}
				if(data[i].type==4){
					str+=" blue ";
				}
				str+="\"><span>"+data[i].price+"元</span><em>有效期至"+data[i].end_time+"</em></div></a>";
				yhqnum+=1;
			}
			$(".coupon a em").html("可用优惠券"+yhqnum+"张");
			$(".yhq-list .list").html(str);
		}
	})
	$(".coupon").click(function(){
		if(yhqnum>0){
		$.adddiv("mask");
		$(".yhq-list").show();
		}
	})
	$(document).on("click",".yhq-list .list a",function(){
		$(this).addClass("cur").siblings().removeClass("cur");
		$("#coupon").val($(this).attr("data-id"));
		if(money-$(this).attr("data-money")>0){
			var money1=$(this).attr("data-money");
			var money2=money-$(this).attr("data-money");
		}
		else{
			var money1=money;
			var money2=0;
		}
		
		$(".jzr-submit .price em").html("已优惠"+money1+"元");
		$(".jzr-submit .price span").html(money2+"元／次");
		$(".coupon a em").html("已用"+$(this).attr("data-money")+"元优惠券");
		$(".yhq-list").hide();
		$.deletediv("mask");
	})
	$(".yhq-list .btn").click(function(){
		$(".yhq-list .list a").removeClass("cur");
		$("#coupon").val("");
		$(".jzr-submit .price em").html("");
		$(".jzr-submit .price span").html(money+"元／次");
		$(".coupon a em").html("可用优惠券"+yhqnum+"张");
		$(".yhq-list").hide();
		$.deletediv("mask");
	})
}
//请求就诊人列表
function ajaxjzr(){
	$.ajax({
		type: "get",
		url: "/Json/getPatientFamily",
		async: false,
		dataType: "json",
		beforeSend: function(){
		},
		success: function (data){
			var str="<div class=\"jzr-select bb zi10 fs14 tc\"><div><p>选择就诊人</p>";
			var num=0;
			for(i in data)
			{
				if(i==0){
					ajaxjzr2(data[i]._id.$id,data[i].name,data[i].age,data[i].gender,data[i].mobile);
				}
				str+="<a class=\"soone\" href=\"javascript:void(0)\" dataid=\""+data[i]._id.$id+"\" datasex=\""+data[i].gender+"\" dataage=\""+data[i].age+"\" datatel=\""+data[i].mobile+"\">"+data[i].name+"</a>";
				num+=1;
			}
			str+="</div><div><a href=\"/user/addpatient\">添加新的就诊人</a></div><div><a href=\"javascript:void(0)\" class=\"ch8 close\">取消</a></div></div>";
			$("body").append(str);
		}
	})
	$(".jzr-form .name").click(function(){
		if($(".soone").length==0){
			location.href="/user/addpatient";
		}
		else{
			$(".jzr-select").show();
			$.adddiv("mask");
			$(".mask").click(function(){
				$(".jzr-select").hide();
				$.deletediv("mask");
			})
		}
	});
	$(document).on("click",".jzr-select .close",function(){
		$(".jzr-select").hide();
		$.deletediv("mask");
	})
	
	$(document).on("click",".soone",function(){
		ajaxjzr2($(this).attr("dataid"),$(this).html(),$(this).attr("dataage"),$(this).attr("datasex"),$(this).attr("datatel"));
	});
	function ajaxjzr2(id,name,age,sex,tel){
		$("#patient").attr("value",id);
		$("#patient_name").html(name);
		$("#patient_age").html(age+"岁");
		var gender = sex;
		var gender_str = gender=="male"?"/ui/images/sex-nan.png":"/ui/images/sex-nv.png";
		$("#patient_sex").attr('src',gender_str);
		$("#patient_tel").html("接听电话："+tel);
		$(".jzr-select").hide();
		$.deletediv("mask");
	}
}
//处理图片
function updateimg(){
	$(document).on("change",".jzr-form .img-add input[type=file]",function(){
		$(this).parent(".img-add").addClass("zhuan");
	    var f=$(this);
		
        if(f.val()=="") {
			 $.alert("请上传图片");
			 f.parent(".img-add").removeClass("zhuan");
			 return false;
		} else {
			if(!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG|JPEG)$/.test(f.val())) {
			  $.alert("图片类型必须是.gif,jpeg,jpg,png中的一种");
			  f.parent(".img-add").removeClass("zhuan");
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
			var img_data={"img_info" : imagedata,"img_type" : "png"}
			$.ajax({
				type: "post",
				url: "/Json/uploadImg",
				async: false,
				data:img_data,
				dataType: "json", 
				beforeSend: function(){
					
				},
				success: function (data){
					if(data.st==1){
						var length=$(".jzr-form .img-add").length;
						var num=f.parent(".img-add").index(".jzr-form .img-add");
						if(length<=19)
						{
							f.parent(".img-add").after("<div class=\"img-add\"><input type=\"file\" capture=\"camera\" accept=\"image/*\" name='file[]'></div>")
						}
						if(length>=1)
						{
							$(".jzr-form .image .right").hide();
						}
						var img=data.msg;
						var thumb=img.replace('.png','_thumb.png')
						var img2 = new Image();
						img2.onload=function(){
							f.parent(".img-add").removeClass("zhuan");
							$(".jzr-form .img-add").eq(num).empty().append("<img src=\""+img2.src+"\" alt=\"\" /><input name=\"img[]\" type=\"hidden\" value=\""+img+"\" />");
						}
						img2.src=thumb;
					}else{
						$.alert(data.msg)
					}
				}
			})
		}
		if(typeof FileReader != 'undefined'){  
			var reader = new FileReader();
			reader.readAsDataURL(this.files[0]); 
			reader.onload=function(e){ 
				 img.src=this.result; 
			};
		}else{			
			if (window.navigator.userAgent.indexOf("MSIE") >= 1) {
				 this.select();
				 img.src=document.selection.createRange().text;	
			} 
		}
		
	})
	$(document).on("click",".jzr-form .img-add img",function(){
		var length=$(".jzr-form .img-add img").length;
		$("body").append("<div class=\"img-pop2\" data-num=\""+length+"\"><div class=\"img\"><img src=\""+$(this).next("input").val()+"\" /></div><div class=\"btn cfix\"><a href=\"javascript:void(0)\" class=\"fl\">删除</a><a href=\"javascript:void(0)\" class=\"fr\">取消</a></div>");
	})
	$(document).on("click",".img-pop2 .fl",function(){ 
		var length=$(".img-pop2").attr("data-num");
		$(".jzr-form .img-add").eq(length-1).remove();
		if(length==20) {
			$(".jzr-form .image .left").append("<div class=\"img-add\"><input type=\"file\" capture=\"camera\" accept=\"image/*\" name='file[]'></div>")
		}
		if(length==1)
		{
			$(".jzr-form .image .right").show();
		}
		$(".img-pop2").remove();
	})
	$(document).on("click",".img-pop2 .fr",function(){
		$(".img-pop2").remove();
	})
}
//弹出文字
function aboutpop(tit,con){
	$.adddiv("mask");
	$("body").append("<div class=\"about-pop-c zi10\"><div class=\"tit fs14\">"+tit+"</div><div class=\"con fs13 lh15\">"+con+"</div></div>");
	$(".mask").click(function(){
		$(".about-pop-c").remove();
		$.deletediv("mask");
	})
};
//未读图文提示
function navdian(){
	$.ajax({
		type: "get",
		url: "/Json/getNoMsg",
		dataType: "json",	
		beforeSend: function(){
		},
		success: function (data){
			if(data!=0){
				$(".nav-dian").show();
			}else{
				$(".nav-dian").hide();
			}
		}
	})
}
/*公有JS end*/

/*私有JS*/
$(function(){
	//医生列表下拉
	$(".ys-select div").each(function(i) {
		if(i<=1){
			var txt=$(this).find("span").text();
		}
		else{
			var txt=$(".ys-select-pop.pop3 .cur div").text();
		}
		if(txt.length>=6){
			txt=txt.substring(0,5)+"...";
		}
		$(this).find("span").text(txt);
	});
	$(".ys-select div:nth-child(3)").click(function(){
		$.adddiv("mask");
		$(this).addClass("cur").siblings().removeClass("cur");
		$(".ys-select-pop").show();
		$(".mask").click(function(){
			$(".ys-select-pop").hide();
			$(".ys-select div").removeClass("cur");
			$.deletediv("mask");
		});
	});
	$(".ys-select-pop.pop3 li").click(function(){
		$(this).addClass("cur").siblings().removeClass("cur");
		$(".ys-select div:nth-child(3)").removeClass("cur").find("span").text($(this).find("div").text());
		$(".ys-select-pop").hide();
		$.deletediv("mask");
	});
	//关注
	$(".ys-info2 .gz").click(function(){
		if(isajax==0){
			if($(this).hasClass("cur")){
				$.ajax({
					type: "get",
					url: "/doctor/fans/"+doctorid+"/2",
					dataType: "json", 
					beforeSend: function(){
						isajax=1;
					},
					success: function (data){
						if(data=="1"){
							$(".gz").removeClass("cur");
							$(".gz span").text("关注");
							$.alert("取消关注成功")
						}
						else{if(data=="2"){
								$.alert("操作失败")
							}
							else{
								$.alert("未登录");
							}
						}
						setTimeout(function(){isajax=0;},1000);
					}
				})
			}
			else{
				$.ajax({
					type: "get",
					url: "/doctor/fans/"+doctorid+"/1",
					async: false,
					dataType: "json", 
					beforeSend: function(){
						isajax=1;
					},
					success: function (data){
						if(data=="1"){
							$(".gz").addClass("cur");
							$(".gz span").text("已关注");
							$.alert("关注成功");
						} else{
							if(data=="2"){
									$.alert("操作失败");
							} else{
									$.alert("未登录");
							}
						}
						setTimeout(function(){isajax=0;},1000);
					}
				})
			}
		}
	})
	//搜索
	/*$(".ys-search #kw-form").submit(function(){
		return redirect($("#kw").val()),
		!1
	})*/
	$(".ys-search #kw").keyup(function(){
		var e = window.event || arguments.callee.caller.arguments[0];
		if (e && e.keyCode == 13 ) {
			redirect($("#kw").val());
		}
	})
	$(".ys-search #kw").focus(function(){
		$(this).parent("div").css({"margin-right":"5rem"})
		$("#rt").show();
		$(".rsjb").show();
	})
	$(".ys-search #rt").click(function(){
		redirect($("#kw").val());
	})
	//表单文本框
	$(".jzr-form .textarea textarea").focus(function(){
		$(".jzr-submit").removeClass("bb");
		$(".jzr-form").removeClass("pb90");
	})
	$(".jzr-form .textarea textarea").blur(function(){
		$(".jzr-submit").addClass("bb");
		$(".jzr-form").addClass("pb90");
	})
	//评论星星
	$(".pl-con .star span").click(function(){
		var num=$(this).index();
		$(".pl-con .star span").each(function(x){
			if(x<=num){
				$(this).addClass("cur");
			}else{
				$(this).removeClass("cur");
			}
		})
		$("#star").val(num+1);
	})	
})
$(window).load(function(){
	//验证码
	$("#send-sms-code").click(function() {
		if(wxst=="1"){
			return false;
		}
		var mobile=$("#RegMobileForm_reg_mobile").val();
		$.ajax({
			type: "POST",
			url: "/msg/regsend",
			data:{mobile:encodeURIComponent(mobile)},
			dataType:'text',
			beforeSend: function(XMLHttpRequest) {
				$("#send-sms-code").html("正在获取");
				$('#RegMobileForm_reg_mobile').removeAttr("readonly");
			},
			success: function(text) {
				//样式修改
				if(text != '1'){
					$("#send-sms-code").html("重新获取");
					$('#RegMobileForm_reg_mobile').attr("disabled",false);
				}else{
					$('#RegMobileForm_reg_mobile').attr("readonly",'readonly');
					$('#send-sms-code').attr("style",'cursor:default');
				}
				if(text == '2'){
					$.alert("120秒内只能发送一次,请稍后再试！");	
				}else if(text == '3'){
					$.alert("发送过于频繁，大人请稍后再试吧！");	
				}else if(text == '4'){
					$.alert("短信发送失败，请检查手机号,或联系客服！");	
				}else if(text == '5'){
					$.alert("手机号码格式错误请修正！");	
				}else if(text == '6'){
					$.alert("手机号码未授权！");
				}else if(text == '1'){
					updateCode1(119);
				}else{
					$.alert("返回失败");
				}
				
			}
		});
	});
})
//医生列表
function redirect(txt){
	$("#kw").parent("div").css({"margin-right":"0"});
	$("#rt").hide();
	$(".rsjb").hide();
	loadnum=10;
	isajax = 0;
	kw=escape(txt);
	$(".nolist").hide();
	$(".ys-list").empty();
	myScroll.refresh();	
	ajaxurl="/home/doctorJson?dep="+dep+"&hos="+hos+"&sort="+sort+"&kw="+kw+"&p=1";
	ajaxlist(ajaxurl,ajaxdata,myScroll,1,"1");
};
function seldep(){
	location.href="/home/seldep?dep="+dep+"&hos="+hos+"&sort="+sort+"&kw="+kw;
}
function selhos(){
	location.href="/home/selhos?dep="+dep+"&sort="+sort+"&kw="+kw;
}
function selsort(val){
	loadnum=10;
	isajax = 0;
	$(".nolist").hide();
	$(".ys-list").empty();
	myScroll.refresh();	
	ajaxurl="/home/doctorJson?dep="+dep+"&hos="+hos+"&sort="+val+"&kw="+kw+"&p=1";
	ajaxlist(ajaxurl,ajaxdata,myScroll,1,"1");
}
//验证码
function updateCode1(num){
	if( num > 1 ) {
		wxst = "1";
		num = num - 1;
		$("#send-sms-code").html("剩余("+num+")");
		var temp = setTimeout(function() {
			updateCode1(num);
		}, 1000);
	} else {
		wxst = "0";
		clearTimeout(temp);
		$("#send-sms-code").html("重新获取");
		$('#RegMobileForm_reg_mobile').removeAttr("readonly");
		$('#send-sms-code').attr("style",'cursor:pointer');
	}
}
/*私有JS end*/

/*外部JS*/
/*页面滚动js - iscroll.js*/
(function (window, document, Math) {
var rAF = window.requestAnimationFrame	||
	window.webkitRequestAnimationFrame	||
	window.mozRequestAnimationFrame		||
	window.oRequestAnimationFrame		||
	window.msRequestAnimationFrame		||
	function (callback) { window.setTimeout(callback, 1000 / 60); };

var utils = (function () {
	var me = {};

	var _elementStyle = document.createElement('div').style;
	var _vendor = (function () {
		var vendors = ['t', 'webkitT', 'MozT', 'msT', 'OT'],
			transform,
			i = 0,
			l = vendors.length;

		for ( ; i < l; i++ ) {
			transform = vendors[i] + 'ransform';
			if ( transform in _elementStyle ) return vendors[i].substr(0, vendors[i].length-1);
		}

		return false;
	})();

	function _prefixStyle (style) {
		if ( _vendor === false ) return false;
		if ( _vendor === '' ) return style;
		return _vendor + style.charAt(0).toUpperCase() + style.substr(1);
	}

	me.getTime = Date.now || function getTime () { return new Date().getTime(); };

	me.extend = function (target, obj) {
		for ( var i in obj ) {
			target[i] = obj[i];
		}
	};

	me.addEvent = function (el, type, fn, capture) {
		el.addEventListener(type, fn, !!capture);
	};

	me.removeEvent = function (el, type, fn, capture) {
		el.removeEventListener(type, fn, !!capture);
	};

	me.prefixPointerEvent = function (pointerEvent) {
		return window.MSPointerEvent ? 
			'MSPointer' + pointerEvent.charAt(9).toUpperCase() + pointerEvent.substr(10):
			pointerEvent;
	};

	me.momentum = function (current, start, time, lowerMargin, wrapperSize, deceleration) {
		var distance = current - start,
			speed = Math.abs(distance) / time,
			destination,
			duration;

		deceleration = deceleration === undefined ? 0.0006 : deceleration;

		destination = current + ( speed * speed ) / ( 2 * deceleration ) * ( distance < 0 ? -1 : 1 );
		duration = speed / deceleration;

		if ( destination < lowerMargin ) {
			destination = wrapperSize ? lowerMargin - ( wrapperSize / 2.5 * ( speed / 8 ) ) : lowerMargin;
			distance = Math.abs(destination - current);
			duration = distance / speed;
		} else if ( destination > 0 ) {
			destination = wrapperSize ? wrapperSize / 2.5 * ( speed / 8 ) : 0;
			distance = Math.abs(current) + destination;
			duration = distance / speed;
		}

		return {
			destination: Math.round(destination),
			duration: duration
		};
	};

	var _transform = _prefixStyle('transform');

	me.extend(me, {
		hasTransform: _transform !== false,
		hasPerspective: _prefixStyle('perspective') in _elementStyle,
		hasTouch: 'ontouchstart' in window,
		hasPointer: window.PointerEvent || window.MSPointerEvent, // IE10 is prefixed
		hasTransition: _prefixStyle('transition') in _elementStyle
	});

	// This should find all Android browsers lower than build 535.19 (both stock browser and webview)
	me.isBadAndroid = /Android /.test(window.navigator.appVersion) && !(/Chrome\/\d/.test(window.navigator.appVersion));

	me.extend(me.style = {}, {
		transform: _transform,
		transitionTimingFunction: _prefixStyle('transitionTimingFunction'),
		transitionDuration: _prefixStyle('transitionDuration'),
		transitionDelay: _prefixStyle('transitionDelay'),
		transformOrigin: _prefixStyle('transformOrigin')
	});

	me.hasClass = function (e, c) {
		var re = new RegExp("(^|\\s)" + c + "(\\s|$)");
		return re.test(e.className);
	};

	me.addClass = function (e, c) {
		if ( me.hasClass(e, c) ) {
			return;
		}

		var newclass = e.className.split(' ');
		newclass.push(c);
		e.className = newclass.join(' ');
	};

	me.removeClass = function (e, c) {
		if ( !me.hasClass(e, c) ) {
			return;
		}

		var re = new RegExp("(^|\\s)" + c + "(\\s|$)", 'g');
		e.className = e.className.replace(re, ' ');
	};

	me.offset = function (el) {
		var left = -el.offsetLeft,
			top = -el.offsetTop;

		// jshint -W084
		while (el = el.offsetParent) {
			left -= el.offsetLeft;
			top -= el.offsetTop;
		}
		// jshint +W084

		return {
			left: left,
			top: top
		};
	};

	me.preventDefaultException = function (el, exceptions) {
		for ( var i in exceptions ) {
			if ( exceptions[i].test(el[i]) ) {
				return true;
			}
		}

		return false;
	};

	me.extend(me.eventType = {}, {
		touchstart: 1,
		touchmove: 1,
		touchend: 1,

		mousedown: 2,
		mousemove: 2,
		mouseup: 2,

		pointerdown: 3,
		pointermove: 3,
		pointerup: 3,

		MSPointerDown: 3,
		MSPointerMove: 3,
		MSPointerUp: 3
	});

	me.extend(me.ease = {}, {
		quadratic: {
			style: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
			fn: function (k) {
				return k * ( 2 - k );
			}
		},
		circular: {
			style: 'cubic-bezier(0.1, 0.57, 0.1, 1)',	// Not properly "circular" but this looks better, it should be (0.075, 0.82, 0.165, 1)
			fn: function (k) {
				return Math.sqrt( 1 - ( --k * k ) );
			}
		},
		back: {
			style: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)',
			fn: function (k) {
				var b = 4;
				return ( k = k - 1 ) * k * ( ( b + 1 ) * k + b ) + 1;
			}
		},
		bounce: {
			style: '',
			fn: function (k) {
				if ( ( k /= 1 ) < ( 1 / 2.75 ) ) {
					return 7.5625 * k * k;
				} else if ( k < ( 2 / 2.75 ) ) {
					return 7.5625 * ( k -= ( 1.5 / 2.75 ) ) * k + 0.75;
				} else if ( k < ( 2.5 / 2.75 ) ) {
					return 7.5625 * ( k -= ( 2.25 / 2.75 ) ) * k + 0.9375;
				} else {
					return 7.5625 * ( k -= ( 2.625 / 2.75 ) ) * k + 0.984375;
				}
			}
		},
		elastic: {
			style: '',
			fn: function (k) {
				var f = 0.22,
					e = 0.4;

				if ( k === 0 ) { return 0; }
				if ( k == 1 ) { return 1; }

				return ( e * Math.pow( 2, - 10 * k ) * Math.sin( ( k - f / 4 ) * ( 2 * Math.PI ) / f ) + 1 );
			}
		}
	});

	me.tap = function (e, eventName) {
		var ev = document.createEvent('Event');
		ev.initEvent(eventName, true, true);
		ev.pageX = e.pageX;
		ev.pageY = e.pageY;
		e.target.dispatchEvent(ev);
	};

	me.click = function (e) {
		var target = e.target,
			ev;

		if ( !(/(SELECT|INPUT|TEXTAREA)/i).test(target.tagName) ) {
			ev = document.createEvent('MouseEvents');
			ev.initMouseEvent('click', true, true, e.view, 1,
				target.screenX, target.screenY, target.clientX, target.clientY,
				e.ctrlKey, e.altKey, e.shiftKey, e.metaKey,
				0, null);

			ev._constructed = true;
			target.dispatchEvent(ev);
		}
	};

	return me;
})();

function IScroll (el, options) {
	this.wrapper = typeof el == 'string' ? document.querySelector(el) : el;
	this.scroller = this.wrapper.children[0];
	this.scrollerStyle = this.scroller.style;		// cache style for better performance

	this.options = {

		resizeScrollbars: true,

		mouseWheelSpeed: 20,

		snapThreshold: 0.334,

// INSERT POINT: OPTIONS 

		startX: 0,
		startY: 0,
		scrollY: true,
		directionLockThreshold: 5,
		momentum: true,

		bounce: true,
		bounceTime: 600,
		bounceEasing: '',

		preventDefault: true,
		preventDefaultException: { tagName: /^(INPUT|TEXTAREA|BUTTON|SELECT)$/ },

		HWCompositing: true,
		useTransition: true,
		useTransform: true
	};

	for ( var i in options ) {
		this.options[i] = options[i];
	}

	// Normalize options
	this.translateZ = this.options.HWCompositing && utils.hasPerspective ? ' translateZ(0)' : '';

	this.options.useTransition = utils.hasTransition && this.options.useTransition;
	this.options.useTransform = utils.hasTransform && this.options.useTransform;

	this.options.eventPassthrough = this.options.eventPassthrough === true ? 'vertical' : this.options.eventPassthrough;
	this.options.preventDefault = !this.options.eventPassthrough && this.options.preventDefault;

	// If you want eventPassthrough I have to lock one of the axes
	this.options.scrollY = this.options.eventPassthrough == 'vertical' ? false : this.options.scrollY;
	this.options.scrollX = this.options.eventPassthrough == 'horizontal' ? false : this.options.scrollX;

	// With eventPassthrough we also need lockDirection mechanism
	this.options.freeScroll = this.options.freeScroll && !this.options.eventPassthrough;
	this.options.directionLockThreshold = this.options.eventPassthrough ? 0 : this.options.directionLockThreshold;

	this.options.bounceEasing = typeof this.options.bounceEasing == 'string' ? utils.ease[this.options.bounceEasing] || utils.ease.circular : this.options.bounceEasing;

	this.options.resizePolling = this.options.resizePolling === undefined ? 60 : this.options.resizePolling;

	if ( this.options.tap === true ) {
		this.options.tap = 'tap';
	}

	if ( this.options.shrinkScrollbars == 'scale' ) {
		this.options.useTransition = false;
	}

	this.options.invertWheelDirection = this.options.invertWheelDirection ? -1 : 1;

	if ( this.options.probeType == 3 ) {
		this.options.useTransition = false;	}

// INSERT POINT: NORMALIZATION

	// Some defaults	
	this.x = 0;
	this.y = 0;
	this.directionX = 0;
	this.directionY = 0;
	this._events = {};

// INSERT POINT: DEFAULTS

	this._init();
	this.refresh();

	this.scrollTo(this.options.startX, this.options.startY);
	this.enable();
}

IScroll.prototype = {
	version: '5.1.3',

	_init: function () {
		this._initEvents();

		if ( this.options.scrollbars || this.options.indicators ) {
			this._initIndicators();
		}

		if ( this.options.mouseWheel ) {
			this._initWheel();
		}

		if ( this.options.snap ) {
			this._initSnap();
		}

		if ( this.options.keyBindings ) {
			this._initKeys();
		}

// INSERT POINT: _init

	},

	destroy: function () {
		this._initEvents(true);

		this._execEvent('destroy');
	},

	_transitionEnd: function (e) {
		if ( e.target != this.scroller || !this.isInTransition ) {
			return;
		}

		this._transitionTime();
		if ( !this.resetPosition(this.options.bounceTime) ) {
			this.isInTransition = false;
			this._execEvent('scrollEnd');
		}
	},

	_start: function (e) {
		// React to left mouse button only
		if ( utils.eventType[e.type] != 1 ) {
			if ( e.button !== 0 ) {
				return;
			}
		}

		if ( !this.enabled || (this.initiated && utils.eventType[e.type] !== this.initiated) ) {
			return;
		}

		if ( this.options.preventDefault && !utils.isBadAndroid && !utils.preventDefaultException(e.target, this.options.preventDefaultException) ) {
			e.preventDefault();
		}

		var point = e.touches ? e.touches[0] : e,
			pos;

		this.initiated	= utils.eventType[e.type];
		this.moved		= false;
		this.distX		= 0;
		this.distY		= 0;
		this.directionX = 0;
		this.directionY = 0;
		this.directionLocked = 0;

		this._transitionTime();

		this.startTime = utils.getTime();

		if ( this.options.useTransition && this.isInTransition ) {
			this.isInTransition = false;
			pos = this.getComputedPosition();
			this._translate(Math.round(pos.x), Math.round(pos.y));
			this._execEvent('scrollEnd');
		} else if ( !this.options.useTransition && this.isAnimating ) {
			this.isAnimating = false;
			this._execEvent('scrollEnd');
		}

		this.startX    = this.x;
		this.startY    = this.y;
		this.absStartX = this.x;
		this.absStartY = this.y;
		this.pointX    = point.pageX;
		this.pointY    = point.pageY;

		this._execEvent('beforeScrollStart');
	},

	_move: function (e) {
		if ( !this.enabled || utils.eventType[e.type] !== this.initiated ) {
			return;
		}

		if ( this.options.preventDefault ) {	// increases performance on Android? TODO: check!
			e.preventDefault();
		}

		var point		= e.touches ? e.touches[0] : e,
			deltaX		= point.pageX - this.pointX,
			deltaY		= point.pageY - this.pointY,
			timestamp	= utils.getTime(),
			newX, newY,
			absDistX, absDistY;

		this.pointX		= point.pageX;
		this.pointY		= point.pageY;

		this.distX		+= deltaX;
		this.distY		+= deltaY;
		absDistX		= Math.abs(this.distX);
		absDistY		= Math.abs(this.distY);

		// We need to move at least 10 pixels for the scrolling to initiate
		if ( timestamp - this.endTime > 300 && (absDistX < 10 && absDistY < 10) ) {
			return;
		}

		// If you are scrolling in one direction lock the other
		if ( !this.directionLocked && !this.options.freeScroll ) {
			if ( absDistX > absDistY + this.options.directionLockThreshold ) {
				this.directionLocked = 'h';		// lock horizontally
			} else if ( absDistY >= absDistX + this.options.directionLockThreshold ) {
				this.directionLocked = 'v';		// lock vertically
			} else {
				this.directionLocked = 'n';		// no lock
			}
		}

		if ( this.directionLocked == 'h' ) {
			if ( this.options.eventPassthrough == 'vertical' ) {
				e.preventDefault();
			} else if ( this.options.eventPassthrough == 'horizontal' ) {
				this.initiated = false;
				return;
			}

			deltaY = 0;
		} else if ( this.directionLocked == 'v' ) {
			if ( this.options.eventPassthrough == 'horizontal' ) {
				e.preventDefault();
			} else if ( this.options.eventPassthrough == 'vertical' ) {
				this.initiated = false;
				return;
			}

			deltaX = 0;
		}

		deltaX = this.hasHorizontalScroll ? deltaX : 0;
		deltaY = this.hasVerticalScroll ? deltaY : 0;

		newX = this.x + deltaX;
		newY = this.y + deltaY;

		// Slow down if outside of the boundaries
		if ( newX > 0 || newX < this.maxScrollX ) {
			newX = this.options.bounce ? this.x + deltaX / 3 : newX > 0 ? 0 : this.maxScrollX;
		}
		if ( newY > 0 || newY < this.maxScrollY ) {
			newY = this.options.bounce ? this.y + deltaY / 3 : newY > 0 ? 0 : this.maxScrollY;
		}

		this.directionX = deltaX > 0 ? -1 : deltaX < 0 ? 1 : 0;
		this.directionY = deltaY > 0 ? -1 : deltaY < 0 ? 1 : 0;

		if ( !this.moved ) {
			this._execEvent('scrollStart');
		}

		this.moved = true;

		this._translate(newX, newY);

/* REPLACE START: _move */
		if ( timestamp - this.startTime > 300 ) {
			this.startTime = timestamp;
			this.startX = this.x;
			this.startY = this.y;

			if ( this.options.probeType == 1 ) {
				this._execEvent('scroll');
			}
		}

		if ( this.options.probeType > 1 ) {
			this._execEvent('scroll');
		}
/* REPLACE END: _move */

	},

	_end: function (e) {
		if ( !this.enabled || utils.eventType[e.type] !== this.initiated ) {
			return;
		}

		if ( this.options.preventDefault && !utils.preventDefaultException(e.target, this.options.preventDefaultException) ) {
			e.preventDefault();
		}

		var point = e.changedTouches ? e.changedTouches[0] : e,
			momentumX,
			momentumY,
			duration = utils.getTime() - this.startTime,
			newX = Math.round(this.x),
			newY = Math.round(this.y),
			distanceX = Math.abs(newX - this.startX),
			distanceY = Math.abs(newY - this.startY),
			time = 0,
			easing = '';

		this.isInTransition = 0;
		this.initiated = 0;
		this.endTime = utils.getTime();

		// reset if we are outside of the boundaries
		if ( this.resetPosition(this.options.bounceTime) ) {
			return;
		}

		this.scrollTo(newX, newY);	// ensures that the last position is rounded

		// we scrolled less than 10 pixels
		if ( !this.moved ) {
			if ( this.options.tap ) {
				utils.tap(e, this.options.tap);
			}

			if ( this.options.click ) {
				utils.click(e);
			}

			this._execEvent('scrollCancel');
			return;
		}

		if ( this._events.flick && duration < 200 && distanceX < 100 && distanceY < 100 ) {
			this._execEvent('flick');
			return;
		}

		// start momentum animation if needed
		if ( this.options.momentum && duration < 300 ) {
			momentumX = this.hasHorizontalScroll ? utils.momentum(this.x, this.startX, duration, this.maxScrollX, this.options.bounce ? this.wrapperWidth : 0, this.options.deceleration) : { destination: newX, duration: 0 };
			momentumY = this.hasVerticalScroll ? utils.momentum(this.y, this.startY, duration, this.maxScrollY, this.options.bounce ? this.wrapperHeight : 0, this.options.deceleration) : { destination: newY, duration: 0 };
			newX = momentumX.destination;
			newY = momentumY.destination;
			time = Math.max(momentumX.duration, momentumY.duration);
			this.isInTransition = 1;
		}


		if ( this.options.snap ) {
			var snap = this._nearestSnap(newX, newY);
			this.currentPage = snap;
			time = this.options.snapSpeed || Math.max(
					Math.max(
						Math.min(Math.abs(newX - snap.x), 1000),
						Math.min(Math.abs(newY - snap.y), 1000)
					), 300);
			newX = snap.x;
			newY = snap.y;

			this.directionX = 0;
			this.directionY = 0;
			easing = this.options.bounceEasing;
		}

// INSERT POINT: _end

		if ( newX != this.x || newY != this.y ) {
			// change easing function when scroller goes out of the boundaries
			if ( newX > 0 || newX < this.maxScrollX || newY > 0 || newY < this.maxScrollY ) {
				easing = utils.ease.quadratic;
			}

			this.scrollTo(newX, newY, time, easing);
			return;
		}

		this._execEvent('scrollEnd');
	},

	_resize: function () {
		var that = this;

		clearTimeout(this.resizeTimeout);

		this.resizeTimeout = setTimeout(function () {
			that.refresh();
		}, this.options.resizePolling);
	},

	resetPosition: function (time) {
		var x = this.x,
			y = this.y;

		time = time || 0;

		if ( !this.hasHorizontalScroll || this.x > 0 ) {
			x = 0;
		} else if ( this.x < this.maxScrollX ) {
			x = this.maxScrollX;
		}

		if ( !this.hasVerticalScroll || this.y > 0 ) {
			y = 0;
		} else if ( this.y < this.maxScrollY ) {
			y = this.maxScrollY;
		}

		if ( x == this.x && y == this.y ) {
			return false;
		}

		this.scrollTo(x, y, time, this.options.bounceEasing);

		return true;
	},

	disable: function () {
		this.enabled = false;
	},

	enable: function () {
		this.enabled = true;
	},

	refresh: function () {
		var rf = this.wrapper.offsetHeight;		// Force reflow

		this.wrapperWidth	= this.wrapper.clientWidth;
		this.wrapperHeight	= this.wrapper.clientHeight;

/* REPLACE START: refresh */

		this.scrollerWidth	= this.scroller.offsetWidth;
		this.scrollerHeight	= this.scroller.offsetHeight;

		this.maxScrollX		= this.wrapperWidth - this.scrollerWidth;
		this.maxScrollY		= this.wrapperHeight - this.scrollerHeight;

/* REPLACE END: refresh */

		this.hasHorizontalScroll	= this.options.scrollX && this.maxScrollX < 0;
		this.hasVerticalScroll		= this.options.scrollY && this.maxScrollY < 0;

		if ( !this.hasHorizontalScroll ) {
			this.maxScrollX = 0;
			this.scrollerWidth = this.wrapperWidth;
		}

		if ( !this.hasVerticalScroll ) {
			this.maxScrollY = 0;
			this.scrollerHeight = this.wrapperHeight;
		}

		this.endTime = 0;
		this.directionX = 0;
		this.directionY = 0;

		this.wrapperOffset = utils.offset(this.wrapper);

		this._execEvent('refresh');

		this.resetPosition();

// INSERT POINT: _refresh

	},

	on: function (type, fn) {
		if ( !this._events[type] ) {
			this._events[type] = [];
		}

		this._events[type].push(fn);
	},

	off: function (type, fn) {
		if ( !this._events[type] ) {
			return;
		}

		var index = this._events[type].indexOf(fn);

		if ( index > -1 ) {
			this._events[type].splice(index, 1);
		}
	},

	_execEvent: function (type) {
		if ( !this._events[type] ) {
			return;
		}

		var i = 0,
			l = this._events[type].length;

		if ( !l ) {
			return;
		}

		for ( ; i < l; i++ ) {
			this._events[type][i].apply(this, [].slice.call(arguments, 1));
		}
	},

	scrollBy: function (x, y, time, easing) {
		x = this.x + x;
		y = this.y + y;
		time = time || 0;

		this.scrollTo(x, y, time, easing);
	},

	scrollTo: function (x, y, time, easing) {
		easing = easing || utils.ease.circular;

		this.isInTransition = this.options.useTransition && time > 0;

		if ( !time || (this.options.useTransition && easing.style) ) {
			this._transitionTimingFunction(easing.style);
			this._transitionTime(time);
			this._translate(x, y);
		} else {
			this._animate(x, y, time, easing.fn);
		}
	},

	scrollToElement: function (el, time, offsetX, offsetY, easing) {
		el = el.nodeType ? el : this.scroller.querySelector(el);

		if ( !el ) {
			return;
		}

		var pos = utils.offset(el);

		pos.left -= this.wrapperOffset.left;
		pos.top  -= this.wrapperOffset.top;

		// if offsetX/Y are true we center the element to the screen
		if ( offsetX === true ) {
			offsetX = Math.round(el.offsetWidth / 2 - this.wrapper.offsetWidth / 2);
		}
		if ( offsetY === true ) {
			offsetY = Math.round(el.offsetHeight / 2 - this.wrapper.offsetHeight / 2);
		}

		pos.left -= offsetX || 0;
		pos.top  -= offsetY || 0;

		pos.left = pos.left > 0 ? 0 : pos.left < this.maxScrollX ? this.maxScrollX : pos.left;
		pos.top  = pos.top  > 0 ? 0 : pos.top  < this.maxScrollY ? this.maxScrollY : pos.top;

		time = time === undefined || time === null || time === 'auto' ? Math.max(Math.abs(this.x-pos.left), Math.abs(this.y-pos.top)) : time;

		this.scrollTo(pos.left, pos.top, time, easing);
	},

	_transitionTime: function (time) {
		time = time || 0;

		this.scrollerStyle[utils.style.transitionDuration] = time + 'ms';

		if ( !time && utils.isBadAndroid ) {
			this.scrollerStyle[utils.style.transitionDuration] = '0.001s';
		}


		if ( this.indicators ) {
			for ( var i = this.indicators.length; i--; ) {
				this.indicators[i].transitionTime(time);
			}
		}


// INSERT POINT: _transitionTime

	},

	_transitionTimingFunction: function (easing) {
		this.scrollerStyle[utils.style.transitionTimingFunction] = easing;


		if ( this.indicators ) {
			for ( var i = this.indicators.length; i--; ) {
				this.indicators[i].transitionTimingFunction(easing);
			}
		}


// INSERT POINT: _transitionTimingFunction

	},

	_translate: function (x, y) {
		if ( this.options.useTransform ) {

/* REPLACE START: _translate */

			this.scrollerStyle[utils.style.transform] = 'translate(' + x + 'px,' + y + 'px)' + this.translateZ;

/* REPLACE END: _translate */

		} else {
			x = Math.round(x);
			y = Math.round(y);
			this.scrollerStyle.left = x + 'px';
			this.scrollerStyle.top = y + 'px';
		}

		this.x = x;
		this.y = y;


	if ( this.indicators ) {
		for ( var i = this.indicators.length; i--; ) {
			this.indicators[i].updatePosition();
		}
	}


// INSERT POINT: _translate

	},

	_initEvents: function (remove) {
		var eventType = remove ? utils.removeEvent : utils.addEvent,
			target = this.options.bindToWrapper ? this.wrapper : window;

		eventType(window, 'orientationchange', this);
		eventType(window, 'resize', this);

		if ( this.options.click ) {
			eventType(this.wrapper, 'click', this, true);
		}

		if ( !this.options.disableMouse ) {
			eventType(this.wrapper, 'mousedown', this);
			eventType(target, 'mousemove', this);
			eventType(target, 'mousecancel', this);
			eventType(target, 'mouseup', this);
		}

		if ( utils.hasPointer && !this.options.disablePointer ) {
			eventType(this.wrapper, utils.prefixPointerEvent('pointerdown'), this);
			eventType(target, utils.prefixPointerEvent('pointermove'), this);
			eventType(target, utils.prefixPointerEvent('pointercancel'), this);
			eventType(target, utils.prefixPointerEvent('pointerup'), this);
		}

		if ( utils.hasTouch && !this.options.disableTouch ) {
			eventType(this.wrapper, 'touchstart', this);
			eventType(target, 'touchmove', this);
			eventType(target, 'touchcancel', this);
			eventType(target, 'touchend', this);
		}

		eventType(this.scroller, 'transitionend', this);
		eventType(this.scroller, 'webkitTransitionEnd', this);
		eventType(this.scroller, 'oTransitionEnd', this);
		eventType(this.scroller, 'MSTransitionEnd', this);
	},

	getComputedPosition: function () {
		var matrix = window.getComputedStyle(this.scroller, null),
			x, y;

		if ( this.options.useTransform ) {
			matrix = matrix[utils.style.transform].split(')')[0].split(', ');
			x = +(matrix[12] || matrix[4]);
			y = +(matrix[13] || matrix[5]);
		} else {
			x = +matrix.left.replace(/[^-\d.]/g, '');
			y = +matrix.top.replace(/[^-\d.]/g, '');
		}

		return { x: x, y: y };
	},

	_initIndicators: function () {
		var interactive = this.options.interactiveScrollbars,
			customStyle = typeof this.options.scrollbars != 'string',
			indicators = [],
			indicator;

		var that = this;

		this.indicators = [];

		if ( this.options.scrollbars ) {
			// Vertical scrollbar
			if ( this.options.scrollY ) {
				indicator = {
					el: createDefaultScrollbar('v', interactive, this.options.scrollbars),
					interactive: interactive,
					defaultScrollbars: true,
					customStyle: customStyle,
					resize: this.options.resizeScrollbars,
					shrink: this.options.shrinkScrollbars,
					fade: this.options.fadeScrollbars,
					listenX: false
				};

				this.wrapper.appendChild(indicator.el);
				indicators.push(indicator);
			}

			// Horizontal scrollbar
			if ( this.options.scrollX ) {
				indicator = {
					el: createDefaultScrollbar('h', interactive, this.options.scrollbars),
					interactive: interactive,
					defaultScrollbars: true,
					customStyle: customStyle,
					resize: this.options.resizeScrollbars,
					shrink: this.options.shrinkScrollbars,
					fade: this.options.fadeScrollbars,
					listenY: false
				};

				this.wrapper.appendChild(indicator.el);
				indicators.push(indicator);
			}
		}

		if ( this.options.indicators ) {
			// TODO: check concat compatibility
			indicators = indicators.concat(this.options.indicators);
		}

		for ( var i = indicators.length; i--; ) {
			this.indicators.push( new Indicator(this, indicators[i]) );
		}

		// TODO: check if we can use array.map (wide compatibility and performance issues)
		function _indicatorsMap (fn) {
			for ( var i = that.indicators.length; i--; ) {
				fn.call(that.indicators[i]);
			}
		}

		if ( this.options.fadeScrollbars ) {
			this.on('scrollEnd', function () {
				_indicatorsMap(function () {
					this.fade();
				});
			});

			this.on('scrollCancel', function () {
				_indicatorsMap(function () {
					this.fade();
				});
			});

			this.on('scrollStart', function () {
				_indicatorsMap(function () {
					this.fade(1);
				});
			});

			this.on('beforeScrollStart', function () {
				_indicatorsMap(function () {
					this.fade(1, true);
				});
			});
		}


		this.on('refresh', function () {
			_indicatorsMap(function () {
				this.refresh();
			});
		});

		this.on('destroy', function () {
			_indicatorsMap(function () {
				this.destroy();
			});

			delete this.indicators;
		});
	},

	_initWheel: function () {
		utils.addEvent(this.wrapper, 'wheel', this);
		utils.addEvent(this.wrapper, 'mousewheel', this);
		utils.addEvent(this.wrapper, 'DOMMouseScroll', this);

		this.on('destroy', function () {
			utils.removeEvent(this.wrapper, 'wheel', this);
			utils.removeEvent(this.wrapper, 'mousewheel', this);
			utils.removeEvent(this.wrapper, 'DOMMouseScroll', this);
		});
	},

	_wheel: function (e) {
		if ( !this.enabled ) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();

		var wheelDeltaX, wheelDeltaY,
			newX, newY,
			that = this;

		if ( this.wheelTimeout === undefined ) {
			that._execEvent('scrollStart');
		}

		// Execute the scrollEnd event after 400ms the wheel stopped scrolling
		clearTimeout(this.wheelTimeout);
		this.wheelTimeout = setTimeout(function () {
			that._execEvent('scrollEnd');
			that.wheelTimeout = undefined;
		}, 400);

		if ( 'deltaX' in e ) {
			if (e.deltaMode === 1) {
				wheelDeltaX = -e.deltaX * this.options.mouseWheelSpeed;
				wheelDeltaY = -e.deltaY * this.options.mouseWheelSpeed;
			} else {
				wheelDeltaX = -e.deltaX;
				wheelDeltaY = -e.deltaY;
			}
		} else if ( 'wheelDeltaX' in e ) {
			wheelDeltaX = e.wheelDeltaX / 120 * this.options.mouseWheelSpeed;
			wheelDeltaY = e.wheelDeltaY / 120 * this.options.mouseWheelSpeed;
		} else if ( 'wheelDelta' in e ) {
			wheelDeltaX = wheelDeltaY = e.wheelDelta / 120 * this.options.mouseWheelSpeed;
		} else if ( 'detail' in e ) {
			wheelDeltaX = wheelDeltaY = -e.detail / 3 * this.options.mouseWheelSpeed;
		} else {
			return;
		}

		wheelDeltaX *= this.options.invertWheelDirection;
		wheelDeltaY *= this.options.invertWheelDirection;

		if ( !this.hasVerticalScroll ) {
			wheelDeltaX = wheelDeltaY;
			wheelDeltaY = 0;
		}

		if ( this.options.snap ) {
			newX = this.currentPage.pageX;
			newY = this.currentPage.pageY;

			if ( wheelDeltaX > 0 ) {
				newX--;
			} else if ( wheelDeltaX < 0 ) {
				newX++;
			}

			if ( wheelDeltaY > 0 ) {
				newY--;
			} else if ( wheelDeltaY < 0 ) {
				newY++;
			}

			this.goToPage(newX, newY);

			return;
		}

		newX = this.x + Math.round(this.hasHorizontalScroll ? wheelDeltaX : 0);
		newY = this.y + Math.round(this.hasVerticalScroll ? wheelDeltaY : 0);

		if ( newX > 0 ) {
			newX = 0;
		} else if ( newX < this.maxScrollX ) {
			newX = this.maxScrollX;
		}

		if ( newY > 0 ) {
			newY = 0;
		} else if ( newY < this.maxScrollY ) {
			newY = this.maxScrollY;
		}

		this.scrollTo(newX, newY, 0);

		if ( this.options.probeType > 1 ) {
			this._execEvent('scroll');
		}

// INSERT POINT: _wheel
	},

	_initSnap: function () {
		this.currentPage = {};

		if ( typeof this.options.snap == 'string' ) {
			this.options.snap = this.scroller.querySelectorAll(this.options.snap);
		}

		this.on('refresh', function () {
			var i = 0, l,
				m = 0, n,
				cx, cy,
				x = 0, y,
				stepX = this.options.snapStepX || this.wrapperWidth,
				stepY = this.options.snapStepY || this.wrapperHeight,
				el;

			this.pages = [];

			if ( !this.wrapperWidth || !this.wrapperHeight || !this.scrollerWidth || !this.scrollerHeight ) {
				return;
			}

			if ( this.options.snap === true ) {
				cx = Math.round( stepX / 2 );
				cy = Math.round( stepY / 2 );

				while ( x > -this.scrollerWidth ) {
					this.pages[i] = [];
					l = 0;
					y = 0;

					while ( y > -this.scrollerHeight ) {
						this.pages[i][l] = {
							x: Math.max(x, this.maxScrollX),
							y: Math.max(y, this.maxScrollY),
							width: stepX,
							height: stepY,
							cx: x - cx,
							cy: y - cy
						};

						y -= stepY;
						l++;
					}

					x -= stepX;
					i++;
				}
			} else {
				el = this.options.snap;
				l = el.length;
				n = -1;

				for ( ; i < l; i++ ) {
					if ( i === 0 || el[i].offsetLeft <= el[i-1].offsetLeft ) {
						m = 0;
						n++;
					}

					if ( !this.pages[m] ) {
						this.pages[m] = [];
					}

					x = Math.max(-el[i].offsetLeft, this.maxScrollX);
					y = Math.max(-el[i].offsetTop, this.maxScrollY);
					cx = x - Math.round(el[i].offsetWidth / 2);
					cy = y - Math.round(el[i].offsetHeight / 2);

					this.pages[m][n] = {
						x: x,
						y: y,
						width: el[i].offsetWidth,
						height: el[i].offsetHeight,
						cx: cx,
						cy: cy
					};

					if ( x > this.maxScrollX ) {
						m++;
					}
				}
			}

			this.goToPage(this.currentPage.pageX || 0, this.currentPage.pageY || 0, 0);

			// Update snap threshold if needed
			if ( this.options.snapThreshold % 1 === 0 ) {
				this.snapThresholdX = this.options.snapThreshold;
				this.snapThresholdY = this.options.snapThreshold;
			} else {
				this.snapThresholdX = Math.round(this.pages[this.currentPage.pageX][this.currentPage.pageY].width * this.options.snapThreshold);
				this.snapThresholdY = Math.round(this.pages[this.currentPage.pageX][this.currentPage.pageY].height * this.options.snapThreshold);
			}
		});

		this.on('flick', function () {
			var time = this.options.snapSpeed || Math.max(
					Math.max(
						Math.min(Math.abs(this.x - this.startX), 1000),
						Math.min(Math.abs(this.y - this.startY), 1000)
					), 300);

			this.goToPage(
				this.currentPage.pageX + this.directionX,
				this.currentPage.pageY + this.directionY,
				time
			);
		});
	},

	_nearestSnap: function (x, y) {
		if ( !this.pages.length ) {
			return { x: 0, y: 0, pageX: 0, pageY: 0 };
		}

		var i = 0,
			l = this.pages.length,
			m = 0;

		// Check if we exceeded the snap threshold
		if ( Math.abs(x - this.absStartX) < this.snapThresholdX &&
			Math.abs(y - this.absStartY) < this.snapThresholdY ) {
			return this.currentPage;
		}

		if ( x > 0 ) {
			x = 0;
		} else if ( x < this.maxScrollX ) {
			x = this.maxScrollX;
		}

		if ( y > 0 ) {
			y = 0;
		} else if ( y < this.maxScrollY ) {
			y = this.maxScrollY;
		}

		for ( ; i < l; i++ ) {
			if ( x >= this.pages[i][0].cx ) {
				x = this.pages[i][0].x;
				break;
			}
		}

		l = this.pages[i].length;

		for ( ; m < l; m++ ) {
			if ( y >= this.pages[0][m].cy ) {
				y = this.pages[0][m].y;
				break;
			}
		}

		if ( i == this.currentPage.pageX ) {
			i += this.directionX;

			if ( i < 0 ) {
				i = 0;
			} else if ( i >= this.pages.length ) {
				i = this.pages.length - 1;
			}

			x = this.pages[i][0].x;
		}

		if ( m == this.currentPage.pageY ) {
			m += this.directionY;

			if ( m < 0 ) {
				m = 0;
			} else if ( m >= this.pages[0].length ) {
				m = this.pages[0].length - 1;
			}

			y = this.pages[0][m].y;
		}

		return {
			x: x,
			y: y,
			pageX: i,
			pageY: m
		};
	},

	goToPage: function (x, y, time, easing) {
		easing = easing || this.options.bounceEasing;

		if ( x >= this.pages.length ) {
			x = this.pages.length - 1;
		} else if ( x < 0 ) {
			x = 0;
		}

		if ( y >= this.pages[x].length ) {
			y = this.pages[x].length - 1;
		} else if ( y < 0 ) {
			y = 0;
		}

		var posX = this.pages[x][y].x,
			posY = this.pages[x][y].y;

		time = time === undefined ? this.options.snapSpeed || Math.max(
			Math.max(
				Math.min(Math.abs(posX - this.x), 1000),
				Math.min(Math.abs(posY - this.y), 1000)
			), 300) : time;

		this.currentPage = {
			x: posX,
			y: posY,
			pageX: x,
			pageY: y
		};

		this.scrollTo(posX, posY, time, easing);
	},

	next: function (time, easing) {
		var x = this.currentPage.pageX,
			y = this.currentPage.pageY;

		x++;

		if ( x >= this.pages.length && this.hasVerticalScroll ) {
			x = 0;
			y++;
		}

		this.goToPage(x, y, time, easing);
	},

	prev: function (time, easing) {
		var x = this.currentPage.pageX,
			y = this.currentPage.pageY;

		x--;

		if ( x < 0 && this.hasVerticalScroll ) {
			x = 0;
			y--;
		}

		this.goToPage(x, y, time, easing);
	},

	_initKeys: function (e) {
		// default key bindings
		var keys = {
			pageUp: 33,
			pageDown: 34,
			end: 35,
			home: 36,
			left: 37,
			up: 38,
			right: 39,
			down: 40
		};
		var i;

		// if you give me characters I give you keycode
		if ( typeof this.options.keyBindings == 'object' ) {
			for ( i in this.options.keyBindings ) {
				if ( typeof this.options.keyBindings[i] == 'string' ) {
					this.options.keyBindings[i] = this.options.keyBindings[i].toUpperCase().charCodeAt(0);
				}
			}
		} else {
			this.options.keyBindings = {};
		}

		for ( i in keys ) {
			this.options.keyBindings[i] = this.options.keyBindings[i] || keys[i];
		}

		utils.addEvent(window, 'keydown', this);

		this.on('destroy', function () {
			utils.removeEvent(window, 'keydown', this);
		});
	},

	_key: function (e) {
		if ( !this.enabled ) {
			return;
		}

		var snap = this.options.snap,	// we are using this alot, better to cache it
			newX = snap ? this.currentPage.pageX : this.x,
			newY = snap ? this.currentPage.pageY : this.y,
			now = utils.getTime(),
			prevTime = this.keyTime || 0,
			acceleration = 0.250,
			pos;

		if ( this.options.useTransition && this.isInTransition ) {
			pos = this.getComputedPosition();

			this._translate(Math.round(pos.x), Math.round(pos.y));
			this.isInTransition = false;
		}

		this.keyAcceleration = now - prevTime < 200 ? Math.min(this.keyAcceleration + acceleration, 50) : 0;

		switch ( e.keyCode ) {
			case this.options.keyBindings.pageUp:
				if ( this.hasHorizontalScroll && !this.hasVerticalScroll ) {
					newX += snap ? 1 : this.wrapperWidth;
				} else {
					newY += snap ? 1 : this.wrapperHeight;
				}
				break;
			case this.options.keyBindings.pageDown:
				if ( this.hasHorizontalScroll && !this.hasVerticalScroll ) {
					newX -= snap ? 1 : this.wrapperWidth;
				} else {
					newY -= snap ? 1 : this.wrapperHeight;
				}
				break;
			case this.options.keyBindings.end:
				newX = snap ? this.pages.length-1 : this.maxScrollX;
				newY = snap ? this.pages[0].length-1 : this.maxScrollY;
				break;
			case this.options.keyBindings.home:
				newX = 0;
				newY = 0;
				break;
			case this.options.keyBindings.left:
				newX += snap ? -1 : 5 + this.keyAcceleration>>0;
				break;
			case this.options.keyBindings.up:
				newY += snap ? 1 : 5 + this.keyAcceleration>>0;
				break;
			case this.options.keyBindings.right:
				newX -= snap ? -1 : 5 + this.keyAcceleration>>0;
				break;
			case this.options.keyBindings.down:
				newY -= snap ? 1 : 5 + this.keyAcceleration>>0;
				break;
			default:
				return;
		}

		if ( snap ) {
			this.goToPage(newX, newY);
			return;
		}

		if ( newX > 0 ) {
			newX = 0;
			this.keyAcceleration = 0;
		} else if ( newX < this.maxScrollX ) {
			newX = this.maxScrollX;
			this.keyAcceleration = 0;
		}

		if ( newY > 0 ) {
			newY = 0;
			this.keyAcceleration = 0;
		} else if ( newY < this.maxScrollY ) {
			newY = this.maxScrollY;
			this.keyAcceleration = 0;
		}

		this.scrollTo(newX, newY, 0);

		this.keyTime = now;
	},

	_animate: function (destX, destY, duration, easingFn) {
		var that = this,
			startX = this.x,
			startY = this.y,
			startTime = utils.getTime(),
			destTime = startTime + duration;

		function step () {
			var now = utils.getTime(),
				newX, newY,
				easing;

			if ( now >= destTime ) {
				that.isAnimating = false;
				that._translate(destX, destY);
				
				if ( !that.resetPosition(that.options.bounceTime) ) {
					that._execEvent('scrollEnd');
				}

				return;
			}

			now = ( now - startTime ) / duration;
			easing = easingFn(now);
			newX = ( destX - startX ) * easing + startX;
			newY = ( destY - startY ) * easing + startY;
			that._translate(newX, newY);

			if ( that.isAnimating ) {
				rAF(step);
			}

			if ( that.options.probeType == 3 ) {
				that._execEvent('scroll');
			}
		}

		this.isAnimating = true;
		step();
	},

	handleEvent: function (e) {
		switch ( e.type ) {
			case 'touchstart':
			case 'pointerdown':
			case 'MSPointerDown':
			case 'mousedown':
				this._start(e);
				break;
			case 'touchmove':
			case 'pointermove':
			case 'MSPointerMove':
			case 'mousemove':
				this._move(e);
				break;
			case 'touchend':
			case 'pointerup':
			case 'MSPointerUp':
			case 'mouseup':
			case 'touchcancel':
			case 'pointercancel':
			case 'MSPointerCancel':
			case 'mousecancel':
				this._end(e);
				break;
			case 'orientationchange':
			case 'resize':
				this._resize();
				break;
			case 'transitionend':
			case 'webkitTransitionEnd':
			case 'oTransitionEnd':
			case 'MSTransitionEnd':
				this._transitionEnd(e);
				break;
			case 'wheel':
			case 'DOMMouseScroll':
			case 'mousewheel':
				this._wheel(e);
				break;
			case 'keydown':
				this._key(e);
				break;
			case 'click':
				if ( !e._constructed ) {
					e.preventDefault();
					e.stopPropagation();
				}
				break;
		}
	}
};
function createDefaultScrollbar (direction, interactive, type) {
	var scrollbar = document.createElement('div'),
		indicator = document.createElement('div');

	if ( type === true ) {
		scrollbar.style.cssText = 'position:absolute;z-index:9999;background:rgba(0,0,0,0.1);';
		indicator.style.cssText = '-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;position:absolute;background:rgba(0,0,0,0.6);';
	}

	indicator.className = 'iScrollIndicator';

	if ( direction == 'h' ) {
		if ( type === true ) {
			scrollbar.style.cssText += ';height:.5rem;left:0px;right:0px;bottom:0';
			indicator.style.height = '100%';
		}
		scrollbar.className = 'iScrollHorizontalScrollbar';
	} else {
		if ( type === true ) {
			scrollbar.style.cssText += ';width:.2rem;bottom:0px;top:0px;right:0px';
			indicator.style.width = '100%';
		}
		scrollbar.className = 'iScrollVerticalScrollbar';
	}

	scrollbar.style.cssText += ';overflow:hidden';

	if ( !interactive ) {
		scrollbar.style.pointerEvents = 'none';
	}

	scrollbar.appendChild(indicator);

	return scrollbar;
}

function Indicator (scroller, options) {
	this.wrapper = typeof options.el == 'string' ? document.querySelector(options.el) : options.el;
	this.wrapperStyle = this.wrapper.style;
	this.indicator = this.wrapper.children[0];
	this.indicatorStyle = this.indicator.style;
	this.scroller = scroller;

	this.options = {
		listenX: true,
		listenY: true,
		interactive: false,
		resize: true,
		defaultScrollbars: false,
		shrink: false,
		fade: false,
		speedRatioX: 0,
		speedRatioY: 0
	};

	for ( var i in options ) {
		this.options[i] = options[i];
	}

	this.sizeRatioX = 1;
	this.sizeRatioY = 1;
	this.maxPosX = 0;
	this.maxPosY = 0;

	if ( this.options.interactive ) {
		if ( !this.options.disableTouch ) {
			utils.addEvent(this.indicator, 'touchstart', this);
			utils.addEvent(window, 'touchend', this);
		}
		if ( !this.options.disablePointer ) {
			utils.addEvent(this.indicator, utils.prefixPointerEvent('pointerdown'), this);
			utils.addEvent(window, utils.prefixPointerEvent('pointerup'), this);
		}
		if ( !this.options.disableMouse ) {
			utils.addEvent(this.indicator, 'mousedown', this);
			utils.addEvent(window, 'mouseup', this);
		}
	}

	if ( this.options.fade ) {
		this.wrapperStyle[utils.style.transform] = this.scroller.translateZ;
		this.wrapperStyle[utils.style.transitionDuration] = utils.isBadAndroid ? '0.001s' : '0ms';
		this.wrapperStyle.opacity = '0';
	}
}

Indicator.prototype = {
	handleEvent: function (e) {
		switch ( e.type ) {
			case 'touchstart':
			case 'pointerdown':
			case 'MSPointerDown':
			case 'mousedown':
				this._start(e);
				break;
			case 'touchmove':
			case 'pointermove':
			case 'MSPointerMove':
			case 'mousemove':
				this._move(e);
				break;
			case 'touchend':
			case 'pointerup':
			case 'MSPointerUp':
			case 'mouseup':
			case 'touchcancel':
			case 'pointercancel':
			case 'MSPointerCancel':
			case 'mousecancel':
				this._end(e);
				break;
		}
	},

	destroy: function () {
		if ( this.options.interactive ) {
			utils.removeEvent(this.indicator, 'touchstart', this);
			utils.removeEvent(this.indicator, utils.prefixPointerEvent('pointerdown'), this);
			utils.removeEvent(this.indicator, 'mousedown', this);

			utils.removeEvent(window, 'touchmove', this);
			utils.removeEvent(window, utils.prefixPointerEvent('pointermove'), this);
			utils.removeEvent(window, 'mousemove', this);

			utils.removeEvent(window, 'touchend', this);
			utils.removeEvent(window, utils.prefixPointerEvent('pointerup'), this);
			utils.removeEvent(window, 'mouseup', this);
		}

		if ( this.options.defaultScrollbars ) {
			this.wrapper.parentNode.removeChild(this.wrapper);
		}
	},

	_start: function (e) {
		var point = e.touches ? e.touches[0] : e;

		e.preventDefault();
		e.stopPropagation();

		this.transitionTime();

		this.initiated = true;
		this.moved = false;
		this.lastPointX	= point.pageX;
		this.lastPointY	= point.pageY;

		this.startTime	= utils.getTime();

		if ( !this.options.disableTouch ) {
			utils.addEvent(window, 'touchmove', this);
		}
		if ( !this.options.disablePointer ) {
			utils.addEvent(window, utils.prefixPointerEvent('pointermove'), this);
		}
		if ( !this.options.disableMouse ) {
			utils.addEvent(window, 'mousemove', this);
		}

		this.scroller._execEvent('beforeScrollStart');
	},

	_move: function (e) {
		var point = e.touches ? e.touches[0] : e,
			deltaX, deltaY,
			newX, newY,
			timestamp = utils.getTime();

		if ( !this.moved ) {
			this.scroller._execEvent('scrollStart');
		}

		this.moved = true;

		deltaX = point.pageX - this.lastPointX;
		this.lastPointX = point.pageX;

		deltaY = point.pageY - this.lastPointY;
		this.lastPointY = point.pageY;

		newX = this.x + deltaX;
		newY = this.y + deltaY;

		this._pos(newX, newY);


		if ( this.scroller.options.probeType == 1 && timestamp - this.startTime > 300 ) {
			this.startTime = timestamp;
			this.scroller._execEvent('scroll');
		} else if ( this.scroller.options.probeType > 1 ) {
			this.scroller._execEvent('scroll');
		}


// INSERT POINT: indicator._move

		e.preventDefault();
		e.stopPropagation();
	},

	_end: function (e) {
		if ( !this.initiated ) {
			return;
		}

		this.initiated = false;

		e.preventDefault();
		e.stopPropagation();

		utils.removeEvent(window, 'touchmove', this);
		utils.removeEvent(window, utils.prefixPointerEvent('pointermove'), this);
		utils.removeEvent(window, 'mousemove', this);

		if ( this.scroller.options.snap ) {
			var snap = this.scroller._nearestSnap(this.scroller.x, this.scroller.y);

			var time = this.options.snapSpeed || Math.max(
					Math.max(
						Math.min(Math.abs(this.scroller.x - snap.x), 1000),
						Math.min(Math.abs(this.scroller.y - snap.y), 1000)
					), 300);

			if ( this.scroller.x != snap.x || this.scroller.y != snap.y ) {
				this.scroller.directionX = 0;
				this.scroller.directionY = 0;
				this.scroller.currentPage = snap;
				this.scroller.scrollTo(snap.x, snap.y, time, this.scroller.options.bounceEasing);
			}
		}

		if ( this.moved ) {
			this.scroller._execEvent('scrollEnd');
		}
	},

	transitionTime: function (time) {
		time = time || 0;
		this.indicatorStyle[utils.style.transitionDuration] = time + 'ms';

		if ( !time && utils.isBadAndroid ) {
			this.indicatorStyle[utils.style.transitionDuration] = '0.001s';
		}
	},

	transitionTimingFunction: function (easing) {
		this.indicatorStyle[utils.style.transitionTimingFunction] = easing;
	},

	refresh: function () {
		this.transitionTime();

		if ( this.options.listenX && !this.options.listenY ) {
			this.indicatorStyle.display = this.scroller.hasHorizontalScroll ? 'block' : 'none';
		} else if ( this.options.listenY && !this.options.listenX ) {
			this.indicatorStyle.display = this.scroller.hasVerticalScroll ? 'block' : 'none';
		} else {
			this.indicatorStyle.display = this.scroller.hasHorizontalScroll || this.scroller.hasVerticalScroll ? 'block' : 'none';
		}

		if ( this.scroller.hasHorizontalScroll && this.scroller.hasVerticalScroll ) {
			utils.addClass(this.wrapper, 'iScrollBothScrollbars');
			utils.removeClass(this.wrapper, 'iScrollLoneScrollbar');

			if ( this.options.defaultScrollbars && this.options.customStyle ) {
				if ( this.options.listenX ) {
					this.wrapper.style.right = '8px';
				} else {
					this.wrapper.style.bottom = '8px';
				}
			}
		} else {
			utils.removeClass(this.wrapper, 'iScrollBothScrollbars');
			utils.addClass(this.wrapper, 'iScrollLoneScrollbar');

			if ( this.options.defaultScrollbars && this.options.customStyle ) {
				if ( this.options.listenX ) {
					this.wrapper.style.right = '0px';
				} else {
					this.wrapper.style.bottom = '0px';
				}
			}
		}

		var r = this.wrapper.offsetHeight;	// force refresh

		if ( this.options.listenX ) {
			this.wrapperWidth = this.wrapper.clientWidth;
			if ( this.options.resize ) {
				this.indicatorWidth = Math.max(Math.round(this.wrapperWidth * this.wrapperWidth / (this.scroller.scrollerWidth || this.wrapperWidth || 1)), 8);
				this.indicatorStyle.width = this.indicatorWidth + 'px';
			} else {
				this.indicatorWidth = this.indicator.clientWidth;
			}

			this.maxPosX = this.wrapperWidth - this.indicatorWidth;

			if ( this.options.shrink == 'clip' ) {
				this.minBoundaryX = -this.indicatorWidth + 8;
				this.maxBoundaryX = this.wrapperWidth - 8;
			} else {
				this.minBoundaryX = 0;
				this.maxBoundaryX = this.maxPosX;
			}

			this.sizeRatioX = this.options.speedRatioX || (this.scroller.maxScrollX && (this.maxPosX / this.scroller.maxScrollX));	
		}

		if ( this.options.listenY ) {
			this.wrapperHeight = this.wrapper.clientHeight;
			if ( this.options.resize ) {
				this.indicatorHeight = Math.max(Math.round(this.wrapperHeight * this.wrapperHeight / (this.scroller.scrollerHeight || this.wrapperHeight || 1)), 8);
				this.indicatorStyle.height = this.indicatorHeight + 'px';
			} else {
				this.indicatorHeight = this.indicator.clientHeight;
			}

			this.maxPosY = this.wrapperHeight - this.indicatorHeight;

			if ( this.options.shrink == 'clip' ) {
				this.minBoundaryY = -this.indicatorHeight + 8;
				this.maxBoundaryY = this.wrapperHeight - 8;
			} else {
				this.minBoundaryY = 0;
				this.maxBoundaryY = this.maxPosY;
			}

			this.maxPosY = this.wrapperHeight - this.indicatorHeight;
			this.sizeRatioY = this.options.speedRatioY || (this.scroller.maxScrollY && (this.maxPosY / this.scroller.maxScrollY));
		}

		this.updatePosition();
	},

	updatePosition: function () {
		var x = this.options.listenX && Math.round(this.sizeRatioX * this.scroller.x) || 0,
			y = this.options.listenY && Math.round(this.sizeRatioY * this.scroller.y) || 0;

		if ( !this.options.ignoreBoundaries ) {
			if ( x < this.minBoundaryX ) {
				if ( this.options.shrink == 'scale' ) {
					this.width = Math.max(this.indicatorWidth + x, 8);
					this.indicatorStyle.width = this.width + 'px';
				}
				x = this.minBoundaryX;
			} else if ( x > this.maxBoundaryX ) {
				if ( this.options.shrink == 'scale' ) {
					this.width = Math.max(this.indicatorWidth - (x - this.maxPosX), 8);
					this.indicatorStyle.width = this.width + 'px';
					x = this.maxPosX + this.indicatorWidth - this.width;
				} else {
					x = this.maxBoundaryX;
				}
			} else if ( this.options.shrink == 'scale' && this.width != this.indicatorWidth ) {
				this.width = this.indicatorWidth;
				this.indicatorStyle.width = this.width + 'px';
			}

			if ( y < this.minBoundaryY ) {
				if ( this.options.shrink == 'scale' ) {
					this.height = Math.max(this.indicatorHeight + y * 3, 8);
					this.indicatorStyle.height = this.height + 'px';
				}
				y = this.minBoundaryY;
			} else if ( y > this.maxBoundaryY ) {
				if ( this.options.shrink == 'scale' ) {
					this.height = Math.max(this.indicatorHeight - (y - this.maxPosY) * 3, 8);
					this.indicatorStyle.height = this.height + 'px';
					y = this.maxPosY + this.indicatorHeight - this.height;
				} else {
					y = this.maxBoundaryY;
				}
			} else if ( this.options.shrink == 'scale' && this.height != this.indicatorHeight ) {
				this.height = this.indicatorHeight;
				this.indicatorStyle.height = this.height + 'px';
			}
		}

		this.x = x;
		this.y = y;

		if ( this.scroller.options.useTransform ) {
			this.indicatorStyle[utils.style.transform] = 'translate(' + x + 'px,' + y + 'px)' + this.scroller.translateZ;
		} else {
			this.indicatorStyle.left = x + 'px';
			this.indicatorStyle.top = y + 'px';
		}
	},

	_pos: function (x, y) {
		if ( x < 0 ) {
			x = 0;
		} else if ( x > this.maxPosX ) {
			x = this.maxPosX;
		}

		if ( y < 0 ) {
			y = 0;
		} else if ( y > this.maxPosY ) {
			y = this.maxPosY;
		}

		x = this.options.listenX ? Math.round(x / this.sizeRatioX) : this.scroller.x;
		y = this.options.listenY ? Math.round(y / this.sizeRatioY) : this.scroller.y;

		this.scroller.scrollTo(x, y);
	},

	fade: function (val, hold) {
		if ( hold && !this.visible ) {
			return;
		}

		clearTimeout(this.fadeTimeout);
		this.fadeTimeout = null;

		var time = val ? 250 : 500,
			delay = val ? 0 : 300;

		val = val ? '1' : '0';

		this.wrapperStyle[utils.style.transitionDuration] = time + 'ms';

		this.fadeTimeout = setTimeout((function (val) {
			this.wrapperStyle.opacity = val;
			this.visible = +val;
		}).bind(this, val), delay);
	}
};

IScroll.utils = utils;

if ( typeof module != 'undefined' && module.exports ) {
	module.exports = IScroll;
} else {
	window.IScroll = IScroll;
}

})(window, document, Math);
/*页面滚动 end*/
/*外部JS end*/


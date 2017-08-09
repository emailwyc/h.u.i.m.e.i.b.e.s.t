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
$(document).ready(function(){
	
});
$(window).load(function() {
    $(".head").fadeIn({duration:500,queue: false}).animate({top:"3.5rem"},{duration:500,queue: false});
	$(".foot").fadeIn({duration:500,queue: false});
	var step = 0;
	var speed = 0;
	setTimeout(function(){
		$(".select").eq(step).animate({left:"0%"},{duration:300,queue: false});
		$(".select .btn .item").click(function(){
			$(this).addClass("cur").siblings().removeClass("cur");
			$(".select").eq(step).find("input").val($(this).text());
			if(step==speed){
				$(".page .fr").show();
				speed+=1;
			}
		});
		$(".page .fl").click(function(){
			if(step==1){
				$(".page .fl").hide();
			}
			$(".select").eq(step).animate({left:"100%"},{duration:300,queue: false});
			step-=1;
			$(".select").eq(step).animate({left:"0%"},{duration:300,queue: false});
			$(".page .fr").show();
		});
		$(".page .fr").click(function(){
			$(".select").eq(step).animate({left:"-100%"},{duration:300,queue: false});
			step+=1;
			if(step==speed&&speed<3){
				$(".page .fr").hide();
			}
			$(".select").eq(step).animate({left:"0%"},{duration:300,queue: false});
			$(".page .fl").show();
			if(speed>=3){
			    if(step==4){
				    $(".page").hide();
			    }
				speed+=1;
			}
		});
		$(".select .btn2 input:button").click(function(){
			var text1=$(".select .btn2 input").eq(0).val();
			var re=/^1[34578][0-9]\d{8}$/;
			if(!re.test(text1))
			{
				$.alert("请输入正确的手机号");
				return false;
			}
			var text2=$(".select .btn2 input").eq(1).val();
			if(text2==""){
				$.alert("请输入微信号");
				return false;
			}
			var data={"sex":$(".sex").val(),"age":$(".age").val(),"disease":$(".disease").val(),"explain":$(".explain").val(),"tel":$(".tel").val(),"weixin":$(".weixin").val()};
			$.ajax({
				type: "post",
				url: "/Json/freeClinic",
				data:data,
				dataType: "json", 
				beforeSend: function(){
					$.adddiv("loading");
				},
				success: function (data){
					$.deletediv("loading");
					if(data==1){
						speed+=1;
						$(".select").eq(step).animate({left:"-100%"},{duration:300,queue: false});
						step+=1;
						$(".ewm").animate({left:"0%"},{duration:300,queue: false});
						$(".head div").hide();
						$(".head p").show();
						$.alert("提交成功，谢谢参与");
					}else{
						$.alert("服务器繁忙");
					}
				}
			})	
		})
	},500);
});
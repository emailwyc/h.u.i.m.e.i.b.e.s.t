$.extend({
	alert:function(txt){
		if(!$(".alert-pop").is(":animated,:visible")){
		if($(".alert-pop").length<=0){
			$("body").append("<div class=\"alert-pop\"><span>"+txt+"</span></div>")
		}
		$(".alert-pop").css("margin-top","0rem").fadeIn({duration:300,queue: false}).animate({marginTop:"-.5rem"},{duration:300,queue: false}).find("span").text(txt)
		var time=setTimeout(function(){$(".alert-pop").fadeOut({duration:300,queue: false}).animate({marginTop:"0rem"},{duration:300,queue: false})},1000);
		}
	}, 
	emptyform:function(){
		$("input,textarea").val("");
	}
}); 
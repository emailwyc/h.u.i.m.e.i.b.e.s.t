<?php $this->load->view('common/header');?>
<title>义诊活动</title>
<style>
.kl-text input{ background:#fff; line-height:3rem; line-height:3rem; border-radius:.5rem; text-align:center; font-size:1.7rem; width:100%;}
html{ background:url(/ui/images/5.jpg) no-repeat top center #0370b9; background-size:100% auto;}
body{ position:fixed; text-align:center; width:25rem; left:50%; margin-left:-12.5rem; bottom:5rem;}
.kl-text p{ color:#fbe707; font-size:1rem; line-height:1.5; padding-top:.2rem;}
.kl-btn{ padding:1.5rem 0;}
.kl-btn div{ background:#4cafee; line-height:3rem; line-height:3rem; border-radius:.5rem; text-align:center; font-size:1.7rem; color:#fff;}
.kl-con{ border-top:1px solid #fff; color:#fff;}
.kl-a{ padding-top:1.5rem;}
.kl-a a{ color:#fbe707;}
</style>
<script>
$(document).ready(function(){
	$.adddiv("loading");
});
$(window).load(function() {
	$.deletediv("loading");
	$(".kl-btn").click(function(){
		var code=$(".kl-text input").val();
		if(code.length==0){
			$.alert("请输入口令");
			return false;
		}
		var data={"code":code,"actid":"<?=@$info['_id']?>"};
		$.ajax({
			type: "post",
			url: "/Act/freeJson",
			data:data,
			dataType: "json", 
			beforeSend: function(){
				$.adddiv("loading");
			},
			success: function (data){
				$.deletediv("loading");
				if(data.st==1){
					location.href="/act/consult/<?=@$info['_id']?>/"+code;
				}else{
					$.alert(data.msg);
				}
			}
		})	
	})
});
</script>
</head>  
<body>
<div class="body">
  <div class="kl-text">
  	<input type="text" placeholder="请输入口令" value="<?=@$code['code']?>"/>
  </div>
  <div class="kl-btn">
    <div>确认</div>
  </div>
  <div class="kl-con lh20 tl">
    <p>活动时间：<?=@$info['startT']?>—<?=@$info['endT']?></p>
	<p>活动方式：<?=$info['style']?></p>
  </div>
  <div class="kl-a tr">
    <a href="/act/freeshare/<?=$info['_id']?>/1">活动详情&gt;&gt;</a>
  </div>
</div>
<?php $this->load->view('common/footer');?>

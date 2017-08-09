<?php $this->load->view('common/header');?>
<title>义诊活动</title>
<script>
$(document).ready(function(){
	$.ajax({
		type: "post",
		url: "/act/getfreecode/<?=$info['_id']?>",
		dataType: "json",	
		beforeSend: function(){
		},
		success: function (data){
		    if(data.st==1){
				$(".kl-text div").text("义诊口令："+data.msg);
	        }
		}
	});
	$(".kl-btn").click(function(){
		window.location.href=weburl+"/act/doclist/";
	});
})
</script>  
<style>
html{ background:url(/ui/images/5.jpg) no-repeat top center #0370b9; background-size:100% auto;}
body{ position:fixed; text-align:center; width:25rem; left:50%; margin-left:-12.5rem; bottom:5rem;}
.kl-text div{ background:#fff; line-height:3rem; line-height:3rem; border-radius:.5rem; text-align:center; font-size:1.7rem;}
.kl-text p{ color:#fbe707; font-size:1rem; line-height:1.5; padding-top:.2rem;}
.kl-btn{ padding:1.5rem 0;}
.kl-btn div{ background:#4cafee; line-height:3rem; line-height:3rem; border-radius:.5rem; text-align:center; font-size:1.7rem; color:#fff;}
.kl-con{ border-top:1px solid #fff; color:#fff;}
.kl-a{ padding-top:1.5rem;}
.kl-a a{ color:#fbe707;}
</style>
</head>  
<body>
<div class="body">
  <div class="kl-text">
    <div>义诊口令：<?=@$code['code']?></div>
    <p>请妥善保存义诊口令,义诊口令是参加义诊的唯一标识。</p>
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
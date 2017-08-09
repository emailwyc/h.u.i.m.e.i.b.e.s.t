<?php $this->load->view('common/header');?>
<title>义诊活动</title>
 
<style>
html{ background:#fff;}
.yzys2{ padding-bottom:8rem;}
.yzys2-doc{ margin:1.2rem .6rem 0; border:.1rem solid #0076c5; padding:.7rem; background:#fff; border-radius:.3rem;}
.yzys2-doc .name{ margin-top:0rem; padding-bottom:1.5rem;}
.yzys2-doc .name div{ display:inline-block; font-size:3.5rem; color:#0076c5;}
.yzys2-doc .name p{ display:inline-block;}
.yzys2-doc .name span{ display:block; line-height:1.5rem; border-left:1px solid #222; padding-left:1rem; margin-left:1rem;}
.yzys2-about{ padding:2rem;}
.yzys2-about span{ color:#0076c5;}
.yzys2-btn{ background:url(/ui/images/yzys2-btn.png) no-repeat top; background-size:100% auto; position:fixed; left:0; bottom:0; width:100%; padding:4.5rem 0 0; height:6.5rem; color:#fff;}
.yzys2-pop{ display:none; background:rgba(0,0,0,0.5); position:fixed; left:0; top:0; width:100%; height:100%;}
</style>
</head>  
<body>
  <div class="yzys2">
    <div class="yzys2-doc">
	<div class="img" style="background:url(<?=@$doctor['avatar']?>) no-repeat center top; background-size:100% auto;"><img src="/ui/images/yzys2-doc-img.png" width="100%" alt="" /></div>
      <div class="name tc">
	  <div><?=@$doctor['name']?></div>
        <p class="tl">
		<span><?=@$doctor['position']?></span>
		<span><?=@$doctor['hospital']?><?=@$doctor['department']?></span>
        </p>
      </div>
	  <div class="con lh20">
	  <?=@$doctor['description']?>
      </div>
    </div>
    <div class="yzys2-about fs15 lh20">
	<div><span>【义诊时间】</span><?=@$info['startT']?>至<?=@$info['endT']?></div>
	<div><span>【义诊方式】</span><?=@$info['style']?></div>
	<div><span>【义诊名额】</span>在线一对一问答咨询解答名额<?=@$info['regnum']['all']?>名</div>
      <div>&nbsp;</div>
	  <div><span>【<?=@$doctor['name']?>医师常规出诊时间】</span></div>
	  <?php foreach($info['schedule'] as $k=>$v):?>
	  <div><?=@$v?></div>
	  <?php endforeach;?>
    </div>
    <div class="yzys2-btn fs16 lh15 tc">
      <p>点击右上角按钮 选择分享到朋友圈</p>
      <p>就可以申请参加义诊，快快行动吧！</p>
    </div>
  </div>
  <div class="yzys2-pop">
    <img src="/ui/images/djfx.png" width="100%" alt="" />
  </div>
<script type="text/javascript">  
$(document).ready(function(){
	$(".yzys2-btn").click(function(){
		$(".yzys2-pop").show();
	})
	$(".yzys2-pop").click(function(){
		$(".yzys2-pop").hide();
	})
})
wx.ready(function () {  
	wx.onMenuShareAppMessage({
		title: '我获得了【找明医】的义诊资格，如果你也需要的话，快来参与吧！', // 分享标题
		desc: '【找明医】义诊公益活动第三期', // 分享描述
		link:weburl+'/act/freeshareover',
		imgUrl: weburl+'/ui/images/yz-ico.jpg', // 分享图标
		success: function () { 
			window.location.href= weburl+"/act/freesharecode/<?=@$info['_id']?>";
		},
		cancel: function () {
		}
	});  
	wx.onMenuShareTimeline({
		title: '我获得了【找明医】的义诊资格，如果你也需要的话，快来参与吧！', // 分享标题
		link:weburl+'/act/freeshareover',
		imgUrl: weburl+'/ui/images/yz-ico.jpg', // 分享图标
		success: function () { 
			window.location.href= weburl+"/act/freesharecode/<?=@$info['_id']?>";
		},
		cancel: function () { 
		}
	});
});
</script> 
<?php $this->load->view('common/footer');?>

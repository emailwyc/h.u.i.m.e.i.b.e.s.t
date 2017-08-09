<?php $this->load->view('common/header');?>
<style>
html{ background:#edf1f5;}
</style>
<title>邀请好友</title>
</head>  
<body>
  <div>
    <div style="background:#fff;border-radius:1rem; border:1px solid #dce2e8; width:26rem; position:fixed; left:50%; top:50%; margin-left:-13rem; margin-top:-22rem; height:44rem">
      <div class="cfix" style="padding:2.5rem 0 2.5rem 2rem; border-bottom:1px solid #e8ecf0;">
        <img class="fl" src="<?=@$pat['avatar']?>" style=" width:7rem; height:7rem; border-radius:8rem; overflow:hidden;"/>
        <div class="fl fs13" style="width:14rem; line-height:1.7rem; padding-left:1.5rem;">
          <p style="font-size:1.5rem; line-height:3rem;">邀请人:<?=@$pat['name'];?></p>
		  <p>关注【找明医】公众号</p>
          <p>开启健康新生活！</p>
        </div>  
      </div>
      <div class="tc" style=" padding:2.5rem 0 5rem; border-bottom:1px solid #e8ecf0;">
     	 <img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=<?=@$pat['rqcode']['ticket'];?>" style=" width:17rem; height:17rem;" alt="" />
      </div>
      <div class="tc" style=" margin-top:-2.5rem;">
        <img src="/ui/images/logo.png" style=" background:#fff; padding:0 1rem; width:5rem; height:5rem;" />
      </div>
      <p class="fs15 lh20 tc" style="color:#2a3f4b;"></p>
    </div>
  </div>
<script>
wx.ready(function () {  
	var imgurl = weburl+'/ui/images/logo.png';
	wx.onMenuShareAppMessage({
		title: '<?=@$pat['name']?>-猛戳这里开启健康生活[找明医]', // 分享标题
		link: weburl+'/share/qrcode/<?=@$pat['_id']?>',
		desc: '关注公众号即得优惠券，【找明医】汇聚国内三甲医院知名专家，关注我们给自己的健康买一份保险吧!', // 分享描述
		imgUrl: imgurl, // 分享图标
		success: function () { 
		},
		cancel: function () {
		}
	});  
	wx.onMenuShareTimeline({
		title: '<?=@$pat['name']?>-猛戳这里开启健康生活[找明医]', // 分享标题
		link: weburl+'/share/qrcode/<?=@$pat['_id']?>',
		desc: '关注公众号即得优惠券，【找明医】汇聚国内三甲医院知名专家，关注我们给自己的健康买一份保险吧!', // 分享描述
		imgUrl: imgurl, // 分享图标
		success: function () { 
		},
		cancel: function () {
		}
	}); 
});   
</script>
</body>  
</html> 

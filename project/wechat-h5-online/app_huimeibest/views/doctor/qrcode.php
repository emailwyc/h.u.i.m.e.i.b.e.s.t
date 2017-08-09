<?php $this->load->view('common/header');?>
<style>
html{ background:#edf1f5;}
</style>
</head>  
<body>
  <div>
    <div style="background:#fff;border-radius:1rem; border:1px solid #dce2e8; width:26rem; position:fixed; left:50%; top:50%; margin-left:-13rem; margin-top:-22rem; height:44rem">
      <div class="cfix" style="padding:2.5rem 0 2.5rem 2rem; border-bottom:1px solid #e8ecf0;">
        <img class="fl" src="<?=@$doctor['avatar']?>" style=" width:7rem; height:7rem; border-radius:8rem; overflow:hidden;"/>
        <div class="fl fs13" style="width:14rem; line-height:1.7rem; padding-left:1.5rem;">
          <p style="font-size:1.5rem; line-height:3rem;"><?=@$doctor['name'];?></p>
          <p><?=@$doctor['position'];?>  <?=@$doctor['title'];?></p>
          <p><?=@$doctor['hospital'];?>  <?=@$doctor['department'];?></p>
        </div>  
      </div>
      <div class="tc" style=" padding:2.5rem 0 5rem; border-bottom:1px solid #e8ecf0;">
     	 <img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=<?=@$qrcode['ticket']?>" style=" width:17rem; height:17rem;" alt="" />
      </div>
      <div class="tc" style=" margin-top:-2.5rem;">
        <img src="/ui/images/app-ico.png" style=" background:#fff; padding:0 1rem; width:5rem; height:5rem;" />
      </div>
      <p class="fs15 lh20 tc" style="color:#2a3f4b;">找明医签约医生</p>
    </div>
  </div>
<script>
wx.ready(function () {  
	wx.onMenuShareAppMessage({
		title: '<?=@$doctor['name']?>-<?=@$doctor['position']?>[找明医]', // 分享标题
		desc: '<?=@$doctor['hospital'];?>  <?=@$doctor['department'];?>', // 分享描述
		imgUrl:  '<?=@$doctor['avatar']?>', // 分享图标
		success: function () { 
		},
		cancel: function () {
		}
	});  
	wx.onMenuShareTimeline({
		title: '<?=@$doctor['name']?>-<?=@$doctor['position']?>[找明医]', // 分享标题
		imgUrl:  '<?=@$doctor['avatar']?>', // 分享图标
		success: function () { 
		},
		cancel: function () { 
		}
	}); 
});   
</script>
</body>  
</html> 

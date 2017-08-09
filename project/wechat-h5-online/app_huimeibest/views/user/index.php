<?php $this->load->view('common/header');?>
<title>个人信息</title>
<style>
html{ height:100%; overflow:hidden;} 
</style>
</head>  
<body>
<div id="wrapper" class="b50">
  <div id="scroller">
    <div class="wd-info tc">
    <div class="fs16 cw1"><?=@$patient['name'];?></div>
    <img src="<?=@$patient['avatar'];?>" />
    </div>
    <div class="wd-list">
      <ul>
        <li>
          <a href="/user/contact"><img src="/ui/images/ico7-1.png" />家庭就诊人</a>
        </li>
        <li>
          <a href="/user/doctor"><img src="/ui/images/ico7-2.png" />我的医生</a>
        </li>
        <li>
          <a href="/user/service"><img src="/ui/images/ico7-3.png" />就诊记录</a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="/user/coupons"><img src="/ui/images/ico7-4.png" />优惠券</a>
        </li>
		<li>
		<a href="/share/qrcode/<?=@$patient['_id']?>"><img src="/ui/images/ico7-7.png" />邀请好友</a>
		  </li>
      </ul>
      <ul>
        <li>
          <a href="/about/question"><img src="/ui/images/ico7-5.png" />常见问题</a>
        </li>
        <li>
          <a href="/about/index"><img src="/ui/images/ico7-6.png" />关于我们</a>
        </li>
      </ul>
    </div>
  </div>
</div>
<?php $this->load->view('common/nav');?>
<?php $this->load->view('common/footer');?>

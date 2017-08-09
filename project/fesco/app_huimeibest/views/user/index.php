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
    <img src="/ui/images/touxiang.png" />
    </div>
    <div class="wd-list">
      <ul>
        <li>
          <div class="cfix"><img src="/ui/images/ico7-4.png" />剩余使用次数<em class="fr"><?= @$fescoInfo['ticket_overplus']; ?>次</em></div>
        </li>
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
    </div>
  </div>
</div>
<?php $this->load->view('common/nav');?>
<?php $this->load->view('common/footer');?>

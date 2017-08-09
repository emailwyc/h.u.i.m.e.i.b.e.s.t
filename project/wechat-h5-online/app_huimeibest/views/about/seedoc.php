<?php $this->load->view('common/header');?>
<title>就医咨询</title>
<style>
html{ height:100%; overflow:hidden;} 
</style>
<style>
.jyzx .item{ margin-top:.5rem; padding:1rem 3rem; background:#fff; border-top:1px solid #e1e1e1; border-bottom:1px solid #e1e1e1;} 
.jyzx .item .tit{ font-size:1.5rem; border-bottom:1px solid #e1e1e1; padding:0 1rem;}
.jyzx .item .con{ padding:.5rem 1rem 0; }
.bb img{ width:3rem;}
</style>
</head>  
<body class="universal">
<div id="wrapper" class="b50">
  <div id="scroller">
    <div class="jyzx lh15">
      <img src="/ui/images/jyzx-head.png" width="100%" alt="" />
      <div class="item">
        <div class="tit">
          电话咨询
        </div>
        <div class="con">
          <p>每周一至周五8：00—17：00（法定节假日除外）</p>
        </div>
      </div>
      <div class="item">
        <div class="tit">
          在线咨询
        </div>
        <div class="con">
          <p>每周一至周五9：00—21：00</p>
          <p>请在微信服务号留言，客服会在第一时间回复您。</p>
        </div>
      </div>
    </div>
  </div>
</div>  
<div class="bb">
  <a href="tel:4000-686-895" class="btn1"><img src="/ui/images/jyzx-btn.png" alt=""/> 服务热线</a>
</div>   
<?php $this->load->view('common/footer');?>

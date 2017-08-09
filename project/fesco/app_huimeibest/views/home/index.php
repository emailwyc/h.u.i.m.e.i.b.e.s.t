<?php $this->load->view('common/header');?>
<title>fesco</title>
<style>
.body{ margin-left:1.2rem;margin-right:1.2rem;}
</style>
</head>
<body>
<div class="ys-search tc fs14 body">
  <a href="/home/doctor"><img src="/ui/images/search.png" /><span>搜索医生</span></a>
</div>
<div class="ks-box body">
  <div class="ks-tit cfix">
    <div class="fl fs14">
      国内专家咨询
    </div>
    <div class="fr fs14">
      <a href="/home/doctor">搜索医生</a>
    </div>
  </div>
  <div class="ks-list cfix fs14">
    <?php if(!empty($info)){?>
    <?php foreach($info as $key=>$val){ $checkline = ($key+1)%3==0?true:false; ?>
    <div class="item <?php if(!$checkline) echo "line"; ?>">
      <a href="/home/seldep?dep=<?php echo $val['_id']."_1"?>">
        <img src="<?=@$val['lcon'];?>" alt="" />
        <span><?=@$val['name'];?></span>
      </a>
    </div>  
    <?php }}?>
  </div>
</div>
<?php $this->load->view('common/nav');?>
<?php $this->load->view('common/footer');?>

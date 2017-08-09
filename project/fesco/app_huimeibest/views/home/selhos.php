<?php $this->load->view('common/header');?>
<title>选择医院</title>
<style>
html{ background:#f2f2f2; height:100%; overflow:hidden;}
</style>  
<script type="text/javascript">  
var back_url="<?=@$back_url;?>";
var hos="<?=@$setting['hos'];?>";
var sort="<?=@$setting['sort'];?>";
var kw="<?=@$setting['kw'];?>";
var dep="<?=@$setting['dep'];?>";
$(function(){
	ajaxnav("yy",back_url,hos,sort,kw,dep);
});
</script>  
</head>
<body>
<div class="fl-tit">选择医院</div>
<div class="fl-box1-pop fs14"></div>
<div class="fl-box1">
  <div class="main fs14">
    <ul>
      <li class="tuijian" data-id="tuijian">推荐医院</li>
      <?php if(!empty($area)){?>
      <?php foreach($area as $v){?>
      <li class="<?=@$v['_id']?>" data-id="<?=@$v['_id']?>"><?=@$v['name']?></li>
      <?php }?>
      <?php }?>
    </ul>
  </div>
</div>
<div class="fl-box2 fs14">
  <div class="main">
    <ul>
    </ul>
  </div>
</div>
<?php $this->load->view('common/footer');?>

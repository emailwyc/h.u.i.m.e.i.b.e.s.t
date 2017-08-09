<?php $this->load->view('common/header');?>
<title>医生列表</title>
<style>
html{ height:100%; overflow:hidden;} 
</style>
</head>  
<body>
<div class="ys-search">
  <form id="kw-form">
    <div>
	<input name="kw" id="kw" type="search" placeholder="搜索医生，医院，疾病" value="<?=@$setting['kw'];?>" />
    </div>
    <input id="rt" type="button" value="确定" />
  </form>
</div>
<div class="ys-select cfix zi10 fs14">
  <div class="line" onClick="seldep();"><span><?=$dep_name;?></span><em></em></a></div>
  <div class="line" onClick="selhos();"><span><?=$hos_name;?></span><em></em></div>
  <div><span>综合排序</span><em></em></div>
</div>
<div id="wrapper" style="top:8.5rem;">
  <div id="scroller">
    <div class="ys-list ajaxlist">
    </div>
  </div>
  <div class="nolist tc fs16">
    <img src="/ui/images/wssjg.png" alt="" />
    <p class="ch6">暂无医生记录</p>
  </div>
</div>
<div class="ys-select-pop pop3 zi10 fs14">
  <ul>
    <li data-val="mul_num" class="<?php if($setting['sort']=="mul_num") echo "cur";?>" onClick="selsort('mul_num');"><a href="javascript:void(0);"><div>综合排序</div></a></li>
    <li data-val="rc_num" class="<?php if($setting['sort']=="rc_num") echo "cur";?>" onClick="selsort('rc_num');"><a href="javascript:void(0);"><div>问诊量</div></a></li>
    <li data-val="comment.per" class="<?php if($setting['sort']=="comment.per") echo "cur";?>" onClick="selsort('comment.per');"><a href="javascript:void(0);"><div>满意度</div></a></li>
    <li data-val="starred" class="<?php if($setting['sort']=="starred") echo "cur";?>" onClick="selsort('starred');"><a href="javascript:void(0);"><div>关注度</div></a></li>
  </ul>
</div>
<script>
var dep="<?=@$setting['dep'];?>";//诊室编号
var hos="<?=@$setting['hos'];?>";//医院编号
var sort="<?=@$setting['sort'];?>";//排序类型
var kw="<?=@$setting['kw'];?>";//关键字
var loadnum=10;
var page=1;
$(function(){
	myScroll.on("scrollEnd",function(){
		if(this.y <= myScroll.maxScrollY+100)
		{
			if(loadnum>=10) {
				ajaxurl="/home/doctorJson?dep="+dep+"&hos="+hos+"&sort="+sort+"&kw="+kw+"&p="+page;
				ajaxlist(ajaxurl,ajaxdata,myScroll,page,"1");
			}
		}	
	})
	ajaxurl="/home/doctorJson?dep="+dep+"&hos="+hos+"&sort="+sort+"&kw="+kw+"&p="+page;
	ajaxlist(ajaxurl,ajaxdata,myScroll,page,"1");

});
</script>
<?php $this->load->view('common/footer');?>

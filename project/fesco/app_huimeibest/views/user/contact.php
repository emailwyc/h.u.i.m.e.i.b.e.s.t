<?php $this->load->view('common/header');?>
<title>家庭就诊人</title> 
<style>
html{ height:100%; overflow:hidden;} 
</style>
</head>  
<body class="universal">
<div class="jzr-clue fs14 tc ch7">已有<?=@$patnum;?>位家庭就诊人</div>
<div id="wrapper" style="bottom:5rem; top:3.5rem;">
  <div id="scroller">
    <div class="jzr-list ajaxlist"></div>
  </div>
  <div class="nolist tc fs16">
    <img src="/ui/images/wssjg.png" alt="" />
    <p class="ch6">暂无家庭就诊人</p>
  </div>
</div>     
<div class="jzr-add bb">
  <div><a href="/user/addpatient" class="btn1">添加家庭就诊人</a></div>
</div>
<script>
var loadnum=10;
var page=1;
$(function(){
	myScroll.on("scrollEnd",function(){
		if(this.y <= myScroll.maxScrollY+100)
		{
			if(loadnum>=10) {
				ajaxurl="/user/contact/1?p="+page;
				ajaxlist(ajaxurl,ajaxdata,myScroll,page,"8");
			}
		}	
	})
	ajaxurl="/user/contact/1?p="+page;
	ajaxlist(ajaxurl,ajaxdata,myScroll,page,"8");
});
</script>  
<?php $this->load->view('common/footer');?>

<?php $this->load->view('common/header');?>
<title>我的医生</title>
<style>
html{ height:100%; overflow:hidden;} 
</style>
</head>  
<body>
<div id="wrapper">
  <div id="scroller">
    <div class="ys-list ajaxlist">
    </div>
  </div>
  <div class="nolist tc fs16">
    <img src="/ui/images/wssjg.png" alt="" />
    <p class="ch6">暂无医生记录</p>
  </div>
</div>
<script>
var loadnum=10;
var page=1;
$(function(){
	myScroll.on("scrollEnd",function(){
		if(this.y <= myScroll.maxScrollY+100)
		{
			if(loadnum>=10) {
				ajaxurl="/user/doctor/1?p="+page;
				ajaxlist(ajaxurl,ajaxdata,myScroll,page,"1");
			}
		}
	})
	ajaxurl="/user/doctor/1?p="+page;
	ajaxlist(ajaxurl,ajaxdata,myScroll,page,"1");
})
</script>  
<?php $this->load->view('common/footer');?>

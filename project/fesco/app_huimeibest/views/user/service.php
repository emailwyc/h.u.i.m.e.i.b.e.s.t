<?php $this->load->view('common/header');?>
<title>就诊记录</title>
<style>
html{ height:100%; overflow:hidden;} 
</style> 
</head>  
<body class="blue">
<div class="jzjl-tit bt">
  <div>
    <a href="javascript:void(0);" class="cur">电话咨询</a>
  </div>
</div>
<div id="wrapper" class="t60 b50">
  <div id="scroller">
    <div class="jzjl-con ajaxlist">
    </div>
  </div>
  <div class="nolist tc fs16">
    <img src="/ui/images/wssjg.png" alt="" />
    <p class="ch6">暂无就诊记录</p>
  </div>
</div>
<script type="text/javascript">
var loadnum=10;
var page=1;
$(function(){
	myScroll.on("scrollEnd",function(){
		if(this.y <= myScroll.maxScrollY+100)
		{
			if(loadnum==10) {
				ajaxurl="/user/service/1?p="+page;
				ajaxlist(ajaxurl,ajaxdata,myScroll,page,"6");
			}
		}
	})
	ajaxurl="/user/servicepc/1?p="+page;
	ajaxlist(ajaxurl,ajaxdata,myScroll,page,"6");
})
</script>
<?php $this->load->view('common/nav');?>
<?php $this->load->view('common/footer');?>
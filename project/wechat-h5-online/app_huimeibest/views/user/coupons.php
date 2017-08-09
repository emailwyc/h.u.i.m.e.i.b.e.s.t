<?php $this->load->view('common/header');?>
<title>优惠券</title> 
<style>
html{ height:100%; overflow:hidden;} 
</style>
</head>  
<body>
<div class="yhq-add">
  <div>
    <input class="text" type="text" placeholder="输入兑换码" />
  </div>
  <input class="btn" type="button" value="兑换" /> 
</div>
<div id="wrapper" class="t50">
  <div id="scroller">
    <div class="yhq-list2 ajaxlist"></div>
  </div>
  <div class="nolist tc fs16">
    <img src="/ui/images/wssjg.png" alt="" />
    <p class="ch6">暂无可用优惠券</p>
  </div>
</div>   
<script>
var loadnum=10;
var page=1;
$(function(){
	myScroll.on("scrollEnd",function(){
		if(this.y <= myScroll.maxScrollY+100)
		{
			if(loadnum==10) {
				ajaxurl="/user/coupons/1?p="+page;
				ajaxlist(ajaxurl,ajaxdata,myScroll,page,"3");
			}
		}
	})
	ajaxurl="/user/coupons/1?p="+page;
	ajaxlist(ajaxurl,ajaxdata,myScroll,page,"3");
})
</script>   
<?php $this->load->view('common/footer');?>

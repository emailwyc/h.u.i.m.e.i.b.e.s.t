<?php $this->load->view('common/header');?>
<title><?=@$doctorinfo['name']?>的文章</title>
<style>
html{ height:100%; overflow:hidden;} 
</style>
</head>  
<body>
<div id="wrapper">
  <div id="scroller">
    <div class="jpwz-list ajaxlist">
    </div>
  </div>
  <div class="nolist tc fs16">
    <img src="/ui/images/wssjg.png" alt="" />
    <p class="ch6">该医生还没有发布文章!</p>
  </div>
</div>
<script>
var doctor="<?=@$doctor?>";
var loadnum=10;
var page=1;
$(function(){
	myScroll.on("scrollEnd",function(){
		if(this.y <= myScroll.maxScrollY+100)
		{
			if(loadnum==10) {
				ajaxurl="/doctor/article/"+doctor+"/1?p="+page;
				ajaxlist(ajaxurl,ajaxdata,myScroll,page,"7");
			}
		}	
	})
	ajaxurl="/doctor/article/"+doctor+"/1?p="+page;
	ajaxlist(ajaxurl,ajaxdata,myScroll,page,"7");
})
</script>  
<?php $this->load->view('common/footer');?>

<?php $this->load->view('common/header');?>
<title>找明医</title>
<link href="/ui/css/swiper.css" rel="stylesheet" />
<script src="/ui/js/swiper.3.1.2.jquery.min.js"></script>
<script>
$(document).ready(function(){
	$.ajax({
		type: "get",
		url: "/home/recdoc",
		dataType: "json",	
		beforeSend: function(){
		},
		success: function (data){
			var str="";
		    for(i in data)
			{
				str+="<a href=\"/act/freeshare/"+data[i]._id.$id+"\" class=\"item swiper-slide\"><img src=\""+data[i].banner.img+"\" width=\"100%\" /><div class=\"banner-tj\">知名专家推荐</div>";
				if(data[i].able==1){
					str+="<div class=\"banner-yz\">义诊</div>";
				}
				str+="<div class=\"banner-con fs14 lh15\" style=\"color:#"+data[i].banner.color+";\"><p><span>姓名："+data[i].doctor.name+"</span><em>职称："+data[i].doctor.position+"</em></p><p><span>科室："+data[i].doctor.department+"</span><em>医院："+data[i].doctor.hospital+"</em></p></div></a>";
			}
			$(".banner-pic").append(str);
			var swiper = new Swiper('.swiper-container', {
			  autoplay: 5000,
			  pagination: '.swiper-pagination',
			  paginationClickable: true,
			  debugger: true
		  });
		}
	});
})
</script>
<style>
.body{ margin-left:1.2rem;margin-right:1.2rem;}  
#ad2{ padding-bottom:6rem;}
</style>
</head>
<body>
<div class="ys-search tc fs14 body">
  <a href="/home/doctor"><img src="/ui/images/search.png" /><span>搜索医生</span></a>
</div>
<div class="banner body swiper-container">
  <div class="banner-pic swiper-wrapper fs14">    
  </div>  
  <div class="banner-page swiper-pagination"></div>
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
<div class="ad body" id="ad1"> <a href="/about/seedoc"><img src="/ui/images/banner1.jpg?v=1" alt="" /></a> </div>
<div class="ad body" id="ad2"> <a href="/about/overseas"><img src="/ui/images/banner2.jpg?v=1" alt="" /></a> </div>
<?php $this->load->view('common/nav');?>
<?php $this->load->view('common/footer');?>

<?php $this->load->view('common/header');?>
<title>医生详情</title>
<style>
html{ height:100%; overflow:hidden;} 
</style>
</head>  
<body>
<div id="wrapper">
  <div id="scroller">
    <div class="ys-info2">
      <div class="top">
        <div class="left tc">
          <img class="tx" src="<?=@$doctor['avatar']?>" onerror="this.onerror=null;this.src='/ui/images/doctor.jpg'" alt="" />
          <img class="jb" src="/ui/images/sanjia.png" alt="" />
        </div>
        <div class="right cw8">
          <p class="xm fs16 cw1"><?=@$doctor['name']?></p>
          <p class="zw"><?=@$doctor['position']?> <?=$doctor['department']?></p>
          <p class="zxgh"><span><img src="/ui/images/ico3-zx.png" alt="" />服务量 <?=@$doctor['rc_num']?></span></p>
        </div>
      </div>
      <?php if($carest==1){?>
      <div class="gz cur"><img src="/ui/images/ico3-gz.png" /><span>已关注</span></div> 
      <?php }else{?>
      <div class="gz"><img src="/ui/images/ico3-gz.png" /><span>关注</span></div> 
      <?php }?> 
    </div>
    <div class="ys-con">
      <div class="item zyd">
        <div class="tit">
          <img src="/ui/images/ico4-2.png" alt="" /><span>执业点</span>
        </div>
        <div class="con">
          <?php if(!empty($location)){ $stor = array();?>
			  <?php foreach($location as $v){?>
				  <?php if(!in_array(($v['hospital'].$v['branch']),$stor)){ ?>
					  <p><?=@$v['hospital'];?><?php if(!empty($v['branch'])){ echo "(".$v['branch'].")";}?></p> 
              <?php } $stor[]=($v['hospital'].$v['branch']); }?>
          <?php }?>
        </div>
      </div>
      <div class="item zjfw">
        <div class="tit">
          <img src="/ui/images/ico4-3.png" alt="" /><span>专家服务</span>
        </div>
        <div class="btn cfix">
          <?php if($doctor['service_provided']['phonecall']['on']){?>
              <?php if($isph){?>
                  <div class="dhzx"> <a href="/phonecall/index/<?=@$doctor['_id']?>"><p><img src="/ui/images/ico2-3.png" alt="" /><span>电话咨询</span><em>未满</em></p></a> </div>
              <?php }else{?>
                  <div class="dhzx no"> <a href="javascript:void(0)"><p><img src="/ui/images/ico2-3.png" alt="" /><span>电话咨询</span><em>已满</em></p></a> </div>
              <?php }?>
          <?php }else{?>
          <div class="dhzx no"> <a href="javascript:void(0)"><p><img src="/ui/images/ico2-3.png" alt="" /><span>电话咨询</span><em>已满</em></p></a> </div>
          <?php }?>
        </div>
      </div>
      <a href="javascript:void(0)" class="item sc about-pop-a">
          <div class="tit arrow">
            <img src="/ui/images/ico4-4.png" alt="" /><span>擅长</span>
          </div>
          <div class="con">
              <?=@$doctor['speciality']?>
          </div>
      </a>
      <a href="javascript:void(0)" class="item jj about-pop-a">
          <div class="tit arrow">
            <img src="/ui/images/ico4-5.png" alt="" /><span>简介</span>
          </div>
          <div class="con">
              <?=@$doctor['description']?>
          </div>
      </a>
      <div class="item pl">
        <div class="tit">
          <img src="/ui/images/ico4-7.png" alt="" />评论(<?=@$com_num?>)
        </div>
        <div class="list ajaxlist">
        </div>
      </div>
    </div>
  </div>
</div>  

<script type="text/javascript">  
var doctorid="<?=@$doctor['_id']?>";
var loadnum=10;
var page=1;
$(function(){
	$(".about-pop-a").click(function(){
		aboutpop($(this).find(".tit span").text(),$(this).find(".con").text());
	})
	myScroll.on("scrollEnd",function(){
		if(this.y <= myScroll.maxScrollY+100)
		{
			if(loadnum==10) {
				ajaxurl="/doctor/getcomment/"+doctorid+"?p="+page;
				ajaxlist(ajaxurl,ajaxdata,myScroll,page,"2");
			}
		}
	})
	ajaxurl="/doctor/getcomment/"+doctorid+"?p="+page;
	ajaxlist(ajaxurl,ajaxdata,myScroll,page,"2");
})
</script> 
<?php $this->load->view('common/footer');?>

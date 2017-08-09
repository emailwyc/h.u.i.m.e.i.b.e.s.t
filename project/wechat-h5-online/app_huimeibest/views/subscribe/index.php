<?php $this->load->view('common/header');?>
<title>预约加号</title>
<script src="/ui/js/swiper.3.1.2.jquery.min.js"></script>
<link rel="stylesheet" href="/ui/css/swiper.css">
</head>
<body class="green">
<div class="ys-info">
  <div class="top">
    <div class="left tc"> <a href="javascript:void(0)"><img class="tx" src="<?=@$doctor['avatar']?>" onerror="this.onerror=null;this.src=\'/ui/images/doctor.jpg\'" alt="" /></a> <img class="jb" src="/ui/images/sanjia.png" alt="" /> </div>
    <div class="right cw8">
      <p class="xm fs16 cw1">
        <?=@$doctor['name']?>
      </p>
      <p class="zw">
        <?=@$doctor['position']?>
      </p>
      <p class="sc">
        <?=@$doctor['hospital']?>
        <?=$doctor['department']?>
      </p>
    </div>
    <div class="zxgh cw8"><img src="/ui/images/ico3-gh.png" alt="" /><span>加号量 <em>
      <?=@$doctor['reg_num']?>
      </em></span></div>
  </div>
</div>
<div class="ys-time swiper-container">
  <div class="tit fs16"> <img src="/ui/images/ico4-1.png" alt="" />选择预约时间 </div>
  <div class="btn tc fs16  fs1"> <em class="prev swiper-button-prev"></em><span class="date">
    <?=@$time_slot[1]?>
    </span><em class="next swiper-button-next"></em> </div>
  <div class="calendar swiper-wrapper">
    <?php foreach($clander as $v){?>
    <div class="swiper-slide">
      <div class="table">
        <div class="top"></div>
        <table width="100%" border="0" cellspacing="1">
          <tr>
            <td align="center" valign="middle"></td>
            <?php foreach($v['riqi'] as $val){?>
            <td align="center" valign="middle" width="12%"><div>
                <?=@$val['week']?>
              </div>
              <p>
                <?=@$val['date']?>
              </p></td>
            <?php }?>
          </tr>
          <tr>
            <td align="center" valign="middle">上午</td>
            <?php foreach($v['list']['morn'] as $key=>$value){?>
            <?php if(!empty($sub_able[$key])){?>
            <?php if($key>=$now_time1 && $sub_able[$key]['quantity']<=0){?>
            <td align="center" valign="middle" class="no"><span>人 数</span><span>已 满</span></td>
            <?php }elseif($key>=$now_time1 && $sub_able[$key]['quantity']>0){?>
            <td align="center" valign="middle" class="yes" data-day="<?=@$sub_able[$key]['id']?>"><span>点 击</span><span>预 约</span></td>
            <?php }elseif($key<$now_time1){?>
            <td align="center" valign="middle"></td>
            <?php }else{?>
            <td align="center" valign="middle"></td>
            <?php }?>
            <?php }else{?>
            <td align="center" valign="middle"></td>
            <?php }?>
            <?php }?>
          </tr>
          <tr>
            <td align="center" valign="middle">下午</td>
            <?php foreach($v['list']['after'] as $i=>$j){?>
            <?php if(!empty($sub_able[$i])){?>
            <?php if($i>=$now_time2 && $sub_able[$i]['quantity']<=0){?>
            <td align="center" valign="middle" class="no"><span>人 数</span><span>已 满</span></td>
            <?php }elseif($i>=$now_time2 && $sub_able[$i]['quantity']>0){?>
            <td align="center" valign="middle" class="yes" data-day="<?=@$sub_able[$i]['id']?>"><span>点 击</span><span>预 约</span></td>
            <?php }elseif($i<$now_time2){?>
            <td align="center" valign="middle"></td>
            <?php }else{?>
            <td align="center" valign="middle"></td>
            <?php }?>
            <?php }else{?>
            <td align="center" valign="middle"></td>
            <?php }?>
            <?php }?>
          </tr>
          <tr>
            <td align="center" valign="middle">晚上</td>
            <td align="center" valign="middle"></td>
            <td align="center" valign="middle"></td>
            <td align="center" valign="middle"></td>
            <td align="center" valign="middle"></td>
            <td align="center" valign="middle"></td>
            <td align="center" valign="middle"></td>
            <td align="center" valign="middle"></td>
          </tr>
        </table>
      </div>
    </div>
    <?php }?>
  </div>
  <div class="page swiper-pagination"> </div>
  <div class="about ch4 lh15">
    <div class="cr1">加号规则：</div>
    <p>1.加号成功后，持加号凭证（预约加号成功通知消息），就诊当天到医院找专家领加号单；</p>
    <p>2.在挂号窗口挂号，交挂号费后排队就诊。</p>
    <p>注：加号是专家牺牲个人时间的额外服务，如因您个人原因未就诊，不补诊不退费，如专家临时停诊，可退费或补诊。</p>
  </div>
</div>
<script type="text/javascript">  
$(function(){
	var num;
	if($(window).width()<="640") {
		$(".calendar td").height($(".calendar td:eq(1)").width());
	} else {
		$(".calendar td").height("80");
	}
	var swiper = new Swiper('.swiper-container', {
		pagination: '.swiper-pagination',
		paginationClickable: true,
		nextButton: '.swiper-button-next',
		prevButton: '.swiper-button-prev',
		// Enable debugger
		debugger: true,
		onTransitionEnd: function(swiper){ 
			if(swiper.activeIndex=="0") {
				num="<?=@$time_slot[1]?>";
			} else{
				if(swiper.activeIndex=="1") {
					num="<?=@$time_slot[2]?>";
				} else{
					if(swiper.activeIndex=="2") {
						num="<?=@$time_slot[3]?>";
					}
				}
			}
			$(".ys-time .date").text(num);
		} 	  
	})
});
$(window).on("load resize",function(){
	$(".yes").click(function(){
		var day=$(this).attr("data-day");
		location.href="/subscribe/order/"+day;
	})
	if($(window).width()<="640") {
		$(".calendar td").height($(".calendar td:eq(1)").width());
	} else {
		$(".calendar td").height("80");
	}
});
wx.ready(function () {
	wx.onMenuShareAppMessage({
		title: '<?=@$doctor['name']?>-<?=@$doctor['position']?>[找明医]', // 分享标题
		desc: '<?=@str_replace(array("\r\n","\n","\r")," ",addslashes($doctor['speciality']))?>', // 分享描述
		imgUrl: '<?=@$doctor['avatar']?>', // 分享图标
		success: function () { 
		},
		cancel: function () {
		}
	});  
	wx.onMenuShareTimeline({
		title: '<?=@$doctor['name']?>-<?=@$doctor['position']?>[找明医]', // 分享标题
		imgUrl: '<?=@$doctor['avatar']?>', // 分享图标
		success: function () { 
		},
		cancel: function () { 
		}
	}); 
});    
</script>
<?php $this->load->view('common/footer');?>

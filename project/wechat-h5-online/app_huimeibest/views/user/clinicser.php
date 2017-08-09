<?php $this->load->view('common/header');?>
<title><?=@$info['stat'];?>通知</title>
<style>
html{ background:#f2f2f2;}
</style>
</head>  
<body>
  <!-- 咨询挂号-成功 -->
  <div class="zxgh-success lh20 fs14">
    <div class="tit fs16"><?=@$info['stat'];?>通知</div>
    <div class="text1">就诊人：<?=@$info['name']?></div>
    <div class="text2">
      <p>性&nbsp;&nbsp;&nbsp;&nbsp;别：<?php if($info['gender']=="female"){ echo "女";}else{ echo "男";}?></p>
      <p>年&nbsp;&nbsp;&nbsp;&nbsp;龄：<?=@$info['age']?></p>
    </div>
    <div class="text1">预约医生：<?=@$doctor['name']?></div>
    <div class="text2">
      <p>门诊地点：<?=@$info['location']?></p>
    </div>
    <div class="text2">
	  <p>就诊时间：<span class="cr1"><?=@$doctor_table['date']?> 
		
		<?php
		$temTimeEnd = explode(',',$doctor_table['interval']);
		if($temTimeEnd[1]<="12:00"){
		?>
			上午 8:00-10:00 
	  <?php }else{?>
			下午 13:00-15:00 
	  <?php }?>
	  </span></p>
      <p>就诊状态：<span class="cr1"><?=@$info['stat']?></span></p>
      <p>就诊费用：<?=@$info['price']?>元</p>
    </div>  
    <div class="con ch7">病情描述：<?=@$info['message']?></div>
    <div class="btn"><a href="/user/clinicser/2/<?=@$info['_id']?>">详情</a></div>
  </div>
  <!-- 咨询挂号-成功 end -->
<?php $this->load->view('common/footer');?>

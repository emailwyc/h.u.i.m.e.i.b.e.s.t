<?php $this->load->view('common/header');?>
<title>评价</title> 
</head>  
<body class="universal">
<div class="ys-info2">
  <div class="top">
    <div class="left tc">
      <img class="tx" src="<?=@$doctor['avatar']?>" onerror="this.onerror=null;this.src='/ui/images/doctor.jpg'" alt="" />
      <img class="jb" src="/ui/images/sanjia.png" alt="" />
    </div>
    <div class="right cw8">
      <p class="xm fs16 cw1"><?=@$doctor['name']?></p>
      <p class="zw"><?=@$doctor['position']?> <?=$doctor['department']?></p>
    </div>
  </div>
</div>
<form method="post" action="/doctor/comment/<?=@$order['_id']?>" name="comment">
<input type="hidden" value="<?=@$from?>" name="from1" />
<div class="pl-con pb35">
  <div class="item cfix myd">
    <div class="fl fs14">满意度</div>
    <div class="fl star"><span></span><span></span><span></span><span></span><span></span></div>
    <input type="hidden" value="" id="star" name="star" />
  </div>
  <div class="item plnr">
    <div class="fs14">评价内容</div>
    <div class="textarea">
      <textarea name="message" id="message" class="lh15 fs13" maxlength="100"  placeholder="写下您的就医经历，为更多人提供宝贵的参考（请输入100字以内）"></textarea>
    </div>
  </div>
</div>
<div class="bb">
  <div><input type="submit" class="btn1" name="btn1" value="提交" onClick="return checkFormat(this.form)"/></div>
</div>
</form>
<script type="text/javascript">  
function checkFormat(form) {
	if(form.star.value<1){
		$.alert("请给出您对医生服务的满意度!");
		return false;
	}
	if(form.message.value.length>100||form.message.value.length<10) {
		$.alert("评价内容输入文字范围:10~100字!");
		form.message.focus();
		return false;
	}
	return true;
}
</script> 
<?php $this->load->view('common/footer');?>

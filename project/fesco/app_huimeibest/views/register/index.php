<?php $this->load->view('common/header');?>
<title>登录</title>
</head>  
<body>
<form method="post" action="/register/index" name="register">
  <div class="login">
    <div class="bg"></div>
    <div class="tx tc fs15 ch7">
      <img src="/ui/images/ren.png" alt="" />
      <div>登录</div>
    </div>
    <div class="con">
      <div class="sjh">
        <input name="sjh" type="tel" maxlength="11" id="RegMobileForm_reg_mobile" placeholder="请输入您的手机号" onafterpaste="this.value=this.value.replace(/\D/g,'')" onKeyUp="this.value=this.value.replace(/\D/g,'')" value="<?=@$mobile?>" />
      </div>
      <div class="yzm">
        <input name="yzm" type="tel" maxlength="4" placeholder="请输入验证码" /><a id="send-sms-code" href="javascript:void(0)" class="fs14 tr">获取验证码</a>
      </div>
    </div>
    <div class="btn">
	  <input type=submit name="btn1" style="width:100%" value="登录" class="btn1" onClick="return checkFormat(this.form)" />
    </div>
    <input name="from11" type="hidden" value="<?=@$from?>"/>
  </div>
</form>
<script type="text/javascript"> 
var wxst = "<?=@$st?>";
var msg = "<?=@$msg?>";
$(document).ready(function() {
	if(msg!=""){
		$.alert(msg);
	}

});

function checkFormat(form) {
	var re=/^1[34578][0-9]\d{8}$/;
	if(!re.test(form.sjh.value)) {
		$.alert("手机格式错误,请检查!");
		form.sjh.focus();
		return false;
	}
	if(form.yzm.value==''){
		$.alert("请输入验证码!");
		form.yzm.focus();
		return false;
	}
	return true;
}
</script>
<?php $this->load->view('common/footer');?>

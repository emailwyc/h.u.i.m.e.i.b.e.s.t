<?php $this->load->view('common/header');?>
<title>编辑就诊人</title>  
</head>  
<body class="green">
  <!-- 我的信息-表单 -->
  <form method="post" action="/user/editpat/<?=@$pid?>" name="editfamily">
  <input name="from11" type="hidden" value="<?=@$from?>">
  <div class="wdxx-form fs13">
    <div class="item">
      <div class="left">姓名：</div>
      <div class="right"><?=@$info['name']?></div>
    </div>
	<div class="item">
      <div class="left">性别:</div>
      <div class="right">
        <?php if($info['gender']=="male"){?>
        <span class="sex nan cur"><em>男</em> <img src="/ui/images/sex-nan.png" alt="" /></span>
        <?php }else{?>
        <span class="sex nv cur"><em>女</em> <img src="/ui/images/sex-nv.png" alt="" /></span>
        <?php }?>
      </div>
    </div>
    <div class="item">
      <div class="left">年龄:</div>
      <div class="right"><?=@$info['age']?></div>
    </div>
    <div class="item">
      <div class="left">关系:</div>
      <div class="right"><?=@$info['relation']?></div>
    </div>
  </div>
  <div class="wdxx-form fs13" style="margin-top:1rem; border-top:1px solid #e1e1e1;">
    <div class="item">
      <div class="left">身份证:</div>
      <div class="right"><input name="idcard" id="idcard" type="text" maxlength="18" placeholder="<?=@$info['idcard']?>"></div>
    </div>
    <div class="item">
      <div class="left">手机:</div>
      <div class="right"><input name="mobile" maxlength="11" type="tel" placeholder="<?=@$info['mobile']?>" onafterpaste="this.value=this.value.replace(/\D/g,'')" onKeyUp="this.value=this.value.replace(/\D/g,'')"></div>
    </div>
    <div class="item default">
      <span class="btn-checkbox <?php if($info['isdefault']==1){?>cur<?php }?>"></span>  <em>设为默认联系人</em>
      <input id="default" type="hidden" name="isdefault" value="<?php if($info['isdefault']==1){ echo 1;}else{ echo 0;}?>">
    </div>
  </div>	
    <div class="wdxx-form-btn tc">
      <div><input type="submit" name="btn1" value="修改该家庭成员" onClick="return checkFormat(this.form)" /></div>
    </div>
  
  </form>
  <!-- 我的信息-表单 end -->
<script type="text/javascript">  
var error_msg = "<?=@$msg?>";
$(window).load(function(){
    if(error_msg!=""){
         $.alert(error_msg) 		
    }
})
$(document).ready(function(){
	$("#idcard").blur(function(){
		var text=$(this).val();
		var sex;
		var ageyear;
		var agemonth;
		var ageday;
		if(text!=""){
			var re=/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
			if(!re.test(text)){
				$.alert("身份证格式不正确");
			}
		}
	});
	$("#mobile").blur(function(){
		var text=$(this).val();
		var re=/^1[34578][0-9]\d{8}$/;
		if(!re.test(text))
		{
			$.alert("请输入正确的手机号")
		}
	});
})
function checkFormat(form) {
	if(form.idcard.value==""){
		form.idcard.value=form.idcard.placeholder;
	}else{
		var re=/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
		if(!re.test(form.idcard.value)) { $.alert("身份证格式不正确"); form.idcard.focus(); return false; }
	}
	if(form.mobile.value==""){
		form.mobile.value=form.mobile.placeholder;
	}else{
		var re2=/^1[34578][0-9]\d{8}$/;
		if(!re2.test(form.mobile.value)) { $.alert("手机格式不正确"); form.mobile.focus(); return false; }
	}
	return true;
}
</script>    
<?php $this->load->view('common/footer');?>

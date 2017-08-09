<?php $this->load->view('common/header');?>
<title>添加家庭就诊人</title>
</head>  
<body class="green">
  <!-- 我的信息-表单 -->
  <form method="post" action="/user/addpatient" name="addfamily">
  <input name="from11" type="hidden" value="<?=@$from?>">
  <div class="wdxx-form fs13">
    <div class="item">
      <div class="left">姓名：</div>
      <div class="right"><input name="name" id="name" maxlength="10" type="text" placeholder="请填写真实姓名（必填）" ></div>
    </div>
    <div class="item">
      <div class="left">身份证：</div>
      <div class="right"><input name="idcard" id="idcard" type="text" maxlength="18" placeholder="一经填写无法修改，请谨慎填写（选填）"></div>
    </div>
    <div class="item">
      <div class="left">性别:</div>
      <div class="right">
        <span class="sex nan cur"><em>男</em> <img src="/ui/images/sex-nan.png" alt="" /></span>
        <span class="sex nv"><em>女</em> <img src="/ui/images/sex-nv.png" alt="" /></span>
        <input id="sex" type="hidden" name="sex" value="1">
      </div>
    </div>
    <div class="item">
      <div class="left">年龄:</div>
      <div class="right"><input name="age" id="age" maxlength="3" type="tel" placeholder="请填写真实年龄（必填）" onafterpaste="this.value=this.value.replace(/\D/g,'')" onKeyUp="this.value=this.value.replace(/\D/g,'')" ></div>
    </div>
    <div class="item">
      <div class="left">关系:</div>
      <div class="right">
      	<select name="relation" id="relation">
          <option value="本人">本人</option>
          <option value="家属">家属</option>
          <option value="朋友">朋友</option>
          <option value="其他">其他</option>
        </select>
      </div>
    </div>
    <div class="item">
      <div class="left">手机:</div>
      <div class="right"><input name="mobile" id="mobile" maxlength="11" type="tel" placeholder="用于接收预约成功的短信，请谨慎填写（必填）" onafterpaste="this.value=this.value.replace(/\D/g,'')" onKeyUp="this.value=this.value.replace(/\D/g,'')" ></div>
    </div>
    <div class="item default">
      <span class="btn-checkbox cur"></span>  <em>设为默认就诊人</em>
      <input id="default" type="hidden" name="isdefault" value="1">
    </div>
  </div>
    <div class="wdxx-form-btn tc">
      <div><input type="submit" name="btn1" value="提交" onClick="return checkFormat(this.form)" /></div>    
    </div>
  </form>
  <!-- 我的信息-表单 end -->
<script type="text/javascript">  
var error_msg = "<?=@$msg?>";
$(window).load(function(){
    if(error_msg!=""){
         $.alert(error_msg);
    }
})
$(document).ready(function(){
	$("#name").blur(function(){
		var txt=$(this).val();
		if(txt=="") {
		} else {
			$.ajax({
				type: "post",
				url: "/user/checkPatient",
				dataType: "json",
				data:{name:txt}, 
				success: function (data){
					var str=data;
					if(str=="2") {
						$.alert("就诊人已存在");
					}
					if(str=="3") {
					    $.alert("数据错误");
					}
				}
			})
		}
	});
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
			else{
				if(text.length==18){
					if(text.charAt(16)%2==1){
						sex=1;
					}else{
						sex=2;
					}
					ageyear=text.substring(6,10);
					agemonth=text.substring(10, 12);
					ageday=text.substring(12, 14);
				}else{
					if(text.charAt(14)%2==1){
						sex=1;
					}else{
						sex=2
					}	
					ageyear="19"+text.substring(6,8);
					agemonth=text.substring(8, 10);
					ageday=text.substring(10, 12);
				}
				if(sex==1){
					$(".nan").addClass("cur");
					$(".nv").removeClass("cur");
					$("#sex").attr("value","1");
				}else{
					$(".nv").addClass("cur");
					$(".nan").removeClass("cur");
					$("#sex").attr("value","2");
				}
				var myDate = new Date(); 
				var month = myDate.getMonth() + 1; 
				var day = myDate.getDate();
				var age = myDate.getFullYear() - ageyear - 1; 
				if (age<=150 && age>=0){
					if (agemonth < month || agemonth == month && ageday <= day ){
						age++; 
					} 
					$("#age").val(age);
				}else{
					$.alert("请输入正确的身份证");
					$("#age").val("");
				}
			}
		}
	});
	$("#age").blur(function(){
		var text=$(this).val();
		var re=/^[0-9]*$/;
		if(!re.test(text)){
			$.alert("年龄格式不正确");
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
	$(".sex").click(function(){
		var text=$(this).find("em").text();
		$(this).addClass("cur").siblings().removeClass("cur");
		if(text=="男"){
			$("#sex").attr("value","1")
		}else{
			$("#sex").attr("value","2")
		}
	});
})
function checkFormat(form) {
	if(form.name.value=='') { $.alert("请填写姓名!"); form.name.focus(); return false; }
	if(form.idcard.value!=""){
		var re=/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
		if(!re.test(form.idcard.value)) { $.alert("身份证格式不正确"); form.idcard.focus(); return false; }
	}
	var re2=/^1[34578][0-9]\d{8}$/;
	if(!re2.test(form.mobile.value)) { $.alert("手机格式不正确"); form.mobile.focus(); return false; }
	if(form.age.value==''){ $.alert("请填写年龄!"); form.age.focus(); return false; }
	return true;
}
</script>  
<?php $this->load->view('common/footer');?>

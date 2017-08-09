<?php $this->load->view('common/header');?>
<title>图文咨询</title>  
<style>
html{ background:#fafafa;}
</style>
</head>
<body class="yellow">
<div class="ys-info">
  <div class="top">
    <div class="left tc">
      <a href="javascript:void(0)"><img class="tx" src="<?=@$doctor['avatar']?>" onerror="this.onerror=null;this.src='/ui/images/doctor.jpg'" alt="" /></a>
      <img class="jb" src="/ui/images/sanjia.png" alt="" />
    </div>
    <div class="right cw8">
      <p class="xm fs16 cw1"><?=@$doctor['name']?></p>
      <p class="zw"><?=@$doctor['position']?></p>
      <p class="sc"><?=@$doctor['hospital']?> <?=$doctor['department']?></p>
    </div>
    <div class="zxgh cw8"><img src="/ui/images/ico3-zx.png" alt="" /><span>图文量 <em><?php echo @$doctor['con_num'];?></em></span></div>
  </div>
</div>
<form method="post" action="/act/consult/<?=@$actinfo['_id']?>/<?=@$code?>" name="addconsult" enctype="multipart/form-data">
<div class="jzr-form pb50">
  <div class="name cfix">
    <div class="left fl fs14">就诊人：</div>
    <div class="right" style="background:none;">
    <span id="patient_name" class="fs14"><?=@$patient['name']?></span>
    </div>
      <input name="patient" id="patient" type="hidden" value="<?=@$patient['name']?>">
      <input name="doctor" type="hidden" value="<?=@$doctor['_id']?>">
  </div>
  <div class="tit cfix"><span class="fs14">病情描述</span><a href="javascript:void(0)" class="about-pop-a bqal fr"><span>病情描述示例</span><img src="/ui/images/bqal.png" alt /></a></div>
  <div class="textarea">
    <textarea name="message" class="lh15 fs13" maxlength="1000"  placeholder="请简述您的【个人信息、主要病史、过敏史、身体基本状况】。请就此次图文咨询提供您的【疾病状况】。包括：疾病起因、症状、持续时间、进行过何种检查和治疗。请在就诊时携带您的相关病历及检查、诊断报告。（请输入10-1000字）"></textarea>
  </div>
  <div class="image cfix">
    <div class="left fl">
      <div class="img-add">
        <input type="file" capture="camera" accept="image/*" name="file0">
      </div>
    </div>
    <div class="right fl lh15">
      <div class="fs13">添加图片（不超过20张）</div>
      <div class="ch4">症状部位，检查报告，诊断报告单，以及病例等其他病情资料</div>
    </div>
  </div>  
  <div class="tit fs14">问诊规则</div>
  <div class="con lh15">
    <p>1.义诊咨询时间及义诊口令码会在活动开始前1天单独发送给您。</p>
    <p>2.由于医生时间宝贵，每个人可以预先提5个问题。咨询结束后，会话终止。</p>
    <p>3.提前准备好您的：姓名+年龄+详细的病情（包括以往病例资料）+您想要咨询的问题。方便医生清楚明白的诊断您的病情。</p>
  </div>
</div>
<div class="jzr-submit bb" style="height:5rem;">  
  <div class="btn"><input type=submit name="btn1" style="width:100%;" value="确 定" class="btn1" onClick="return checkFormat(this.form)" /></div>
</div>
</form>  
  <!-- 就诊人-选择 -->
  
  <!-- 就诊人-选择 end --> 
</div>
<script type="text/javascript">  
$(document).ready(function(){
	$(".about-pop-a").click(function(){
		aboutpop("病情描述示例","嫦娥，女，65岁，现住月宫市。高血压13年，一直药物控制。糖尿病5年，药物控制。子宫肌瘤，2011年手术。青霉素过敏。2月前不明原因出现左侧肢体麻木，感觉异常；有时出现头痛，持续大约20分钟，发作时视物模糊，伴呕吐。在当地做了脑电图、脑血流图、头部CT，诊断缺血性脑卒中、大面积腔梗，椎-基底动脉供血不足。现在服用硝苯地平、复方丹参、氯吡格雷、VE等药物，但效果不明显。咨询目的：1、诊断上有没有问题？2、治疗药物推荐；3、平时生活上需要注意什么？4、治疗上需要注意些什么？");
	})
	updateimg();//图片处理
});

function checkFormat(form) {
	if(form.patient.value=='') { $.alert("就诊人不能为空!"); form.patient.focus();return false; }
	if(form.message.value=='') { $.alert("病情描述不能为空!"); form.message.focus(); return false; }	
    if(form.message.value.length>1000||form.message.value.length<10) { $.alert("病情描述输入文字范围:10~1000字!"); form.message.focus(); return false; }
	$.adddiv("mask");$(".btn1").hide();
	return true;
}
wx.ready(function () { 
	wx.onMenuShareAppMessage({
		title: '<?=@$doctor['name']?>-<?=@$doctor['position']?>[找明医]', // 分享标题
		desc: '<?=$doctor['hospital']?> <?=$doctor['department']?>', // 分享描述
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

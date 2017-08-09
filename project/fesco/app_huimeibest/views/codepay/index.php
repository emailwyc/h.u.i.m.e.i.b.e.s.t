<!DOCTYPE html>
<html>
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="format-detection" content="telephone=no">
  <meta content="yes" name="apple-mobile-web-app-capable">
  <meta content="black" name="apple-mobile-web-app-status-bar-style">
  <meta name="screen-orientation" content="portrait">
  <meta name="x5-orientation" content="portrait">
  <title>【找明医】支付</title>
  <style>
* { margin: 0; padding: 0; }
.cfix { zoom: 1; }
.cfix:after { content: "."; display: block; clear: both; height: 0; overflow: hidden; visibility: hidden; }
.fl { float: left; }
 .fr { float: right; }
html { background: #f3f5f7; font: 12px/1 "微软雅黑"; color: #666; }
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button{ -webkit-appearance: none !important; margin: 0; }
.body { padding: 12px; }
@media screen and (min-width: 768px) { .body { width: 720px; margin: 0 auto; } }
input, textarea { -webkit-appearance: none; width: 100%; outline: none; resize: none; border: none; }
.input { background: #fff; border-bottom: 1px solid #eee; margin-bottom: 15px; }
 .input .logo { padding-bottom: 10px; }
.input .logo img { padding-right: 10px; }
.input .logo h1 { font-size: 14px; color: #333; line-height: 25px; padding-top: 5px; }
.input .money { position: relative; border: 1px solid #1278c2; padding: 0 10px; border-radius: 5px; margin: 10px 0; }
.input .money input { border: none; height: 40px; font-size: 16px; }
.input .money span { font-size: 20px; right: 10px; position: absolute; top: 0px; line-height: 42px; color: #ccc; }
.input .represent { border: 1px solid #ccc; padding: 5px 10px; border-radius: 5px; margin-top: 10px; }
.input .represent textarea { border: none; height: 60px; font-size: 16px; line-height: 20px; }
.input .line { background: #eee; height: 1px; overflow: hidden; }
.output { background: #fff; border: 1px solid #eee; margin-bottom: 3px; }
.output .fl { font-size: 16px; } .output .fr { font-size: 20px; color: #e7566b; }
.submit input { background: #1278c2; height: 40px; border-radius: 5px; color: #fff; font-size: 17px; }
</style>
 <script src="/ui/js/jquery-1.12.0.min.js"></script>
 <!--<script src="/ui/js/XTools.js?v=<?=@config_item("global_version");?>"></script>-->
<script>
//浮点数转换
function changeTwoDecimal_f(x) {
	if(x==""){ x=0;}
	var f_x = parseFloat(x);
	if (isNaN(f_x)) {
		alert("输入金额格式错误!");
		return false;
	}
	f_x = Math.round(f_x*100)/100;
	var s_x = f_x.toString();
	var pos_decimal = s_x.indexOf('.');
	if (pos_decimal < 0) {
		pos_decimal = s_x.length;
		s_x += '.';
	}
	while (s_x.length <= pos_decimal + 2) {
		s_x += '0';
	}
	return s_x;
}


var bindVerify = function(priceDomId,showDomId){
	$("#"+priceDomId).bind('change keyup mousemove',function(){
		//过滤非数字
		var v = $("#" + priceDomId).val();
		$("#" + priceDomId).val(v);
		txt = changeTwoDecimal_f(v);
		if(!isNaN(txt)) {$("#"+showDomId).html(txt);}
	});
}
$(document).ready(function () {
	bindVerify('priceDomId','showDomId');	
});
    function checkFormat(form) {
		var v = form.priceDomId.value;
		var regular = /^([1-9][\d]{0,7}|0)(\.[\d]{1,2})?$/;
		var txt = 0;
		if (regular.test(v)) {
			$("#priceDomId").val(v);
			txt = changeTwoDecimal_f(v);
			$("#showDomId").html(txt);
		}else{
			alert("金额格式不对，请检查！");
			$("#priceDomId").val("");
			form.priceDomId.focus();
			return false;
		}
      if (form.priceDomId.value == '') {
        alert("请输入支付金额");
        form.priceDomId.focus();
        return false;
      }
      return true;
    }
</script>
  </head>
  <body>
  <form method="post" action="/CodePay/index" name="addcodepay" enctype="multipart/form-data">
    <div class="input">
      <div class="body">
        <div class="logo cfix">
          <div class="fl"><img src="/ui/images/logo.png" width="50" height="50" alt=""/></div>
          <div class="fl">
            <h1>找明医</h1>
            <p>严肃对待医疗 温暖对待生命</p>
          </div>
        </div>
        <div class="line"></div>
        <div class="money">
          <input type="number" name="price" id="priceDomId" min="0.01" max="9999999" step="0.01" placeholder="支付金额" />
          <span>￥</span></div>
        <div class="line"></div>
        <div class="represent">
          <textarea placeholder="描述（可选）" name="desc" maxlength="100"></textarea>
        </div>
      </div>
    </div>
    <div class="output">
      <div class="body cfix">
        <div class="fl">实付金额</div>
        <div class="fr">¥<span id="showDomId">0.00</span></div>
      </div>
    </div>
    <div class="submit">
      <div class="body">
        <input type="submit" value="支付" name="btn1" onclick="return checkFormat(this.form)"/>
      </div>
    </div>
  </form>
</body>
</html>

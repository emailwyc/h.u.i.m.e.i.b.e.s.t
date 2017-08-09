<?php $this->load->view('common/header');?>
<title>义诊活动</title>
<style type="text/css">
html{ background:#f3f5f7;}
.yzys-head{margin:1rem 1.2rem; border-radius:.5rem; background:#fff; box-shadow:0 .2rem .3rem .1rem rgba(0,0,0,0.1); background:url(/ui/images/freeclinic-head.jpg) no-repeat top center; background-size:100% auto; padding:.5rem;}
.yzys-head .tit{ line-height:2.2rem;}
.yzys-head img{ width:2.2rem; height:2.2rem;}
.yzys-head .con{ padding-top:.5rem;}
.yzys-box{ margin:1rem 1.2rem; border-radius:.5rem; background:#fff; box-shadow:0 .2rem .3rem .1rem rgba(0,0,0,0.1);}
.yzys-tit{ padding:1rem 0;}
.yzys-tit div{ line-height:1.5rem; border-left:.4rem solid #0076c5; padding-left:.5rem; }
.yzys-text{ border-top:1px solid #d7d7d7; padding:1rem;}
.yzys-list .item{ border-top:1px solid #d7d7d7;}
.yzys-list .item .top{ line-height:2.8rem; color:#0076c5; display:-moz-box; display:-webkit-box; display:box; padding:0 3rem;}
.yzys-list .item .top span{-moz-box-flex:1;-webkit-box-flex:1;box-flex:1; display:block; height:1.4rem; border-bottom:1px solid #0076c5;}
.yzys-list .item .top em{ padding:0 .5rem; display:block;  }
.yzys-list .item .btm{ display:-moz-box; display:-webkit-box; display:box; padding:0 1rem; -webkit-box-align:center; -moz-box-align:center; box-align:center;}
.yzys-list .item .btm .img{ width:8.7rem; padding-bottom:.5rem;}
.yzys-list .item .btm .img i{ display:inline-block; width:7rem; height:7rem; border:.5rem solid #eee; border-radius:15rem; overflow:hidden;}
.yzys-list .item .btm .img img{width:7rem; height:7rem;}
.yzys-list .item .btm .img p{ background:url(/ui/images/ribbon.png) no-repeat center; background-size:100% 100%; width:8.7rem; height:2.1rem; color:#fff; margin:-1rem auto 0; position:relative; z-index:10;}
.yzys-list .item .btm .img div{ color:#999;}
.yzys-list .item .btm .about{-moz-box-flex:1;-webkit-box-flex:1;box-flex:1; padding:0 .5rem;}
.yzys-list .item .btm .btn{ width:6rem;}
.yzys-list .item .btm .btn a{ display:block; height:2.4rem; width:6rem; line-height:2.4rem; background:#0076c5; color:#fff; border-radius:.5rem; box-shadow:0 .15rem .25rem rgba(0,0,0,0.20); margin:2rem 0;}
.yzys-list .item .btm .btn .btn-ksyz{ background:#fff; color:#0076c5;}
.yzys-list .item .btm .btn .gray{ background:#eee; color:#999;}
</style>
<script>
$(window).load(function(){
	ajaxyzys("0","/act/doclistjson");
	ajaxyzys("1","/act/doclistjson1");
	$(document).on("click",".yzys-list .item .btm .btn .gray",function(){
		$.alert("义诊时间："+$(this).attr("data-time"));
	});	
});
function ajaxyzys(num,url){
	$.ajax({
		type: "get",
		url: url,
		dataType: "json",	
		beforeSend: function(){
		},
		success: function (data){
			var str="";
			for(i in data)
			{
				str+="<div class=\"item\"><div class=\"top\"><span></span><em>限额"+data[i].regnum.all+"人，已报名："+data[i].regnum.reg+"人</em><span></span></div><div class=\"btm\"><div class=\"img lh15 tc\"><i><img src=\""+data[i].doctor.avatar+"\" alt=\"\" /></i><p class=\"fs10\">"+data[i].doctor.name+"</p>";
				if(num=="1"){
					str+="<div>"+data[i].startT+"</div>";
				}
				str+="</div><div class=\"about lh20\"><p>职称："+data[i].doctor.position+"</p><p>科室："+data[i].doctor.department+"</p><p>医院："+data[i].doctor.hospital+"</p><p>"+data[i].daysT+" — "+data[i].dayeT+"</p></div><div class=\"btn tc\">";
				if(num=="0"){
					if(data[i].isshow==1){
						str+="<a href=\"/act/free/"+data[i]._id.$id+"\" class=\"btn-ksyz\">开始义诊</a>";
					}else{
						str+="<a href=\"javascript:void(0);\" class=\"btn-ksyz gray\" data-time=\""+data[i].daysT+"-"+data[i].dayeT+"\">开始义诊</a>";
					}
				}
				str+="<a href=\"/act/freeshare/"+data[i]._id.$id+"\" class=\"btn-yzbm\">义诊报名</a></div></div></div>";

			}
			$(".yzys-list").eq(num).append(str);
		}
	})
}
</script>

</head>  
<body>
  <div class="yzys">
    <div class="yzys-box">
      <div class="yzys-tit">
        <div class="fs15">今日义诊</div>
      </div>
      <div class="yzys-list">
        
      </div>
    </div>  
    <div class="yzys-box">
      <div class="yzys-tit">
        <div class="fs15">其他义诊</div>
      </div>
      <div class="yzys-list">
      </div>
    </div>  
    <div class="yzys-box">
      <div class="yzys-tit">
        <div class="fs15">活动规则</div>
      </div>
      <div class="yzys-text lh15">
        <p>1.点击您想参与义诊医生的右侧【义诊报名】按键<p>
        <p>2.将所弹出界面分享至朋友圈，获得【义诊口令】<p>
        <p>3.在义诊活动开始后点击【开始义诊】，输入义诊口令后即可参与义诊<p>
        <p>（找明医温馨提示：如果您忘记保存义诊口令，可以在报名成功后再次点击【义诊报名】查看义诊口令）<p>
        <p>本活动最终解释权归找明医所有。</p>
      </div>
    </div>
  </div>
<?php $this->load->view('common/footer');?>

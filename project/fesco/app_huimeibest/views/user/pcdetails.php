<?php $this->load->view('common/header');?>
<title><?=@$info['stat'];?>通知</title>
<script type="text/javascript">
$(document).ready(function(){
		if($(".zxgh-con .text3").height()>$(".zxgh-con .text3 span").height()*2){
			$(".zxgh-con .text3").addClass("con");
			$(".zxgh-con .btn").show();
		}
	$(".zxgh-con .btn").click(function(){
		if($(".zxgh-con .text3").hasClass("con"))
		{
			$(this).children("a").text("收回")
			$(".zxgh-con .text3").removeClass("con")
		}
		else
		{
			$(this).children("a").text("展开")
			$(".zxgh-con .text3").addClass("con")
		}
	})
 $(document).on("click",".img-fd",function(){
         var image = new Image();
         image.src=$(this).attr("data-img");
         image.onload = function () {
             $(".img-pop").append("<img src=\""+image.src+"\"/>").show();
             var h1=$(".img-pop").height()-20;
             var w1=$(".img-pop").width()-20;
             var hw1=h1/w1;
             var hw2=this.height/this.width;
             if(hw1<=hw2){
                 $(".img-pop img").height(h1).width(h1/hw2)
             }
             else{
                 $(".img-pop img").width(w1).height(w1*hw2)
             }

             $(".img-pop img").css({"padding-top":($(".img-pop").height()-$(".img-pop img").height())/2,"padding-left":($(".img-pop").width()-$(".img-pop img").width())/2})
         }

     })
     $(".img-pop").click(function(){
         $(this).find("img").remove();
		 $(this).hide();
     })
})
</script>
<style>
html{ background:#f2f2f2;}
</style> 
</head>  
<body>
  <!-- 咨询挂号-内容 -->
  <div class="zxgh-con lh20 fs14 <?php if(empty($imgs)){?>zxgh-con2<?php }?>">
      <div class="item">
        <div class="text1 fs14">就诊人：<?=@$info['name']?></div>
        <div class="text2">
          <p>性&nbsp;&nbsp;&nbsp;别：<?php if($info['gender']=="female"){ echo "女";}else{ echo "男";}?></p>
          <p>年&nbsp;&nbsp;&nbsp;龄：<?=@$info['age']?></p>
        </div>
        <div class="yuan"></div>
      </div>
      <div class="item">
        <div class="text1">预约医生：<?=@$doctor['name']?></div>
        <div class="text1">服务类型：电话咨询</div>
        <div class="yuan"></div>
      </div>
      <div class="item">  
        <div class="text2">
		<p>接听时间：<span class="cr1"><?=@date('Y-m-d',$info['schedule']->sec);?> <?=@$info['interval'];?></span></p>
          <p>订单状态：<span class="cr1"><?=@$info['stat']?></span></p>
          <p>使用次数：1次</p>
        </div>  
        <div class="yuan"></div>
      </div>
      <div class="item">
        <div class="text3 ch4"><span class="ch8">病情描述：</span> <?=@$info['message']?></div>
        <div class="btn tr"><a href="javascript:void(0)">展开</a></div>
        <div class="yuan"></div>
      </div>
		<?php if(!empty($imgs)){?>
      <div class="photo">
         <p>图片信息</p>
         <div class="cfix">
			<?php foreach($imgs as $v){?>
           <div><img class="img-fd" data-img="<?=@$v['thumbnail'];?>" src="<?=@$v['thumbnail'];?>" alt="" /></div>
	   <?php }?>
         </div> 
      </div>
	   <?php }?>
  </div>
  <div class="img-pop"></div>
  <!-- 咨询挂号-内容 end -->
<?php $this->load->view('common/footer');?>

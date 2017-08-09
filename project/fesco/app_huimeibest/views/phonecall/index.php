<?php $this->load->view('common/header');?>
<title>电话咨询</title>
<script src="/ui/js/swiper.3.1.2.jquery.min.js"></script>
<link rel="stylesheet" href="/ui/css/swiper.css">
</head>
<body class="blue">
<div class="ys-info">
  <div class="top">
    <div class="left tc"> <a href="javascript:void(0)"><img class="tx" src="<?=@$doctor['avatar']?>" onerror="this.onerror=null;this.src='/ui/images/doctor.jpg'" alt="" /></a> <img class="jb" src="/ui/images/sanjia.png" alt="" /> </div>
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
    <div class="zxgh cw8"><img src="/ui/images/ico3-dh.png" alt="" /><span>咨询量 <em>
      <?=isset($doctor['phonecall_num'])?$doctor['phonecall_num']:0;?>
      </em></span></div>
  </div>
</div>
<div class="ys-time swiper-container">
  <div class="tit fs16"> <img src="/ui/images/ico4-6.png" alt="" />请选择与医生通话时间 </div>
  <div class="btn tc fs16  fs1"> <em class="prev swiper-button-prev"></em><span class="date">
    <?=@$table[0]['showt']?>
    <?=@$table[0]['week']?>
    </span><em class="next swiper-button-next"></em> </div>
  <div class="calendar swiper-wrapper">
    <?php foreach($table as $v){?>
    <div class="swiper-slide">
      <?php if(!empty($v['1'])):?>
	  <div class="list-tit"> 上午 (<?=@str_replace(',','-',$v['1']['interval']);?>)</div>
      <div class="list cfix">
        <?php if(@$doctor['service_provided']['phonecall']['price_05']>=0 && $v['1']['minutes_remain']>=5):?>
        <div><a href="/phonecall/order/<?=@$v['1']['_id']?>/05">5分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_10']>=0 && $v['1']['minutes_remain']>=10):?>
        <div><a href="/phonecall/order/<?=@$v['1']['_id']?>/10">10分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_15']>=0 && $v['1']['minutes_remain']>=15):?>
        <div><a href="/phonecall/order/<?=@$v['1']['_id']?>/15">15分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_20']>=0 && $v['1']['minutes_remain']>=20):?>
        <div><a href="/phonecall/order/<?=@$v['1']['_id']?>/20">20分钟</a></div>
        <?php endif;?>
      </div>
      <?php endif;?>
      <?php if(!empty($v['2'])):?>
		  <div class="list-tit"> 下午 (<?=@str_replace(',','-',$v['2']['interval']);?>)</div>
      <div class="list cfix">
        <?php if(@$doctor['service_provided']['phonecall']['price_05']>=0 && $v['2']['minutes_remain']>=5):?>
        <div><a href="/phonecall/order/<?=@$v['2']['_id']?>/05">5分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_10']>=0 && $v['2']['minutes_remain']>=10):?>
        <div><a href="/phonecall/order/<?=@$v['2']['_id']?>/10">10分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_15']>=0 && $v['2']['minutes_remain']>=15):?>
        <div><a href="/phonecall/order/<?=@$v['2']['_id']?>/15">15分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_20']>=0 && $v['2']['minutes_remain']>=20):?>
        <div><a href="/phonecall/order/<?=@$v['2']['_id']?>/20">20分钟</a></div>
        <?php endif;?>
      </div>
      <?php endif;?>
      <?php if(!empty($v['3'])):?>
		  <div class="list-tit"> 晚上 (<?=@str_replace(',','-',$v['3']['interval']);?>)</div>
      <div class="list cfix">
        <?php if(@$doctor['service_provided']['phonecall']['price_05']>=0 && $v['3']['minutes_remain']>=5):?>
        <div><a href="/phonecall/order/<?=@$v['3']['_id']?>/05">5分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_10']>=0 && $v['3']['minutes_remain']>=10):?>
        <div><a href="/phonecall/order/<?=@$v['3']['_id']?>/10">10分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_15']>=0 && $v['3']['minutes_remain']>=15):?>
        <div><a href="/phonecall/order/<?=@$v['3']['_id']?>/15">15分钟</a></div>
        <?php endif;?>
        <?php if(@$doctor['service_provided']['phonecall']['price_20']>=0 && $v['3']['minutes_remain']>=20):?>
        <div><a href="/phonecall/order/<?=@$v['3']['_id']?>/20">20分钟</a></div>
        <?php endif;?>
      </div>
      <?php endif;?>
    </div>
    <?php }?>
  </div>
  <div class="about ch4"> 点击浅色格子，选择电话咨询时长 </div>
  <div class="page swiper-pagination"> </div>
</div>
<script type="text/javascript">  
var num;
var table = JSON.parse('<?=@$table_json;?>');
var swiper = new Swiper('.swiper-container', {
	pagination: '.swiper-pagination',
	paginationClickable: true,
	nextButton: '.swiper-button-next',
	prevButton: '.swiper-button-prev',
	// Enable debugger
	debugger: true,
	onTransitionEnd: function(swiper){
		num = swiper.activeIndex;
		$(".ys-time .date").text(table[num].showt+" "+table[num].week);
	} 	  
});
</script>
<?php $this->load->view('common/footer');?>

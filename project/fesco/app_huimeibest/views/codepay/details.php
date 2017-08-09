<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="format-detection" content="telephone=no">
  <meta content="yes" name="apple-mobile-web-app-capable">
  <meta content="black" name="apple-mobile-web-app-status-bar-style">
  <meta name="screen-orientation" content="portrait">
  <meta name="x5-orientation" content="portrait">
  <title>支付成功</title>
  <style>
    * {
      margin: 0;
      padding: 0;
    }

    .cfix {
      zoom: 1;
    }

    .cfix:after {
      content: ".";
      display: block;
      clear: both;
      height: 0;
      overflow: hidden;
      visibility: hidden;
    }

    .fl {
      float: left;
    }

    .fr {
      float: right;
    }
    img{ vertical-align: middle;}
    html {
      background: #fff;
      font: 12px/1 "微软雅黑";
      color: #666;
    }
    .body {
      padding: 12px;
    }

    @media screen and (min-width: 768px) {
      .body {
        width: 720px;
        margin: 0 auto;
      }
    }
    .info{ padding:0 15px; font-size: 14px; line-height: 25px;}
    .info span{ color: #1278c2;}

    .true{ text-align: center; height: 300px;}
    .true .tit{ font-size: 20px; color: #1278c2;}
    .true-text .tit{ padding-top: 50px;}
    .true-text img{ width: 25px; height: 25px; padding-right: 10px;}
    .true-text .con{ padding: 90px 70px 0; line-height: 1.5; font-size: 14px; text-align: left;}
    .true-img .img{ padding-top: 100px;}
    .true-img img{ width: 50px; height: 50px; padding-bottom: 20px;}
    .hint{ position: fixed; left: 0; bottom: 20px; width: 100%; text-align: center; color: #999;}
  </style>
</head>
<body>
<form>
  <div class="body">
<?php if(empty($order['desc'])){?>
    <div class="true true-text">
      <div class="tit"><img src="/ui/images/true.png" alt=""/>支付成功</div>
	  <div class="con"><?=@$order['desc'];?></div>
	</div>
<?php }else{?>
    <div class="true true-img">
      <div class="img"><img src="/ui/images/true.png" alt=""/></div>
      <div class="tit">支付成功</div>
	</div>
<?php }?>
    <div class="info">
      <div class="cfix">
        <div class="fl">收款方：</div>
        <div class="fr"><span>找明医</span></div>
      </div>
      <div class="cfix">
        <div class="fl">支付金额：</div>
		<div class="fr"><span>¥ <?=@$order['price'];?></span></div>
      </div>
      <div class="cfix">
        <div class="fl">支付单号：</div>
		<div class="fr"><?=@$order['_id'];?></div>
      </div>
      <div class="cfix">
        <div class="fl">支付时间：</div>
		<div class="fr"><?php echo date('Y-m-d H:i:s',$order['pay_at']);?></div>
      </div>
      <div class="cfix">
        <div class="fl">客服电话：</div>
        <div class="fr">400-068- 6895</div>
      </div>
    </div>
  </div>
  <div class="hint">
    需要的情况下，请将该页面截屏保存
    </div>
</form>
</body>
</html> 

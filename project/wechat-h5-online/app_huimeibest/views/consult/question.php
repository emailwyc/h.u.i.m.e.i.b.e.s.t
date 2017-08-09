<?php $this->load->view('common/header');?>
<title>咨询问题</title>
</head>
<body class="yellow">
<div class="zxwt-tit lh15"> 为了方便您更好的获取医生建议，请尽量详细描述您的症状，准确提出每个问题，这样您和医生可以更有针对性的探讨病情。 </div>
<form method="post" action="/consult/question/<?=@$order['_id']?>" name="addquetion" enctype="multipart/form-data">
  <div class="zxwt-list pb50">
    <div class="item">
      <div class="tit"> 问题一 </div>
      <div class="con">
        <input type="text" name="question[]" maxlength="50" placeholder="问题描述（50字内）" />
      </div>
    </div>
    <div class="item">
      <div class="tit"> 问题二 </div>
      <div class="con">
        <input type="text" name="question[]" maxlength="50" placeholder="问题描述（50字内）" />
      </div>
    </div>
    <div class="item">
      <div class="tit"> 问题三 </div>
      <div class="con">
        <input type="text" name="question[]" maxlength="50" placeholder="问题描述（50字内）" />
      </div>
    </div>
    <div class="item">
      <div class="tit"> 问题四 </div>
      <div class="con">
        <input type="text" name="question[]" maxlength="50" placeholder="问题描述（50字内）" />
      </div>
    </div>
    <div class="item">
      <div class="tit"> 问题五 </div>
      <div class="con">
        <input type="text" name="question[]" maxlength="50" placeholder="问题描述（50字内）" />
      </div>
    </div>
  </div>
  <div class="zxwt-btn bb tc cfix">
    <div>
      <input type="submit" name="btn1" class="btn1" value="跳过" />
    </div>
    <div>
      <input type="submit" name="btn1" class="btn1" value="提交" />
    </div>
  </div>
</form>
<?php $this->load->view('common/footer');?>

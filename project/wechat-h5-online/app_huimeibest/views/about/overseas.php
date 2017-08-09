<?php $this->load->view('common/header');?>
<title>海外医疗</title>
<style>
html{ height:100%; overflow:hidden;} 
</style>
<style>
.hwyl-list{ border-top:1px solid #e1e1e1;}
.hwyl-list .item{ padding:1rem 2rem; border-bottom:1px solid #e1e1e1; background:#fff; line-height:1.5;}
.hwyl-list .item .tit{ font-size:1.4rem; padding-bottom:.5rem;}
.hwyl-list .item .con .fl{ padding-right:.5rem;}
.hwyl-list .item .con .fr{ padding-left:.5rem;}
.hwyl-list img{ width:8rem;}

.hwyl-top1{ padding-bottom:1rem;}
.hwyl-top1 .tit{ line-height:3rem;}
.hwyl-top1 .con{ display:box; display:-webkit-box;}
.hwyl-top1 .con div{ box-flex:1; -webkit-box-flex:1; height:6rem; padding:1rem 0;}
.hwyl-top1 .con p{ line-height:2rem;}
.hwyl-top1 .one{ width:100%;}
.hwyl-top1 .two{ width:50%;}
.hwyl-top1 .three{ width:50%;}
.hwyl-top1 .one .tit{ background:#fff2b3; color:#f29400;}
.hwyl-top1 .two .tit{ background:#c6dffc; color:#3377f1;}
.hwyl-top1 .three .tit{ background:#d1ecca; color:#5aaa3e;}
.hwyl-top1 .one .con{ background:#fcf9e7;}
.hwyl-top1 .two .con{ background:#e7eff9;}
.hwyl-top1 .three .con{ background:#eaf4e7;}

.hwyl-tit{ line-height:5rem; text-align:center; font-size:1.5rem;}
</style>
</head>  
<body class="universal">
<div id="wrapper" class="b50">
  <div id="scroller">
    <div class="hwyl-tit">
      Mayo Clinic医疗服务项目
    </div>
    <div class="hwyl-list">
      <div class="item">
        <div class="tit">远程医学会诊（eConsults）</div>
        <div class="con cfix">
          <img src="/ui/images/hwyl1.jpg" class="fl" />Mayo Clinic远程医学会诊服务是在患者罹患疾病的时候，协助患者从疾病类型、病情轻重等方面综合分析、评估，帮助患者完成病历的整理、翻译，上传到Mayo Clinic的线上系统，远程咨询Mayo Clinic医生，Mayo Clinic医生向患者提供专业的医疗建议。
        </div>  
      </div>
      <div class="item">
        <div class="tit">转诊服务（Referral）</div>
        <div class="con cfix">
          <img src="/ui/images/hwyl2.jpg" class="fr" />患者前往Mayo Clinic，享用医生专家资源、最新药物、创新技术手段和先进诊疗设备，对其疾病进一步诊断和治疗。Mayo Clinic中国转诊办公室提供让患者更有效率的见到医生的一系列服务。
        </div>  
      </div>
      <div class="item">
        <div class="tit">高管健康保障计划(Executive Health Program)</div>
        <div class="con cfix">
          <img src="/ui/images/hwyl3.jpg" class="fl" />该计划由传统诊断医学和预防医学两部分组成。目前已经为世界500强企业中近300家公司服务了超过40年。计划设置的初衷是帮助这些繁忙的CEO和高管们提供个性化的、有序的、具有时间效率的健康管理服务。Mayo Clinic中国转诊办公室提供让患者更有效率的完成该计划的一系列服务。
        </div>  
      </div>
      <div class="item"> 
        <div class="tit">健康生活提升计划（Healthy Living Program）</div>
        <div class="con cfix">
          <img src="/ui/images/hwyl4.jpg" class="fr" />该计划是基于Mayo Clinic几十年健康生活提升的经验，致力于为那些寻求个性化健康生活提升、延长寿命的贵宾提供一系列的科学计划。我们为您提供个性化的锻炼、营养和生活计划。Mayo Clinic中国转诊办公室提供让患者更有效率的完成该计划的一系列服务。
        </div>
      </div>
    </div>
    <div class="hwyl-tit">
      Mayo Clinic在2015-2016年美国最佳医院排名
    </div>
    <div class="hwyl-top1 cfix tc">
      <div class="fl one"> 
        <div class="tit">8个专科排名第一</div>
        <div class="con cfix">
          <div class="line">
            <p>糖尿病/内分泌科</p>
            <p>肾内科</p>
            <p>妇科</p>
          </div>
          <div class="line">
            <p>胃肠外科</p>
            <p>呼吸科</p>
            <p>泌尿外科</p>
          </div>
          <div>  
            <p>老年病学</p>
            <p>神经内外科</p>
          </div>
        </div>
      </div>
      <div class="fl two">    
        <div class="tit">3个专科排名第二</div>
        <div class="con cfix">
          <div>
            <p>心脏内/外科</p>
            <p>五官科</p>
            <p>骨科</p>
          </div>
        </div>  
      </div>
      <div class="fl three">  
        <div class="tit">1个专科排名第三</div>
        <div class="con cfix">
          <div>
            <p>肿瘤科</p>
          </div>  
        </div> 
      </div>   
    </div>
  </div>
</div>  
<div class="bb">
  <a href="tel:400-691-5199" class="btn1">咨询请点击拨打客服电话</a>
</div>   
<?php $this->load->view('common/footer');?>

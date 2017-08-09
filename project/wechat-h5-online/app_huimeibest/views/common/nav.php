  <div class="bb nav tc">
    <ul>
        <li class="<?php if($cur_nav=="home"){?>cur<?php }?>"><a href="/"><img src="/ui/images/nav-1<?php if($cur_nav=="home"){echo 2;}else{echo 1;}?>.png" alt=""/><span>首页</span></a></li>
        <li class="<?php if($cur_nav=="service"){?>cur<?php }?>"><a  href="/user/service"><img src="/ui/images/nav-2<?php if($cur_nav=="service"){echo 2;}else{echo 1;}?>.png" alt=""/><span>就诊动态</span><div class="nav-dian"></div></a></li>
        <li class="<?php if($cur_nav=="my"){?>cur<?php }?>"><a href="/user/index"><img src="/ui/images/nav-3<?php if($cur_nav=="my"){echo 2;}else{echo 1;}?>.png" alt=""/><span>我</span></a></li>
    </ul>
  </div>
<script type="text/javascript">
	$(window).load(function(){
		navdian();
		setInterval(function(){
			navdian();
		},45000)
	})
</script>

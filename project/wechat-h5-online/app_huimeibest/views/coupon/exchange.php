<?php $this->load->view('common/header');?>
<title>兑换优惠券</title>
</head>  
<body>
  <div class="yhq-form">
    <div>
      <input type="text" class="text" placeholder="请输入兑换码" />
    </div>
    <div>
      <input type="button" class="btn" value="立即兑换" />
    </div> 
  </div>
<script>
$(document).ready(function(){
	$(".yhq-form .btn").click(function(){
		var text=$(".yhq-form .text").val();
		$.ajax({
			type: "post",
			url: "/Json/CodeExchange",
			async: false,
			data:{"code":text},
			dataType: "json", 
			beforeSend: function(){
				
			},
			success: function (data){
				alert(data.msg)
			}
		})
	})
})
</script>
<?php $this->load->view('common/footer');?>

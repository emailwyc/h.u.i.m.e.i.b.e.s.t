<?php
$Agent = $_SERVER['HTTP_USER_AGENT'];
preg_match('/android|iphone/i',$Agent,$matches);
header("Location:http://a.app.qq.com/o/simple.jsp?pkgname=huimei.com.patient");exit;
?>

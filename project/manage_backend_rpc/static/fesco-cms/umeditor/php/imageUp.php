<?php
    header("Content-Type:text/html;charset=utf-8");
    error_reporting( E_ERROR | E_WARNING );
    date_default_timezone_set("Asia/chongqing");
    include "Uploader.class.php";
    //上传配置
    $config = array(
        "savePath" => "/Server/www/manage_backend_rpc/ui/ueditor/image/" ,             //存储文件夹
		"viewPath" => "http://h5test.huimeibest.com:8082/ui/ueditor/image/",
        "maxSize" => 1000 ,                   //允许的文件最大尺寸，单位KB
		"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" )  //允许的文件格式
    );
    //上传文件目录
    $Path = "/Server/www/manage_backend_rpc/ui/ueditor/image/";

    //背景保存在临时目录中
    $up = new Uploader( "upfile" , $config );
    $type = $_REQUEST['type'];
    $callback=$_GET['callback'];

    $info = $up->getFileInfo();
    /**
     * 返回数据
     */
    if($callback) {
        echo '<script>'.$callback.'('.json_encode($info).')</script>';
    } else {
        echo json_encode($info);
    }

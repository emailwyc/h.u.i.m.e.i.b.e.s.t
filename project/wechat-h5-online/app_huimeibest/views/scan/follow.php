<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>极致健康</title>
    <style>
        body, h1, h2, h3, h4, h5, h6, hr, p, dl, dt, dd, ul, ol, li, button, input, textarea, th, td, table, a, em, span, img {
            margin: 0;
            padding: 0;
        }

        body, button, input, select, textarea {
            font: 14px/1.5 "微软雅黑";
            color: #3e3e3e;
            outline: none;
            resize: none;
        }

        em, i {
            font-style: normal;
        }

        ul {
            list-style: none;
        }

        li {
            overflow: hidden
        }

        img {
            border: none;
            display: inline-block;
            vertical-align: middle;
        }

        .focus {
            outline: none;
        }

        .tl {
            text-align: left;
        }

        .tc {
            text-align: center;
        }

        a {
            text-decoration: none;
            color: #666;
        }

        html {
            overflow-y: scroll;
            height: 100%;
            background: #f3f3f3;
        }

        body {

            width: 100%;
            overflow-x: hidden;
            height: 100%;
        }

        .body {
            display: none;
            background: url("/ui/images/app/bg.png") no-repeat fixed bottom center #fff;
            padding: 0 10px;
            max-width: 730px;
            margin: 0 auto;
            height: 100%;
        }

        .share {
            padding: 120px 0 20px;
            font-size: 17px;
            background: url("/ui/images/app/share.png") no-repeat top right;
            background-size: 150px auto;
            border-bottom: 1px solid #ccc;
        }
        .share span {
            color: #d00000;
        }

        .share img {
            width: 100px;
            padding: 60px 0 10px;
        }

        .upload {
            padding-top: 20px;
        }

        .upload a {
            background: #fff;
            margin: 0 auto;
            display: block;
            width: 200px;
            height: 45px;
            line-height: 45px;
            font-size: 18px;
            border: 1px solid #ccc;
            border-radius: 90px;
        }
    </style>
</head>
<body>
<div class="body tc">
    <div class="share">
        <div id="text">
            <p><b>严肃对待医疗</b></p>
            <p><b>温暖对待生命</b></p>
        </div>
        <img src="/ui/images/app/logo.png" alt=""/>
        <p>极致健康</p>
    </div>
    <div class="upload">
        <a href="http://h5.huimeibest.com:8081/download-patient.php">下载安装</a>
    </div>
</div>
</body>
<script>
    //域名传参数
    function GetQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) {
            return unescape(r[2]);
        }
        return null;
    }
    function is_weixn() {
        var ua = navigator.userAgent;
        if (ua.toLowerCase().match(/MicroMessenger/i) == "micromessenger") {
            document.getElementsByClassName("body")[0].style.display = "block";
            if (!!ua.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)) {
                document.getElementById("text").innerHTML = "<p>请点击右上角&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p><p>选择<span>“在Safari中打开”</span></p>";
            } else {
                document.getElementById("text").innerHTML = "<p>请点击右上角&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p><p>选择<span>“在浏览器中打开”</span></p>";
            }
        } else {
            var timeout, t = 1000, hasApp = true;
            var t1 = Date.now();
            /*var ifr = document.createElement("iframe");
            ifr.setAttribute('src', "hmpatient://patient/follow/?doctorId=" + GetQueryString("doctorId"));
            ifr.setAttribute('style', 'display:none');
            document.body.appendChild(ifr);*/
            window.location.href="hmpatient://patient/follow/?doctorId=" + GetQueryString("doctorId");
            timeout = setTimeout(function () {
                var t2 = Date.now();
                if (!t1 || t2 - t1 < t + 200) {
                    hasApp = false;
                }
            }, t);
            setTimeout(function () {
                if (hasApp) {

                } else {
                    document.getElementsByClassName("body")[0].style.display = "block";
                    document.getElementsByClassName("share")[0].style.background="none";
                }
                document.body.removeChild(ifr);
            }, 2000);
        }
    }
    window.onload = function () {

        is_weixn()
    }
</script>
</html>
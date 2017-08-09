/**
 * Created by aosi on 16/4/22.
 */

//域名切换
var isDebug = true;
var huiMeiRestDomain = "http://h5test.huimeibest.com:8082"; //8086 for assistant
if (!isDebug) {
    huiMeiRestDomain = "http://h5.huimeibest.com:8082";
}

//ajax模板
function formatTemplate(dta, tmpl) {
    var format = {
        name: function (x) {
            return x
        }
    };
    return tmpl.replace(/{(\w+)}/g, function (m1, m2) {

        if (!m2)
            return "";
        var text = (format && format[m2]) ? format[m2](dta[m2]) : dta[m2];
        return typeof (text) == 'undefined' ? '' : text;
    });
}

//域名传参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;

}
$(document).ready(function () {
    var templateUrl = "../../html/template/navigator.html";
    $.get(templateUrl, function (res) {
        $('#navContainer').html(res);
        $('#' + currentNavigator).addClass('active');
    });
});

function parseTimeToTimeString(timestamp) {
    return new Date(parseInt(timestamp) * 1000).Format("yyyy-MM-dd hh:mm:ss")
}
function parseTimeToDateString(timestamp) {
    return new Date(parseInt(timestamp) * 1000).Format("yyyy-MM-dd")
}


Date.prototype.Format = function (fmt) { //author: meizz
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

//现在时间
function nowTime(object) {
    var date = new Date();
    var month = date.getMonth() + 1;
    var day = date.getDate();
    var hour = date.getHours();
    var minute = date.getMinutes();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (day >= 0 && day <= 9) {
        day = "0" + day;
    }
    if (hour >= 0 && hour <= 9) {
        hour = "0" + hour;
    }
    if (minute >= 0 && minute <= 9) {
        minute = "0" + minute;
    }
    $(object).val(date.getFullYear() + "-" + month + "-" + day + "T" + hour + ":" + minute);
}

//文本不为空
$(".uploadtext").each(function () {
    $(this).change(function () {
        if ($(this).val() == "") {
            $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
        } else {
            $(this).parents(".form-group").find(".alert").remove();
        }
    })
});
//文本验证
function uploadText(json) {
    var isTrue = 0;
    $(".uploadtext").each(function () {
        $(this).parents(".form-group").find(".alert").remove();
        if ($(this).val() == "") {
            $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
            isTrue += 1;
        }
        json[$(this).attr("name")] = $(this).val();
    });
    return isTrue;
}
//图片验证
function uploadImage(object) {
    var isTrue = 0;
    var img = $(object);
    img.parent(".input-group").next(".alert").remove();
    if (img.val() == "") {
        img.parent(".input-group").next(".preview").remove();
        img.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">请上传图片</div>");
        isTrue += 1;
    } else {
        if (!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG|JPEG)$/.test(img.val())) {
            img.parent(".input-group").next(".preview").remove();
            img.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">图片类型必须是.gif,jpeg,jpg,png中的一种</div>");
            isTrue += 1;
        }
        else {
            img.parent(".input-group").next(".alert").remove();
        }
    }
    return isTrue;
}
function uploadMobile(object) {
    var isTrue = 0;
    var Mobile = $(object);
    Mobile.parents(".form-group").find(".alert").remove();
    if (Mobile.val() != "") {
        var json={"mobile":Mobile.val()};
        var restUrl = huiMeiRestDomain + "/doctor/checkPhone";
        $.post(restUrl, json, function (jsonData) {
            if (jsonData.status != 0) {
                Mobile.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">手机号已注册</div>");
                isTrue += 1;
            }
        }, "json");
    }
    else {
        Mobile.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + Mobile.attr("data-error") + "</div>");
        isTrue += 1;
    }
    return isTrue;
}
//图片上传
$("#icon").change(function () {
    var f = $(this);
    f.parent(".input-group").next(".preview").remove();
    f.parent(".input-group").next(".alert").remove();
    if (f.val() == "") {
        f.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">请上传图片</div>");
        return false;
    } else {
        if (!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG|JPEG)$/.test(f.val())) {
            f.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">图片类型必须是.gif,jpeg,jpg,png中的一种</div>");
            return false;
        }
    }
    var canvas = document.createElement("canvas");
    var img = new Image();
    img.onload = function () {
        var width = 702;
        var height = 408;
        var bor = img.width > img.height ? 1 : 0;
        if (bor) {
            if (img.width > width) {
                var target_w = width;
                var target_h = parseInt(width / img.width * img.height);
            } else {
                var target_w = img.width;
                var target_h = img.height;
            }
        } else {
            if (img.height > height) {
                var target_w = parseInt(height / img.height * img.width);
                var target_h = height;
            } else {
                var target_w = img.width;
                var target_h = img.height;
            }
        }
        canvas.width = target_w;
        canvas.height = target_h;
        canvas.getContext("2d").drawImage(img, 0, 0, target_w, target_h);
        var imgData = canvas.toDataURL();
        imgData = imgData.replace('data:image/png;base64,', '');
        var imagedata = encodeURIComponent(imgData);

        var data = {'msg': imagedata, 'type': 'png', 'class': 'site_article_icon'};
        $.ajax({
            type: "post",
            url: huiMeiRestDomain + "/util/ossUploadFile",
            async: false,
            data: data,
            dataType: "json",
            beforeSend: function () {
            },
            success: function (data) {
                if (data.status == 0) {
                    f.parent(".input-group").after("<img class=\"preview\" src=\"" + data.data.path + "\"/>");
                } else {
                    $.alert(data.message);
                }
            }
        })
    };
    if (typeof FileReader != 'undefined') {
        var reader = new FileReader();
        reader.readAsDataURL(f.prop('files')[0]);
        reader.onload = function (e) {
            img.src = e.target.result;
        };
    } else {
        if (window.navigator.userAgent.indexOf("MSIE") >= 1) {
            this.select();
            img.src = document.selection.createRange().text;
        }
    }
});
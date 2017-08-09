/**
 * Created by aosi on 16/4/22.
 */
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
//网址传参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}
//调用通用页面
var commonHtml={
    navbar:function(cur){
        var restUrl = "../../html/common/navbar.html";
        $.get(restUrl,function (Data) {
            $(".navbar").html(Data);
            $("#"+cur).addClass("active");
        });
    }
}
var isDebug=false;

var huiMeiRestDomain="http://h5test.huimeibest.com:8082/"; //8086 for assistant
if(!isDebug){
    huiMeiRestDomain="http://h5.huimeibest.com:8082/";
}
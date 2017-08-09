//网址传参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}
$(document).ready(function () {
    $.get("js/configure.json", function (data) {
        var url = data.url;
        var json = {"_id": GetQueryString("id")};
        if (GetQueryString("userid")) {
            json.userid = GetQueryString("userid");
        }
        var str = "";
        var restUrl = url + ":8082/patArticle/getDetails";
        $.post(restUrl, json, function (postData) {
            var data = postData.data;
            if (postData.status == 0) {
                if (data.type == 1) {
                    var body = data.body || "";
                    if (!data.body) {
                        var body = data.free_content;
                    }
                    if (GetQueryString("type") == 2) {
                        str += "<div class=\"title\">" + data.title + "</div>";
                        if (data.classes == 2) {
                            str += "<div class=\"time\">" + data.pubdate + "<span>" + data.author + "</span></div>";
                        } else {
                            str += "<div class=\"time\">" + data.pubdate + "</div>";
                        }
                        str += "<div class=\"content content2\">" + body + "</div>";
                        if (!data.body) {
                            str += "<div class=\"upload\"><a href=\"" + url + ":8081/download-patient.php\">此文章为付费内容，请购买后阅读全文</a></div>";
                        } else {
                            str += "<div class=\"upload\"><a href=\"" + url + ":8081/download-patient.php\">下载客户端浏览更多资讯</a></div>";
                        }
                    } else {
                        str += "<div class=\"content\">" + body + "</div>";

                    }
                    $(".body").append(str);
                    document.title = data.title;
                } else {
                    window.location.href = data.link_url;
                }
            } else {

            }
        }, "json");
    }, "json");
})
//网址传参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}

var wechatTokenURL;

$(document).ready(function () {
    $.get("js/configure.json", function (data) {
        var url = data.url;
        var url2= data.url2;
        wechatTokenURL = data.url3 + "/json/getclientsign";
        var json = {"_id": GetQueryString("id")};
        if (GetQueryString("userid")) {
            json.userid = GetQueryString("userid");
        }
        var str = "";
        var restUrl = url + "/patArticle/getDetails";
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
                            str += "<div class=\"upload\"><a href=\"" + url2 + "/download-patient.php\">此文章为付费内容，请购买后阅读全文</a></div>";
                        } else {
                            str += "<div class=\"upload\"><a href=\"" + url2 + "/download-patient.php\">下载客户端浏览更多资讯</a></div>";
                        }
                    } else {
                        str += "<div class=\"content\">" + body + "</div>";

                    }
                    $(".body").append(str);
                    document.title = data.title;

                } else {
                    window.location.href = data.link_url;

                }

                configWechat(data.title, data.description, window.location.href, data.icon);

            } else {

            }

        }, "json");
    }, "json");
})


//微信分享：
function configWechat(title, desc, link, imageUrl) {
    var url = location.href.split('#').toString();
    $.ajax({
        type : "POST",
        url : wechatTokenURL,
        dataType : "json",
        async : false,
        data:{"url":url},
        success : function(data) {
            wx.config({
                debug: false,
                appId: data.appId,
                timestamp: data.timestamp,
                nonceStr: data.nonceStr,
                signature: data.signature,
                jsApiList: [
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage'
                ]
            });
        },
        error: function(res) {
           // alert("failed");
        },
    });

    wx.ready(function () {
        wx.onMenuShareAppMessage({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: link,
            imgUrl: imageUrl,// 分享图标
            dataUrl:'',
            success: function () {
                //alert('share');
            },
            cancel: function () {
                //alert('cancel');
            }
        });

        wx.onMenuShareTimeline({
            title: title, // 分享标题
            link: link, // 分享链接
            imgUrl: imageUrl, // 分享图标
            success: function () {
            },
            cancel: function () {
            }
        });
    });
}
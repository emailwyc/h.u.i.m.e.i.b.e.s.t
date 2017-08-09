$.extend({
    //提示框
    alert: function (txt) {
        if (!$(".alert-pop").is(":animated,:visible")) {
            if ($(".alert-pop").length == 0) {
                $("body").append("<div class=\"alert-pop\"><span>" + txt + "</span></div>");
            }
            $(".alert-pop").css("margin-top", "0rem").fadeIn({
                duration: 300,
                queue: false
            }).animate({marginTop: "-.5rem"}, {duration: 300, queue: false}).find("span").text(txt);
            setTimeout(function () {
                $(".alert-pop").fadeOut({duration: 300, queue: false}).animate({marginTop: "0rem"}, {
                    duration: 300,
                    queue: false
                })
            }, 1000);
        } else {
            return false;
        }
    },
    //添加元素
    adddiv: function (type) {
        if (type == "loading") {
            if ($(".loading").length == 0) {
                $("body").append("<div class=\"loading\"><div></div></div>");
            }
            return false;
        }
        if (type == "mask") {
            if ($(".mask").length == 0) {
                $("body").append("<div class=\"mask\"></div>");
            }
            return false;
        }
    },
    //删除元素
    deletediv: function (type) {
        $("." + type).remove();
    }
});
$(document).ready(function () {
    $.adddiv("loading");
});
$(window).load(function () {
    $.deletediv("loading");
    $(".box1").show();
    $(".form button").click(function () {
        var name = $("#name").val();
        if (name.length==0) {
            $.alert("请输入您的姓名");
            return false;
        }
        var tel = $("#tel").val();
        if (tel.length==0) {
            $.alert("请输入您的手机号码");
            return false;
        };
        var re = /^1[34578][0-9]\d{8}$/;
        if (!re.test(tel)) {
            $.alert("请输入正确的手机号码");
            return false;
        };
        var description = $("#description").val();
        if (description.length==0) {
            $.alert("请输入病情描述");
            return false;
        }
        var data = {
            "name": name,
            "tel": tel,
            "act":"MayoClinic4",
            "description": description
        };
        $.ajax({
            type: "post",
            url: "http://h5.huimeibest.com/Json/websiteQue",
            data: data,
            dataType: "json",
            beforeSend: function () {
                $.adddiv("loading");
            },
            success: function (data) {
                $.deletediv("loading");
                if (data == 1) {
                    $(".box1").hide();
                    $(".box2").show();
                } else {
                    $.alert("服务器繁忙");
                }
            }
        })
    })
});
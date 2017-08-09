/**
 * Created by aosi on 16/4/22.
 */
function goBack() {
    window.history.go(-1);
}
function reload(){
    window.location.reload();
}
function getQueryStringByName(name){

    var result = location.search.match(new RegExp("[\?\&]" + name+ "=([^\&]+)","i"));

    if(result == null || result.length < 1){

        return "";

    }

    return result[1];

}
var common = {
    //刷新分页页数
    refreshPageNumber: function (number) {
        countPage = number;
        var contentHtml = "";
        if (countPage > 0) {
            contentHtml += "<li class=\"pagePrevious\">" +
                "<span aria-label=\"Previous\">" +
                "<i aria-hidden=\"true\">&laquo;</i>" +
                "</span>" +
                "</li>";
            for (var i = 1; i <= countPage; i++) {
                contentHtml += "<li class=\"pagenum\"><span>" + i + "</span></li>";
            }
            contentHtml += "<li class=\"pageNext\">" +
                "<span aria-label=\"Next\">" +
                "<i aria-hidden=\"true\">&raquo;</i>" +
                "</span>" +
                "</li>";
        }
        $("#page .pagination").html(contentHtml);
    },
    //刷新分页样式
    refreshPageStyle: function () {
        $(" #page .disabled ").removeClass("disabled");
        $(" #page .active ").removeClass("active");
        if (currentPage <= 1) {
            $(" #page .pagePrevious ").addClass("disabled");
        }
        if (currentPage >= countPage) {
            $(" #page .pageNext ").addClass("disabled");
        }
        $(" #page .pagenum ").eq(currentPage * 1 - 1).addClass("active");
    },
    //点击分页
    clickPage: function (fn, json) {
        $(document).on("click", "#page li span", function () {
                if (!$(this).parents("li").hasClass("disabled") && !$(this).parents("li").hasClass("active")) {
                    if ($(this).parents("li").hasClass("pagenum")) {
                        currentPage = ($(this).text()) * 1;
                    }
                    else {
                        if ($(this).parents("li").hasClass("pagePrevious")) {
                            currentPage -= 1;
                        }
                        else {
                            if ($(this).parents("li").hasClass("pageNext")) {
                                currentPage += 1;
                            } else {
                                alert("分页程序错误");
                                return false;
                            }
                        }
                    }
                    json.p = currentPage;
                    fn(json);
                }
            }
        )
    },
    //搜索列表
    searchList: function (fn,json) {
        $(" #searchSubmit button ").click(function () {
            var type = $(" #searchSelect button ").attr("data-type");
            var str = $(" #search .form-control ").val();
            json[type] = str;
            fn(json);
        });
        $(" #searchSelect .dropdown-menu li a ").click(function () {
            $(" #searchSelect button ").attr("data-type", $(this).attr("data-type")).html($(this).text() + " <span class=\"caret\"></span> ");
        })
    },
    //处理json
    filterJson: function (i, json) {
        //序号
        json.index = i + 1;
        //编号
        if (json._id) {
            json.id = json._id.$id;
        }
        return json;
    },
    //确认登录
    confirmLogin:function(postJson){
        if(postJson.status==403){
            alert("请先登录");
            window.location.href="../login/login.html";
            return false;
        }
    }
};


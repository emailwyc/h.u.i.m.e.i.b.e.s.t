var art_con = {
        //加载文章列表
        loadArticles: function () {
            var json = {"p": 1};
            art_con.refreshArticles(json);
            common.clickPage(art_con.refreshArticles, json);
            common.searchList(art_con.refreshArticles, json);
        },
        //刷新文章列表
        refreshArticles: function (json) {
            currentPage = json.p;
            var restUrl = huiMeiRestDomain + "gwArticle/getList";
            $.post(restUrl, json, function (postJson) {
                var contentHtml = '';
                if (postJson.data.data.length > 0) {
                    $.each(postJson.data.data, function (i, eachJson) {
                        var data = common.filterJson(i, eachJson);
                        var str = " <tr class=\"clicktable-row\" data-id=\"{id}\"><td style=\" width: 20px\">{index}</td> " +
                            " <td style=\" width: 160px\"><img src=\"{icon}\" class=\"img-thumbnail\" /></td> " +
                            " <td><a href=\"http://www.huimeibest.com/new.html?id={id}\" target=\"_blank\">{title}</a> " +
                            " <p class=\"text-muted small\">{created_at}</p> " +
                            " <p class=\"text-muted small\">{description}</p></td></tr> ";
                        contentHtml += formatTemplate(data, str);
                    });
                    $(" #pageTable tbody ").html(contentHtml);
                    if (postJson.data.page != countPage) {
                        common.refreshPageNumber(postJson.data.page);
                    }
                    common.refreshPageStyle();
                } else {
                    $(" #pageTable tbody ").html("<tr><td colspan=\"3\"><br><p class=\"text-center\">没有更多文章</p><br></td></tr>");
                    common.refreshPageNumber(0);
                    common.refreshPageStyle();
                }
            }, "json");
        },
        //加载文章
        loadArticle: function () {
            $(".uploadtext").each(function () {
                $(this).change(function () {
                    if ($(this).val() == "") {
                        $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
                    } else {
                        $(this).parent(".input-group").next(".alert").remove();
                    }
                })
            });
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
                    var width = 290;
                    var height = 210;
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
                        url: huiMeiRestDomain + "util/ossUploadFile",
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
        },
        //创建文章
        createArticle: function () {
            var json = {};
            var istrue = true;
            var restUrl = huiMeiRestDomain + "gwArticle/addArticle";
            $(".uploadtext").each(function () {
                $(this).parent(".input-group").next(".alert").remove();
                if ($(this).val() == "") {
                    $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
                    istrue = false;
                    return false;
                }
                json[$(this).attr("name")] = $(this).val();
            });
            if (!istrue) {
                return;
            }
            var f = $("#icon");
            f.parent(".input-group").next(".alert").remove();
            if (f.val() == "") {
                f.parent(".input-group").next(".preview").remove();
                f.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">请上传图片</div>");
                return false;
            } else {
                if (!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG|JPEG)$/.test(f.val())) {
                    f.parent(".input-group").next(".preview").remove();
                    f.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">图片类型必须是.gif,jpeg,jpg,png中的一种</div>");
                    return false;
                }
                else {
                    f.parent(".input-group").next(".alert").remove();
                }
            }
            json["class"] = $("#class").val();
            json["order"] = $("#order").val() || 0;
            json["icon"] = $(".preview").attr("src") || "";
            json["description"] = $("#description").val() || "";
            json["body"] = body.getContent();
            $.post(restUrl, json, function (postData) {
                if (postData.status == 0) {
                    alert("添加成功");
                    window.location.href = "list.html";
                } else {
                    alert(postData.message);
                }
            }, "json");
        },
        //加载文章2
        loadArticle2: function (json) {
            var restUrl = huiMeiRestDomain + "gwArticle/getDetails";
            $.post(restUrl, json, function (postData) {
                var data = postData.data;
                if (postData.status == 0) {
                    $("#title").attr("value", data.title);
                    $("#class").val(data.class);
                    $("#order").attr("value", data.order);
                    $("#icon").parent(".input-group").after("<img class=\"preview\" src=\"" + data.icon + "\"/>");
                    $("#description").text(data.description);
                    body.ready(function () {
                        body.setContent(data.body);
                    });
                } else {
                    alert(postData.message);
                    window.location.href = "list.html";
                }
            }, "json");
            $(".uploadtext").each(function () {
                $(this).change(function () {
                    if ($(this).val() == "") {
                        $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
                    } else {
                        $(this).parent(".input-group").next(".alert").remove();
                    }
                })
            });
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
                    var width = 290;
                    var height = 210;
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
                        url: huiMeiRestDomain + "util/ossUploadFile",
                        async: false,
                        data: data,
                        dataType: "json",
                        beforeSend: function () {
                        },
                        success: function (data) {
                            if (data.status == 0) {
                                f.parent(".input-group").after("<img class=\"preview\" src=\"" + data.data.path + "\"/>");
                            } else {
                                $.alert(data.message)
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
        },
        //修改文章
        updateArticle: function (json) {
            var istrue = true;
            var restUrl = huiMeiRestDomain + "gwArticle/editArticle";
            $(".uploadtext").each(function () {
                $(this).parent(".input-group").next(".alert").remove();
                if ($(this).val() == "") {
                    $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
                    istrue = false;
                    return false;
                }
                json[$(this).attr("name")] = $(this).val();
            });
            if (!istrue) {
                return;
            }
            if ($(".preview").attr("src").length == 0) {
                var img = $("#icon");
                img.parent(".input-group").next(".alert").remove();
                if (img.val() == "") {
                    img.parent(".input-group").next(".preview").remove();
                    img.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">请上传图片</div>");
                    return false;
                } else {
                    if (!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG|JPEG)$/.test(img.val())) {
                        img.parent(".input-group").next(".preview").remove();
                        img.parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">图片类型必须是.gif,jpeg,jpg,png中的一种</div>");
                        return false;
                    }
                    else {
                        img.parent(".input-group").next(".alert").remove();
                    }
                }
            }
            json["class"] = $("#class").val();
            json["order"] = $("#order").val() || 0;
            json["icon"] = $(".preview").attr("src") || "";
            json["description"] = $("#description").val() || "";
            json["body"] = body.getContent();
            $.post(restUrl, json, function (postData) {
                if (postData.status == 0) {
                    alert("修改成功");
                    window.location.href = "list.html";
                } else {
                    alert(postData.message);
                }
            }, "json");
        },
        //删除文章
        deleteArticle: function (json) {
            if (confirm("你确定要删除这篇文章吗？")) {
                var restUrl = huiMeiRestDomain + "gwArticle/delArticle";
                $.post(restUrl, json, function (postData) {
                    if (postData.status == 0) {
                        alert("删除成功");
                        window.location.href = "list.html";
                    } else {
                        alert(postData.message);
                    }
                }, "json");
            }
        }
    };
var mes_con = {
    //加载表单列表
    loadMessages: function () {
        var json = {"p": 1,"where":{"act":"MayoClinic4"}};
        mes_con.refreshMessages(json);
        common.clickPage(mes_con.refreshMessages, json);
        common.searchList2(mes_con.refreshMessages, json);
    },
    //刷新表单列表
    refreshMessages: function (json) {
        currentPage = json.p;
        var restUrl = huiMeiRestDomain + "actlog/getLogList";
        $.post(restUrl, json, function (postJson) {
            var contentHtml = '';
            if (postJson.data.data.length > 0) {
                $.each(postJson.data.data, function (i, eachJson) {
                    var data = common.filterJson(i, eachJson);
                    var str = "<tr class=\"clicktable-row\" data-id=\"{id}\">" +
                        "<td>{index}</td>" +
                        "<td>{name}</td>" +
                        "<td>{tel}</td>" +
                        "<td>{description}</td>" +
                        "<td>{ct}</td>" +
                        "</tr>";
                    contentHtml += formatTemplate(data, str);
                });
                $(" #pageTable tbody ").html(contentHtml);
                if (postJson.data.page != countPage) {
                    common.refreshPageNumber(postJson.data.page);
                }
                common.refreshPageStyle();
            } else {
                $(" #pageTable tbody ").html("<tr><td colspan=\"5\"><br><p class=\"text-center\">没有更多表单</p><br></td></tr>");
                common.refreshPageNumber(0);
                common.refreshPageStyle();
            }
        }, "json");
    }
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
    searchList: function (fn, json) {
        $(" #searchSubmit button ").click(function () {
            var type = $(" #searchSelect button ").attr("data-type");
            var str = $(" #search .form-control ").val();
            json.p = 1;
            json[type] = str;
            fn(json);
        });
        $(" #searchSelect .dropdown-menu li a ").click(function () {
            $(" #searchSelect button ").attr("data-type", $(this).attr("data-type")).html($(this).text() + " <span class=\"caret\"></span> ");
        })
    },
    searchList2: function (fn, json) {
        $(" #searchSubmit button ").click(function () {
            var act = $(" #searchSelect button ").attr("data-act");
            var str = $(" #search .form-control ").val();
            json.p = 1;
            json.where["act"] = act;
            fn(json);
        });
        $(" #searchSelect .dropdown-menu li a ").click(function () {
            $(" #searchSelect button ").attr("data-act", $(this).attr("data-act")).html($(this).text() + " <span class=\"caret\"></span> ");
        })
    },
    //处理json
    filterJson: function (i, json) {
        //序号
        json.index = i + 1;
        //是否显示
        if (json.freeze == "no") {
            json.freeze = "显示";
        } else {
            json.freeze = "未显示";
        }
        //编号
        if (json._id) {
            json.id = json._id.$id;
        }
        //手机号码(医生)
        if (json.user) {
            json.user_mobile = json.user.mobile;
        }
        //医生助理
        if (json.assistant) {
            json.assistant_name = json.assistant.name;
        }
        //一级区域
        if (json.region_id) {
            json.region_id_name = json.region_id.name;
        }
        //二级区域
        if (json.region_child_id) {
            json.region_child_id_name = json.region_child_id.name;
        }
        return json;
    },
};
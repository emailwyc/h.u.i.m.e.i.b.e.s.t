var userView={
    //加载用户列表页
    loadUserList:function(){
        currentNavigator = "user_nav";
        var json = {"p": 1};
        userView.refreshUserList(json);
        common.clickPage(userView.refreshUserList, json);
        $(document).on("click",".update",function () {
           var id=$(this).parents("tr").attr("data-id");
           window.location.href = "update.html?id="+id;
        })
    },
    //刷新用户列表
    refreshUserList: function (json) {
        currentPage = json.p;
        var restUrl = huiMeiRestDomain + "/FescoUser/all";
        $.post(restUrl, json, function (postJson) {
            common.confirmLogin(postJson);
            var contentHtml = '';
            if (postJson.data.data.length > 0) {
                $.each(postJson.data.data, function (i, eachJson) {
                    var data = common.filterJson(i, eachJson);
                    var str = " <tr data-id=\"{id}\"><td>{index}</td> " +
                        " <td>{name}</td> " +
                        " <td>{mobile}</td> " +
                        " <td>{ticket_overplus}</td> " +
                        " <td>{ticket_consume}</td> " +
                        " <td><input type='button' value='修改' class='update'></td></tr>";
                    contentHtml += formatTemplate(data, str);
                });
                $(" #pageTable tbody ").html(contentHtml);
                if (postJson.data.page != countPage) {
                    common.refreshPageNumber(postJson.data.page);
                }
                common.refreshPageStyle();
            } else {
                $(" #pageTable tbody ").html("<tr><td colspan=\"5\"><br><p class=\"text-center\">没有更多文章</p><br></td></tr>");
                common.refreshPageNumber(0);
                common.refreshPageStyle();
            }
        }, "json");
    },
    //加载添加用户页
    loadCreateUser:function(){
        currentNavigator = "user_nav";
        $("#submit").click(function () {
            userView.cearteUser();
        });
        $("#return").click(function () {
            goBack();
        })
    },
    //添加用户
    cearteUser:function(){
        var json = {};
        var isTrue = true;
        var restUrl = huiMeiRestDomain + "/FescoUser/add";
        $(".uploadtext").each(function () {
            $(this).parents(".form-group").find(".alert").remove();
            if ($(this).val() == "") {
                $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
                isTrue = false;
            }
            json[$(this).attr("name")] = $(this).val();
        });
        if (!isTrue) {
            return;
        }
        json["ticket_overplus"] = $("#ticket_overplus").val();
        $.post(restUrl, json, function (postData) {
            common.confirmLogin(postData);
            if (postData.status == 0) {
                alert("添加成功");
                goBack();
            } else {
                alert(postData.message);
            }
        }, "json");
    },
    //加载修改用户页
    loadUpdateUser:function(){
        currentNavigator = "user_nav";
        var json = {"id": GetQueryString("id")};
        var restUrl = huiMeiRestDomain + "/FescoUser/one";
        $.post(restUrl, json, function (postData) {
            common.confirmLogin(postData);
            var data = postData.data;
            if (postData.status == 0) {
                $("#name").val(data.name);
                $("#mobile").val(data.mobile);
                $("#ticket_overplus").val(data.ticket_overplus);
                $("#status").val(data.status);
            } else {
                alert(postData.message);
                goBack();
            }
        }, "json");
        $("#submit").click(function () {
            userView.updateUser(json);
        });
        $("#return").click(function () {
            goBack();
        });

    },
    //修改用户
    updateUser:function(){
        var json = {"id": GetQueryString("id")};
        var isTrue = true;
        var restUrl = huiMeiRestDomain + "/FescoUser/update";
        $(".uploadtext").each(function () {
            $(this).parents(".form-group").find(".alert").remove();
            if ($(this).val() == "") {
                $(this).parent(".input-group").after("<div class=\"alert alert-danger\" role=\"alert\">" + $(this).attr("data-error") + "</div>");
                isTrue = false;
            }
            json[$(this).attr("name")] = $(this).val();
        });
        if (!isTrue) {
            return;
        }
        json["ticket_overplus"] = $("#ticket_overplus").val();
        json["status"] = $("#status").val();
        $.post(restUrl, json, function (postData) {
            if (postData.status == 0) {
                alert("修改成功");
                goBack();
            } else {
                alert(postData.message);
            }
        }, "json");
    }
}
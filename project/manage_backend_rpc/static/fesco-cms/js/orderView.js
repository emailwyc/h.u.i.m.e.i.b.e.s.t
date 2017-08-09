var orderView={
    //加载订单列表页
    loadOrderList:function(){
        currentNavigator = "order_nav";
        var json = {"p": 1};
        orderView.refreshOrderList(json);
        common.clickPage(orderView.refreshOrderList, json);
    },
    //刷新订单列表
    refreshOrderList: function (json) {
        currentPage = json.p;
        var restUrl = huiMeiRestDomain + "/FescoUser/orderList";
        $.post(restUrl, json, function (postJson) {
            common.confirmLogin(postJson);
            var contentHtml = '';
            if (postJson.data.data.length > 0) {
                $.each(postJson.data.data, function (i, eachJson) {
                    var data = common.filterJson(i, eachJson);
                    data.doctor_name=eachJson.doctor.name;
                    data.created_time=parseTimeToDateString(eachJson.created_at.sec);
                    data.interval_time=parseTimeToDateString(eachJson.schedule.sec)+" "+eachJson.interval;
                    var str = " <tr data-id=\"{id}\"><td>{index}</td> " +
                        " <td>{name}</td> " +
                        " <td>{mobile}</td> " +
                        " <td>{doctor_name}</td> " +
                        " <td>{created_time}</td> " +
                        " <td>{longTime}分钟</td> " +
                        " <td>{interval_time}</td> " +
                        " <td>{status}</td></tr>";
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
    }
}
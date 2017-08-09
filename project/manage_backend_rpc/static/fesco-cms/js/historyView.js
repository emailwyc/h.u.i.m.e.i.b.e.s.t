var historyView={
    //加载订单列表页
    loadHistoryList:function(){
        currentNavigator = "history_nav";
        var json = {"p": 1};
        historyView.refreshHistoryList(json);
        common.clickPage(historyView.refreshHistoryList, json);
        common.searchList(historyView.refreshHistoryList);
    },
    //刷新订单列表
    refreshHistoryList: function (json) {
        currentPage = json.p;
        var restUrl = huiMeiRestDomain + "/FescoUser/all";
        $.post(restUrl, json, function (postJson) {
            var contentHtml = '';
            if (postJson.data.data.length > 0) {
                $.each(postJson.data.data, function (i, eachJson) {
                    var data = common.filterJson(i, eachJson);
                    var str = " <tr data-id=\"{id}\"><td>{index}</td> " +
                        " <td>{name}</td> " +
                        " <td>{mobile}</td> " +
                        " <td>{ticket_overplus}</td> " +
                        " <td></td> " +
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
    }
}
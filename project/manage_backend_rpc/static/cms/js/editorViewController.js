/**
 * Created by xujian on 5/5/16.
 */
var editorViewController = {
    _parseData: function () {

    },
    refreshDoctors: function () {
        var json = {"p": 1};
        currentPage = json.p;
        var restUrl = "http://h5test.huimeibest.com:8080/doctor/getlist";
        $.post(restUrl, json, function (postJson) {
            var contentHtml = '';
            if (postJson.data.data.length > 0) {
                $.each(postJson.data.data, function (i, data) {
                    //var data = common.filterJson(i, eachJson);
                    if (data.service_provided) {
                        if (data.service_provided.consult) {
                            if (data.service_provided.consult.on) {
                                data.consult = "开启";
                            }
                            else {
                                data.consult = "关闭";
                            }
                        } else {
                            data.consult = "关闭";
                        }
                        if (data.service_provided.phonecall) {
                            if (data.service_provided.phonecall.price_05 >= 0 || data.service_provided.phonecall.price_10 >= 0 || data.service_provided.phonecall.price_15 >= 0 || data.service_provided.phonecall.price_20 >= 0) {
                                data.phonecall = "开启";
                            }
                            else {
                                data.phonecall = "关闭";
                            }
                        } else {
                            data.phonecall = "关闭";
                        }
                    }
                    var redStyle="style='color: red;'";
                    if(i>4){
                        redStyle="";
                    }
                    var str = "<tr class=\"clicktable-row\" data-id=\"{id}\">" +
                        "<td>{index}</td>" +
                        "<td>{name}</td>" +
                        "<td "+redStyle+" >本周文章已发布</td>" +
                        "<td>{consult}</td>" +
                        "<td>{phonecall}</td>" +
                        "<td>{hospital}</td>" +
                        "<td>{department}</td>" +
                        "<td>{position}</td>" +
                        "<td>{assistant_name}</td>" +
                        "<td>{starred}</td>" +
                        "<td>{articels}</td>" +
                        "<td>{region_id_name}</td>" +
                        "<td>{region_child_id_name}</td>" +
                        "<td>{actived_at}</td>" +
                        "</tr>";
                    data.id=data._id.$id;
                    contentHtml += formatTemplate(data, str);
                });
                $(" #pageTable tbody ").html(contentHtml);
                $(".clicktable-row").each(function(index,ele){
                    console.log(ele);
                    $(ele).bind('click',function(){
                        window.location.href = "../../html/shuali/show?id=" + $(ele).attr("data-id");
                    })
                });
            }
        }, "json");
    }
};
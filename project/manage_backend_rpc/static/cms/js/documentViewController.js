/**
 * Created by xujian on 5/5/16.
 */
var documentViewController = {
    _parseData: function () {

    },
    loadDocument: function () {
        var restUrl = "http://localhost/HuimeiNewCode/WMS/data/documents.json";
        //var restUrl ='http://h5test.huimeibest.com:8080/doctor/getlist';
        $.get(restUrl,function (response,status) {
            var contentHtml = '';
            $.each(response, function (i, data) {
                var str = "<tr class=\"clicktable-row\" data-id=\"{id}\">" +
                    "<td>{No}</td>" +
                    "<td>{name}</td>" +
                    "<td>{type}</td>" +
                    "<td>{department}</td>" +
                    "<td>{percent}</td>" +
                    "<td>{status}</td>" +
                    "</tr>";
                contentHtml += formatTemplate(data, str);
            });
            $(" #pageTable tbody ").html(contentHtml);

        });
    }
};
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>玩家征文列表</title>
    <link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <style>
        table td,th {
            border: 1px solid #e5e5e5;
            padding: 8px;
        }
        .table{
            width: 100%;
            width: 800px;
            table-layout:fixed;
        }
    </style>
</head>
<body>
<div>
    <div style="width: 800px;">
        <div>
            <h1>征文列表</h1>
            <button type="button" onclick="exportCotent()" style="float: right">导出征文信息</button>
            <button type="button" onclick="exportGiftLog()" style="float: right">导出奖品记录</button>
        </div>
        <table class="table">
            <tr>
                <th style="width:100px;">手机号</th>
                <th>征文内容</th>
                <th style="width:150px;">提交时间</th>
            </tr>
            @foreach($list as $value)
            <tr>
                <td style="width:100px;">{{$value->mobile}}</td>
                <td style="max-width: 400px;word-break:break-all;">{{$value->content}}</td>
                <td style="width:150px;">{{$value->create_time}}</td>
            </tr>
            @endforeach
        </table>
    </div>
        {{ $list->links() }}
</div>
</body>
</html>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
<script>
    function exportCotent() {
        window.location.href = '/export_content';
    }

    function exportGiftLog() {
        window.location.href = '/export_gift_log';
    }
</script>

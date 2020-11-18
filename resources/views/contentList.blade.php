<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>玩家征文列表</title>
</head>
<body>
<div>
    <div>
        <table>
            <tr>
                <th>手机号</th>
                <th>征文内容</th>
                <th>提交时间</th>
            </tr>
            @foreach($list as $value)
            <tr>
                <td>{{$value->mobile}}</td>
                <td style="max-width: 400px;word-break:break-all;">{{$value->content}}</td>
                <td>{{$value -> create_time}}</td>
            </tr>
            @endforeach
        </table>
    </div>
        {{ $list->links() }}
</div>
</body>
</html>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>周年庆抽奖</title>
</head>
<body>
<div>
    <form id="content_form">
        <div>
            <div>
                <label for="mobile">手机号：{{$mobile}}</label>
            </div>
            <div id="submit_div">
                <button type="button" onclick="submitForm()">抽奖</button>
                <button type="button" onclick="quiteMobile()">退出手机号</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
<script>
    function submitForm() {
        $.get('/lucky', {}, function(response){
            console.log(response);

            if (response.msg) {
                alert(response.msg);
            }
        });
    }

    function quiteMobile() {
        $.get('/quite', {}, function(response){
            if (!response.code) {
                window.location='/';
            }
        });
    }

</script>

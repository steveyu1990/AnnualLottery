<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>周年庆征文活动</title>
</head>
<body>
<div>
    <form id="content_form">
        <div>
            <div>
                <label for="mobile">手机号：</label>
                <input id="mobile" name="mobile" type="text" onkeyup="checkMobile(this)" maxlength="11">
            </div>
            <div id="code_div" style="display: none">
                <label for="code">验证码：</label>
                <input id="code" name="code" type="text" maxlength="4" placeholder="默认值: 6666">
            </div>
            <div id="content_div" style="display: none">
                <label for="content">征文内容：</label>
                <textarea id="content" name="content" maxlength="500"></textarea>
            </div>
            <div id="submit_div" style="display: none">
                <button type="button" onclick="submitForm()">提交</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
<script>
    function submitForm() {
        let mobile = $('#mobile').val();
        let code = $('#code').val();
        let content = $('#content').val();

        let param = {
            'mobile': mobile,
            'code': code,
            'content': content,
            '_token':"{{ csrf_token() }}"
        }

        $.post('/register', param, function(response){
            if (response.msg) {
                alert(response.msg);
            }

            if (response.result.url) {
                window.location=response.result.url;
            }
        });
    }

    function checkMobile(ts) {
        let mobile = ts.value;

        if (/^[1][3,4,5,7,8,9][0-9]{9}$/.test(mobile)) {
            let param = {
                'mobile':mobile,
                '_token':"{{ csrf_token() }}"
            };
            $.post('/check_mobile', param, function(response){
                console.log(response);
                alert(response.msg);

                if (response.code) {
                    return false;
                }

                if (response.result.has_content == false) {
                    $('#content_div').show();
                }

                $('#code_div').show();
                $('#submit_div').show();
            });
        }
    }
</script>

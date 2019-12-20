<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>出错了</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="__STATIC__/admin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="__STATIC__/admin/style/admin.css" media="all">
</head>
<body>


<div class="layui-fluid">
    <div class="layadmin-tips">
        <i class="layui-icon" face>&#xe664;</i>

        <div class="layui-text" style="font-size: 20px;">
            <?php echo(strip_tags($msg));?> 页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
        </div>

    </div>
</div>


<script src="__STATIC__/admin/layui/layui.js"></script>
<script>
    layui.config({
        base: '__STATIC__/admin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index']);

    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
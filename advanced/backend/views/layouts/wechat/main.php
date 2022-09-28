<?php
$weuiroot = '../public/weui';
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,viewport-fit=cover">
    <title>收银台</title>
    <link rel="stylesheet" href="<?=$weuiroot?>/style/weui.css"/>
    <link rel="stylesheet" href="<?=$weuiroot?>/example/example.css"/>
</head>
<body ontouchstart>
    <script type="text/javascript">
     //   document.body.style.webkitTextSizeAdjust = JSON.parse(window.__wxWebEnv.getEnv()).fontScale + '%';
    </script>

    <span aria-hidden="true" id="js_a11y_comma" class="weui-hidden_abs">，</span>

    <div role="alert" class="weui-toptips weui-toptips_warn js_tooltips">错误提示</div>

    <div class="container" id="container"></div>

    <script type="text/html" id="tpl_home">

		<?=$content?>

		</script>

    <script type="text/template" id="footerTmpl">
        <div class="page__ft">
            <a href="javascript:home()"><img src="<?=$weuiroot?>/example/images/icon_footer_link.png" /></a>
        </div>
    </script>
    <script src="<?=$weuiroot?>/example/zepto.min.js"></script>
    <script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
    <script src="https://res.wx.qq.com/t/wx_fed/cdn_libs/res/weui/1.2.8/weui.min.js"></script>
    
    <script src="<?=$weuiroot?>/example/example.js"></script>
    <script src="<?=$weuiroot?>/example/wah.js"></script>
    <script type="text/javascript">
      //WAH.default.init()
    </script>
    <script type="text/javascript">
      

    </script>

</body>
</html>


<?php

//$overtime = (strtotime($order->created_at) + 600);
?>
<div class="page">
  <div class="weui-bottom-fixed-opr-page" id="js_wrp">
    <div class="weui-bottom-fixed-opr-page__content">
      <div class="weui-bottom-fixed-opr-demo">
<?php
//echo json_encode($config);

?>
     <div class="page__hd">
    <h1 >订单无效</h1>
    <p class="page__desc" id="time">请联系管理员</p>
   
  </div>


    </div>
    </div>
    <div class="weui-bottom-fixed-opr" id="js_opr">
      <a onclick="wx.closeWindow()" href="javascript:;" role="button" class="weui-btn weui-btn_primary" id="js_btn">确认</a>
    </div>
  </div>
  <script type="text/javascript">
    window.onload = function(){
     
      wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: "<?= $parameter['appid'] ?>", // 必填，公众号的唯一标识
        timestamp: "<?= $parameter['timestamp'] ?>", // 必填，生成签名的时间戳
        nonceStr: "<?= $parameter['nonceStr'] ?>", // 必填，生成签名的随机串
        signature:"<?= $parameter['signature'] ?>",// 必填，签名
        jsApiList: ['closeWindow'] // 必填，需要使用的JS接口列表
      })

      wx.ready(function() {
        //self.loaded = true
        console.log('微信 isReady')
        alert('微信 isReady')
      })
      wx.error(function(res) {
       // alert(res)
        // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
      })

    }

  </script>

</div>

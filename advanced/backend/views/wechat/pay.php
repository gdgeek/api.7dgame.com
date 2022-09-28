<?php

use yii\helpers\Url;

use api\modules\v1\models\Token;

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
    <h1 >付款页面</h1>
    <p class="page__desc" id="time">请在1分钟之内支付</p>
  </div>
  <div class="weui-form-preview">
    <div role="option" class="weui-form-preview__hd">
        <div class="weui-form-preview__item">
            <label class="weui-form-preview__label">付款金额</label>
            <em class="weui-form-preview__value">¥<?=number_format(json_decode($order->trade->amount)->total / 100, 2)?></em>
        </div>
    </div>
    <div role="option" aria-labelledby="p1 js_a11y_comma p2 js_a11y_comma p3" class="weui-form-preview__bd">
        <div id="p1" class="weui-form-preview__item">
            <label class="weui-form-preview__label">名称</label>
            <span class="weui-form-preview__value"><?=$order->trade->description?></span>
        </div>
        <div id="p2" class="weui-form-preview__item">
            <label class="weui-form-preview__label">订单号</label>
            <span class="weui-form-preview__value"><?=$order->uuid?></span>
        </div>
    </div>
  </div>

    </div>
    </div>
    <div class="weui-bottom-fixed-opr" id="js_opr">
      <a onclick="pay()" href="javascript:;" role="button" class="weui-btn weui-btn_primary" id="js_btn">去支付</a>
    </div>
  </div>
  <script type="text/javascript">
    const overtime = <?=$overtime?>;

    var btn = document.getElementById('js_btn');
    var wrp = document.getElementById('js_wrp');
    function s_to_hs(s){
      //计算分钟
      //算法：将秒数除以60，然后下舍入，既得到分钟数
      var h;
      h  =   Math.floor(s/60);
      //计算秒
      //算法：取得秒%60的余数，既得到秒数
      s  =   s%60;
      //将变量转换为字符串
      h    +=    '';
      s    +=    '';
      //如果只有一位数，前面增加一个0
      h  =   (h.length==1)?'0'+h:h;
      s  =   (s.length==1)?'0'+s:s;
      return h+'分'+s + '秒';
  }
    function getLocalTime(nS) {
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    }
    const handler = setInterval(() => {
      clock()
    }, 1000);
    function clock(){
        const timestamp = Date.parse(new Date())/1000;
        const left = overtime - timestamp;
        if(left <= 0){
          document.getElementById("time").innerText = getLocalTime(overtime)+'支付过期，请重新下单。';
          btn.setAttribute('disabled', true);

          clearInterval(handler)
        }else{

          //btn.removeAttribute('disabled');

          document.getElementById("time").innerText = s_to_hs(overtime-timestamp)+'后订单过期，请确认支付。';
        }

    }
    clock()

    const btnHeight = 48;

    try {
      document.body.style.webkitTextSizeAdjust = JSON.parse(window.__wxWebEnv.getEnv()).fontScale + '%';
    } catch (e) {
      console.warn(e);
    }
    wrp.style.visibility = 'hidden';

    window.addEventListener('switched', function (e) {
      if(btn.offsetHeight > 48){
        wrp.classList.add('weui-bottom-fixed-opr-page_btn-wrap');
      }
      wrp.style.visibility = 'visible';
    });
    window.onload = function(){
    // const parameter = JSON.parse('<?=json_encode($parameter)?>');
     //alert(parameter)
     // alert(signature)

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
        pay();
        
       // alert('微信 isReady')
      })
      wx.error(function(res) {
       // alert(res)
        // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
      })

    }
    function pay(){

        wx.chooseWXPay({
          timestamp: "<?= $config['timestamp'] ?>",
          nonceStr: "<?= $config['nonceStr'] ?>",
          package: "<?= $config['package'] ?>",
          signType: "<?= $config['signType'] ?>",
          paySign: "<?= $config['paySign'] ?>",
          success: function (res) {
            alert('ok')
            wx.closeWindow()
          }
      });
    }
   
  </script>

</div>

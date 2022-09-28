<?php

use yii\helpers\Url;

if($model->image == null){



    $this->registerJs(
<<<EOF

        $(function () {
            //let old_video, output;
            //output = document.getElementById('output');
            let old_video = document.getElementById("video");
        
        
        
            // 获取新的视频
            let new_video = document.getElementById('new_video')
            // 获取老的视频链接地址
            let url = $("#src").attr("src")
        
            // 重点 为新video的src赋值 这里给URL增加后缀是防止浏览器对视频进行缓存，否则截图无法成功
            new_video.src = url + "?t=" + new Date();
            // 重点 此处新video的视频进度如果是0则无法截取图片，随意这是一个比0大的数字即可
            new_video.currentTime = 0.000001
        
            // 重点 开启跨域支持
            new_video.crossOrigin = 'anonymous';
        
            // 重点 使用事件监听方式捕捉事件（通过改变老的视频播放时间改变新的视频播放时间）
            old_video.addEventListener("timeupdate", function () {
                new_video.currentTime = old_video.currentTime
            }, false);
        
            
        
            $("#new_video").bind("canplaythrough", function() {
                loaded();
            });
        })
EOF
    
    );

}

?>
<script>

function prepare (result) {
    let canvas = document.createElement("canvas");
    canvas.width = new_video.videoWidth * 0.5;
    canvas.height = new_video.videoHeight * 0.5;
    // 将所截图片绘制到canvas上，并转化成图片
    canvas.getContext('2d').drawImage(new_video, 0, 0, canvas.width, canvas.height);
    
    console.log(canvas.toDataURL(image_type));

    canvas.toBlob(function(blob) {
        progress(25, '处理完成，提交数据');
        console.log(blob);
        prepare_upload(blob2file(blob, '$name', '.jpg'), function(data) {
            data.type = image_type;
            result(data);
        });
    }, image_type);
    //添加到展示区域
 //   output.prepend(image);
}
</script>

<video id="video" controls="controls" style="height:300px;width:100%">
    <source id="src" src="<?= $model->file->url ?>">
</video>
<video id="new_video" style="height:100%;width:100%" hidden></video>
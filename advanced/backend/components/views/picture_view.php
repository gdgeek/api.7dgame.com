<?php
if ($model->image == null) {



    $this->registerJs(
        <<<EOF
$(function () {
    let old_image, output;
    output = document.getElementById('output');
    old_image = document.getElementById("image");

        
    // 获取新的视频
    let new_image = document.getElementById('new_image')
    // 获取老的视频链接地址
    let url = $("#image").attr("src")
//alert(url);
    // 重点 为新video的src赋值 这里给URL增加后缀是防止浏览器对视频进行缓存，否则截图无法成功
    new_image.src = url + "?t=" + new Date();
   // $("#new_image").attr("src",url )
    // 重点 开启跨域支持
    new_image.crossOrigin = 'anonymous';

})

EOF
    );
}

?>
<script>
    function prepare(result) {
        const new_image = document.getElementById('new_image');
        new_image.crossOrigin = 'anonymous';
        //    const image = $("#image");
        let canvas = document.createElement("canvas");
        if (new_image.width > 256) {
            canvas.width = 256;
            canvas.height = new_image.height * (256 / new_image.width);

            canvas.getContext('2d').drawImage(new_image, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function(blob) {
                progress(25, '处理完成，提交数据');
                console.log(blob);
                prepare_upload(blob2file(blob, '$name', '.jpg'), function(data) {
                    data.type = image_type;
                    result(data);
                });
            }, image_type);
        } else {
            result(null);
        }
    }
</script>
<img id="image" src="<?= $model->file->url ?>" value="erff" alt="..." class="img-thumbnail center-block" style="height:300px;" />
<img id="new_image" onload="loaded()" src="" hidden style="height:300px;" />
<div id="output"></div>
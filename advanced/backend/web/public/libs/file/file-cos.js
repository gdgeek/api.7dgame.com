


var getAuthorization = function (options, callback) {


    var url = config.Url;//'../cos/auth?bucket=' + config.Bucket + '&region=' + config.Region;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onload = function (e) {
        try {
            var data = JSON.parse(e.target.responseText);
            var credentials = data.credentials;
        } catch (e) {
        }
        callback({
            TmpSecretId: credentials.tmpSecretId,
            TmpSecretKey: credentials.tmpSecretKey,
            XCosSecurityToken: credentials.sessionToken,
            ExpiredTime: data.expiredTime, // SDK 在 ExpiredTime 时间前，不会再次调用 getAuthorization
        });
    };
    xhr.send();



};



let cos = new COS({
    getAuthorization: getAuthorization
});

function file_url(filename) {
    let url = cos.getObjectUrl({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: filename,
        Expires: 60,
        Sign: true,
    }, function (err, data) {
        console.log(err || data && data.Url);
    });
    return url;
}
function file_has(filename, end){
	 cos.headObject({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: filename
    }, function (err, data) {
		console.log(err);
		console.log("data" +data);
        console.log(err ||  data);
		if(data){
			end(true);
		}else{
			end(false);
		}
    });
}
function file_upload(filename, md5, file, progress, end){
	alert(filename);
	if (file) {
	
        if (file.size > 1024 * 1024) {
	
            cos.sliceUploadFile({
                Bucket: config.Bucket, // Bucket 格式：test-1250000000
                Region: config.Region,
                Key: filename,
                Body: file,
                onTaskReady: function (tid) {
                    TaskId = tid;
                },
                onHashProgress: function (progressData) {
                    console.log('onHashProgress', JSON.stringify(progressData.percent));
                },
                onProgress: function (progressData) {
					//var width = Math.round(progressData.percent*100)+'%';
					//$("#bar").css('width', width);
					//$("#bar_text").text(width);
					progress(progressData.percent*100)
                    console.log('上传中('+Math.round(progressData.percent*100)+'%)');
                },
            }, function (err, data) {

				//$('#uploadform-filename').val(data.Location);
                console.log(err || '上传完成');
				//$('#upload').text('上传完成');
					
				//$("#uploadform-title").attr("disabled",false);
				//$('#w0').submit();

				end();
            });
        } else {
		//alert(2);
            cos.putObject({
                Bucket: config.Bucket, // Bucket 格式：test-1250000000
                Region: config.Region,
                Key: filename,
                Body: file,
                onTaskReady: function (tid) {
                    TaskId = tid;
                },
                onProgress: function (progressData) {
                       
					//var width = Math.round(progressData.percent*100)+'%';
				//	$("#bar").css('width', width);
					//$("#bar_text").text(width);
					
					progress(progressData.percent*100)
                    console.log('上传中('+Math.round(progressData.percent*100)+'%)');
                },
            }, function (err, data) {
                console.log(err);
				//$('#uploadform-filename').val(data.Location);
                console.log(err || '上传完成');
				//$('#upload').text('上传完成');
				//$("#uploadform-title").attr("disabled",false);
				//$('#w0').submit();
				
				end();
            });
        }
    }
}

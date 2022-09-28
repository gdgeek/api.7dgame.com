
var config = {
    Bucket: 'files-1257979353',
    Region: 'ap-chengdu'
};


var getAuthorization = function (options, callback) {

    // 格式一、（推荐）后端通过获取临时密钥给到前端，前端计算签名
    // 服务端 JS 和 PHP 例子：https://github.com/tencentyun/cos-js-sdk-v5/blob/master/server/
    // 服务端其他语言参考 COS STS SDK ：https://github.com/tencentyun/qcloud-cos-sts-sdk
    // var url = 'http://127.0.0.1:3000/sts';
    var url = 'https://MrPP.com/cos/sts';
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

function selectFile(ext) {

	var input = document.createElement('input');
    input.type = 'file';
	input.accept = '.'+ext;
    input.onchange = function (e){
		alert ('onchange');
	};
//md5.type

}
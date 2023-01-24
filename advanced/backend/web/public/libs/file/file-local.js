function file_url(filename) {
  return config.url + config.path + '/' + filename
}
function file_has(filename, end) {
  $.ajax({
    url: file_url(filename),
    type: 'HEAD',
    error: function () {
      end(false)
    },
    success: function () {
      end(true)
    }
  })
}
function file_update_impl(filename, md5, file, progress, end, skip) {
  var formData = new FormData() //初始化一个FormData对象
  var blockSize = 100000 //每块的大小
  var nextSize = Math.min((skip + 1) * blockSize, file.size) //读取到结束位置
  var fileData = file.slice(skip * blockSize, nextSize) //截取 部分文件 块

  formData.append('file', fileData) //将 部分文件 塞入FormData
  formData.append('name', file.name) //保存文件名字
  formData.append('filename', filename) //保存文件名字
  formData.append('md5', md5)
  formData.append('skip', skip)
  formData.append('block_size', blockSize)
  formData.append('upload_size', nextSize)
  formData.append('size', file.size)

  $.ajax({
    url: config.upload,
    type: 'POST',
    data: formData,
    processData: false, // 告诉jQuery不要去处理发送的数据
    contentType: false, // 告诉jQuery不要去设置Content-Type请求头
    success: function (responseText) {
      if (file.size <= nextSize) {
        //如果上传完成，则跳出继续上传

        end()
        return
      }

      progress((nextSize / file.size) * 100)
      file_update_impl(filename, md5, file, progress, end, ++skip) //递归调用
    },
    error: function (obj) {
      alert(JSON.stringify(obj))
    }
  })
}
function file_upload(filename, md5, file, progress, end) {
  if (file) {
    file_update_impl(filename, md5, file, progress, end, 0)
  }
}




function file_md5(file, progress, end){
	var reader = new FileReader(); 
	var blobSlice = File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice;
	var chunkSize = 2097152;
	var chunks = Math.ceil(file.size / chunkSize);
	var currentChunk = 0;

	var spark = new SparkMD5();

	reader.onload = function(e) {
		console.log("read chunk nr", currentChunk + 1, "of", chunks);
		spark.appendBinary(e.target.result);
 		                // append binary string
        currentChunk++;
		progress((currentChunk+1)*100/chunks);
		if (currentChunk < chunks) {
			do_load();          
		} else {	
			end(spark.end());
			console.info("computed hash");
		}

	};
	function do_load(){
		var start = currentChunk * chunkSize, end = start + chunkSize >= file.size ? file.size : start + chunkSize;
		reader.readAsBinaryString(blobSlice.call(file, start, end));
	}

    do_load();
                
	
}



function files_open(accept, end){
	var input = document.createElement('input');
    input.type = 'file';
    input.accept = accept;
    input.multiple = 'multiple';
    input.onchange = function (e){
	alert(this.files[0].type)
        console.log(this.files.length);
		end(this.files);
	};
	
    input.click();
}

function file_compressed(file,progress, end){

    var zip = new JSZip();
    zip.file("MrPP.fbx", file, { date: new Date('September 27, 2016 00:00:01') });
    var  p = 0;
    var interval = setInterval(function(){
        progress(p*100);
        p = p+((0.95-p)/20) *Math.random();
    }, 500);

    zip.generateAsync({type:"Blob",compression: "DEFLATE"}).then(function(content) {
        var f = new File([content], file.name+'.zip',{type: "zip"});
        p=1;
        progress(p*100);
        window.clearInterval(interval)
        end(f);
    });
} 
function file_open(accept, end) {

	var input = document.createElement('input');
    input.type = 'file';
    input.accept = accept;
    input.onchange = function (e){
	
        var file = this.files[0];
		console.log(file);

		var patternFileExtension = /\.([0-9a-z]+)(?:[\?#]|$)/i;
		var extension = (file.name).match(patternFileExtension);
		file.extension = extension[0];
		end(file);
		
	};
	
    input.click();
//md5.type

}

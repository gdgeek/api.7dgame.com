/*
class Video extends Rete.Component {

    constructor(){
        super("Video");
        this.export = null;
        let self = this;
        this.setVideos = function (videos) {
            self.Videos = videos;
        };
        this.getVideoData = null;
    }

    builder(node) {
        let options = Array();
        for (let i = 0; i < this.Videos.length; ++i) {
            let op = {};
            op.value = this.Videos[i].file_id;
            op.text = this.Videos[i].file_id + ":" + this.Videos[i].file_name;
            options.push(op);
        }

        var id = new StringControl(this.editor, 'id', 'ID', 'ID');
        var video = new SelectControl(this.editor,'video','test', options,'zzz');
        var local = new Rete.Input('transform', "Transform", TransformSocket);
        var out = new Rete.Output('video', "Video", VideoSocket);

        return node
            .addControl(id)
            .addControl(video)
            .addInput(local)
            .addOutput(out);
    }

    result(data, inputs){
        var ret = {};
        ret.id = data.id;
        ret.local = inputs["transform"][0];
        for (let i = 0; i < this.Videos.length; ++i) {
            console.log(this.Videos[i].file_id);
            console.log(data.video);
            if(this.Videos[i].file_id == data.video){
                ret.url = this.Videos[i].url.url;
            }
        }
        return ret;
    }
}
*/
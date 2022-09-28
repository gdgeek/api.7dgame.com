/*

class Picture extends Rete.Component {


    constructor(){
        super("Picture");
        this.export = null;
        let self = this;
        this.setPictures = function (pictures) {
            self.Pictures = pictures;
        };
        this.getPictureData = null;
    }

    builder(node) {
        let options = Array();
        for (let i = 0; i < this.Pictures.length; ++i) {
            let op = {};
            op.value = this.Pictures[i].file_id;
            op.text = this.Pictures[i].file_id + ":" + this.Pictures[i].file_name;
            options.push(op);
        }

        var id = new StringControl(this.editor, 'id', 'ID');
        var picture = new SelectControl(this.editor,'picture',options,0);
        var local = new Rete.Input('transform', "Transform", TransformSocket);
        var out = new Rete.Output('picture', "Picture", PictureSocket);

        return node
            .addControl(id)
            .addControl(picture)
            .addInput(local)
            .addOutput(out);
    }
}

 */
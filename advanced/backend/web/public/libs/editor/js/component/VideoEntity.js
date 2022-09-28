/*class VideoEntity extends Rete.Component {
    constructor(){
        super("VideoEntity");
        this.push = null;
        this.removed = null;
        let self = this;
        this.setNodes = function (nodes) {
            self.nodes = nodes;
        };
    }
    builder(node) {
        var pack = {
            push: this.push,
            data: {temple:'video', node_id: node.id}
        };
        var editor = new EditorControl(this.editor, 'editor', this.nodes[node.id], '(id:'+node.id+")", function () {
            this.push(this.data);
        }.bind(pack));

        var out = new Rete.Output('video', "Video", VideoSocket);
        return node
            .addControl(editor)
            .addOutput(out);
    }

    worker(node, inputs, outputs) {
        outputs['video'] = "asdada";//JSON.parse(node.data.editor);

    }
}
*/
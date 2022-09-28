/*class PictureEntity extends Rete.Component {
    constructor(){
        super("PictureEntity");
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
            data: {temple:'picture', node_id: node.id}
        };
        var editor = new EditorControl(this.editor, 'editor', this.nodes[node.id], '(id:'+node.id+")", function () {
            this.push(this.data);
        }.bind(pack));

        var out = new Rete.Output('picutre', "Picture", PictureSocket);
        return node
            .addControl(editor)
            .addOutput(out);
    }
}
*/
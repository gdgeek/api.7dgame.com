/*class SampleEntity extends Rete.Component {
    constructor(){
		super("SampleEntity");
		this.push = null;
		this.removed = null;
		let self = this;
		this.setNodes = function (nodes) {
			//alert(JSON.stringify(nodes));
			self.nodes = nodes;
		};
		this.title =  "样品实体";
    }
	
    builder(node) {
		node.title =this.title;
		var pack = {
			push: this.push,
			data: {temple:'Sample', node_id: node.id}
		};
		var editor = new EditorControl(this.editor, 'editor', this.nodes[node.id], '(id:'+node.id+")", function () {
			this.push(this.data);
		}.bind(pack));
		
		var out = new Rete.Output('sample', "样品", SampleSocket);
		return node.addControl(editor)
			.addOutput(out);
	}
	
	worker(node, inputs, outputs) {
		//alert(1111);
		console.log(node.data.editor);
		outputs['sample'] = JSON.parse(node.data.editor);
		//alert(JSON.stringify(node)+"!!");
		//outputs['sample'] = node.data.editor;//this.adapt(node.data, inputs);//node.data.num;//输出'num'为数据的num
    }
}
*/
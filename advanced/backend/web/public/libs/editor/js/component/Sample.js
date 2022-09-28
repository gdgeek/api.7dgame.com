/*
class Sample extends Rete.Component {//数字组件

    constructor(){
		super("Sample");
		this.export = null;
    }

    builder(node) {//构建节点
        var id = new StringControl(this.editor, 'id', 'Id');
        var polygen = new Rete.Input('polygen', "Polygen+", PolygenSocket, true);//输入
        var native = new Rete.Input('native', "Native+", NativeSocket, true);//输入
        var hints = new Rete.Input('hints', "Hint+", HintSocket, true);//输入
		var boards = new Rete.Input('boards', "Board+", BoardSocket, true);//输入
		var local = new Rete.Input('transform', "Local", TransformSocket);//输入
		var toolbar = new Rete.Input('toolbar', "Toolbar",ToolbarSocket);//输入
		var out = new Rete.Output('sample', "Sample", SampleSocket);//输出节点

		return node.addControl(id)
            .addInput(local)
            .addInput(polygen)
            .addInput(native)
			.addInput(toolbar)
			.addInput(hints)
			.addInput(boards)
			.addOutput(out);
    }
	result(data, inputs){
		var ret = {};
		ret.id = data.id;
        ret.local = inputs["transform"][0];
        ret.polygens = inputs["polygen"];
        ret.natives = inputs["native"];
		ret.toolbar = inputs["toolbar"][0];
		ret.hints = inputs["hints"];
		ret.boards = inputs["boards"];
		return ret;

	}
	
	worker(node, inputs, outputs) {//工人（节点，输入，输出）

	
    }
}
*/
/*
class Sample extends Rete.Component {//�������

    constructor(){
		super("Sample");
		this.export = null;
    }

    builder(node) {//�����ڵ�
        var id = new StringControl(this.editor, 'id', 'Id');
        var polygen = new Rete.Input('polygen', "Polygen+", PolygenSocket, true);//����
        var native = new Rete.Input('native', "Native+", NativeSocket, true);//����
        var hints = new Rete.Input('hints', "Hint+", HintSocket, true);//����
		var boards = new Rete.Input('boards', "Board+", BoardSocket, true);//����
		var local = new Rete.Input('transform', "Local", TransformSocket);//����
		var toolbar = new Rete.Input('toolbar', "Toolbar",ToolbarSocket);//����
		var out = new Rete.Output('sample', "Sample", SampleSocket);//����ڵ�

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
	
	worker(node, inputs, outputs) {//���ˣ��ڵ㣬���룬�����

	
    }
}
*/
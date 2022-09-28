
// class SampleComponent extends Rete.Component {//�������

//     constructor(){
// 		super("Sample");
// 		this.export = null;
//     }

//     builder(node) {//�����ڵ�
// 		var name = new StringControl(this.editor, 'name', 'Title');
//         var polygen = new Rete.Input('polygen',"Polygen", PolygenSocket);//����
//         var hints = new Rete.Input('hints', "Hint(Array)", HintSocket, true);//����
// 		var boards = new Rete.Input('boards', "Board(Array)", BoardSocket, true);//����
// 		var local = new Rete.Input('transform', "Transform", TransformSocket);//����
// 		var out = new Rete.Output('sample', "Sample", SampleSocket);//����ڵ�


// 		return node.addControl(name)
// 			.addInput(local)
// 			.addInput(polygen)
// 			.addInput(hints)
// 			.addInput(boards)
// 			.addOutput(out);
//     }
// 	result(data, inputs){

// 		var ret = {};
// 		ret.name = data.name;
// 		ret.local = inputs["transform"][0];
// 		ret.poly = inputs["polygen"][0];
// 		ret.hints = inputs["hints"];
// 		ret.boards = inputs["boards"];
// 		return ret;

// 	}
// 	worker(node, inputs, outputs) {//���ˣ��ڵ㣬���룬�����
// 		var ret = result(node.data, inputs);
// 		if (this.export) {
// 			this.export(ret);
// 		}
// 		outputs['sample'] = ret;//node.data.num;//���'num'Ϊ���ݵ�num
//     }
// }


// class SampleEntityComponent extends Rete.Component {//数字组件

//     constructor(){
// 		super("Sample Entity");//super(名字)
// 		this.push = null;
//     }
	
//     builder(node) {//构建节点
// 		var pack = {
// 			push: this.push,
// 			data: {temple:'Sample', node_id: node.id}
// 		};
// 		var edit = new ButtonControl(this.editor, '<i class="fa fa-arrow-circle-right"></i> 编辑', function () {
// 			this.push(this.data);
// 		}.bind(pack));


// 		var out = new Rete.Output('sample', "Sample", SampleSocket);//输出节点
// 		return node.addControl(edit)
// 			.addOutput(out);
//     }
// 	adapt(data, inputs){

// 		var ret = {};
// 		return ret;

// 	}
//     worker(node, inputs, outputs) {//工人（节点，输入，输出）
		
//         outputs['sample'] = this.adapt(node.data, inputs);//node.data.num;//输出'num'为数据的num
//     }
// }

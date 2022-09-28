
/*

class Local extends Rete.Component {//数字组件


    constructor(){
        super("Local");//super(名字)
	
    }
	
	builder(node) {//构建节点

		var effect = new Rete.Input('effects', "Effect+", EffectSocket, true);//输入
		var out = new Rete.Output('transform', "Local", TransformSocket);//输出节点
		return node
			.addControl(new Vector3Control(this.editor, 'position', [0, 0, 0]))
			.addControl(new Vector3Control(this.editor, 'scale', [1, 1, 1]))
			.addControl(new Vector3Control(this.editor, 'angle', [0, 0, 0]))
			.addInput(effect)
			.addOutput(out);//返回node的具体（control， output）
    }

	vector3(v3, multi) {
		

		return {
			"x": Math.round(v3[0] * multi),
			"y": Math.round(v3[1] * multi),
			"z": Math.round(v3[2] * multi)
		};
	}
    worker(node, inputs, outputs) {//工人（节点，输入，输出）
		
		var multi = 1000;
		var data = {};
		data.position = this.vector3(node.data.position, multi);
		data.scale = this.vector3(node.data.scale, multi);
		data.angle = this.vector3(node.data.angle, multi);
		data.multi = multi;
		var effects = inputs['effects'];
		data.effects = [];
		effects.forEach(v => {
			console.log(v);
			data.effects.push(JSON.stringify(v));
		});
		
		outputs['transform'] = data;
		
    }
}

*/
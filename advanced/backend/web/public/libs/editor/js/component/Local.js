
/*

class Local extends Rete.Component {//�������


    constructor(){
        super("Local");//super(����)
	
    }
	
	builder(node) {//�����ڵ�

		var effect = new Rete.Input('effects', "Effect+", EffectSocket, true);//����
		var out = new Rete.Output('transform', "Local", TransformSocket);//����ڵ�
		return node
			.addControl(new Vector3Control(this.editor, 'position', [0, 0, 0]))
			.addControl(new Vector3Control(this.editor, 'scale', [1, 1, 1]))
			.addControl(new Vector3Control(this.editor, 'angle', [0, 0, 0]))
			.addInput(effect)
			.addOutput(out);//����node�ľ��壨control�� output��
    }

	vector3(v3, multi) {
		

		return {
			"x": Math.round(v3[0] * multi),
			"y": Math.round(v3[1] * multi),
			"z": Math.round(v3[2] * multi)
		};
	}
    worker(node, inputs, outputs) {//���ˣ��ڵ㣬���룬�����
		
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
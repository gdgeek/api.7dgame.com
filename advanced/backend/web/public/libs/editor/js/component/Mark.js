
/*

class Mark extends Rete.Component {//�������


    constructor(){
        super("Mark");//super(����)
	
    }
	
	builder(node) {//�����ڵ�

        var out = new Rete.Output('effect', "Effect", EffectSocket);//����ڵ�
        return node
            .addControl(new NumberControl(this.editor, 'id', 0))
            .addControl(new Vector3Control(this.editor, 'position', 'Position', [0, 0, 0]))
			.addControl(new Vector3Control(this.editor, 'angle', 'Angle',[0, 0, 0]))
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

        let data = node.data;
        data.type = "mark";
        outputs['effect'] = data;
		
    }
}

*/
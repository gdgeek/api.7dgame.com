
/*

class Mark extends Rete.Component {//数字组件


    constructor(){
        super("Mark");//super(名字)
	
    }
	
	builder(node) {//构建节点

        var out = new Rete.Output('effect', "Effect", EffectSocket);//输出节点
        return node
            .addControl(new NumberControl(this.editor, 'id', 0))
            .addControl(new Vector3Control(this.editor, 'position', 'Position', [0, 0, 0]))
			.addControl(new Vector3Control(this.editor, 'angle', 'Angle',[0, 0, 0]))
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

        let data = node.data;
        data.type = "mark";
        outputs['effect'] = data;
		
    }
}

*/
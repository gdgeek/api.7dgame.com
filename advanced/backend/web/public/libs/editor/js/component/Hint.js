


/*

class Hint extends Rete.Component {//�ӷ�
    constructor(){
        super("Hint");//����
    }

    builder(node) {//�����ڵ�

		var transform = new Rete.Input('transform', "Local", TransformSocket);//����
		var act = new Rete.Input('act', "Action", ActionSocket);//����
		var propertis = new Rete.Input('propertis', 'Property+', PropertySocket, true);
        var out = new Rete.Output('hint', "Hint", HintSocket);//���

		return node
			.addInput(transform)
			.addInput(act)//����
			.addInput(propertis)//����
			.addControl(new StringControl(this.editor, 'id', 'Id'))
			.addControl(new StringControl(this.editor, 'title', 'Title'))
			.addOutput(out);
    }


	worker(node, inputs, outputs) {//���ڵ㣬���룬�����

    }
}
*/
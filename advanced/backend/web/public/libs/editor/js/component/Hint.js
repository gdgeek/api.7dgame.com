


/*

class Hint extends Rete.Component {//加法
    constructor(){
        super("Hint");//名字
    }

    builder(node) {//构建节点

		var transform = new Rete.Input('transform', "Local", TransformSocket);//输入
		var act = new Rete.Input('act', "Action", ActionSocket);//输入
		var propertis = new Rete.Input('propertis', 'Property+', PropertySocket, true);
        var out = new Rete.Output('hint', "Hint", HintSocket);//输出

		return node
			.addInput(transform)
			.addInput(act)//输入
			.addInput(propertis)//输入
			.addControl(new StringControl(this.editor, 'id', 'Id'))
			.addControl(new StringControl(this.editor, 'title', 'Title'))
			.addOutput(out);
    }


	worker(node, inputs, outputs) {//（节点，输入，输出）

    }
}
*/
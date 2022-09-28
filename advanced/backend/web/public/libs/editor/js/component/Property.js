/*
class Property extends Rete.Component {


	constructor(){
        super("Property");//名字
	
    }

	builder(node) {//构建节点
		var out = new Rete.Output('property', "Property", PropertySocket);//输出
	
		return node
			.addControl(new KeyStringControl(this.editor, 'property', 'Property',{ key: 'Key', value: 'Value' }))//数字控制
            .addOutput(out);//输出
    }

	worker(node, inputs, outputs) {//节点，输入，输出

		outputs['property'] = node.data['property'];
		
    }
}
*/
/*
class Property extends Rete.Component {


	constructor(){
        super("Property");//����
	
    }

	builder(node) {//�����ڵ�
		var out = new Rete.Output('property', "Property", PropertySocket);//���
	
		return node
			.addControl(new KeyStringControl(this.editor, 'property', 'Property',{ key: 'Key', value: 'Value' }))//���ֿ���
            .addOutput(out);//���
    }

	worker(node, inputs, outputs) {//�ڵ㣬���룬���

		outputs['property'] = node.data['property'];
		
    }
}
*/
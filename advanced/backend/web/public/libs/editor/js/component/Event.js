
class Event extends Rete.Component {//�ӷ�
    constructor(){
		super("Event");//����
    }

    builder(node) {//�����ڵ�
     
		var data = new Rete.Input('data',"Data", DataSocket);//����
		data.addControl(new StringControl(this.editor, 'data', '{}'));//json
		var out = new Rete.Output('event', "Event", EventSocket);//���

		return node
			.addControl(new StringControl(this.editor, 'name', 'Name'))//����
			.addInput(data)//���
            .addOutput(out);//���
    }

	

    worker(node, inputs, outputs) {//�ڵ㣬���룬���
		var ret = {};
		ret.id = node.data.id;
		ret.data = node.data.data;
		ret.enabled = true;
		outputs['event'] = ret;//{"file":file, "type":type, "local":local} 
    }
}

// class ItemComponent extends Rete.Component {//�ӷ�
// 	constructor() {
// 		super("Item");//����
// 	}
// 	builder(node) {//�����ڵ�


// 		var json = new Rete.Input('json', "Json", jsonSocket);//����
// 		var out = new Rete.Output('item', "Item", itemSocket);//���
// 		json.addControl(new StringControl(this.editor, 'json', false));//json

// 		return node
// 			.addControl(new StringControl(this.editor, 'id', false))//����
// 			.addInput(json)//���
// 			.addOutput(out);//���
// 	}

// 	isset(r) {
// 		if (typeof (r) === "undefined") {
// 			return false;
// 		}
// 		return true;
// 	}
// 	isstring(s, def) {
// 		return this.isset(s) ? s : def;
// 	}

// 	adapt(data, inputs) {

// 		var ret = {};
// 		ret.id = this.isstring(data.id);
// 		ret.json = this.isstring(data.json, "{}");
// 		return ret;

// 	}


// 	worker(node, inputs, outputs) {//�ڵ㣬���룬���
	
// 		outputs['item'] = this.adapt(node.data, inputs);
// 	}
// }
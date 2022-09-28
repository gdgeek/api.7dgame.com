
// class StorableComponent extends Rete.Component {

// 	constructor() {
// 		super('Storable');

// 		this.task = {
// 			outputs: {}
// 		};
// 	}

// 	builder(node) {var list = new Rete.Input('list', "Serialize(Array)", SerializeSocket, true);//����
// 		return node.addInput(list);
// 	}

// 	worker(node, inputs, data) {
// 		if (typeof StorableComponent.Receive !== "undefined") {
// 			StorableComponent.Receive(JSON.stringify(inputs));
// 		}
// 	}
// }

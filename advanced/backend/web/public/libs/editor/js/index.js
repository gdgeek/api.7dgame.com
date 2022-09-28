class StorableComponent extends Rete.Component {
  constructor() {
    super("Storable");
  }

  builder(node) {
    var list = new Rete.Input('list', "Serialize(Array)", serializeSocket, true);//输入
    return node.addInput(list);
  }

  worker(node, inputs, outputs) {//节点，输入，输出
    if (typeof StorableComponent.Receive !== "undefined") {
      StorableComponent.Receive(JSON.stringify(inputs));
    }
  }
}

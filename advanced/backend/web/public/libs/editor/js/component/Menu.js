// // 
// class MenuComponent extends Rete.Component {//�������

//     constructor() {
//         super("Menu");//super(����)
//     }

//     builder(node) {//�����ڵ�
//         var name = new StringControl(this.editor, 'name');
//         var active = new BoolControl(this.editor, 'active');
//         var items = new Rete.Input('items', "Item(Array)", itemSocket, true);//����
//         var local = new Rete.Input('transform', "Transform", transformSocket);//����
//         var collection = new Rete.Input('collection', "Collection", collectionSocket);//����
//         var out = new Rete.Output('menu', "Menu", menuSocket);//����ڵ�

//         return node
//             .addControl(name)
//             .addControl(active)
//             .addInput(items)
//             .addInput(local)
//             .addInput(collection)
//             .addOutput(out);
//     }

//     isset(r) {
//         if (typeof (r) === "undefined") {
//             return false;
//         }
//         return true;
//     }

//     isstring(s, def) {
//         return this.isset(s) ? s : def;
//     }

//     isvector3(v3) {
//         console.log("isvector3 : " + v3);
//         return v3;
//     }

//     adapt(data, inputs) {
//         var ret = {};
//         ret.name = data.name;
//         ret.active = data.active;
//         ret.local = inputs["transform"][0];
//         ret.items = inputs["items"];
//         ret.collection = inputs["collection"][0];
//         return ret;
//     }

//     worker(node, inputs, outputs) {//���ˣ��ڵ㣬���룬�����
//         outputs['menu'] = this.adapt(node.data, inputs);//node.data.num;//���'num'Ϊ���ݵ�num
//     }
// }

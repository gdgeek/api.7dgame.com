

// class SampleRootComponent extends Rete.Component {//�ӷ�
//     constructor(){
// 		super("SampleRoot");//����

// 		this.task = {
// 			outputs: {}
// 		};

//     }

//     builder(node) {//�����ڵ�
//         var input = new Rete.Input('samples',"Sample (Array)", SampleSocket, true);//����
//         var out = new Rete.Output('serialize', "Serialize", SerializeSocket);//���

// 		return node.addInput(input).addOutput(out);//����

//     }

//     worker(node, inputs, outputs) {//�ڵ㣬���룬���
		
// 		var list = [];
// 		for(var i =0; i<inputs['samples'].length; ++i){
// 			list[i] = JSON.stringify(inputs["samples"][i]);
// 		}
// 		var serialize = {"list":list};
// 		var ret = { "component": "MrPP.SampleLib.SampleStorable",
//             "path": [
//                 "SampleRoot"
//             ],
//             "serialize":JSON.stringify(serialize)}
// 		outputs['serialize'] =  ret;
// 	}
// }

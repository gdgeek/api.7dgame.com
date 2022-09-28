// // 

// class MenuRootComponent extends Rete.Component {//�ӷ�
//     constructor(){
//         super("MenuRoot");//����
//     }

//     builder(node) {//�����ڵ�
//         var menus = new Rete.Input('menus',"Menu (Array)", menuSocket, true);//����
//         var out = new Rete.Output('serialize', "Serialize", serializeSocket);//���

// 		return node.addInput(menus).addOutput(out);//����

//     }

//     worker(node, inputs, outputs) {//�ڵ㣬���룬���
		
// 		var list = [];

// 		for(var i =0; i<inputs['menus'].length; ++i){
// 			list[i] = JSON.stringify(inputs["menus"][i]);
// 		}
// 		var serialize = {"list":list};
// 		var ret = {
// 			"component": "MrPP.SampleLib.MenuStorable",
// 			"path": [
// 				"MenuRoot"
// 			],
// 			"serialize": JSON.stringify(serialize)
// 		};
// 		outputs['serialize'] =  ret;
// 	}
// }

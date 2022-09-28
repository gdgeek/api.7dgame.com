


// class _Local_ extends Rete.Component {//�������


//     constructor(){
//         super("Local");//super(����)
// 		this.values = this.values();
// 		this.values['scale']  = [1,1,1];
// 		this.values['position']  = [0,0,0];
// 		this.values['angle']  = [0,0,0];
//     }
// 	values() {//data?
// 		return {
// 			values: [],
// 		}
// 	};/*
// 	getData(ikey){
// 		return this.values[ikey];
// 	}

// 	putData(ikey, value){

// 		this.values[ikey] = value;
// 	}
// 	*/
//     builder(node) {//�����ڵ�
//         var out1 = new Rete.Output('transform', "Transform", transformSocket);//����ڵ�
//         return node
// 			.addControl(new Vector3Control(this.editor, 'position', false))
// 			.addControl(new Vector3Control(this.editor, 'scale', false))
// 			.addControl(new Vector3Control(this.editor, 'angle', false))
// 			.addOutput(out1);//����node�ľ��壨control�� output��
//     }

// 	isset(r){
// 		if(typeof(r) === "undefined"){
// 			return false;
// 		}
// 		return true;
// 	}
// 	isstring(s,def){
// 		return this.isset(s)?s:def
// 	}
// 	isvector3(v3, multi, def){
// 		if(!this.isset(v3)){
// 			v3 = def;
// 		}

// 		return {"x":Math.round(v3[0]* multi) ,
// 				"y":Math.round(v3[1]* multi),
// 				"z":Math.round(v3[2]* multi)};
// 	}

//     worker(node, inputs, outputs) {//���ˣ��ڵ㣬���룬�����

// 		var data = {};
// 		var multi = 1000;
// 		data.position = this.isvector3(node.data.position,multi,  this.values['position']);
// 		data.scale = this.isvector3(node.data.scale, multi, this.values['scale']);
// 		data.angle = this.isvector3(node.data.angle, multi,  this.values['angle']);
	
// 		data.multi = multi;
//         outputs['transform'] = data;//= node.data;
//     }
// }


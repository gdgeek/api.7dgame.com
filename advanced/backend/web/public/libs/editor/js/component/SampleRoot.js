/*class SampleRoot extends Rete.Component {//加法
    constructor(){
        super("SampleRoot");//名字
		this.task = {
			outputs: {}
		};
		this.title = "样品根";
    }

    builder(node) {//构建节点
		node.title = this.title;
        var input = new Rete.Input('samples',"样品+", SampleSocket, true);//输入
        var out = new Rete.Output('serialize', "序列化", SerializeSocket);//输出
		return node.addInput(input).addOutput(out);//输入
    }

    worker(node, inputs, outputs) {//节点，输入，输出
		var list = [];
		for(var i =0; i<inputs['samples'].length; ++i){
			list[i] = JSON.stringify(inputs["samples"][i]);
		}
		var serialize = {"list":list};
		var ret = { "component": "MrPP.SampleLib.SampleStorable",
            "path": [
                "SampleRoot"
            ],
            "serialize":JSON.stringify(serialize)}
		outputs['serialize'] =  ret;
	}
}
*/
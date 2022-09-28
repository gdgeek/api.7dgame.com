// class PictureRoot extends Rete.Component {
//     constructor(){
//         super("PictureRoot");
//         this.task = {
//             outputs: {}
//         };
//         this.title = "图片根节点";
//     }
//     builder(node) {
//         node.title = this.title;
//         var input = new Rete.Input("pictures", "图片+", PictureSocket, true);
//         var out = new Rete.Output("serialize", "序列化", SerializeSocket);
//         return node
//             .addInput(input)
//             .addOutput(out);
//     }
//     worker(node, inputs, outputs) {
//         var list = [];
//         for(var i =0; i<inputs['pictures'].length; ++i){
//             list[i] = JSON.stringify(inputs["pictures"][i]);
//         }
//         var serialize = {"list":list};
//         var ret = { "component": "MrPP.SampleLib.VideoStorable",
//             // "path": [
//                 "VideoRoot"
//             ],
//             "serialize":JSON.stringify(serialize)}
//         outputs['serialize'] =  ret;
//     }
// }

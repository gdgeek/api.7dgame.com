// class VideoRoot extends Rete.Component {
//     constructor(){
//         super("VideoRoot");
//         this.task = {
//             outputs: {}
//         };
//     }
//     builder(node) {
//         var input = new Rete.Input('videos',"Video+", VideoSocket, true);
//         var out = new Rete.Output('serialize', "Serialize", SerializeSocket);
//         return node
//             .addInput(input)
//             .addOutput(out);
//     }
//     worker(node, inputs, outputs) {
//         var list = [];
//         for(var i =0; i<inputs['videos'].length; ++i){
//             list[i] = JSON.stringify(inputs["videos"][i]);
//         }
//         var serialize = {"list":list};
//         var ret = { "component": "MrPP.SampleLib.VideoStorable",
//             "path": [
//                 "VideoRoot"
//             ],
//             "serialize":JSON.stringify(serialize)}
//         outputs['serialize'] =  ret;
//     }
// }

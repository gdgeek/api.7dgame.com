
// class CollectionComponent extends Rete.Component {

//     constructor(){
//         super("Collection");
//     }

//     builder(node) {
//         var out = new Rete.Output('collection', "Collection", collectionSocket);

//         var ops = new Array();
//         ops.push({ value: "Column Then Row", text: "Column Then Row"});
//         ops.push({ value: "Row Then Column", text: "Row Then Column"});

//         return node
//             .addControl(new NumControl(this.editor, 'Rows'))
//             .addControl(new NumControl(this.editor, 'CellWidth'))
//             .addControl(new NumControl(this.editor, 'CellHeight'))
//             .addControl(new SelectControl(this.editor, 'LayoutType', true, ops))
// 			.addOutput(out);
//     }

//     worker(node, input, outputs) {

//         var Rows = node.data.Rows;
//         var CellWidth = node.data.CellWidth
//         var CellHeight = node.data.CellHeight;
//         var LayoutType = node.data.LayoutType;
	
//         outputs['collection'] = { "rows": Rows, "cellWidth": CellWidth, "cellHeight" : CellHeight, "layoutType" : LayoutType};
//     }
// }


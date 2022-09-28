
var Vector3ControlImpl = {//创建一个string ctrl
	props: ['emitter', 'ikey', "title", 'value', 'getData', 'putData'],//属性
	template: '<div><b>{{title}}</b>:<div class="input-group input-group-sm"  style="width:180px;">\
			<input type="number" :value="value[0]" @input="change(0, $event)" class="form-control" style="width:60px;display:inline;"/>\
			<input type="number" :value="value[1]" @input="change(1, $event)" class="form-control" style="width:60px;display:inline;"/>\
			<input type="number" :value="value[2]" @input="change(2, $event)" class="form-control" style="width:60px;display:inline;"/>\
			</div></div>',
	

	data() {//data?
		return {
		};
	},


	methods: {//方法
		
		change(idx, e) {

            if (idx === 0) this.value = [e.target.value, this.value[1], this.value[2]];
            if (idx === 1) this.value = [this.value[0], e.target.value, this.value[2]];
            if (idx === 2) this.value = [this.value[0], this.value[1], e.target.value];


			this.update();
		},
		update() {//更新
			if (this.ikey) {
				this.putData(this.ikey, this.value); 
			}
			this.emitter.trigger('process');
		}
	},


	mounted() {//挂接
		this.value = this.getData(this.ikey) || this.value;
		this.update();
	}
};



class Vector3Control extends Rete.Control {//数字控制器

	constructor(emitter, key, title, value) {//构造函数（发射器，key，只读）
		super(key);//key


		this.component = Vector3ControlImpl;//这个里面的组件用VueNumControl
		this.props = { emitter, ikey: key,title, value };//属性，发射器，ikey,key，readonly

	}


}

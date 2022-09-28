
var KeyStringControlImpl = {//创建一个string ctrl
	props: ['emitter', 'ikey', 'title','value', 'getData', 'putData'],//属性




	template: '<div><b>{{title}}</b>:<div class="input-group input-group-sm"  style="width:130px">\
			<input style="width:50px;display:inline;" type="text" :value="value.key" @input="change(0, $event)" class="form-control"/>\
			<input style="width:80px;display:inline;" type="text" :value="value.value" @input="change(1, $event)" class="form-control"/>\
			</div></div>',
	
	data() {//data?
		return {
			value: { key:'', value:''}
		};
	},


	methods: {//方法
		
		change(idx, e) {

			if (idx === 0) this.value.key = e.target.value;// = [, this.value[1]];
			if (idx === 1) this.value.value = e.target.value;//[this.value[0], e.target.value];


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



class KeyStringControl extends Rete.Control {//数字控制器

	constructor(emitter, key, title, value) {//构造函数（发射器，key，只读）
		super(key);//key


		this.component = KeyStringControlImpl;//这个里面的组件用VueNumControl
		this.props = { emitter, ikey: key, title, value };//属性，发射器，ikey,key，readonly

	}


}

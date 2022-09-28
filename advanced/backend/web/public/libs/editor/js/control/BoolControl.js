var BoolControlImpl = {
	props: ['emitter', 'value', 'ikey', 'title', 'getData', 'putData'],
	template: '<div><b>{{title}}:</b><div class="input-group input-group-sm"  style="width:130px">\
				<span class="input-group-addon"><input style="width:20px" type="checkbox" v-model = "value" @input="change($event)" /></span>\
			</div></div>',
	data() {
		return {
			value: false
		};
	},
	methods: {
		change(e) {
			this.value = e.target.checked;
			this.update();
		},
		update() {
			if (this.ikey)
				this.putData(this.ikey, this.value);
			this.emitter.trigger('process');
		},
	},
	mounted() {//创建时调用

		var data = this.getData(this.ikey);
		if (typeof (data) !== "undefined") {
			this.value = data;
		}
		this.update();
	}
}

/**
 * 布尔值控制器
 **/
class BoolControl extends Rete.Control {
	constructor(emitter, key, title, value) {//构造函数（发射器，key，只读）
		super(key);
		this.component = BoolControlImpl;
		this.props = { emitter, ikey: key, title, value };
	}
}
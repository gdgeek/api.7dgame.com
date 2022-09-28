var ButtonControlImpl = {
	props: ['emitter', 'ikey', 'value', 'getData', 'putData', 'func'],//���� 
	template: '<button type = "button" class="btn btn-xs btn-primary"  v-html="value" @click="execute()" ></button>',//ģ��
	data() {
		return {
			value: ""
		};
	},
	methods: {
		change(e) {
			this.value = e.target.value;
			this.update();
		},
		execute() {
			this.func();
		},
		update() {
			if (this.ikey)
				this.putData(this.ikey, this.value);
			this.emitter.trigger('process');
		}
	},
	mounted() {

		this.value = this.getData(this.ikey) || this.value;
	}
};

class ButtonControl extends Rete.Control {

	constructor(emitter, key, value, func) {
		super(key);//key
		this.component = ButtonControlImpl;
		this.props = { emitter, ikey: key, value, func };

	}
}

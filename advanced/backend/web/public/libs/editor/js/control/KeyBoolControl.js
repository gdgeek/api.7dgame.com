
var KeyBoolControlImpl = {
	props: ['emitter', 'ikey', 'value', 'getData', 'putData'],
	template: '<div><b>{{ikey}}</b>:<div class="input-group input-group-sm"  style="width:130px">\
			<span class="input-group-addon input-group-sm" ><input style="width:80px" type="text" :value="value[0]" @input="change(0, $event)" class="form-control"/></span>\
			<span class="input-group-addon "><input   style="width:20px"  type="checkbox" v-model = "value[1]" @input="change(1, $event)" /></span>\
			</div></div>',

	data() {//data?
		return {
			value: ['key', false]
		};
	},
	methods: {
		change(idx, e) {
			if (idx === 0) this.value = [e.target.value, this.value[1]];
			if (idx === 1) this.value = [this.value[0], e.target.value];
			this.update();
		},
		update() {
			if (this.ikey) {
				this.putData(this.ikey, this.value);
			}
			this.emitter.trigger('process');
		}
	},
	mounted() {
		this.value = this.getData(this.ikey) || this.value;
		this.update();
	}
};

class KeyBoolControl extends Rete.Control {

	constructor(emitter, key, value) {
		super(key);
		this.component = KeyBoolControlImpl;
		this.props = { emitter, ikey: key, value };
	}
}

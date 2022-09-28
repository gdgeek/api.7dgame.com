
var KeyStringControlImpl = {//����һ��string ctrl
	props: ['emitter', 'ikey', 'title','value', 'getData', 'putData'],//����




	template: '<div><b>{{title}}</b>:<div class="input-group input-group-sm"  style="width:130px">\
			<input style="width:50px;display:inline;" type="text" :value="value.key" @input="change(0, $event)" class="form-control"/>\
			<input style="width:80px;display:inline;" type="text" :value="value.value" @input="change(1, $event)" class="form-control"/>\
			</div></div>',
	
	data() {//data?
		return {
			value: { key:'', value:''}
		};
	},


	methods: {//����
		
		change(idx, e) {

			if (idx === 0) this.value.key = e.target.value;// = [, this.value[1]];
			if (idx === 1) this.value.value = e.target.value;//[this.value[0], e.target.value];


			this.update();
		},
		update() {//����
			if (this.ikey) {
				this.putData(this.ikey, this.value); 
			}
			this.emitter.trigger('process');
		}
	},


	mounted() {//�ҽ�
		this.value = this.getData(this.ikey) || this.value;
		this.update();
	}
};



class KeyStringControl extends Rete.Control {//���ֿ�����

	constructor(emitter, key, title, value) {//���캯������������key��ֻ����
		super(key);//key


		this.component = KeyStringControlImpl;//�������������VueNumControl
		this.props = { emitter, ikey: key, title, value };//���ԣ���������ikey,key��readonly

	}


}

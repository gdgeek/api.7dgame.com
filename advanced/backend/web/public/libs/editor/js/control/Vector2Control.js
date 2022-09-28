
var Vector2ControlImpl = {//����һ��string ctrl
	props: ['emitter', 'ikey', "title", 'value', 'getData', 'putData'],//����
	template: '<div><b>{{title}}</b>:<div class="input-group input-group-sm"  style="width:180px;">\
			<input type="number" :value="value[0]" @input="change(0, $event)" class="form-control" style="width:90px;display:inline;"/>\
			<input type="number" :value="value[1]" @input="change(1, $event)" class="form-control" style="width:90px;display:inline;"/>\
			</div></div>',
	

	data() {//data?
		return {
		};
	},


	methods: {//����
		
		change(idx, e) {

            if (idx === 0) this.value = [e.target.value, this.value[1]];
            if (idx === 1) this.value = [this.value[0], e.target.value];
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



class Vector2Control extends Rete.Control {//���ֿ�����

	constructor(emitter, key, title, value) {//���캯������������key��ֻ����
		super(key);//key


		this.component = Vector2ControlImpl;//�������������VueNumControl
		this.props = { emitter, ikey: key,title, value };//���ԣ���������ikey,key��readonly

	}


}

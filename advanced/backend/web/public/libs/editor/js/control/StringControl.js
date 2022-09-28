

var StringControlImpl = {//����һ��string ctrl
	props: ['emitter', 'ikey', "title", 'value', 'getData', 'putData'],//����
	template: '<b>{{title}}:\
		<input class="form-control"  type="text" :value="value" @input="change($event)" />\
		</b>',//ģ��



	data() {//data?
		return {
			value: ""
		};
	},


	methods: {//����
		change(e) {//�ı�
			this.value = e.target.value;
			this.update();
		},
		update() {//����
			if (this.ikey)
				this.putData(this.ikey, this.value);
			this.emitter.trigger('process');
		}
	},


	mounted() {//�ҽ�
		this.value = this.getData(this.ikey) || this.value;
		this.update();
	}
};


class StringControl extends Rete.Control {//���ֿ�����

  constructor(emitter, key, title, value) {//���캯������������key��ֻ����
    super(key);//key
	//alert(def);
	

    this.component = StringControlImpl;//�������������VueNumControl
	this.props = { emitter, ikey: key, title, value};//���ԣ���������ikey,key��readonly
	
  }

 
}

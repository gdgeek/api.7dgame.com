



var ColorPickerControlImpl = {//����һ��string ctrl
    props: ['emitter', 'ikey', 'value', 'title', 'getData', 'putData'],//����
    template: '<b>{{title}}:\
		<input class="form-control"  type="color" :value="value" @input="change($event)" />\
		</b>',//ģ��



    data() {
        return {
            value: 0
        };
    },


    methods: {
        change(e) {
            this.value = e.target.value;
            this.update();
        },
        color(){
            return {
                color: '#ff0000'
              }
        },
        update() {
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


class ColorPickerControl extends Rete.Control {//���ֿ�����

    constructor(emitter, key, title,  value) {//���캯������������key��ֻ����
        super(key);//key
        //alert(def);


        this.component = ColorPickerControlImpl;//�������������VueNumControl
        this.props = { emitter, ikey: key, title, value };//���ԣ���������ikey,key��readonly

    }


}


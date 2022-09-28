



var NumberControlImpl = {
    props: ['emitter', 'ikey', 'value', 'title', 'getData', 'putData'],//����
    template: '<b>{{title}}:\
	    <input class="form-control"  type="number" :value="value" @input="change($event)" />\
		</b>',//ģ��



    data() {//data?
        return {
            value: 0
        };
    },


    methods: {
        change(e) {
            this.value = e.target.value;
            this.update();
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


class NumberControl extends Rete.Control {

    constructor(emitter, key, title,  value) {
        super(key);//key
        //alert(def);


        this.component = NumberControlImpl;
        this.props = { emitter, ikey: key, title, value };

    }


}


var EditorEmptyControlImpl = {
    props: ['emitter', 'ikey', 'value', 'getData', 'putData', 'func', 'message'],
    template: '<a type = "button" class="btn btn-xs btn-warning" @click="execute()" > <i class="fa fa-star"></i> <i v-html="message" ></i></a>',//模板

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

var EditorControlImpl = {
    props: ["emitter", "ikey", "value", "getData", "putData", "message", "func"],
    template: '<div class="btn-group" :title="value"><a type = "button" class="btn btn-xs btn-primary " @click="execute()"><i class="fa fa-arrow-circle-right"></i><i v-html="message" ></i></a></div>',//模板

    data() {
        return {
            value: "",
            obj: JSON.parse(this.value)
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
        this.update();
    }
};

class EditorControl extends Rete.Control {

    constructor(emitter, key, value, message, func) {
        super(key);
        this.props = {emitter, ikey: key, value, message, func};
        if (value) {
            this.component = EditorControlImpl;
        } else {
            this.component = EditorEmptyControlImpl;
        }
    }
}

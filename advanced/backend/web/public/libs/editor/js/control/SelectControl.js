var SelectControlImpl = {
    props: ["value", "emitter", "ikey", "title", "getData", "putData", "options"],
    template: '<div class="input-group input-group-sm" ><b>{{title}}:</b> \
	                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="ture" v-html="text">\
                    </button>\
                <ul class="dropdown-menu" style = "height:200px;overflow:scroll">\
                <input type="text"  v-model="searchValue" v-on:focus="focus()">\
                <li v-for="option in optionsFiltered">\
                <a v-bind:target="option.value" v-model="value" @click="change($event)" v-html="option.text">{{ option.text }}</a>\
                </li>\
                </ul>\
                </div>',
    data() {
        return {
            text: this.options[0].text,
            value: this.options[0].value,
            searchValue: "",
            optionsFiltered: this.options
        };
    },
    methods: {
        focus: function () {
            this.fuzzy_search(this.searchValue.trim());
        },
        fuzzy_search(value) {
            var that = this;
            if (!value) {
                that.optionsFiltered = this.options;
                return;
            }
            that.optionsFiltered = that.options.filter(function (currentValue) {
                return currentValue.text.indexOf(value) != -1;
            });
        },
        change(e) {
            this.value = e.target.target;
            this.text = e.target.innerHTML;
            this.update();
        },
        update() {
            if (this.ikey) {
                this.putData(this.ikey, this.value);
            }
            this.emitter.trigger('process');
        }
    },
    watch: {
        searchValue: function () {
            this.focus();
        },
    },
    mounted() {
        this.value = this.getData(this.ikey) || this.value;
        this.text = this.getData(this.ikey) || this.value;
        for (var i = 0; i < this.options.length; ++i) {
            if (this.options[i].value == this.value) {
                this.text = this.options[i].text;
                break;
            }
        }
        this.update();
    }
};

class SelectControl extends Rete.Control {
    constructor(emitter, key, title, options, value) {
        super(key);
        this.component = SelectControlImpl;
        this.props = {
            emitter, ikey: key, title, options, value
        };
    }

    setValue(val) {
        this.vueContext.value = val;
    }
}


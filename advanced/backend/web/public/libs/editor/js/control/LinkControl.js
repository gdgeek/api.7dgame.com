

var LinkControlImpl = {
	props: ['emitter', 'ikey', 'id', "title", 'value', 'getData', 'putData'],
	template: '<b>\
        <a type = "button" class="btn btn-xs btn-primary" @click="execute()"><i class="fa fa-star"></i><i>{{title}}</i></a>\
		</b>',


	data() {
		return {
			value: ""
		};
	},


	methods: {
        execute(){

            const project = this.getQueryString('project');
            $(window).attr('location', (this.value + '?project='+project+'&id=' + this.id));
        },
		change(e) {
			this.value = e.target.value;
			this.update();
		},
		update() {
			if (this.ikey)
				this.putData(this.ikey, this.value);
			this.emitter.trigger('process');
		},
        getQueryString(name) { 
            let reg = `(^|&)${name}=([^&]*)(&|$)`
            let r = window.location.search.substr(1).match(reg); 
            if (r != null) return unescape(r[2]); return null; 
        }
          
	},


	mounted() {
		this.value = this.getData(this.ikey) || this.value;
		this.update();
	}
};


class LinkControl extends Rete.Control {

  constructor(emitter, key, id, title, value) {
    super(key);
    this.component = LinkControlImpl;
	this.props = { emitter, ikey: key, id, title, value};
	
  }

 
}

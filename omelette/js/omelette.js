/**
 * @type {Object} Wrapper for javascript needed by all Omelette apps
 */
var Omelette = {};

/**
 * Stolen shamelessly from Ben Nolan's Behaviour, but using Prototype's $$ to do the selector-magic
 */
Omelette.Behaviour = {
	/**
	 * Contains the rules that will be applied when go is called
	 * @type {Array}
	 */
	rulesets: [],
	/**
	 * Adds a ruleset to the rules array
	 * @param {Object} rules An object-literal (hash) of css-selector=>function(element) pairs
	 */
	add: function(rules) {
		Omelette.Behaviour.rulesets.push(rules);
	},
	/**
	 * Applies all currently registered roles against the supplied context
	 * @param {Object} [d] A context for the getElementsBySelector call, defaults to document
	 */
	go: function(d) {
		d = d || document;
		Omelette.Behaviour.rulesets.each(function(ruleset) {
			$H(ruleset).each(function(pair) {
				Element.getElementsBySelector(d,pair.key).each(function(node) {
					pair.value($(node));
				});
			});
		});
	}	
};
document.observe("dom:loaded",function(e) {
	Omelette.Behaviour.go();	
});





/**
 * for handling a 'foldable' area, that remembers user-choices (that bit handled elsewhere- see the MagicController, and the Smarty 'foldable' block)
 */
Omelette.FoldingArea = Class.create();
Omelette.FoldingArea.prototype = {
	switching: false,
	handle: null,
	content: null,
	open: true,
	initialize: function(container) {
		this.container=$(container);
		this.handle =this.container.down('h3');
		this.content = this.container.down('div');
		if (this.handle) {
			this.handle.observe('click',this.onclickListener.bindAsEventListener(this));
		}
		this.fold = this.onclickListener.bindAsEventListener(this);
		this.unfold = this.onclickListener.bindAsEventListener(this);
		if (this.handle && this.handle.hasClassName('closed')) {
			this.open=false;
		}
	},
	onclickListener: function(e) {
		if(this.switching) {return;}
		e.stop();
		this.switching=true;
		Effect.toggle(this.content,'Blind',{afterFinish: function() { this.switching=false}.bind(this)});
		var value;
		var key = this.container.id;
		if(this.handle.hasClassName('closed')) {
			this.handle.removeClassName('closed');
			value='open';
		}
		else {
			this.handle.addClassName('closed');
			value='closed';
		}
		var options = {
			parameters: {key:key,value:value}
		};
		new Ajax.Request('/magic/save_foldable_preference/',options);
	}
};
/*folding areas that only grab their content when they're opened
   - Behaviour is applied to the loaded content
*/
Omelette.LazyFoldingArea = Class.create();
Omelette.LazyFoldingArea.prototype={
	open: false,
	open_link: '',
	switching: false,
	clean:true,
	initialize: function(container) {
		this.container=$(container);
		this.content = new Element('div');
		this.content.hide();
		this.container.appendChild(this.content);
		this.container.close = this._close.bind(this);
		this.container.open = this.onclickListener.bindAsEventListener(this);
		this.handle =this.container.down('h3');
		this.open = !this.handle.hasClassName('closed');
		if(!this.open) {
			this.clean=false;
		}
		this.add_link = this.container.$$('.add_related').reduce();
		if(this.add_link) {
			this.add_link.observe('click',this.addClickListener.bindAsEventListener(this));
		}
		this.handle.observe('click',this.onclickListener.bindAsEventListener(this));
		this.url = this.handle.down('a').href;
	},
	onclickListener: function(e) {
		if(e) {Event.stop(e);}
		var target = this.handle.down('a');
		
		if(this.switching) {return;}
		this._start();
		if(this.open&&this.open_link==target.href) {
			this._close();
		}
		else {
			this.open_link=target.href;
			var options = {
				method:'get',
				requestHeaders: {
					'Accept':'text/html'
				}
			};
			options.onComplete = this.onComplete.bind(this);
			new Ajax.Request(this.url,options);
		}
	},
	onComplete: function(xhr) {
		this.content.innerHTML=xhr.responseText;
		if(!this.content.visible()) {
			this.content.blindDown({
				duration:0.6,
				afterFinish: function() { 
					this.switching=false;
					Omelette.Behaviour.go(this.container);
				}.bind(this)
			});
		}
		else {
			Omelette.Behaviour.go(this.container);
		}
		var first = this.content.down('input[type!=hidden], select');
		if(first) { first.activate();}
		this.handle.removeClassName('closed');
		this.open=true;
		this._end();
		Omelette.Indicator.stopLoading();
	},
	addClickListener: function(e) {
		var target = Event.findElement(e,'a');
		Event.stop(e);
		if(this.switching) {return;}
		this._start();
		if(this.open&&this.open_link==target.href) {
			this._close();
		}
		else {
			this.open_link=target.href;
			var options = {
				method:'get',
				requestHeaders: {
					'Accept':'text/html'
				}
			};
			options.onComplete = this.onComplete.bind(this);
			new Ajax.Request(Event.findElement(e,'a').href,options);
		}
	},
	_start: function() {
		this.switching=true;
		Omelette.Indicator.startLoading();
	},
	_end: function() {
		this.switching=false;
		if(!this.clean) {
			var key = this.container.id;
			var value = this.handle.hasClassName('closed')?'closed':'open';
			var options = {
				parameters:{key:key,value:value}
			};
			new Ajax.Request('/magic/save_foldable_preference/',options);
		}
		this.clean=false;
	},
	_close: function() {
		this.handle.addClassName('closed');
		this.content.blindUp({duration:0.4,afterFinish: function() { this.switching=false; Omelette.Indicator.stopLoading();}.bind(this)});
		this.open=false;
		this._end();
	}
};

//for attaching (and detaching) a spinning graphic to elements
/*
usage:
new Omelette.Spinner(element);
element.spin(); - to start spinning
element.stopSpin(); - to stop spinning
*/

Omelette.Spinner = Class.create();
Omelette.Spinner.src='/graphics/omelette/spinner.gif';

Omelette.Spinner.prototype = {
	//makes an image and attaches it (hidden) to the element
	initialize: function(element, insertwhere) {
		insertwhere = insertwhere || 'before';
		this.spinning = false;
		this.element=$(element);
		var spinner= new Element('img',{src:Omelette.Spinner.src});
		spinner=$(spinner);
		//absolute means it won't make things move
		spinner.style.position='absolute';
		var position = Position.positionedOffset(element);

		spinner.style.top = (position[1])+'px';
		spinner.style.left = (position[0] - 20)+'px';
		spinner.hide();
		spinner.addClassName('spinner');
		var insertion = {};
		insertion[insertwhere] = spinner;
		element.insert(insertion);
		this.spinner=spinner;
		//make it so that calling .spin() on the element will pass through
		this.element.spin=this.spin.bind(this);
		//likewise for stopSpin
		this.element.stopSpin=this.stopSpin.bind(this);
	},
	//start spinning
	spin: function() {
		if(this.spinning) {
			return;
		}
		this.spinning=true;
		Effect.Appear(this.spinner);
	},
	//stop spinning
	stopSpin: function() {
		if(!this.spinning) {
			return;
		}
		this.spinning=false;
		Effect.Fade(this.spinner);
	}
};

/*puts a gmail-like 'Loading...' indicator at the top right of 'element'*/
/*
Usage:
new Omelette.Loading(some_el);
some_div.startLoading();
some_div.stopLoading();
*/
Omelette.Indicator = Class.create();

Omelette.Indicator = {
	loading: false,
	initialized: false,
	queue: [],
	indicator: null,
	init: function() {
		Omelette.Indicator.queue = [];
		Omelette.Indicator.indicator = new Element('div',{className:'loading',style:'display:none;'}).update('Loading...');
		$('container').appendChild(Omelette.Indicator.indicator);
		Omelette.Indicator.initialized = true;
	},
	startLoading: function() {
		if(!Omelette.Indicator.initialized) {
			Omelette.Indicator.init();
		}
		if(Omelette.Indicator.queue.length==0) {
			Omelette.Indicator.t = setTimeout('Omelette.Indicator.timeoutHappen()', 500);
		}
		Omelette.Indicator.queue[Omelette.Indicator.queue.length]='set';
	},
	timeoutHappen: function() {
		Omelette.Indicator.to = setTimeout('Omelette.Indicator.timeoutHappenTooLong()', 10000);
		Omelette.Indicator.indicator.show();
		Omelette.Indicator.loading = true;
	},
	timeoutUnhappen: function() {
		clearTimeout(Omelette.Indicator.t);
		clearTimeout(Omelette.Indicator.to);
		Omelette.Indicator.indicator.hide();
		Omelette.Indicator.loading = false;
	},
	timeoutHappenTooLong: function() {
		Omelette.Indicator.indicator.hide();
	},
	stopLoading: function() {
		if(!Omelette.Indicator.initialized) {
			Omelette.Indicator.init();
		}
		if(Omelette.Indicator.queue.length>0) {
			Omelette.Indicator.queue.length-=1;
		}
		if(Omelette.Indicator.queue.length==0) {
			this.timeoutUnhappen();
		}
	},
	show: function() {
		Omelette.Indicator.startLoading();
	}
};

/* A Base/Abstract class for inplace-form widgets
	- calling setup() will create a form, save-button and cancel-link. The form's submit- and the link's click- events are hooked on to.
	- there are open() and close() functions that it is sensible to call when beginning and ending the 'editing' part of the widget. 
	Both of these allow for before- and after- callbacks.
	- the submitListener attaches the xhr's onComplete to the class's onComplete, if it exists.
*/
Omelette.InplaceForm = {
	widgets: [],
	register: function(widget) {
		Omelette.InplaceForm.widgets.push(widget);
	},
	unregister: function(widget) {
		var widgets = [];
		Omelette.InplaceForm.widgets.each(function(w) {
			if(w!=widget) {
				widgets.push(w);
			}
		});
		Omelette.InplaceForm.widgets = widgets;
	},
	notify: function(widget) {
		Omelette.InplaceForm.widgets.each(function(w) {
			if(w!=widget) {
				w.remoteClose();
			}
		});
	}
};
Omelette.InplaceForm.Base = Class.create({
	createForm: function(formclass) {
		this.form = new Element('form',{action:this.url,method:'post',className:formclass});
		this.form.observe('submit',this.submitListener.bindAsEventListener(this));
		
		this.cancel = new Element('a',{href:'#'}).update('Cancel');
		this.cancel.observe('click',this.cancelClickListener.bindAsEventListener(this));
		this.cancel.observe('fakeclick',this.cancelClickListener.bindAsEventListener(this));
		
		this.submit = new Element('input',{type:'submit',value:'Save',className:'savebutton'});
		
		this.form.insert(this.submit);
		this.form.insert(this.cancel);
	},
	openListener: function(e) {
		e.stop();
		this.open();
	},
	open: function() {
		this.editing = true;
		this.createForm();
		Omelette.InplaceForm.notify(this);
		this.form.down('input').activate();
	},
	cancelClickListener: function(e) {
		e.stop();
		this.restoreState();
	},
	submitListener: function(e) {
		e.stop();
		if(!this.form.validate()) {
			return false;
		}
		Omelette.Indicator.startLoading();
		var options = {
			requestHeaders: {
				'Accept':'application/json'
			}
		};
		options.onComplete = this.onComplete.bind(this);
		this.form.request(options);
	},
	remoteClose: function() {
		if(this.editing) {
			this.cancel.fire('fakeclick');
		}
	}
});

Omelette.QuickAddForm = Class.create({
	initialize: function(input, options) {
		this.options = options || {};
		this.input = $(input);
		this.hidden = $(this.input.id+'_id');
		this.open = false;
		this.createTriggers();
		this.createForm();
	},
	createTriggers: function() {
		this.trigger = new Element('a',{href:'#',className:'quick_add_trigger'}).update('Add new');
		this.trigger.observe('click',this.triggerClickListener.bindAsEventListener(this));
		this.input.insert({after: this.trigger});
		
		this.clear_trigger = new Element('a',{href:'#',className:'quick_add_trigger'}).update('Clear').hide();
		this.clear_trigger.observe('click',this.reset.bindAsEventListener(this));
		this.input.insert({after: this.clear_trigger});
	},
	createForm: function(elements) {
		elements = elements || [];
		this.container = new Element('div',{className:'quick_insert'});
		this.form = new Element('form',{className:'saveform',method:'post',action:this.options.url});
		
		var fs2 = new Element('fieldset',{className:'cancel_or_save'});
		var submit = new Element('input',{type:'submit',value:'Save'});
		var or = new Element('span',{className:'or'}).update(' or ');
		var cancel = new Element('a',{href:'#', className:'cancel_link'}).update('Cancel');
		cancel.observe('click',this.toggleForm.bindAsEventListener(this));
		
		fs2.insert(cancel).insert(or).insert(submit);
		elements.each(this.form.insert.bind(this.form));
		this.form.insert(fs2);
		this.form.setTabIndexes();
		
		var inner = new Element('div', {className:'shadow-inner'}); 
		this.container.insert(new Element('div', {className:'modal-shadow'}).insert(inner));
		inner.insert(this.form);
		this.container.insert(new Element('div',{style:'clear: left;'})).hide();
		document.body.appendChild(this.container);
		
		this.form.observe('submit', this.submitHandler.bindAsEventListener(this));
	},
	triggerClickListener: function(e) {
		e.stop();
		this.toggleForm();
	},
	toggleForm: function() {
		if(!this.open) {
			this.trigger.hide();
			this.container.show();
			this.form.focusFirstElement.bind(this.form).defer();
			this.input?this.input.up('form').disable():false;
			this.open = true;
		}
		else {
			this.trigger.show();
			this.container.hide();
			this.form.reset();
			this.input?this.input.up('form').enable():false;
			this.open = false;
		}
	},
	submitHandler: function(e) {
		e.stop();
		var options = {
			requestHeaders: {
				'Accept':'application/json'
			}			
		};
		options.onComplete = this.onComplete.bind(this);
		this.form.request(options);
		Omelette.Indicator.startLoading();
	},
	onComplete: function(xhr, message) {
		var response = xhr.responseJSON;
		Omelette.Flash.clear();
		if(response.status=='success') {
			Omelette.Flash.addMessage(message || response.message);
			this.input.setValue(this.getDisplayValue());
			this.input.removeClassName('subtle');
			this.hidden.setValue(response.id);
			this.toggleForm();
			this.input.disable();
			this.trigger.hide();
			this.clear_trigger.show();
		}
		else if(response.status=='failure') {
			Omelette.Flash.addErrors(response.errors);
		}
		else {
			Omelette.Flash.addErrors(['There was a problem performing the save, please try again']);
		}
		Omelette.Indicator.stopLoading();
	},
	reset: function(e) {
		e.stop();
		this.input.setValue('Begin Typing');
		this.hidden.setValue('');
		this.clear_trigger.hide();
		this.trigger.show();
		this.input.addClassName('subtle');
	}
});

Omelette.CompanyAdder = Class.create(Omelette.QuickAddForm, {
	initialize: function($super, input, options) {
		options = Object.extend({
			url: '/organisations/save'
		},options || {});
		$super(input, options);
	},	
	createForm: function($super, fs) {
		if (!fs) {
			var fs = new Element('fieldset');
			this.name_field = new Element('input',{type:'text',name:'Organisation[name]'});
			var name_label = new Element('label',{htmlFor:this.name_field.identify()}).update('Organisation Name *');
			
			this.acc_num = new Element('input',{type:'text',name:'Organisation[accountnumber]'});
			var acc_label = new Element('label',{htmlFor:this.acc_num.identify()}).update('Account Number');
			
			this.person_field = new Element('input',{type:'hidden',name:'Organisation[person_id]'});
			
			fs.insert(new Element('h3').update('New Organisation'));
			fs.insert(name_label).insert(this.name_field).insert(acc_label).insert(this.acc_num);
			fs.insert(this.person_field);
		}
		$super([fs]);		
	},	
	submitHandler: function($super, e) {
		var prefix = this.input.id.replace(/_organisation/, '');
		if ($(prefix + '_person_id')) {
			this.person_field.setValue($F(prefix + '_person_id'));
		}
		$super(e);
	},
	onComplete: function($super,xhr) {
		$super(xhr, 'Organisation saved successfully');
	},
	getDisplayValue: function() {
		return $F(this.name_field);
	}
});

Omelette.PersonAdder = Class.create(Omelette.QuickAddForm, {
	initialize: function($super, input, options) {
		options = Object.extend({
			url: '/people/save'
		},options || {});
		$super(input, options);
	},
	createForm: function($super, fs) {
		if (!fs) {
			var fs = new Element('fieldset');
			this.first_name = new Element('input',{type:'text',name:'Person[firstname]'});
			var first_name_label = new Element('label',{htmlFor:this.first_name.identify()}).update('First Name *');
			
			this.surname = new Element('input',{type:'text',name:'Person[surname]'});
			var surname_label = new Element('label',{htmlFor:this.surname.identify()}).update('Surname *');
	
			this.email_field = new Element('input',{type:'text',name:'Person[email][contact]'});
			var email_label = new Element('label',{htmlFor:this.email_field.identify()}).update('Email Address');		
	
			this.company_field = new Element('input',{type:'hidden',name:'Person[organisation_id]'});
			
			fs.insert(new Element('h3').update('New Person'));
			fs.insert(first_name_label).insert(this.first_name).insert(surname_label).insert(this.surname).insert(email_label).insert(this.email_field);
			fs.insert(this.company_field);
		}
		$super([fs]);
	},
	submitHandler: function($super, e) {
		var prefix = this.input.id.replace(/_person/, '');
		if ($(prefix + '_organisation_id')) {
			this.company_field.setValue($F(prefix + '_organisation_id'));
		}
		$super(e);
	},
	onComplete: function($super,xhr) {
		$super(xhr,"Person Saved Successfully");
	},
	getDisplayValue: function() {
		return $F(this.first_name) + ' ' + $F(this.surname);
	}
});


/**
 * Widget to wrap a textarea in if you want it to auto-grow when a scrollbar would normally appear
 * the area needs to have overflow:hidden. It uses a div that's the same width as the textarea, with the
 * same font-size and style, and watches how that grows and shrinks vertically
 */
Omelette.ExpandingArea = Class.create({
	initialize: function(area) {
		this.area = $(area);
		this.setupCopy();
		this.value = $F(this.area);
		this.observer = new Form.Element.Observer(this.area, 0.1, this.handleChange.bind(this));
		this.handleChange();
	},
	setupCopy: function() {
		this.copy = new Element('div').setStyle({
			fontSize:this.area.getStyle('fontSize'),
			fontFamily:this.area.getStyle('fontFamily'),
			width: (parseInt(this.area.getStyle('width')))+'px',
			position: 'absolute',
			left: '-10000px',
			top: '-10000px'
		});
		this.area.insert({after:this.copy});
		this.min_height = this.area.clientHeight;
	},
	handleChange: function() {
		this.copy.update($F(this.area).escapeHTML().replace(/[\n]/g,'<br />&nbsp;'));
		var height;
		if(this.copy.clientHeight < this.min_height) {
			height = this.min_height;
		}
		else {
			height = 20+this.copy.clientHeight;
		}
		this.area.morph({'height': height+'px'},{duration:0.3});
	}
});

Omelette.Flash = {
	addErrors: function(errors, form) {
		var json_errors = $H(errors);
		if(!$('errors')) {
			var error_flash = new Element('ul',{id:'errors',style:'display:none;'});
			$('flash').appendChild(error_flash);
		}
		json_errors.each(function(pair) {
			var msg = pair.value;
			if (form && form[pair.key]) {
				var field = form[pair.key];
				var inline_msg = new Element('div',{className:'inline_error'}).update(msg);
				field.insert({after:inline_msg});
			} else {
				$('errors').appendChild(new Element('li').update(msg));
			}
		});
		if ($$('#errors li').length > 0) {
			$('errors').show();
		}
		Omelette.Behaviour.go($('flash'));
		$('flash').scrollTo();
	},
	addMessage: function(msg) {
		if(!$('messages')) {
			var msg_flash = new Element('ul',{id:'messages',style:'display:none;'});
			$('flash').appendChild(msg_flash);
		}
		$('messages').appendChild(new Element('li').update(msg));
		$('messages').show();
		Omelette.Behaviour.go($('flash'));
	},
	clearErrors: function() {
		if($('errors')) {
			$('errors').remove();
		}
	},
	clearMessages: function() {
		if($('messages')) {
			$('messages').remove();
		}
	},
	clear: function() {
		Omelette.Flash.clearErrors();
		Omelette.Flash.clearMessages();
	}
};


/**
Handles the inplace-adding of person and company contact methods. 
Creates the elements that ContactEditor needs, and then simulates a click on the element to trigger it.
Attaches to the Editor's onCancel() to get rid of the row, rather than returning.
Handles the special-case of there being no contact methods, td should have classname 'empty'
*/
Omelette.ContactAdder = Class.create();
Omelette.ContactAdder.prototype = {
	initialize: function(link,url,type) {
		this.first=false;
		this.link=$(link);
		this.link.observe('click',this.clickListener.bindAsEventListener(this));
		this.url=url;
		this.type=type;
		this.adding = false;
	},
	clickListener: function(e) {
		Event.stop(e);
		if(this.adding) { return false; }
		this.adding = true;
		
		this.title = this.link.up('dt');
		if(this.title.next().hasClassName('empty')) {
			this.cell = this.title.next();
		}
		else {
			this.cell = new Element('dd');
			if(this.title.next('dt')) {
				this.title.next('dt').insert({before:this.cell});
			}
			else {
				this.title.up().insert(this.cell);
			}
		}
		
		this.cell.update(Omelette.ContactEditorTemplate.interpolate({contact:'',name:'Main'}));
		
		this.editor = new Omelette.ContactEditor(this.cell,this.url,null,this.type);
		this.editor.onRestore = this.restoreListener.bind(this);
		this.editor.onSuccess = this.onSuccess.bind(this);
		this.editor.open();
	},
	onSuccess: function() {
		this.adding = false;
		this.editor.onRestore=null;
		this.cell.removeClassName('empty');
	},
	restoreListener: function() {
		if(this.cell.previous('dd')) {
			this.cell.remove();
		}
		else {
			this.cell.update('-');
		}
		Omelette.InplaceForm.unregister(this.editor);
		this.adding = false;
	}
};
Omelette.ContactEditorTemplate = '<span class="contact">#{contact}</span> (<span class="contact_name">#{name}</span>)'
Omelette.ContactEditor = Class.create({
	initialize: function(cell,url,id,type) {
		this.cell = $(cell);
		this.url = url;
		this.id = id;
		this.type = type;
		
		this.editing = false;
		this.saveState();
		this.setupListeners();
		Omelette.InplaceForm.register(this);
	},
	setupListeners: function() {
		this.cell.observe('click',this.openListener.bindAsEventListener(this));
	},
	openListener: function(e) {
		if(this.editing) { return false; }
		e.stop();
		this.open();
	},
	open: function() {
		this.editing = true;
		Omelette.InplaceForm.notify(this);
		this.createForm();
		this.cell.addClassName('open');
		this.cell.update(this.form);
		this.form.down('input').activate();
	},
	saveState: function() {
		this.state = this.cell.innerHTML;
	},
	restoreState: function() {
		this.cell.removeClassName('open');
		this.cell.innerHTML = this.state;
		this.editing = false;
		if(this.onRestore) {
			this.onRestore();
		}
	},
	remoteClose: function() {
		if(this.editing) {
			this.cancel.fire('fakeclick');
		}
	},
	createForm: function() {
		this.form = new Element('form',{action:this.url,method:'post',className:'contact'});
		this.form.observe('submit',this.submitListener.bindAsEventListener(this));
		
		this.cancel = new Element('a',{href:'#',className:'cancel_link'}).update('Cancel');
		this.cancel.observe('click',this.cancelClickListener.bindAsEventListener(this));
		this.cancel.observe('fakeclick',this.cancelClickListener.bindAsEventListener(this));
		
		this.submit = new Element('input',{type:'submit',value:'Save',className:'savebutton'});
		
		var fs = new Element('fieldset',{className:'contact_options'});
		this.form.insert(fs);
		fs.insert(this.submit);
		fs.insert(this.cancel);
		
		if(this.id) {
			this.delete_link = new Element('a',{href:'#',className:'delete_link'}).update('Delete');
			this.delete_link.observe('click',this.deleteClickListener.bindAsEventListener(this));
			this.submit.insert({before:this.delete_link});
		}
		else {
			this.submit.setStyle({marginLeft:'84px'});
		}
		
		this.contact_field = new Element('input',{
			type:'text',
			name:'contact',
			className:'contact required',
			value:this.cell.down('.contact').innerHTML
		});
		this.form.insert({top:this.contact_field});
		
		this.contact_label = new Element('label',{
			htmlFor: this.contact_field.identify()
		}).update(this.type.capitalize()+':');
		this.contact_field.insert({before:this.contact_label});
		
		this.name_field = new Element('input',{
			type:'text',
			name:'name',
			className:'contact_name required',
			value:this.cell.down('.contact_name').innerHTML
		});
		this.contact_field.insert({after:this.name_field});
		this.name_label = new Element('label',{
			htmlFor: this.name_field.identify()
		}).update('Name:');
		this.name_field.insert({before:this.name_label});
		this.contact_field.insert({after:new Element('br')});
		
		this.name_field.insert({after:new Element('br')});
		
		if(this.id) {
			this.id_field = new Element('input',{type:'hidden',name:'id',value:this.id});
			this.form.insert(this.id_field);
		}
	},
	submitListener: function(e) {
		e.stop();
		if(!this.form.validate()) {
			return false;
		}
		Omelette.Indicator.startLoading();
		var options = {
			requestHeaders: {
				'Accept':'application/json'
			}
		};
		options.onComplete = this.onComplete.bind(this);
		this.form.request(options);
		this.form.disable();
	},
	cancelClickListener: function(e) {
		e.stop();
		this.restoreState();		
	},
	deleteClickListener: function(e) {
		e.stop();
		var sure = confirm('Are you sure you want to delete the contact method?');
		if(!sure) {
			return false;
		}
		var url = this.url.replace('save_contact','delete_contact');
		Omelette.Indicator.startLoading();
		this.form.disable();
		var options = {
			parameters: {id: this.id},
			requestHeaders: {
				'Accept':'application/json'
			},
			onComplete: this.onDelete.bind(this)
		};
		new Ajax.Request(url,options);
	},
	onDelete: function(xhr) {
		var response = xhr.responseJSON;
		if(response.status && response.status == 'success') {
			Omelette.Flash.addMessage(response.message);
			this.cell.fade({afterFinish:function() { 
			if(this.cell.siblings().length==1) {
					this.cell.replace(new Element('dd',{className:'empty'}).update('-'));
				}
				else {
					this.cell.remove();
				}
				
			}.bind(this)});
		}
		else if(response.errors){
			this.form.enable();
			Omelette.Flash.addErrors(response.errors);
		}
		else {
			this.form.enable();
			Omelette.Flash.addErrors(['There was a problem communicating with the server, please try again']);
		}
		Omelette.Indicator.stopLoading();
	},
	onComplete: function(xhr) {
		var response = xhr.responseJSON;
		if(response.status && response.status == 'success') {
			Omelette.Flash.addMessage(response.message);
			this.cell.removeClassName('open');
			this.cell.update(Omelette.ContactEditorTemplate.interpolate({
				contact:response.contact.contact.value,
				name:response.contact.name.value
			}));
			this.id = response.contact.id.value;
			
			this.saveState();
			this.editing=false;
			if(this.onSuccess) {
				this.onSuccess();
			}
		}
		else if(response.errors){
			this.form.enable();
			Omelette.Flash.addErrors(response.errors);
		}
		else {
			this.form.enable();
			Omelette.Flash.addErrors(['There was a problem communicating with the server, please try again']);
		}
		Omelette.Indicator.stopLoading();
	}
});



Omelette.methods = {
	/**
	 * convenience for element.cloneNode() - returns the _cloned_ element, so chaining will be different!
	 * @param {Object} element
	 * @param {Boolean} childNodes Whether or not it should include childnodes (be a 'deep' clone)
	 */
	clone: function(element,childNodes) {
		childNodes = childNodes || true;
		return $(element.cloneNode(childNodes));
	},
	/**
	 * allows the appending of multiple children, deals with strings sensibly
	 * @param {Object} element
	 * @param {Array} children An array of elements and/or strings to be appended to the element
	 */
	append: function(element,children) {
		element=$(element);
		 if(!children instanceof Array) {
			children=[children];
		 }
		children.each(function(el) { 
			if(typeof el == 'string') {
				el = document.createTextNode(el);
			}
			element.appendChild(el);
		});
		return element;
	},
	/**
	 * shortcut for getElementsBySelector but also returns just a single element if the collection is length=1
	 * @param {Object} element
	 * @param {String} selector A CSS-style selector
	 * @see #$$
	 */
	$$: function(element,selector) {
		element=$(element);
		return element.getElementsBySelector(selector) || $A();
	},
	/**
	 * Returns true iff the coordinates of the mouse pointer are within the boundaries of the element
	 * @param {Object} element
	 * @param {Event} event An event to compare the position with
	 * @see Position#eventWithin 
	 */	
	eventWithin: function(element,event) {
		element=$(element);
		return element.within(event.pointerX(), event.pointerY());
	},
	within: function(element, x, y) {
		element = $(element);
	    this.xcomp = x;
	    this.ycomp = y;
	    this.offset = element.cumulativeOffset();
	
	    return (y >= this.offset[1] &&
	            y <  this.offset[1] + element.offsetHeight &&
	            x >= this.offset[0] &&
	            x <  this.offset[0] + element.offsetWidth);
	},
	unabsolutize: function(element) {
	    element = $(element);
	    if (element.getStyle('position') != 'absolute') return;
	 	
	    element.style.position = '';
	    element.style.top    = '';
	    element.style.left   = '';
	    element.style.width  = '';
	    element.style.height = '';
	    return element;
	},
	toggleContent: function(element,values) {
		element=$(element);
		element.update(values.without(element.innerHTML).reduce());
		return element;
	},
	hasInvisibleAncestor: function(element) {
		element = $(element);
		return element.ancestors().any(function(el) {
			return !el.visible();
		});
	},
	lightenColour: function(element, amount) {
		element = $(element);
		/*var color = new Plotr.Color(element.getStyle('color'));
		element.setStyle({color:color.lighten(amount).toHexString()});*/
		return element;
	},
	quickupdate: function(element, content) {
		element = $(element);
	    if (content && content.toElement) content = content.toElement();
	    if (Object.isElement(content)) return element.update().insert(content);
	    content = Object.toHTML(content);
	    element.innerHTML = content;
		return element;
	}
};
/*
Element.Methods.siblings = Element.Methods.siblings.wrap(function(proceed, element, selector) {
	element = $(element);	
	return !selector ? proceed(element) : Selector.matchElements(proceed(element), selector || '*');
});
*/
Omelette.tableMethods = {
	/**
	 * adds the classname 'even' to every other tr in the table's tbody
	 * @param {Object} element
	 */
	stripe: function(element) {
		element = $(element);
		element.$$('tr:nth-child(even)').invoke('addClassName','even');
		return element;
	}
};
/**
 * convenience increase and decrease rowspan methods for td and th nodes
 */
Omelette.tableCellMethods = {
	/**
	 * Increase the rowspan of a cell (td or th)
	 * @param {Object} element
	 */
	increaseRowspan: function(element) {
		element=$(element);
		element.setAttribute('rowspan',parseInt(element.getAttribute('rowspan'))+1);
	},
	/**
	 * decrease the rowspan of the cell (td or th)
	 * @param {Object} element
	 */
	decreaseRowspan: function(element) {
		element=$(element);
		element.setAttribute('rowspan',parseInt(element.getAttribute('rowspan'))-1);
	}
};
Omelette.SelectMethods = {
	/**
	 * sets the option with value=value to 'selected'
	 * @param {Object} element The select element the value is to be applied to
	 * @param {String} value The value of the option to be 'selected'
	 */
	setSelected: function(element,value) {
		element=$(element);
		$A(element.options).find(function(opt) {
			return (opt.value==value);
		}).selected=true;
		return element;
	},
	/**
	 * Returns the shown value (the innerHTML) of the currently selected option
	 * @param {Object} element The element to get the value of
	 */
	getSelection: function(element) {
		return element.options[element.selectedIndex].innerHTML;
	}
};
Omelette.FormMethods = {
	/**
	 * Validates a form. At the moment, this is just 'required' fields.
	 */
	validate: function(element,e) {
		element = $(element);
		Omelette.Flash.clear();
		$$('.has_error').invoke('removeClassName','has_error');
		var requireds = element.getElementsBySelector('input.required');
		var errors = $H();
		requireds.reject(function(el) {
			return el.hasInvisibleAncestor();
		}).each(function(field) {
			if(!$F(field)) {
				if(field.previous('label')) {
					var tag = field.previous('label').innerHTML.replace('*','');
				}
				else {
					var tag = field.name.capitalize();
				}
				errors.set(field.name,tag+' is a required field');
				field.addClassName('has_error');
			}
		});
		if(errors.size()>0) {
			Omelette.Flash.addErrors(errors, element);
			return false;
		}
		return true;
	},
	foo: function(element) {
		alert("boo");
	},
	setTabIndexes: function(element) {
		element = $(element);
		element.getElements().each(function(element, i) {
			element.setAttribute('tabindex',i+1);
		});
	}
};
Element.addMethods(Omelette.methods);
Element.addMethods('SELECT',Omelette.SelectMethods);
Element.addMethods('TABLE',Omelette.tableMethods);
Element.addMethods(['TD','TH'],Omelette.tableCellMethods);
Element.addMethods(Omelette.FormMethods);

Object.extend(Array.prototype,{
	/**
	 * adds the handler to every item in the array (so pass an array of HTMLElements)
	 * @param {String} event The name of the event ('click','mouseover' etc.)
	 * @param {Function} handler A function to be attached as the handler for the given event
	 */
	allObserve:function(event,handler) {
		this.invoke('observe',event,handler);
	},
	/**
	 * removes the handler to every item in the array (so pass an array of HTMLElements)
	 * @param {String} event The name of the event ('click', 'mouseover' etc.)
	 * @param {Function} handler The function to be removed.
	 */
	allUnobserve: function(event,handler) {
		this.invoke('stopObserving',event,handler);
	},
	/* kind of like list() in PHP, builds a hash from 2 arrays:
		['a','b','c'].$H([1,2,3]) => {a:1,b:2,c:3}
	 -  skips nulls:
		['a',null,'c'].$H(1,2,3) => {a:1,c:3}
	*/
	$H: function(array) {
		var hash =$H();
		this.each(function(item,i) {
			if(item) {
				hash.set(item,array[i]);
			}
		});
		return hash;
	}
});
Object.extend(Event,{
	stopDefault: function(e) {
		 if (event.preventDefault) {
	      event.preventDefault();
	    } else {
	      event.returnValue = false;
	    }
	}
});
Object.extend(Position,{
	/**
	 * returns true iff the event's coordinates are within the element 
	 * @param {Event} event An event
	 * @param {Element} element A DOM Element
	 * @return {Boolean}
	 */
	eventWithin: function(event,element) {
		return Position.within(element,Event.pointerX(event),Event.pointerY(event));
	}
});
Hash.addMethods({
	/**
	 * swap round key and value in a Hash. Obviously only works when the value is valid for a key, i.e. string or number
	 * @return Hash
	 */
	flip: function() {
		var __hash = $H();
		this.each(function(pair) {
			__hash[pair.value]=pair.key;
		});
		return __hash;
	}
});

Object.extend(Number.prototype,{
	zeroPad: function() {
		var num = this;
		return (num<10)?'0'+num:num;
	}
});
Object.extend(Date,{
	tomorrow: function() {
		var d = new Date();
		d.setDate(d.getDate()+1);
		return d;
	},
	end_of_week: function() {
		var d = new Date();
		var w = 5;
		var x = d.getDay() || 7;
		d.setDate(d.getDate() - x + w);
		return d;
	}
});
Object.extend(Date.prototype,{
	toFormat: function(format) {
		//"d/m/Y H:i";
		var day = this.getDate().zeroPad();
		var month = (this.getMonth()+1).zeroPad();
		var longyear = this.getFullYear();
		var shortyear = (this.getYear()-100).zeroPad();
		
		var hour = this.getHours().zeroPad();
		var minute = this.getMinutes().zeroPad();
		
		var date_string = format.replace('d',day).replace('m',month).replace('Y',longyear).replace('y',shortyear)
							.replace('H',hour).replace('i',minute);
		return date_string;
	}
});




/**
 * a hash of countrycode->names
 * @type {Hash}
 */
Omelette.countryMap = {"AF":"Afghanistan","AL":"Albania","DZ":"Algeria","AS":"American Samoa","AD":"Andorra","AO":"Angola","AI":"Anguilla","AQ":"Antarctica","AG":"Antigua And Barbuda","AR":"Argentina","AM":"Armenia","AW":"Aruba","AU":"Australia","AT":"Austria","AZ":"Azerbaijan","BS":"Bahamas","BH":"Bahrain","BD":"Bangladesh","BB":"Barbados","BY":"Belarus","BE":"Belgium","BZ":"Belize","BJ":"Benin","BM":"Bermuda","BT":"Bhutan","BO":"Bolivia","BA":"Bosnia And Herzegovina","BW":"Botswana","BV":"Bouvet Island","BR":"Brazil","IO":"British Indian Ocean Territory","BN":"Brunei Darussalam","BG":"Bulgaria","BF":"Burkina Faso","BI":"Burundi","KH":"Cambodia","CM":"Cameroon","CA":"Canada","CV":"Cape Verde","KY":"Cayman Islands","CF":"Central African Republic","TD":"Chad","CL":"Chile","CN":"China","CX":"Christmas Island","CC":"Cocos (keeling) Islands","CO":"Colombia","KM":"Comoros","CG":"Congo","CD":"Congo, The Democratic Republic Of The","CK":"Cook Islands","CR":"Costa Rica","CI":"Cote D'ivoire","HR":"Croatia","CU":"Cuba","CY":"Cyprus","CZ":"Czech Republic","DK":"Denmark","DJ":"Djibouti","DM":"Dominica","DO":"Dominican Republic","TL":"East Timor","EC":"Ecuador","EG":"Egypt","SV":"El Salvador","GQ":"Equatorial Guinea","ER":"Eritrea","EE":"Estonia","ET":"Ethiopia","FK":"Falkland Islands (malvinas)","FO":"Faroe Islands","FJ":"Fiji","FI":"Finland","FR":"France","GF":"French Guiana","PF":"French Polynesia","TF":"French Southern Territories","GA":"Gabon","GM":"Gambia","GE":"Georgia","DE":"Germany","GH":"Ghana","GI":"Gibraltar","GR":"Greece","GL":"Greenland","GD":"Grenada","GP":"Guadeloupe","GU":"Guam","GT":"Guatemala","GN":"Guinea","GW":"Guinea-bissau","GY":"Guyana","HT":"Haiti","HM":"Heard Island And Mcdonald Islands","VA":"Holy See (vatican City State)","HN":"Honduras","HK":"Hong Kong","HU":"Hungary","IS":"Iceland","IN":"India","ID":"Indonesia","IR":"Iran, Islamic Republic Of","IQ":"Iraq","IE":"Ireland","IL":"Israel","IT":"Italy","JM":"Jamaica","JP":"Japan","JO":"Jordan","KZ":"Kazakhstan","KE":"Kenya","KI":"Kiribati","KP":"Korea, Democratic People's Republic Of","KR":"Korea, Republic Of","KW":"Kuwait","KG":"Kyrgyzstan","LA":"Lao People's Democratic Republic","LV":"Latvia","LB":"Lebanon","LS":"Lesotho","LR":"Liberia","LY":"Libyan Arab Jamahiriya","LI":"Liechtenstein","LT":"Lithuania","LU":"Luxembourg","MO":"Macao","MK":"Macedonia, The Former Yugoslav Republic Of","MG":"Madagascar","MW":"Malawi","MY":"Malaysia","MV":"Maldives","ML":"Mali","MT":"Malta","MH":"Marshall Islands","MQ":"Martinique","MR":"Mauritania","MU":"Mauritius","YT":"Mayotte","MX":"Mexico","FM":"Micronesia, Federated States Of","MD":"Moldova, Republic Of","MC":"Monaco","MN":"Mongolia","MS":"Montserrat","MA":"Morocco","MZ":"Mozambique","MM":"Myanmar","NA":"Namibia","NR":"Nauru","NP":"Nepal","NL":"Netherlands","AN":"Netherlands Antilles","NC":"New Caledonia","NZ":"New Zealand","NI":"Nicaragua","NE":"Niger","NG":"Nigeria","NU":"Niue","NF":"Norfolk Island","MP":"Northern Mariana Islands","NO":"Norway","OM":"Oman","PK":"Pakistan","PW":"Palau","PS":"Palestinian Territory, Occupied","PA":"Panama","PG":"Papua New Guinea","PY":"Paraguay","PE":"Peru","PH":"Philippines","PN":"Pitcairn","PL":"Poland","PT":"Portugal","PR":"Puerto Rico","QA":"Qatar","RE":"Reunion","RO":"Romania","RU":"Russian Federation","RW":"Rwanda","SH":"Saint Helena","KN":"Saint Kitts And Nevis","LC":"Saint Lucia","PM":"Saint Pierre And Miquelon","VC":"Saint Vincent And The Grenadines","WS":"Samoa","SM":"San Marino","ST":"Sao Tome And Principe","SA":"Saudi Arabia","SN":"Senegal","SC":"Seychelles","SL":"Sierra Leone","SG":"Singapore","SK":"Slovakia","SI":"Slovenia","SB":"Solomon Islands","SO":"Somalia","ZA":"South Africa","GS":"South Georgia And The South Sandwich Islands","ES":"Spain","LK":"Sri Lanka","SD":"Sudan","SR":"Suriname","SJ":"Svalbard And Jan Mayen","SZ":"Swaziland","SE":"Sweden","CH":"Switzerland","SY":"Syrian Arab Republic","TW":"Taiwan, Province Of China","TJ":"Tajikistan","TZ":"Tanzania, United Republic Of","TH":"Thailand","TG":"Togo","TK":"Tokelau","TO":"Tonga","TT":"Trinidad And Tobago","TN":"Tunisia","TR":"Turkey","TM":"Turkmenistan","TC":"Turks And Caicos Islands","TV":"Tuvalu","UG":"Uganda","UA":"Ukraine","AE":"United Arab Emirates","GB":"United Kingdom","US":"United States","UM":"United States Minor Outlying Islands","UY":"Uruguay","UZ":"Uzbekistan","VU":"Vanuatu","VE":"Venezuela","VN":"Viet Nam","VG":"Virgin Islands, British","VI":"Virgin Islands, U.s.","WF":"Wallis And Futuna","EH":"Western Sahara","YE":"Yemen","YU":"Yugoslavia","ZM":"Zambia","ZW":"Zimbabwe"};
Omelette.countryMap = $H(Omelette.countryMap);
Omelette.flippedMap = Omelette.countryMap.flip();
Omelette.countryOptions = Omelette.countryMap.map(function(pair) {
	var opt = new Option(pair.value,pair.key);
	return opt;
});

Element.Methods.toggle = Element.Methods.toggle.wrap(function(proceed, element, effect, options) {
	element = $(element);
	if(!effect) { return proceed(element); }
	new Effect.toggle(element, effect, options || {});
	return element;
});
Element.addMethods();


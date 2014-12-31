// Catches waylaid debugging messages
if (!window.console) {
	window.console = new function() {
		this.log = function(x) {};
		this.dir = function(x) {};
	};
}

var Om = $.extend(Om, {
	types: {'organisation': 'organisations','person':'people','opportunity':'opportunities','activity':'activities'},
	plural: function(type) {
		return (Om.types[type] === undefined ? false : Om.types[type]);
	}
});


// Loading indicator with whirly swirls
Om.Indicator = {
	initialized: false,
	queue: [],
	indicator: null,
	delay: 3000,
	
	init: function() {
		Om.Indicator.queue = [];
		Om.Indicator.indicator = $('<div />').text('Loading...').addClass('loading').hide().appendTo('#container');
		Om.Indicator.initialized = true;
	},
	
	startLoading: function() {
		if (!Om.Indicator.initialized) {
			Om.Indicator.init();
		}
		if (Om.Indicator.queue.length < 1) {
			// Set a minimum wait time to display the indicator
			Om.Indicator.t = setTimeout(function(){
				Om.Indicator.timeoutHappen();
			}, Om.Indicator.delay);
		}
		Om.Indicator.queue[Om.Indicator.queue.length] = 1;
	},
	
	timeoutHappen: function() {
		// Hide the indicator after a timeout
		Om.Indicator.to = setTimeout(function(){
			Om.Indicator.timeoutHappenTooLong();
		}, 10000);
		Om.Indicator.indicator.show();
	},
	
	timeoutUnhappen: function() {
		clearTimeout(Om.Indicator.t);
		clearTimeout(Om.Indicator.to);
		Om.Indicator.indicator.hide();
	},
	
	timeoutHappenTooLong: function() {
		Om.Indicator.indicator.hide();
	},
	
	stopLoading: function() {
		if (!Om.Indicator.initialized) {
			Om.Indicator.init();
		}
		if (Om.Indicator.queue.length > 0) {
			Om.Indicator.queue.length -= 1;
		}
		if (Om.Indicator.queue.length < 1) {
			Om.Indicator.timeoutUnhappen();
		}
	}
};

// Attach the loading indicator to all Ajax requests
$(document).ajaxStart(function() {
	Om.Indicator.startLoading();
});
$(document).ajaxStop(function() {
	Om.Indicator.stopLoading();
});
$(document).ajaxError(function(){
	console.log('Ajax error occurred');
	//Om.Flash.handleJSONResponse();
})


// Error and info messages
Om.Flash = {
	addErrors: function(errors) {
		errors = (errors instanceof Array || errors instanceof Object) ? errors : [errors];
		
		// Create error flash if it doesn't exist
		var $messages = $('#errors').length ? $('#errors') :
			$('<ul id="errors" />').css({display: "none"}).appendTo('#flash')
				.append($('<li class="dismiss" />').append($('<a />').text('Click to dismiss')));
		
		// Add errors
		$.each(errors, function(name, msg) {
			$('#errors').append($("<li />").text(msg));
		});
		
		// Display flash
		$('#no_trial').slideUp();
		if ($('#errors li').length > 0) {
			if ($("#main_nav li.current").length) {
				$("#main_nav li.current").animate({backgroundColor:"#fdd"}, "normal", function() {
					$('#errors').slideDown();
					scroll(0,0);
				});
			} else {
				// Sometimes we're not on a tab
				$('#errors').slideDown();
				scroll(0,0);
			}
		}
	},
	
	addMessages: function(messages) {
		messages = (messages instanceof Array || messages instanceof Object) ? messages : [messages];
	
		// Create message flash if it doesn't already exist
		var $messages = $('#messages').length ? $('#messages') :
			$('<ul id="messages" />').css({display: "none"}).appendTo('#flash');
		
		// Add messages
		$.each(messages, function(name, msg) {
			$messages.append($("<li />").text(msg));
		});
		
		// Display Flash
		$('#no_trial').slideUp();
		if ($("#main_nav li.current").length) {
			$("#main_nav li.current").animate({backgroundColor:"#ffa"}, "normal", function(){
				// Add message and show flash
				$messages.slideDown();
				setTimeout(function(){
					Om.Flash.clearMessages();
				}, 5000);
			});
		} else {
			// Add message and show flash
			$messages.slideDown();
			setTimeout(function(){
				Om.Flash.clearMessages();
			}, 5000);
		}
	},
	
	clearErrors: function() {
		$('#errors li.dismiss').remove();
		$('#errors').slideUp("normal", function() {
			$("#main_nav li.current").animate({backgroundColor:"#fff"}, "normal");
			$(this).remove();
		});
	},
	
	clearMessages: function() {
		$('#messages li.dismiss').remove();
		$('#messages').slideUp("normal", function() {
			$("#main_nav li.current").animate({backgroundColor:"#fff"}, "normal");
			$(this).remove();
		});
	},
	
	clear: function() {
		Om.Flash.clearErrors();
		Om.Flash.clearMessages();
	},
	
	handleJSONResponse: function(json, success, failure, complete) {
		Om.Flash.clear();
		if (!$.isPlainObject(json)) {
			Om.Flash.addErrors('A general error occurred. Please try again later');
			if ($.isFunction(failure)) { failure(); }
		} else if (json.status && json.status === 'success') {
			if (json.message) {
				Om.Flash.addMessages(json.message);
			}
			if ($.isFunction(success)) { success(); }
		} else if (json.errors) {
			Om.Flash.addErrors(json.errors);
			if ($.isFunction(failure)) { failure(); }
		} else {
			Om.Flash.addErrors('A general error occurred. Please try again later');
			if ($.isFunction(failure)) { failure(); }
		}
		if ($.isFunction(complete)) { complete(); }
	}
};


// Modal forms
Om.ModalForm = {
	initialized: false,
	title: "Save Details",
	successMsg: "Saved successfully",
	submitByAjax: true,
	location: false,
	$modal: null,
	$form: null,
	$fieldset: null,
	$cancel: null,
	$save: null,
	options: {},
	preSubmit: null,
	postSuccess: null,
	postCancel: null,
	
	init: function(action, title, msg) {
		if (Om.ModalForm.initialized) {
			Om.ModalForm.hide();
		}
		Om.ModalForm.title = title;
		Om.ModalForm.successMsg = msg;
		Om.ModalForm.submitByAjax = true;
		Om.ModalForm.location = false;
		Om.ModalForm.$modal = $('#modal_form'); 
		Om.ModalForm.$form = Om.ModalForm.$modal.find('form');
		Om.ModalForm.$form.attr("action", action).empty();
		Om.ModalForm.$fieldset = $('<fieldset />');
		Om.ModalForm.$cancel = $('<a title="Cancel" />').text(' ').addClass('sprite sprite-dismiss');
		Om.ModalForm.$save = $('<fieldset class="save"><div class="row"><input class="submit" type="submit" value="Save" /></div></fieldset>');  
		
		Om.ModalForm.$form
			.append($('<div />').addClass('cancel').append(Om.ModalForm.$cancel))
			.append($('<h3 />').text(title))
			.append($('<div />').addClass('maxHeightWrapper').append(Om.ModalForm.$fieldset))
			.append(Om.ModalForm.$save);
		
		Om.ModalForm.$modal.find("div.shadow-inner").append(Om.ModalForm.$form);
		
		Om.ModalForm.preSubmit = function(){};
		Om.ModalForm.postSuccess = function(){};
		Om.ModalForm.postCancel = function(){};
		
		Om.ModalForm.options = {
			url: action,
			type: "POST",
			dataType: "json",
			success: function(json) {
				if (Om.ModalForm.successMsg !== '') {
					json.message = null;
				}
				Om.Flash.handleJSONResponse(json, function(){
					if (Om.ModalForm.location) {
						if (json.id) {
							window.location = Om.ModalForm.location + json.id;
						} else {
							window.location = Om.ModalForm.location;
						}
					} else {
						Om.Flash.addMessages(Om.ModalForm.successMsg);
						Om.ModalForm.postSuccess(json);
						Om.ModalForm.hide();
					}
				}, null, function(){
					Om.ModalForm.$save.find('input').removeAttr('disabled');
				});
			}
		};
		
		Om.ModalForm.setupListeners();
		Om.ModalForm.initialized = true;
	},
	setupListeners: function() {
		Om.ModalForm.$cancel.click(function(ev) {
			Om.ModalForm.hide();
			Om.ModalForm.initialized = false;
		});
		Om.ModalForm.$form.submit(function(ev) {
			Om.ModalForm.preSubmit();
			Om.ModalForm.$fieldset.find('input.subtle').val('');
			Om.ModalForm.$save.find('input').attr('disabled', 'disabled');
			if (Om.ModalForm.submitByAjax) {
				ev.preventDefault();
				Om.ModalForm.options.data = Om.ModalForm.$form.serialize();
				$.ajax(Om.ModalForm.options);
			}
		});
	},
	cancelListeners: function() {
		Om.ModalForm.$cancel.unbind('click');
		Om.ModalForm.$form.unbind('submit');
	},
	show: function() {
		Om.ModalForm.$modal.show();
		if (Om.ModalForm.$modal.find('input[type=text][value=]:first').length) {
			Om.ModalForm.$modal.find('input[type=text][value=]:first').focus();
		} else {
			Om.ModalForm.$modal.find('input.subtle[type=text]:first').focus();
		}
	},
	hide: function() {
		Om.ModalForm.$modal.hide();
		Om.ModalForm.$form.empty();
		Om.ModalForm.cancelListeners();
		Om.ModalForm.postCancel();
	},
	formRow: function($input, label, type) {
		type = type || 'text';
		$input = ($input instanceof jQuery) ? $input : $('<input />').attr({'name': $input, 'type': type}).addClass(type);
		var inputName = $input.is('input') ? $input.attr('name') : '';
		var input_id = $input.attr('id') ||
			'qa_' + Om.ModalForm.$form.find('h3').text().replace(/^.*\s+/,'').toLowerCase() + '_' + inputName.replace(/\]\[/,'_').replace(/^.*\[/,'').replace(/\].*$/,'');
		$input.attr('id', input_id);
		
		label = label || inputName.replace('_', ' ').replace(/^(.)|\s(.)/g, function ($1){ return $1.toUpperCase( ); });
		var $label = label instanceof jQuery ? label : $('<label />').text(label).attr('for', input_id); 
		
		var $row = $('<div />').addClass('row');
		$row.append($label).append($input);
		return $row;
	}
};


// Adding and removing tags
Om.Tagger = {
	$container: null,
	$add_trigger: null,
	$edit_trigger: null,
	initialized: false,
	
	init: function($container) {
		Om.Tagger.$container = $container;
		Om.Tagger.$add_trigger = $container.find("li.add a");
		Om.Tagger.$edit_trigger = $container.find("li.edit a");
		
		Om.Tagger.$add_trigger.click(function(){
			// Add some tags
			var controller = Om.Tagger.$container.attr('id').replace('_tags','');
			var action = '/' + controller + '/add_tag/';
			Om.ModalForm.init(action, 'Add Tags',  'Tag information saved successfully');
				
			var $fs = Om.ModalForm.$fieldset;
			var $tags = $('<input id="qa_tags" type="text" name="tags" />');
			$fs.append($('<div class="row" />')
				.append($('<label for="qa_tags" />').text("Separated by commas"))
				.append($tags)
			);
			$tags.autocomplete({url:'/tags/search/', lock:false, tagMode:true, minChars:1});
			Om.ModalForm.$form.unbind('submit');
			Om.ModalForm.$form.submit(function(ev){
				ev.preventDefault();
				Om.ModalForm.$fieldset.find('input.subtle').val('');
				Om.ModalForm.$save.find('input').attr("disabled", "disabled");
				var tags = Om.ModalForm.$form.find("input#qa_tags").val().split(/,\s*/);
				var url = Om.ModalForm.$form.attr('action');
				$.each(tags, function(){
					var tag = this.toString();
					if (tag.length > 0) {
						var options = {
							url: url,
							type: "GET",
							dataType: "json",
							data: {tag: tag, id: Tactile.id},
							success: function() {
								var $new_tag = $('<li />')
									.append(
										$('<a />').attr('title', "See all " + controller + " tagged '" + tag + "'")
											.attr('href', action.replace('add_tag', 'by_tag') + '?tag=' + encodeURIComponent(tag))
											.text(tag)
									)
									.append(" ")
									.addClass('tag').css({display: "none"});
								Om.Tagger.clickTag($new_tag.find('a'));
								Om.Tagger.$container.find("ul li.edit").after($new_tag);
								Om.Tagger.$edit_trigger.parent().removeClass('no_tags');
								$new_tag.fadeIn("normal", function(){ $(this).show(); });
							}
						};
						$.ajax(options);
					}
				});
				Om.ModalForm.hide();
			});

			Om.ModalForm.show();
		});
		Om.Tagger.$edit_trigger.click(function(){
			if (Om.Tagger.$edit_trigger.text() == 'Finished') {
				Om.Tagger.$container.removeClass("edit_mode");
				Om.Tagger.$edit_trigger.text("Remove Tags").addClass("action");
			} else {
				// Launch editor
				Om.Tagger.$container.addClass("edit_mode");
				Om.Tagger.$edit_trigger.text("Finished").removeClass("action");
			}
		});
		Om.Tagger.$container.find("li.tag a").each(function(){
			Om.Tagger.clickTag($(this));
		});
		
		Om.Tagger.initialized = true;
	},
	
	clickTag: function(tag) {
		var $a = $(tag);
		
		$a.click(function(ev){
			if ($a.parents("div.tag_list").is(".edit_mode")) {
				// We are deleting the tag
				ev.preventDefault();
				
				var url = $a.attr('href').replace('by_tag', 'remove_tag') + '&id=' + Tactile.id;
				var options = {
					url: url,
					dataType: 'json',
					success: function() {
						$a.parent().fadeOut(function(){
							$(this).remove();
							// Any tags left?
							if (Om.Tagger.$container.find("li.tag").length < 1) {
								Om.Tagger.$edit_trigger.parent().addClass('no_tags');
								Om.Tagger.$edit_trigger.click();
							}
						});
					}
				};
				$.ajax(options);
				return false;
			}
		});
	}
};


Om.ConfigTable = {
	$table: null,
	group: '',
	option: '',

	init: function($table) {
		Om.ConfigTable.$table = $table;
		Om.ConfigTable.group = $table.attr('id').replace(/_.*$/, '');
		Om.ConfigTable.option = $table.attr('id').replace(/^.*_/, '');
		var $add_link = $table.find('tfoot a.action');
		$add_link.click(function(){
			Om.ConfigTable.addClickListener();
		});
		$table.find('tbody tr a.action').live('click', function(ev){
			Om.ConfigTable.deleteClickListener(ev);
		});
	},
	addClickListener: function() {
		// Add a new row
		var new_id = 'x' + (Om.ConfigTable.$table.find('tbody tr.new').length + 1);
		var $head_tr = Om.ConfigTable.$table.find('thead tr');
		var $tr = $('<tr />').addClass('new').attr('id', new_id);
		
		var input_name = Om.ConfigTable.group + '[' + Om.ConfigTable.option + '][' + new_id + ']';
		$head_tr.find('th').each(function() {
			var $th = $(this);
			var $td = $('<td />');
			switch ($th.text()) {
				case 'Name':
					$td.append($('<input />').addClass('name').attr({type: 'text', name: input_name + '[name]'}));
					break;
				case 'Position':
					$td.append($('<input />').addClass('position short').attr({type: 'text', name: input_name + '[position]'}));
					break;
				case 'Open?':
					$td.addClass('toggle');
					$td.append($('<input />').addClass('open checkbox').attr({type: 'checkbox', name: input_name + '[open]'}));
					break;
				case 'Won?':
					$td.addClass('toggle');
					$td.append($('<input />').addClass('won checkbox').attr({type: 'checkbox', name: input_name + '[won]'}));
					break;
				case '':
					$td.addClass('t-right').css({width: '100%'});
					$td.append($('<a />').addClass('action').text('Delete'));
					break;
			}
			$tr.append($td);
		});
		
		Om.ConfigTable.$table.find('tbody').append($tr);
	},
	deleteClickListener: function(ev) {
		if (Om.ConfigTable.group == 'opportunities') {
			switch (Om.ConfigTable.option) {
				case 'status':
				case 'type':
					return true;
					break;
			}
		}
		ev.preventDefault();
		// Delete the row if it's not the last
		var $a = $(ev.target);
		
		var n = Om.ConfigTable.$table.find('tbody tr').length;
		if (n > 1) {
			// Ok to delete
			if ($a.attr('href')) {
				// Do with ajax
				var sure = confirm("Are you sure you want to do this?");
				if (sure) {
					var options = {
						type: 'post',
						url: $a.attr('href'),
						dataType: 'json',
						success: function(xhr){
							Om.ConfigTable.onCompleteDelete(xhr);
						}
					};
					$.ajax(options);
				}
			} else {
				// Note created yet; just do it
				Om.ConfigTable.removeRow($a.parents('tr'));
			}
		} else {
			// Not ok to delete
			alert(Om.ConfigTable.group + ' must have at least one ' + Om.ConfigTable.option + '!');
		}
	},
	onCompleteDelete: function(response) {
		if (response.status && response.status == 'success') {
			Om.Flash.addMessages(response.messages);
			var $row = $('#' + Om.ConfigTable.group + '_' + Om.ConfigTable.option + '_' + response.id);
			if ($row) {
				Om.ConfigTable.removeRow($row);
			}
		} else if (response.errors){
			Tactile.Flash.addErrors(response.errors);
		} else {
			Tactile.Flash.addErrors(['There was a problem communicating with the server, please try again']);
		}
	},
	removeRow: function($tr) {
		$tr.fadeOut(function(){
			$(this).remove();
		});
	}
};


Om.CustomvalueTable = {
	$table: null,
	group: '',
	option: '',

	init: function($table) {
		Om.CustomvalueTable.$table = $table;
		var $add_link = $table.find('tfoot a.action');
		$add_link.click(function(){
			Om.CustomvalueTable.addClickListener();
		});
		$table.find('tbody tr a.action').live('click', function(ev){
			Om.CustomvalueTable.deleteClickListener(ev);
		});
		$table.find('span.addRow img.plus').live('click', function(ev){
			Om.CustomvalueTable.addOption($(this).parents('tr'));
		});
		$table.find('span.addRow img.delete_option').live('click', function(ev){
			Om.CustomvalueTable.deleteOption(ev);
		});
	},
	addClickListener: function() {
		// Add a new row
		var x = Om.CustomvalueTable.$table.find('tbody tr.new').length + 1;
		var new_id = 'x' + x;
		while ($('#custom_field_'+new_id).length > 0) {
			x++;
			new_id = 'x' + x;
		}
		var $head_tr = Om.CustomvalueTable.$table.find('thead tr');
		var $tr = $('<tr />').addClass('new').attr('id', 'custom_field_'+new_id);
		
		var input_name = 'custom[' + new_id + ']';
		$head_tr.find('th').each(function() {
			var $th = $(this);
			var $td = $('<td />');
			switch ($th.text()) {
				case 'Field Name':
					$td.append($('<input />').addClass('name').attr({type: 'text', name: input_name + '[name]'}));
					break;
				case 'Label':
					$td.append($('<input />').addClass('label').attr({type: 'text', name: input_name + '[label]'}));
					break;
				case 'Type':
					var options = "<option value='t'>Text</option><option value='n'>Numeric</option><option value='c'>Yes/No</option><option value='s'>Option List</option>";
					$select = $('<select/>').attr({name: input_name + '[type]'}).html(options);
					$select.change(function(ev){
						Om.CustomvalueTable.selectListner(ev);
					});
					$td.append($select);
					break;
				case 'Enabled For':
					$orgspan = $('<span />').attr('title', 'Organisations').addClass('sprite sprite-organisation').append($('<input />').attr({type: 'checkbox', checked:'checked', value:'1', name: input_name + '[organisations]'}));
					$perspan = $('<span />').attr('title', 'People').addClass('sprite sprite-person').append($('<input />').attr({type: 'checkbox', checked:'checked',value:'1', name: input_name + '[people]'}));
					$opspan = $('<span />').attr('title', 'Opportunities').addClass('sprite sprite-opportunity').append($('<input />').attr({type: 'checkbox', checked:'checked',value:'1', name: input_name + '[opportunities]'}));
					$acspan = $('<span />').attr('title', 'Activities').addClass('sprite sprite-activity').append($('<input />').attr({type: 'checkbox',checked:'checked',value:'1', name: input_name + '[activities]'}));
					$td.append($orgspan).append($perspan).append($opspan).append($acspan);
					break;
				case '':
					$td.addClass('delete');
					$td.append($('<a />').addClass('action').text('Delete'));
					break;
			}
			$tr.append($td);
		});
		Om.CustomvalueTable.$table.find('tr.none_yet').remove();
		Om.CustomvalueTable.$table.find('tbody').append($tr);
	},
	deleteClickListener: function(ev) {
		ev.preventDefault();
		// Delete the row if it's not the last
		var $a = $(ev.target);
		
		// Ok to delete
		if ($a.attr('href')) {
			// Do with ajax
			var sure = confirm("Are you sure you want to do this? \n\nThis field will be removed from all associated records and cannot be undone.");
			if (sure) {
				var options = {
					type: 'post',
					url: $a.attr('href'),
					dataType: 'json',
					success: function(response) {
						if (response.status && response.status == 'success') {
							Om.Flash.addMessages(response.messages);
								Om.CustomvalueTable.removeRow($a.parents('tr'));
						} else if (response.errors){
							Tactile.Flash.addErrors(response.errors);
						} else {
							Tactile.Flash.addErrors(['There was a problem communicating with the server, please try again']);
						}
					}
				};
				$.ajax(options);
			}
		} else {
			// Note created yet; just do it
			Om.CustomvalueTable.removeRow($a.parents('tr'));
		}
	},
	onCompleteDelete: function(response) {
		if (response.status && response.status == 'success') {
			Om.Flash.addMessages(response.messages);
			var $row = $('#custom_field_' + response.id);
			if ($row) {
				Om.CustomvalueTable.removeRow($row);
			}
		} else if (response.errors){
			Tactile.Flash.addErrors(response.errors);
		} else {
			Tactile.Flash.addErrors(['There was a problem communicating with the server, please try again']);
		}
	},
	removeRow: function($tr) {
		$tr.fadeOut(function(){
			$(this).remove();
			// Was that the last one?
			var n = Om.CustomvalueTable.$table.find('tbody tr').length;
			if (n === 0) {
				var $none_yet = $('<tr class="none_yet"><td colspan="4">You haven\'t added any Custom Fields yet</td></tr>');
				Om.CustomvalueTable.$table.find('tbody').append($none_yet);
			}
		});
	}, 
	selectListner: function(ev) {
		var $select = $(ev.target);
		switch($select.val()){
			case 's':
				$row = $select.parents('tr');
				var $hidden = $('<input />').attr({type:'hidden', name:$select.attr('name'), value:'s'});
				$select.replaceWith($hidden);
				$hidden.after('Option List');
				Om.CustomvalueTable.addOption($row);
				break;
			default:
		}
	},
	addOption: function($row){
		var $hidden = $row.find('input[type=hidden]');
		var $span = $('<span />').addClass('addRow');
		
		var x = $hidden.parent().find('input[type=text]').length + 1;
		var new_option_id = 'x' + x;
		while ($('#custom_option_'+new_option_id).length > 0) {
			x++;
			new_option_id = 'x' + x;
		}
		var $opt = $('<input />').addClass('name').attr({id: 'custom_option_'+new_option_id, type: 'text', name: $hidden.attr('name')+'[option]['+new_option_id+']'});
		$span.append($opt);
		
		// Don't add delete control to only option 
		if ($row.find('span.addRow').length > 0) {
			var $delete = $('<img />').addClass('delete_option').attr({src: '/graphics/tactile/icons/cross.png'});
			$span.append(' ').append($delete);
		}
		$.each($row.find('span.addRow'), function(i, existing_span) {
			if (!$(existing_span).find('img.delete_option').length) {
				var $add_delete = $delete.clone();
				$(existing_span).append(' ').append($add_delete);
			}
		});
		
		$row.find('img.plus').remove();
		var $plus = $('<img />').attr({src: '/graphics/tactile/icons/add.png'}).addClass('plus');
		$span.append(' ').append($plus);
		$hidden.parent().append($span);
		$opt.focus();
	},
	deleteOption: function(ev){
		ev.preventDefault();
		var $target = $(ev.target);
		var option_id = $target.parent().find('input').attr('id').replace('custom_option_', '');
		var is_new = option_id.match(/^x/);
		var $row = $target.parents('tr');
		
		if (is_new) {
			$target.parent().remove();
		} else {
			var sure = confirm('Are you sure you want to delete this option?');
			if (sure) {
				var options = {
					type: 'post',
					url: '/customfields/delete_option/',
					data: {id: option_id},
					dataType: 'json',
					success: function(response) {
						if (response.status && response.status == 'success') {
							Om.Flash.addMessages(response.messages);
							$target.parent().remove();
						} else if (response.errors){
							Tactile.Flash.addErrors(response.errors);
						} else {
							Tactile.Flash.addErrors(['There was a problem communicating with the server, please try again']);
						}
					}
				};
				$.ajax(options);
			}
		}
		var n = $row.find('span.addRow').length;
		if (n == 1) {
			$row.find('img.delete_option').remove();
		}
		if (!$row.find('img.plus').length) {
			$row.find('span.addRow:last-child').append(' ').append('<img src="/graphics/tactile/icons/add.png" class="plus" />');
		}
	}
};

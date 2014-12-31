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
/*
 * jQuery Hotkeys Plugin
 * Copyright 2010, John Resig
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Based upon the plugin by Tzury Bar Yochay:
 * http://github.com/tzuryby/hotkeys
 *
 * Original idea by:
 * Binny V A, http://www.openjs.com/scripts/events/keyboard_shortcuts/
*/

(function($){
	
	jQuery.hotkeys = {
		version: "0.8",

		specialKeys: {
			8: "backspace", 9: "tab", 13: "return", 16: "shift", 17: "ctrl", 18: "alt", 19: "pause",
			20: "capslock", 27: "esc", 32: "space", 33: "pageup", 34: "pagedown", 35: "end", 36: "home",
			37: "left", 38: "up", 39: "right", 40: "down", 45: "insert", 46: "del", 
			96: "0", 97: "1", 98: "2", 99: "3", 100: "4", 101: "5", 102: "6", 103: "7",
			104: "8", 105: "9", 106: "*", 107: "+", 109: "-", 110: ".", 111 : "/", 
			112: "f1", 113: "f2", 114: "f3", 115: "f4", 116: "f5", 117: "f6", 118: "f7", 119: "f8", 
			120: "f9", 121: "f10", 122: "f11", 123: "f12", 144: "numlock", 145: "scroll", 191: "/", 224: "meta"
		},
	
		shiftNums: {
			"`": "~", "1": "!", "2": "@", "3": "#", "4": "$", "5": "%", "6": "^", "7": "&", 
			"8": "*", "9": "(", "0": ")", "-": "_", "=": "+", ";": ": ", "'": "\"", ",": "<", 
			".": ">",  "/": "?",  "\\": "|"
		}
	};

	function keyHandler( handleObj ) {
		// Only care when a possible input has been specified
		if ( typeof handleObj.data !== "string" ) {
			return;
		}
		
		var origHandler = handleObj.handler,
			keys = handleObj.data.toLowerCase().split(" ");
	
		handleObj.handler = function( event ) {
			// Don't fire in text-accepting inputs that we didn't directly bind to
			if ( this !== event.target && (/textarea|select/i.test( event.target.nodeName ) ||
				 event.target.type === "text" || event.target.type === "password") ) {
				return;
			}
			
			// Keypress represents characters, not special keys
			var special = event.type !== "keypress" && jQuery.hotkeys.specialKeys[ event.which ],
				character = String.fromCharCode( event.which ).toLowerCase(),
				key, modif = "", possible = {};

			// check combinations (alt|ctrl|shift+anything)
			if ( event.altKey && special !== "alt" ) {
				modif += "alt+";
			}

			if ( event.ctrlKey && special !== "ctrl" ) {
				modif += "ctrl+";
			}
			
			// TODO: Need to make sure this works consistently across platforms
			if ( event.metaKey && !event.ctrlKey && special !== "meta" ) {
				modif += "meta+";
			}

			if ( event.shiftKey && special !== "shift" ) {
				modif += "shift+";
			}

			if ( special ) {
				possible[ modif + special ] = true;

			} else {
				possible[ modif + character ] = true;
				possible[ modif + jQuery.hotkeys.shiftNums[ character ] ] = true;

				// "$" can be triggered as "Shift+4" or "Shift+$" or just "$"
				if ( modif === "shift+" ) {
					possible[ jQuery.hotkeys.shiftNums[ character ] ] = true;
				}
			}

			for ( var i = 0, l = keys.length; i < l; i++ ) {
				if ( possible[ keys[i] ] ) {
					return origHandler.apply( this, arguments );
				}
			}
		};
	}

	jQuery.each([ "keydown", "keyup", "keypress" ], function() {
		jQuery.event.special[ this ] = { add: keyHandler };
	});

})(jQuery);
// Activity functionality, e.g. [un]completing
(function($) {
	
	$.fn.markAsComplete = function(options) {
		var opts = $.extend({}, $.fn.markAsComplete.defaults, options);
		var inputs = this;
		
		return inputs.each(function() {
			var $cb = $(this);

			$cb.click(function(){
				$cb.attr('disabled', 'disabled');
				var url = $cb.parent().find('a.completable').attr('href').replace(/view/, $cb.is(':checked') ? 'complete' : 'uncomplete');
				$.ajax({
					url: url,
					dataType: 'json',
					success: function(json) {
						Om.Flash.handleJSONResponse(json, function(){
							var td = $cb.is(':checked') ? 'line-through' : 'none';
							$cb.attr('title', $cb.is(':checked') ? 'Untick to uncomplete' : 'Tick to complete');
							$cb.parent().find('a.completable').css('text-decoration', td);
						}, null, function(){
							$cb.removeAttr('disabled');
						});
					}
				});
			});
		});
	};
	$.fn.markAsComplete.defaults = {};
	
}) (jQuery);
// Automatically expanding text areas
(function($) {
	
	$.fn.expandingTextarea = function() {
		var areas = this;
		
		return areas.each(function() {
			var $ta = $(this);
			if (true === $ta.data('isExpanding')) {
				return false;
			}
			$ta.data('isExpanding', true);
			
			var lineHeight = parseInt($ta.css('line-height'), 10);
			lineHeight = isNaN(lineHeight) ? 20 : lineHeight;
			var maxHeight = parseInt($ta.css('max-height'), 10);
			maxHeight = isNaN(maxHeight) ? 700 : maxHeight;
				
			$dummy = $('<div />').appendTo('body').css({
				'font-size'    : $ta.css('font-size'),
				'font-family'  : $ta.css('font-family'),
				'width'        : $ta.css('width'), // A width must be specified!
				'paddingTop'   : $ta.css('paddingTop'),
				'paddingRight' : $ta.css('paddingRight'),
				'paddingBottom': $ta.css('paddingBottom'),
				'paddingLeft'  : $ta.css('paddingLeft'),
				'borderTop'    : $ta.css('borderTop'),
				'borderRight'  : $ta.css('borderRight'),
				'borderBottom' : $ta.css('borderBottom'),
				'borderLeft'   : $ta.css('borderLeft'),
				'line-height'  : $ta.css('line-height'),
				'max-height'   : maxHeight,
				'position'     : 'absolute',
				'top'          : -9999,
				'left'		   : -9999,
				'overflow'     : 'hidden'
			});
			
			var etaInterval = null;
			$ta.focus(function(){
				clearInterval(etaInterval);
				etaInterval = setInterval(function(){
					$ta.checkExpand();
				}, 800);
			}).blur(function(){
				clearInterval(etaInterval);
			});
			$ta.checkExpand = function() {
				if (!$ta.parent().length) {
					// Tidy up
					clearInterval(etaInterval);
					$dummy.remove();
					return false;
				}
				// Grab the contents of the textarea, and HTML-ify it
				var html = $ta.val()
					.replace(/&/, '&amp;')
					.replace(/</, '&lt;')
					.replace(/>/, '&gt;')
					.replace(/[\n]/g, '<br />&nbsp;');
				// Populate the dummy element with the text, and work out the new height
				$dummy.html(html);
				var newHeight = $dummy.height() + (2 * lineHeight);
				if ($ta.height() !== newHeight) {
					if ($dummy.height() >= maxHeight) {
						$ta.css({'overflow-y':'scroll'});
						$ta.height(maxHeight);
					} else {
						$ta.css({'overflow-y':'hidden'});
						$ta.height(newHeight);
					}
				}
			}
			$ta.checkExpand();
		});
	};
}) (jQuery);
// Swooshy sidebar boxes that remember their open-arity
(function($) {

	// Normal Foldables 
	$.fn.foldable = function() {
		// All the foldables
		var foldables = this;
		
		return foldables.each(function() {
			// An individual foldable
			var f = this;
			f.$container = $(this);
			if (f.$container.is('.unfoldable')) {
				return;
			}
			f.$header = f.$container.find("h3");
			f.$content = f.$container.find("div:first");
			
			f.$header.click(function(ev) {
				var key = f.$container.attr("id");
				var value = f.$content.is(":hidden") ? "open" : "closed"; 
				
				// Save magic prefs
				var options = {
					url: "/magic/save_foldable_preference/",
					data: ({key: key, value: value}),
					success: function() {
						if (f.$content.is(":hidden")) {
							f.$content.slideDown("slow");
							f.$header.removeClass("closed");
						} else {
							f.$content.slideUp("slow");
							f.$header.addClass("closed");
						}
					}
				};
				$.ajax(options);
			});
		});
	};
	
	
	// Ajax Foldables
	$.fn.ajaxFoldable = function() {
		// All the foldables
		var foldables = this;
		
		return foldables.each(function() {
			// An individual foldable
			var f = this;
			$(this).foldable = f;
			f.$container = $(this);
			f.$header = f.$container.find("h3");
			f.$content = f.$container.find("div:first").length ?
				f.$container.find("div:first") : $("<div />").css({display: "none"});
			
			// Do the Ajax
			f.fetch = function() {
				var href = f.$header.find("a:first").attr("href");
				$.ajax({
					url: href,
					type: "GET",
					success: function(msg) {
						// Show content and open
						f.$content.html(msg);
						f.$container.append(f.$content);
						f.setupListeners();
						f.$content.slideDown("slow");
						f.$header.removeClass("closed").addClass("open");
						
						// Add listeners
						f.$content.find('a.email').sendEmail();
					}
				});
			};
			
			// Slide open
			f.open = function() {
				this.fetch();
				this.updatePrefs('open');
			};
			
			// Slide close
			f.close = function() {
				f.$content.slideUp("slow");
				f.$header.removeClass("open").addClass("closed");
				this.updatePrefs('closed');
			};
			
			// Save magic prefs
			f.updatePrefs = function(value) {
				var key = f.$container.attr("id");
				var options = {
					url: "/magic/save_foldable_preference/",
					data: ({key: key, value: value})
				};
				$.ajax(options);
			};
			
			// Click listener
			f.$header.click(function(ev) {
				ev.preventDefault();
				if (f.$header.is(".closed")) {
					f.open();
				} else {
					f.close();
				}
			});
			
			// Open on page load
			if (f.$header.is(".open")) {
				f.fetch();
			}
			
			f.setupListeners = function() {
				f.$container.find('#activities input.checkbox').markAsComplete();
				f.$container.find('#showActivityTrackForm').show().click(function(){
					$(this).hide();
					$('#activityTrackForm').show();
					return false;
				});
				f.$container.find('form#activityTrackForm').hide().activityTrackAdder();
			}
		});
	};

}) (jQuery);
// Lightbox forms for saving/editing items
(function($) {

	// Custom Field row
	function $custom_field_row(kind, cfield, id, value) {
		var controller = Om.plural(kind);
		id = id || 'x';
		value = value || false;
		var is_new = id.toString().match(/^[0-9]+$/) ? false : true;
		
		var $id = $('<input />').attr({'type':'hidden', 'name':'custom_field['+id+'][field_id]'}).val(cfield.id);
		var $name = $('<label />').text(cfield.name).attr('for', 'qa_custom_field_'+id);
		var $value;
		switch (cfield.type) {
			case 'c':
				$value = $('<input />').attr({'id':'qa_custom_field_'+id, 'type':'checkbox', 'name':'custom_field['+id+'][enabled]'}).addClass('checkbox');
				if (value) {
					$value.attr('checked', 'checked');
				}
				break;
			case 's':
				$value = $('<select />').attr({'id':'qa_custom_field_'+id, 'name':'custom_field['+id+'][option]'}).append($('<option />').text('-- Select Option --'));
				$.each(cfield.options, function(i, option) {
					var $option = $('<option />').val(option.id).text(option.value);
					if (parseInt(value, 10) === parseInt(option.id, 10)) {
						$option.attr('selected', 'selected');
					}
					$value.append($option);
				});
				break;
			case 'n':
				$value = $('<input />').attr({'id':'qa_custom_field_'+id, 'name': 'custom_field['+id+'][value_numeric]', 'type':'text'});
				if (false !== value) {
					$value.val(value);
				}
				break;
			default:
				$value = $('<input />').attr({'id':'qa_custom_field_'+id, 'name': 'custom_field['+id+'][value]', 'type':'text'});
				if (false !== value) {
					$value.val(value);
				}
		}
		var $delete = $('<a />').attr({title: 'Delete this Field'}).addClass('delete action sprite sprite-remove');
		
		var $row = $('<tr />')
			.append($('<td />').addClass('name').append($id).append($name))
			.append($('<td />').addClass('value').append($value))
			.append($('<td />').addClass('delete').append($delete));
		if (is_new) {
			$row.addClass('new');
		}
		
		// Listeners
		$delete.click(function() {
			var $new_option = $('<option />').val(cfield.id).text(cfield.name);
			var sure;
			if (!is_new) {
				sure = confirm('Are you sure you want to delete this value for '+cfield.name+'? This process cannot be undone.');
				if (sure) {
					var options = {
						url: '/' + controller + '/delete_custom/',
						dataType: 'json',
						data: {'id': id},
						success: function(json) {
							if (json.status && json.status === 'success') {
								$('#'+kind+'_custom_field_row_'+id).fadeOut(function(){
									$(this).remove();
								});
								$row.fadeOut(function() {
									$row.parents('table').find('tfoot select').append($new_option);
									$row.parents('table').find('tfoot span').show();
									$row.remove();
									Tactile.existing_custom_fields = json.existing_custom_fields;
								});
								Om.Flash.addMessages(json.message);
								Om.ModalForm.hide();
							}
						}
					};
					$.ajax(options);
				}
			} else {
				sure = true;
				$row.fadeOut(function() {
					$row.parents('table').find('tfoot select').append($new_option);
					$row.parents('table').find('tfoot').show();
					$row.remove();
				});
			}
		});
		if (cfield.type === 'n') {
			$value.bind('keyup mouseup', function(ev){
				$(this).val($(this).val().replace(/[^0-9\.]/g,''));
			});
		}

		return $row;
	}
	

	// Contact method row
	function $contact_row(kind, main, type, label, contact, id) {
		var controller = Om.plural(kind);
		main = (main === undefined) ? true : main;
		type = type || 'T';
		label = label || 'Main';
		var eg = 'e.g. +44 (0)2476 010105';
		contact = contact || eg;
		id = id || 'x';
		var is_new = id.toString().match(/^[0-9]+$/) ? false : true;
		
		var $main = $('<input type="radio" name="contact_method_main['+type+']"'+(main?' checked="checked"':'')+' />')
			.addClass('checkbox radio').val(id);
		var $type = $('<select />').attr({name: 'contact_method['+id+'][type]'}).addClass("false_label qa_cm_type")
			.append($('<optgroup />').attr({label: 'Basic'})
				.append($('<option />').val('T').html('<img src="/graphics/tactile/icons/phone.png" /> Phone'))
				.append($('<option />').val('E').html('<img src="/graphics/tactile/icons/email.png" /> Email'))
				.append($('<option />').val('M').html('<img src="/graphics/tactile/icons/mobile.png" /> Mobile'))
				.append($('<option />').val('W').html('<img src="/graphics/tactile/icons/website.png" /> Website'))
				.append($('<option />').val('F').html('<img src="/graphics/tactile/icons/fax.png" /> Fax'))
			)
			.append($('<optgroup />').attr({label: 'Networking'})
				.append($('<option />').val('S').html('<img src="/graphics/tactile/icons/skype.png" /> Skype'))
				.append($('<option />').val('L').html('<img src="/graphics/tactile/icons/linkedin.png" /> LinkedIn'))
				.append($('<option />').val('I').html('<img src="/graphics/tactile/icons/twitter.png" /> Twitter'))
				.append($('<option />').val('K').html('<img src="/graphics/tactile/icons/facebook.png" /> Facebook'))
			);
		$type.val(type);
		var $delete = $('<a />').attr({title: 'Delete this Contact Method'}).addClass('delete action sprite sprite-remove');
			
		if ($.browser.safari) {
			$type.css({'margin-top': '2px'});
		}
		var $label = $('<input />').attr({type: 'text', name: 'contact_method['+id+'][name]'}).addClass('qa_cm_label').val(label);
		var $contact = $('<input />').attr({type: 'text', name: 'contact_method['+id+'][contact]'}).addClass('qa_cm_contact').val(contact);
		if ($contact.val() === eg) {
			$contact.addClass('subtle');
		}
	
		var $row = $('<tr />').addClass(type)
			.append($('<td />').addClass('main t-center').append($main))
			.append($('<td />').addClass('type').append($type))
			.append($('<td />').addClass('label').append($label))
			.append($('<td />').addClass('contact').append($contact))
			.append($('<td />').addClass('delete').append($delete));
		if (is_new) {
			$row.addClass('new');
		}
		
		// Listeners
		$type.change(function(){
			var previous_type = $row.attr('class').replace(/new/,'');
			previous_type = $.trim(previous_type);
			var new_type = $(this).val();
			
			var $new_main = $('<input type="radio" name="contact_method_main['+new_type+']" />')
				.addClass('checkbox radio').val(id);
			
			// Must have at least one of new type chcked 
			if ($row.parents('table').find('tr.'+new_type+' input[type=radio]:checked').length < 1) {
				$label.val('Main');
				$new_main.attr({checked: 'checked'});
			}
			$main.replaceWith($new_main);
			$main = $new_main;
			
			$row.attr({'class': new_type});
			if (is_new) {
				$row.addClass('new');
			}
			
			// Check previous type has at least one checked (if any)
			if ($row.parents('table').find('tr.'+previous_type+' input[type=radio]:checked').length < 1) {
				$row.parents('table').find('tr.'+previous_type+' input[type=radio]:first').attr('checked', 'checked');
			}
		});
		$delete.click(function() {
			if (!is_new) {
				var sure = confirm('Are you sure you want to delete this Contact Method?');
				if (sure) {
					var options = {
						url: '/' + controller + '/delete_contact/',
						dataType: 'json',
						data: {'id': id, 'type': type},
						success: function(json) {
							if (json.status && json.status === 'success') {
								$row.fadeOut(function() {
									$row.remove();
								});
								Om.Flash.addMessages(json.messages);
							}
						}
					};
					$.ajax(options);
				}
			} else {
				$row.fadeOut(function() {
					$row.remove();
				});
			}
			if ($main.is(':checked')) {
				// Assign another of same type as main
				$row.parents('table').find('tr.'+type+' input[type=radio]:first').attr('checked', 'checked');
			}
		});
		$contact.focus(function() {
			if ($contact.is('.subtle')) {
				$contact.removeClass('subtle').val('');
			}
		});
		$contact.blur(function() {
			if ($contact.val() === '') {
				$contact.addClass('subtle').val(eg);
			}
		});

		return $row;
	}
	
	
	// Organisation Form
	function $organisation_fs() {
		var $fs = Om.ModalForm.$fieldset;
		$fs.append(Om.ModalForm.formRow('Organisation[name]', 'Name *'))
			.append(Om.ModalForm.formRow('Organisation[accountnumber]', 'Account Number'));
			
		if (Tactile.person_id) {
			$fs.append($('<input type="hidden" name="Organisation[person_id]" />').val(Tactile.person_id));
		}
		return $fs;
	}
	
	
	// Related Contact Form
	function $related_contact_fs(type) {
		var $fs = Om.ModalForm.$fieldset;
		if (Tactile.opportunity_id) {
			$fs.append($('<input type="hidden" name="Related_Contact[opportunity_id]" />').val(Tactile.opportunity_id));
		}
		$fs.append(Om.ModalForm.formRow('Related_Contact[relationship]', 'Role/Relationship *'));
		
		var $type_select = $('<select name="type" id="qa_related_contact_type" />')
			.append($('<option value="organisation" />').html('<img src="/graphics/tactile/items/organisation_small.png" /> Organisation'))
			.append($('<option value="person" />').html('<img src="/graphics/tactile/items/person_small.png" /> Person'));
		$type_select.find('option[value='+type+']').attr('selected', 'selected');
		$fs.append(Om.ModalForm.formRow($type_select, 'Contact Type'));
		
		var $contact_id = $('<input id="qa_related_contact_'+type+'_id" type="hidden" name="Related_Contact['+type+'_id]" />');
		var $contact = $('<input id="qa_related_contact_'+type+'" type="text" name="Related_Contact['+type+']" />');
		var $contact_row = Om.ModalForm.formRow($contact, type === 'person' ? 'Person *' : 'Organisation *').append($contact_id);
		$fs.append($contact_row);
		$contact.autocomplete({url:'/'+Om.plural(type)+'/filtered_list'});
		
		$type_select.change(function(){
			var type = $(this).val();
			$('#opportunity_contacts_list').removeClass('organisation').removeClass('person').addClass(type);
			$contact.parent().remove();
			$contact_id = $('<input type="hidden" />')
				.attr({'id': 'qa_related_contact_'+type+'_id', 'name':'Related_Contact['+type+'_id]'});
			$contact = $('<input type="text" />')
				.attr({'id': 'qa_related_contact_'+type, 'name':'Related_Contact['+type+']'});
			$contact_row = Om.ModalForm.formRow($contact, type === 'person' ? 'Person *' : 'Organisation *').append($contact_id);
			$fs.append($contact_row);
			$contact.autocomplete({url:'/'+Om.plural(type)+'/filtered_list'});
		});
		
		return $fs;
	}


	// Person Form
	function $person_fs() {
		var $fs = Om.ModalForm.$fieldset;
		$fs.append(Om.ModalForm.formRow('Person[firstname]', 'First Name *'))
			.append(Om.ModalForm.formRow('Person[surname]', 'Surname *'))
			.append(Om.ModalForm.formRow('Person[jobtitle]', 'Job Title'));
		
		if (Tactile.organisation_id) {
			$fs.append($('<input type="hidden" name="Person[organisation_id]" />').val(Tactile.organisation_id));
		} else if ($('#email_organisation_id').length) {
			$fs.append($('<input type="hidden" name="Person[organisation_id]" />').val($('#email_organisation_id').val()));
		} else {
			// Organisation input
			var $org_id_input = $('<input id="qa_person_organisation_id" type="hidden" name="Person[organisation_id]" />');
			var $org_input = $('<input id="qa_person_organisation" type="text" name="Person[organisation]" />');
			$fs.append($org_id_input)
				.append(Om.ModalForm.formRow($org_input, 'Organisation'));
			$org_input.autocomplete({url:'/organisations/filtered_list'});
		}
		
		$fs.append(Om.ModalForm.formRow('Person[phone][contact]', 'Phone Number'))
			.append(Om.ModalForm.formRow('Person[email][contact]', 'Email Address'));
		return $fs;
	}
	
	
	// Opportunity Form
	function $opportunity_fs() {
		var $status = $('<select id="qa_opp_status" name="Opportunity[status_id]" />');
		$.ajax({
			dataType: 'json',
			url: '/opportunities/options',
			success: function(json) {
				$.each(json.opportunity_options.status, function(value, label) {
					$status.append($('<option />').attr('value', value).text(label));
				});
			}
		});
		
		var $fs = Om.ModalForm.$fieldset;
		var $probability = $('<select id="qa_opp_probability" name="Opportunity[probability]" />');
		var i;
		for (i=0;i<=100;i+=5) {
			$probability.append($('<option />').val(i).text(i));
		}
		$fs.append(Om.ModalForm.formRow('Opportunity[name]', 'Name *'))
			.append(Om.ModalForm.formRow($('<input id="qa_opp_enddate" type="text" name="Opportunity[enddate]" />').datepicker(), 'Expected Close Date *'))
			.append($('<div class="row" />')
				.append($('<label for="qa_opp_status">Sales Stage *</label>'))
				.append($status)
			)
			.append(Om.ModalForm.formRow($('<input id="qa_opp_cost" type="text" name="Opportunity[cost]" />').val('0.0'), 'Value'))
			.append(Om.ModalForm.formRow($probability, 'Probability'));
		return $fs;
	}
	
	
	// Handler for hour and minute inputs
	$.fn.timeInput = function() {
		var inputs = this;
		return inputs.each(function() {
			var $input = $(this);
			$input.focus(function(){
				$(this).removeClass('subtle');
				if (($(this).val() === 'hh' && $(this).is(".hours")) || ($(this).val() === 'mm' && $(this).is(".minutes"))) {
					$(this).val('');
				}
			});
			$input.blur(function(){
				if ($(this).is(".hours")) {
					if ($(this).val() === '' || $(this).val() === 'hh') {
						$(this).val('hh').addClass('subtle');
						$(this).prev('input[type=hidden]:first').val('');
					} else {
						$(this).prev('input[type=hidden]:first').val($(this).val());
					}
				} else if ($(this).is(".minutes")) {
					if ($(this).val() === '' || $(this).val() === 'mm') {
						$(this).val('mm').addClass('subtle');
						$(this).prev('input[type=hidden]:first').val('');
					} else {
						$(this).prev('input[type=hidden]:first').val($(this).val());
					}
				}
			});
		});
	};
	
	
	// Activity Form
	$.fn.actControls = function() {
		var $container = $(this);
		var $fs = $container;
		
		// Class
		var $class_choice = $('<select id="activity_select_class" name="Activity[class]" tabindex="4" />')
			.append('<option value="todo">To Do</option>')
			.append('<option value="event">Event</option>');
		$container.append(Om.ModalForm.formRow($class_choice, 'This is a...'));
		
		// Location
		$container.append($('<div class="row" id="activity_location_container" />')
			.append($('<label id="activity_location_label" for="activity_location">Location</label>'))
			.append($('<input id="activity_location" type="text" name="Activity[location]" tabindex="5" />'))
		);
		
		// When
		var $when = $('<div id="when_container" />');
		var $skipToDate = $('<a class="skipToDate" />').html('&nbsp;').appendTo($when);
		var $date_choice = $('<select id="activity_select_date" name="Activity[date_choice]" tabindex="6" />').addClass("date_select")
			.append('<option value="today">Today</option>')
			.append('<option value="tomorrow">Tomorrow</option>')
			.append('<option value="date">Choose a Date...</option>')
			.append('<option value="later">Later</option>');
		
		// Date/Time
		var $dt = $('<div id="datetime_container" />');
		var $datetime = $('<div id="activity_datetime" />');
		$datetime
			.append($('<input id="activity_date" type="text" name="Activity[date]" tabindex="1" tabindex="9" />').addClass("date").datepicker())
			.append($('<input id="activity_time_minutes_hidden" type="hidden" name="Activity[time_minutes]" />'))
			.append($('<input id="activity_time_minutes" type="text" tabindex="8" />').addClass("time minutes subtle").val("mm"))
			.append($('<input id="activity_time_hours_hidden" type="hidden" name="Activity[time_hours]" />'))
			.append($('<input id="activity_time_hours" type="text" tabindex="7" />').addClass("time hours subtle").val("hh"));
		$dt.append($datetime);
		
		var $mark = $('<div id="time_range_mark" />').text("to");
		$dt.append($mark);
		
		var $end_datetime = $('<div id="activity_end_datetime" />');
		$end_datetime
			.append($('<input id="activity_end_date" type="text" name="Activity[end_date]" tabindex="12" />').addClass("date").datepicker())
			.append($('<input id="activity_end_time_minutes_hidden" type="hidden" name="Activity[end_time_minutes]" />'))
			.append($('<input id="activity_end_time_minutes" type="text" tabindex="11" />').addClass("time minutes subtle").val("mm"))
			.append($('<input id="activity_end_time_hours_hidden" type="hidden" name="Activity[end_time_hours]" />'))
			.append($('<input id="activity_end_time_hours" type="text" tabindex="10" />').addClass("time hours subtle").val("hh"));
		$dt.append($end_datetime);
		
		$when.append($date_choice).append($dt);
		$container.append($('<div class="row" />')
			.append($('<label for="activity_select_date">When</label>'))
			.append($when)
		);
		
		// Listeners
		$('input.time').timeInput();
		$('#activity_select_class, #activity_select_date').change(function(){
			switch ($class_choice.val()) {
				case 'todo':
					$('#activity_location').val('');
					$('#activity_end_datetime').find('input').val('');
					switch ($date_choice.val()) {
						case 'today':
						case 'tomorrow':
							$fs.attr('class', 'activity_fs todo_t');
							$('#activity_date').val('');
							if ($('#activity_time_hours').val() === '') {
								$('#activity_time_hours').addClass('subtle').val('hh');
							}
							if ($('#activity_time_minutes').val() === '') {
								$('#activity_time_minutes').addClass('subtle').val('mm');
							}
							break;
						case 'date':
							$fs.attr('class', 'activity_fs todo_date');
							if ($('#activity_time_hours').val() === '') {
								$('#activity_time_hours').addClass('subtle').val('hh');
							}
							if ($('#activity_time_minutes').val() === '') {
								$('#activity_time_minutes').addClass('subtle').val('mm');
							}
							break;
						case 'later':
							$fs.attr('class', 'activity_fs todo_later');
							$('#activity_datetime').find('input').val('');
							$('#activity_end_datetime').find('input').val('');
							break;
					}
					break;
				case 'event':
					switch ($date_choice.val()) {
						case 'today':
						case 'tomorrow':
							$fs.attr('class', 'activity_fs event_t');
							$('#activity_time_hours, #activity_end_time_hours').addClass('subtle').val('hh');
							$('#activity_time_minutes, #activity_end_time_minutes').addClass('subtle').val('mm');
							$('#activity_date').val('');
							$('#activity_end_date').val('');
							break;
						case 'date':
							$fs.attr('class', 'activity_fs event_date');
							$('#activity_time_hours, #activity_end_time_hours').addClass('subtle').val('hh');
							$('#activity_time_minutes, #activity_end_time_minutes').addClass('subtle').val('mm');
							break;
						case 'later':
							$fs.attr('class', 'activity_fs event_later');
							$('#activity_datetime').find('input').val('');
							$('#activity_end_datetime').find('input').val('');
							$('#activity_end_date').val('');
							break;
					}
					break;
			}
		});
		$('#activity_time_hours, #activity_time_minutes').keyup(function(){
			if ($class_choice.val() === 'event') {
				var sh = $('#activity_time_hours').val();
				var sm = $('#activity_time_minutes').val();
				var eh = $('#activity_end_time_hours').val();
				var em = $('#activity_end_time_minutes').val();
				if (sh !== '' && sh !== 'hh' && sm !== '' && sm !== 'mm' && sm.length > 1 && (eh === '' || eh === 'hh') && (em === '' || em === 'mm')) {
					// Set end to one hour later
					var nh = (sh === '23' ? '00' : (parseInt(sh, 10) + 1));
					$('#activity_end_time_hours').val(nh).removeClass('subtle');
					$('#activity_end_time_minutes').val(sm).removeClass('subtle');
				}
			} 
		});
		$skipToDate.click(function(){
			$date_choice.val('date').change();
			$('#activity_date').focus();
		});
	};
	

	// Organisation Adder
	$.fn.orgAdder = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				ev.preventDefault();
				Om.ModalForm.init('/organisations/save', 'New Organisation', 'Organisation saved successfully');
				
				var $fs = $organisation_fs();

				var $goto = $('<input />').attr({type: 'submit'}).val('Save and View').addClass('submit');
				$goto.click(function(ev){
					Om.ModalForm.location = '/organisations/view/';
				});
				Om.ModalForm.$save.find('input')
					.after($goto).after($('<span />').addClass('or').text(' or '));

				Om.ModalForm.show();
				return false; // Prevent bubble-up
			});
		});
	};
	
	
	// Person Adder
	$.fn.personAdder = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				ev.preventDefault();
				Om.ModalForm.init('/people/save', 'New Person', 'Person saved successfully');
				
				var $fs = $person_fs();
				
				// Foldable to update?
				if ($('#related_people').length) {
					var $foldable = $('#related_people');
					Om.ModalForm.postSuccess = function(json) {
						var $content = $foldable.find("div:first").length ?
							$foldable.find("div:first") : $('<div />').css({display: "none"}).appendTo($foldable);
						var href = $foldable.find("h3 a:first").attr("href");
						$.ajax({
							url: href,
							type: "GET",
							success: function(msg) {
								$content.html(msg).slideDown("slow");
								$foldable.find("h3").removeClass("closed").addClass("open");
							}
						});
					};
				}
				
				var $goto = $('<input />').attr({type: 'submit'}).val('Save and View').addClass('submit');
				$goto.click(function(ev){
					Om.ModalForm.location = '/people/view/';
				});
				Om.ModalForm.$save.find('input[value=Save]')
					.after($goto).after($('<span />').addClass('or').text(' or '));
				
				Om.ModalForm.show();
				return false; // Prevent bubble-up
			});
		});
	};
	
	
	// Opportunity Adder
	$.fn.oppAdder = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				ev.preventDefault();
				Om.ModalForm.init('/opportunities/save', 'New Opportunity', 'Opportunity saved successfully');
				
				var $fs = $opportunity_fs();
				if (Tactile.organisation_id) {
					$fs.append($('<input type="hidden" name="Opportunity[organisation_id]" />').val(Tactile.organisation_id));
				}
				if (Tactile.person_id) {
					$fs.append($('<input type="hidden" name="Opportunity[person_id]" />').val(Tactile.person_id));
				}
				
				// Foldable to update?
				if ($('#related_opportunities').length) {
					var $foldable = $('#related_opportunities');
					Om.ModalForm.postSuccess = function(json) {
						var $content = $foldable.find("div:first").length ?
							$foldable.find("div:first") : $('<div />').css({display: "none"}).appendTo($foldable);
						var href = $foldable.find("h3 a:first").attr("href");
						$.ajax({
							url: href,
							type: "GET",
							success: function(msg) {
								$content.html(msg).slideDown("slow");
								$foldable.find("h3").removeClass("closed").addClass("open");
							}
						});
					};
				}
				
				var $goto = $('<input />').attr({type: 'submit'}).val('Save and View').addClass('submit');
				$goto.click(function(ev){
					Om.ModalForm.location = '/opportunities/view/';
				});
				Om.ModalForm.$save.find('input[value=Save]')
					.after($goto).after($('<span />').addClass('or').text(' or '));
				
				Om.ModalForm.show();
				return false; // Prevent bubble-up
			});
		});
	};
	
	
	// Related Contact Adder
	$.fn.relContactAdder = function() {
		var triggers = this;

		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				ev.preventDefault();
				Om.ModalForm.init('/opportunities/save_opportunity_contact', 'New Related Contact', 'Contact saved successfully');
				
				var type = $('#opportunity_contacts_list').is('.person') ? 'person' : 'organisation';
				var $fs = $related_contact_fs(type);
				
				// Foldable to update?
				if ($('#opportunity_contacts').length) {
					var $foldable = $('#opportunity_contacts');
					Om.ModalForm.postSuccess = function(json) {
						var $content = $foldable.find("div:first").length ?
							$foldable.find("div:first") : $('<div />').css({display: "none"}).appendTo($foldable);
						var href = $foldable.find("h3 a:first").attr("href");
						$.ajax({
							url: href,
							type: "GET",
							success: function(msg) {
								$content.html(msg).slideDown("slow");
								$foldable.find("h3").removeClass("closed").addClass("open");
							}
						});
						$.ajax({
							url: '/magic/save_opportunity_related_contact_preference',
							data: {'value':$fs.find('select').val()}
						});
					};
				}
				
				Om.ModalForm.show();
				return false; // Prevent bubble-up
			});
		});
	};
	
	
	// Activity Adder
	$.fn.actAdder = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				ev.preventDefault();
				Om.ModalForm.init('/activities/save', 'New Activity', 'Activity saved successfully');
				
				var $fs = Om.ModalForm.$fieldset;
				$fs.append(Om.ModalForm.formRow('Activity[name]', 'Name *')).addClass('activity_fs todo_t');
					
				$fs.actControls();
				
				// Assign to user
				var $user_select = $('<select name="Activity[assigned_to]" />').append($('<option />').text('Loading...'));
				$fs.append(Om.ModalForm.formRow($user_select, 'Assign To User'));
				$.ajax({
					url: '/organisations/options',
					dataType: 'json',
					success: function(json){
						$user_select.empty();
						$.each(json.organisation_options.assigned_to, function(user, username) {
							var $user_option = $('<option value="'+username+'" />').text(user).appendTo($user_select);
							if (Tactile.Account.username === username) {
								$user_option.attr('selected', 'selected');
							}
						});
					}
				});
				
				// Relationships
				if (Tactile.organisation_id) {
					$fs.append($('<input type="hidden" name="Activity[organisation_id]" />').val(Tactile.organisation_id));
				}
				if (Tactile.person_id) {
					$fs.append($('<input type="hidden" name="Activity[person_id]" />').val(Tactile.person_id));
				}
				if (Tactile.opportunity_id) {
					$fs.append($('<input type="hidden" name="Activity[opportunity_id]" />').val(Tactile.opportunity_id));
				}
				
				// Foldable to update?
				if ($('#related_activities').length) {
					var $foldable = $('#related_activities');
					Om.ModalForm.postSuccess = function(json) {
						var $content = $foldable.find("div:first").length ?
							$foldable.find("div:first") : $('<div />').css({display: "none"}).appendTo($foldable);
						var href = $foldable.find("h3 a:first").attr("href");
						$.ajax({
							url: href,
							type: "GET",
							success: function(msg) {
								$content.html(msg).slideDown("slow");
								$foldable.find("h3").removeClass("closed").addClass("open");
							}
						});
					};
				}
				
				var $goto = $('<input />').attr({type: 'submit'}).val('Save and View').addClass('submit');
				$goto.click(function(ev){
					Om.ModalForm.location = '/activities/view/';
				});
				Om.ModalForm.$save.find('input[value=Save]')
					.after($goto).after($('<span />').addClass('or').text(' or '));
				
				// Show form
				Om.ModalForm.show();
				
				return false; // Prevent bubble-up
			});
		});
	};
	
	
	// File Adder
	$.fn.fileAdder = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				var url = $t.attr('href').replace('new_file','save_file').replace('?id=','');
				Om.ModalForm.init(url, 'Upload File', 'File uploaded successfully');
				Om.ModalForm.$form.attr('enctype', 'multipart/form-data');
				
				var $fs = Om.ModalForm.$fieldset;
				$fs.append($('<div class="row" />')
					.append($('<input type="hidden" name="MAX_FILE_SIZE" />').val("10000000"))
					.append($('<input id="qa_file_data" type="file" name="Filedata" />').addClass("file").css({'float':'none'}))
				).append($('<div class="row" />')
					.append($('<label for="qa_file_comment">Comment</label>'))
					.append($('<textarea id="qa_file_comment" name="comment" />'))
				);
				
				// Don't submit via Ajax
				Om.ModalForm.$form.unbind('submit');
				Om.ModalForm.$form.submit(function(){
					Om.ModalForm.$save.find('input').attr("disabled", "disabled");
				});
				
				Om.ModalForm.show();
				
				return false; // Prevent bubble-up
			});
		});
	};
	
	
	// Address Adder
	$.fn.addressAdder = function() {
		var triggers = this;
		
		return triggers.each(function(){
			var $t = $(this);
			$t.click(function(ev){
				ev.preventDefault();
				var type = $t.parents('div.foldable').attr('id').replace('_addresses', '');
				var controller = Om.plural(type);
				var url = '/' + controller + '/save_address/?'+type+'_id=' + Tactile.id;
				var is_edit = ($t.text() === 'Edit');
				var address = null;
				if (is_edit) {
					var address_id = $t.parent().attr('id').replace(type + '_address_','');
					$.each(Tactile.addresses, function(i, add) {
						if (parseInt(add.id,10) === parseInt(address_id,10)) {
							address = add;
						}
					});
				}
				Om.ModalForm.init(url, (is_edit ? 'Edit ' + address.name + ' Address' : 'Add an Address'), 'Address saved successfully');
				
				var $name = $('<input type="text" id="qa_address_name" name="name" />').val('Alternative');
				var $main;
				
				var $fs = Om.ModalForm.$fieldset;
				$fs.append(Om.ModalForm.formRow($name, 'Label'));
				
				// Do we let them choose if this should be the main one?
				if (!is_edit && Tactile.addresses && Tactile.addresses.length === 0) {
					// No previous addresses, this will be the main one
					$name.val('Main');
					$main = $('<input type="hidden" id="qa_address_main" name="main" value="on" />');
					$fs.append($main);
				} else if (is_edit) {
					// Editing an existing address
					if ($t.parents('div.address').is('.main')) {
						// Don't allow to de-main an address
						$main = $('<input type="hidden" id="qa_address_main" name="main" value="on" />');
						$fs.append($main);
					} else {
						// Allow a non-main address to be made the main one
						$main = $('<input type="checkbox" class="checkbox" id="qa_address_main" name="main" />');
						$fs.append(Om.ModalForm.formRow($main, 'Set as Main Address?'));
					}
				} else {
					// Previous addresses, new address, allow to set as main
					$main = $('<input type="checkbox" class="checkbox" id="qa_address_main" name="main" />');
					$fs.append(Om.ModalForm.formRow($main, 'Set as Main Address?'));
				}
				
				var $street1 = $('<input type="text" id="qa_address_street1" name="street1" />');
				var $street2 = $('<input type="text"id="qa_address_street2" name="street2" />');
				var $street3 = $('<input type="text"id="qa_address_street3" name="street3" />');
				var $town = $('<input type="text"id="qa_address_town" name="town" />');
				var $county = $('<input type="text"id="qa_address_county" name="county" />');
				var $postcode = $('<input type="text"id="qa_address_postcode" name="postcode" />');
				var $country_code = $('<select id="qa_address_country_code" name="country_code" />')
					.append('<option value="'+Tactile.COUNTRY_CODE+'">'+Tactile.COUNTRY_CODE+'</option>');
				
				$fs.append(Om.ModalForm.formRow($street1, 'Street 1'))
					.append(Om.ModalForm.formRow($street2, 'Street 2'))
					.append(Om.ModalForm.formRow($street3, 'Street 3'))
					.append(Om.ModalForm.formRow($town, 'Town / City'))
					.append(Om.ModalForm.formRow($county, 'County / State'))
					.append(Om.ModalForm.formRow($postcode, 'Postcode / ZIP'))
					.append(Om.ModalForm.formRow($country_code, 'Country'));
				
				// Are we editing?
				if (is_edit) {
					var id = $t.parents('div.address').attr('id').replace(type+'_address_', '');
					$fs.append($('<input type="hidden" name="id" />').val(id));
					$name.val($t.siblings('strong').text());
					$street1.val($t.siblings('address').find('span.street1').text());
					$street2.val($t.siblings('address').find('span.street2').text());
					$street3.val($t.siblings('address').find('span.street3').text());
					$town.val($t.siblings('address').find('span.town').text());
					$county.val($t.siblings('address').find('span.county').text());
					$postcode.val($t.siblings('address').find('span.postcode').text());
					
					var $delete = $('<a />').addClass('action').text('Delete');
					$delete.click(function(){
						if (confirm('Are you sure you want to delete this address?')) {
							$.ajax({
								dataType: 'json',
								url: '/'+controller+'/delete_address/'+id,
								success: function(json){
									if (json.status === 'success') {
										Om.ModalForm.postSuccess(json);
										Om.ModalForm.hide();
									} else {
										Om.Flash.addErrors(json.errors);
									}
								}
							});
						}
					});
					Om.ModalForm.$save.find('div').prepend($('<span />').addClass('or').text(' or ')).prepend($delete);
				}
				
				// Populate list off countries
				$.ajax({
					dataType: 'json',
					url: '/' + controller + '/options',
					success: function(json) {
						var foo_options = type + '_options';
						$.each(json[foo_options].country_code, function(value, label) {
							$country_code.append($('<option />').attr('value', value).text(label));
						});
						if (is_edit) {
							$country_code.val($t.siblings('address').find('span.country').text());
						} else {
							$country_code.find('option[value=' + Tactile.COUNTRY_CODE + ']').attr('selected', 'selected');
						}
					}
				});
				
				Om.ModalForm.postSuccess = function(json) {
					if (json.status === 'success') {
						Tactile.addresses = json.addresses;
					}
					var $container = $t.parents('div.foldable').find('ul li ul:first').hide().empty();
					if (json.addresses.length) {
						if (json.addresses.length > 1) {
							$container.addClass('has_multiple');
						} else {
							$container.removeClass('has_multiple');
						}
						$.each(json.addresses, function(i, address){
							var $li = $('<li />');
							if (i === json.addresses.length - 1) {
								$li.addClass('last');
							}
							var $trigger = $('<a />').addClass('action edit right').text('Edit').addressAdder();
							var $address = $('<address />');
							
							var lines = [];
							if (address.street1 !== '' && address.street1 !== null) {
								lines.push('<span class="street1">'+address.street1+'</span>');
							}
							if (address.street2 !== '' && address.street2 !== null) {
								lines.push('<span class="street2">'+address.street2+'</span>');
							}
							if (address.street3 !== '' && address.street3 !== null) {
								lines.push('<span class="street3">'+address.street3+'</span>');
							}
							if (address.town !== '' && address.town !== null) {
								lines.push('<span class="town">'+address.town+'</span>');
							}
							if (address.county !== '' && address.county !== null) {
								lines.push('<span class="county">'+address.county+'</span>');
							}
							if (address.postcode !== '' && address.postcode !== null) {
								lines.push('<span class="postcode">'+address.postcode+'</span>');
							}
							if (address.country !== '' && address.country !== null) {
								lines.push('<span class="country">'+address.country+'</span>');
							}
							$address.html(lines.join('<br />'));
							
							var $div = $('<div />').addClass('address').attr('id', type + '_address_' + address.id)
								.append($trigger)
								.append($('<strong />').text(address.name).addClass('sprite sprite-address' + (address.main ? '_main' : '')))
								.append(' &ndash; ').append($('<a />').attr({'href': address.map_url, 'target': '_new'}).text('Map'))
								.append($address);
							if (address.main) {
								$div.addClass('main');
							}
							$li.append($div);
							
							if (address.main) {
								$container.prepend($li);
							} else {
								$container.append($li);
							}
						});
					} else {
						$container.append($('<li />').addClass('none_yet').text("You haven't added an address yet, use the add link to add one"));  
					}
					$container.slideDown();
				};
				Om.ModalForm.show();
				return false;
			});
		});
	};
	
	
	// Contact Method Adder
	$.fn.contactAdder = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				ev.preventDefault();
				
				var type = $t.parents('div.foldable').attr('id').replace(/_contact_methods$/, '');
				var controller = Om.plural(type);
			
				// Build form
				var url = '/' + controller + '/save_contact_multi/';
				Om.ModalForm.init(url, 'Contact Methods', 'Contact methods saved successfully');
				var $fs = Om.ModalForm.$fieldset.attr({id: 'qa_contact_method'})
					.append($('<input />').attr({type: 'hidden', name: type+'_id'}).val(Tactile[type+'_id']))
					.append($('<div />').addClass('form_help')
						.append($('<p />').text("You can have a 'Main' contact method for each contact type."))
					);
				
				var $table = $('<table />');
				var $thead = $('<thead />')
					.append($('<tr />')
						.append($('<th />').text('Main'))
						.append($('<th />').text(''))
						.append($('<th />').text('Label'))
						.append($('<th />').text('Contact'))
						.append($('<th />').text(''))
					);
				$table.append($thead);
				var $add = $('<a />').text('Add another Contact Method...');
				var $tfoot = $('<tfoot />')
					.append($('<tr />')
						.append($('<td colspan="5" />').append($add))
					);
				$table.append($tfoot);
				var $tbody = $('<tbody />');
				$table.append($tbody);
				$fs.append($('<div class="row" />').append($table));
				
				// Listeners
				$add.click(function(){
					var create_main = $table.find('tr.T input[type=radio]:checked').length ? false : true;
					var date = new Date();
					var create_id = 'x' + (1 + $table.find('tr.new').length) + date.getTime();
					$table.append($contact_row(type, create_main, null, (create_main?null:'Alt'), null, create_id));
				});
				
				// Load existing methods
				$.each(Tactile.contact_methods, function(i, cm) {
					$table.append($contact_row(type, cm.main, cm.type, cm.name, cm.contact, cm.id));
				});
				if ($tbody.find('tr').length < 1) {
					$tbody.append($contact_row(type, true, null, null, null, 'x1'));
				}
				
				Om.ModalForm.postSuccess = function(json) {
					Tactile.contact_methods = json.contacts;
					var $ul = $('#'+type+'_contact_method_items ul').empty();
					if (json.contacts.length < 1) {
						$ul.append($('<li />').addClass('none_yet').text("You haven't added any contact methods yet."));
					} else {
						$.each(json.contacts, function(index, cm) {
							var $li = $('<li />').attr({id: 'contact_method_'+cm.id});
							$li.html('<span class="sprite">' + cm.contact + '</span> (' + cm.name + ')');
							if (cm.main) {
								$li.addClass('main');
							}
							switch (cm.type) {
								case 'T':
									$li.addClass('phone');
									$li.find('span').addClass('sprite-phone');
									break;
								case 'M':
									$li.addClass('mobile');
									$li.find('span').addClass('sprite-mobile');
									break;
								case 'F':
									$li.addClass('fax');
									$li.find('span').addClass('sprite-fax');
									break;
								case 'E':
									var $a = $('<a class="sprite sprite-email" href="mailto:' + cm.contact + (Tactile.DROPBOX_ADDRESS !== '' ? '?bcc=' + Tactile.DROPBOX_ADDRESS : '') + '" />')
										.text(cm.contact)
										.sendEmail();
									$li.find('span').replaceWith($a);
									$li.addClass('email');
									break;
								case 'W':
									$li.html('<a class="sprite sprite-website" href="' + cm.contact + '">' + cm.contact + '</a> (' + cm.name + ')');
									$li.addClass('website');
									break;
								case 'L':
									$li.html('<a class="sprite sprite-linkedin" href="http://www.linkedin.com/in/' + cm.contact + '">' + cm.contact + '</a> (' + cm.name + ')');
									$li.addClass('linkedin');
									break;
								case 'K':
									$li.html('<a class="sprite sprite-facebook" href="http://www.facebook.com/' + cm.contact + '">' + cm.contact + '</a> (' + cm.name + ')');
									$li.addClass('facebook');
									break;
								case 'S':
									$li.html('<img src="http://mystatus.skype.com/smallicon/' + cm.contact + '" width="16" height="16" alt="My Status" /> <a href="skype:' + cm.contact + '?chat">' + cm.contact + '</a> (' + cm.name + ')');
									$li.addClass('skype');
									break;
								case 'I':
									$li.html('<a class="sprite sprite-twitter" href="http://twitter.com/' + cm.contact + '">@' + cm.contact + '</a> (' + cm.name + ')');
									$li.addClass('twitter');
									break;
							}
							$ul.append($li);
						});
					}
				};
				Om.ModalForm.show();
				
				return false; // Prevent bubble-up
			});
		});
	};

	
	// Custom Field Adder
	$.fn.customAdder = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $t = $(this);
			
			$t.click(function(ev) {
				ev.preventDefault();
				
				var type = $t.parents('li.single').attr('id').replace(/_custom_fields$/, '');
				var controller = Om.plural(type);
			
				// Build form
				var url = '/' + controller + '/save_custom_multi/';
				Om.ModalForm.init(url, 'Custom Fields', 'Custom Field values saved successfully');
				
				var $fs = Om.ModalForm.$fieldset.attr({id: 'qa_custom_fields'})
					.append($('<input />').attr({type: 'hidden', name: type+'_id'}).val(Tactile[type+'_id']));
				
				var $table = $('<table />');
				var $new_fields = $('<select />').append($('<option />').text('-- Add Field --'));
				$.each(Tactile.custom_fields, function(i, cm) {
					if (cm.enabled_for[controller]) {
						$new_fields.append($('<option />').val(cm.id).html(cm.name).addClass('type-'+controller));
					}
				});
				var $tfoot = $('<tfoot />')
					.append($('<tr />')
						.append($('<td />').addClass('empty'))
						.append($('<td />').addClass('select').append($new_fields))
						.append($('<td />').addClass('add').append($('<span />').addClass('sprite sprite-add')))
					);
				$table.append($tfoot);
				var $tbody = $('<tbody />');
				$table.append($tbody);
				$fs.append($('<div class="row" />').append($table));
				
				// Listeners
				$new_fields.change(function(){
					var cfield = getCustomField($new_fields.val());
					var date = new Date();
					var create_id = 'x' + (1 + $table.find('tr.new').length) + date.getTime();
					$table.append($custom_field_row(type, cfield, create_id));
					
					// Remove option
					$new_fields.find('option[value='+$(this).val()+']').remove();
					if ($new_fields.find('option').length < 2) {
						$tfoot.hide();
					}
				});
				
				// Populate form with existing custom field values
				$.each(Tactile.existing_custom_fields, function(i, cm) {
					var cf = getCustomField(cm.field_id);
					var value = false;
					switch (cm.type) {
						case 's':
							value = cm.option;
							break;
						case 'c':
							value = (cm.enabled === 't'); 
							break;
						case 'n':
							value = cm.value_numeric;
							break;
						default:
							value = cm.value;
					}
					$new_fields.find('option[value='+cf.id+']').remove();
					if ($new_fields.find('option').length < 2) {
						$tfoot.hide();
					}
					$tbody.append($custom_field_row(type, cf, cm.id, value));
				});
				
				Om.ModalForm.postSuccess = function(json) {
					Tactile.existing_custom_fields = json.existing_custom_fields;
					
					$('#summary_info li.custom').remove();
					var n = 0;
					$.each(Tactile.existing_custom_fields, function(i, cf){
						var $li = $("<li />").attr('id', type+'_custom_field_row_'+cf.id).addClass('custom');
						$li.addClass(n % 2 === 0 ? 'odd' : 'even');
						
						var field = getCustomField(cf.field_id);
						var $label = $('<span />').addClass('view_label').append($('<span />').text(cf.name));
						var $value = $('<span />').addClass('view_data');
						switch(field.type){
							case 's':
								$value.append($('<span />').text(cf.option_name));
								break;
							case 'c':
								var $img = (cf.enabled === 't' ? $('<img />').attr({src:'/graphics/tactile/icons/tick.png'}) : $('<img />').attr({src:'/graphics/tactile/icons/cross.png'}));
								$value.append($('<span />').append($img));	
								break;
							case 'n':
								$value.append($('<span />').text(cf.value_numeric));
								break;
							default:
								$value.append($('<span />').text(cf.value));
								break;
						}
						$li.append($label).append($value);
						if ($('#summary_info li.custom').length) {
							$li.insertAfter($('#summary_info li.custom:last'));
						} else {
							$li.insertAfter($('#'+type+'_custom_fields'));
						}
						n++;
					});
				};
				Om.ModalForm.show();
				
				return false; // Prevent bubble-up
			});
		});
	};
	
	function getCustomField(id){
		var field = false;
		$.each(Tactile.custom_fields, function(i,cf){
			if (parseInt(cf.id,10) === parseInt(id,10)) {
				field = cf;
			}
		});
		return field;
	}
	
	$.fn.activityTrackAdder = function() {
		return this.each(function(){
			var $form = $(this);
			$form.submit(function(ev){
				ev.preventDefault();
				var track_id = $form.find('select').val();
				var auto = $form.find('input[name="auto"]').is(':checked') ? true : false;
				
				if (auto) {
					$.ajax({
						url: $form.attr('action').replace(/add_/, 'save_'),
						data: {'track_id':track_id,'auto':'on'},
						dataType: 'json',
						success: function(json) {
							Om.Flash.handleJSONResponse(json, function(){
								// Update foldable 
								var $f = $('#related_activities');
								$f.find('> div').hide();
								$f.find('h3').removeClass('open').addClass('closed').click();
							});
						}
					});
				} else {
					$.ajax({
						url: $form.attr('action'),
						data: {'track_id':track_id},
						dataType: 'json',
						success: function(json) {
							Om.ModalForm.init('/'+Om.Page.controller+'/save_activity_track/'+Tactile.id, 'Add an Activity Track', 'Activities Added');
							var $fs = Om.ModalForm.$fieldset;
							$fs.append($('<input type="hidden" name="track_id" />').val(track_id));
							Om.Flash.handleJSONResponse(json, function(){
								var at = json.activity_track;
								var opts = json.activity_track_options;
								var n = 0;
								$.each(at.stages, function(i, s){
									n++;
									var $name = $('<input type="text" />').val(s.name)
										.attr({'name':'ActivityTrackStage['+s.id+'][name]','id':'activitytrackstage_'+s.id+'_name'});
									var $date = $('<input type="text" class="datefield" />').val(s.date)
										.attr({'name':'ActivityTrackStage['+s.id+'][date]','id':'activitytrackstage_'+s.id+'_date'})
										.datepicker();
									var $assigned_to = $('<select />')
										.attr({'name':'ActivityTrackStage['+s.id+'][assigned_to]','id':'activitytrackstage_'+s.id+'_assigned_to'});
									$.each(opts.assigned_to, function(k,v){
										var $option = $('<option />').val(k).text(v);
										if (k === s.assigned_to) {
											$option.attr('selected', 'selected');
										}
										$assigned_to.append($option);
									});
									var $type = $('<select />')
										.attr({'name':'ActivityTrackStage['+s.id+'][type_id]','id':'activitytrackstage_'+s.id+'_type_id'});
									$.each(opts.type, function(k,v){
										var $option = $('<option />').val(k).text(v);
										if (parseInt(k,10) === parseInt(s.type_id,10)) {
											$option.attr('selected', 'selected');
										}
										$type.append($option);
									});
									var $desc = $('<textarea />').text(s.description)
										.attr({'name':'ActivityTrackStage['+s.id+'][desciption]','id':'activitytrackstage_'+s.id+'_description'});
									$fs.append($('<h4 />').text('Stage ' + n));
									$fs.append(Om.ModalForm.formRow($name, 'Name'));
									$fs.append(Om.ModalForm.formRow($date, 'Date'));
									$fs.append(Om.ModalForm.formRow($assigned_to, 'Assigned To'));
									$fs.append(Om.ModalForm.formRow($type, 'Type'));
									$fs.append(Om.ModalForm.formRow($desc, 'Description'));
								});
								// Update foldable 
								var $f = $('#related_activities');
								Om.ModalForm.postSuccess = function(json) {
									$f.find('> div').hide();
									$f.find('h3').removeClass('open').addClass('closed').click();
								};
								Om.ModalForm.show();
							});
						}
					});
				}
			});
		});
	};
	
	// Autocomplete
	$.fn.autocomplete = function(options) {
		var opts = $.extend({}, $.fn.autocomplete.defaults, options);
		
		return this.each(function() {
			var $input = $(this);
			$input.attr('autocomplete', 'off');
			opts.url = opts.url || $input.parents('form').attr('action');
			var controller = opts.url.replace(/^\//, '').replace(/\/.*/, '');
			var $idField = ($('#' + $input.attr('id') + '_id').length < 1 ? $('#person_reports_to') : $('#' + $input.attr('id') + '_id')); // Untidy fix for "person reports to" field
			var timeout = null;
			var ajaxRequest = new XMLHttpRequest();
			var lastQuery = '';
			
			var $lock = $('<span />').append($('<strong class="sprite sprite-add">New</strong>')).append($('<a class="sprite">Clear</a>')).addClass('new_clear');
			if (opts.lock) {
				$lock.find('a').click(function(){
					$lock.attr('class', 'new_clear');
					$input.removeAttr('disabled').val(opts.subtleText).addClass('subtle');
					$idField.val('');
				});
				var $adder = $lock.find('strong');
				switch (controller) {
					case 'organisations':
						$adder.click(function(){
							Om.ModalForm.init('/organisations/save', 'New Organisation', 'Organisation saved successfully');
							Om.ModalForm.$fs = $organisation_fs();
							$('#qa_organisation_name').val($input.val());
							Om.ModalForm.postSuccess = function(json) {
								$lock.attr('class', 'new_clear clear');
								$input.val(json.name).attr('disabled', 'disabled');
								$idField.val(json.id);
								Tactile.organisation_id = json.id;
							};
							Om.ModalForm.show();
						});
						break;
					case 'people':
						$adder.click(function(){
							Om.ModalForm.init('/people/save', 'New Person', 'Person saved successfully');
							Om.ModalForm.$fs = $person_fs();
							var names = $input.val().match(/\S+/g);
							if (names[0]) {
								$('#qa_person_firstname').val(names[0]);
							}
							if (names[1]) {
								$('#qa_person_surname').val(names[1]);
							}
							Om.ModalForm.postSuccess = function(json) {
								$lock.attr('class', 'new_clear clear');
								$input.val(json.name).attr('disabled', 'disabled');
								$idField.val(json.id);
								Tactile.person_id = json.id;
							};
							Om.ModalForm.show();
						});
						break;
					case 'opportunities':
						$adder.click(function(){
							Om.ModalForm.init('/opportunities/save', 'New Opportunity', 'Opportunity saved successfully');
							Om.ModalForm.$fs = $opportunity_fs();
							$('#qa_opportunity_name').val($input.val());
							Om.ModalForm.postSuccess = function(json) {
								$lock.attr('class', 'new_clear clear');
								$input.val(json.name).attr('disabled', 'disabled');
								$idField.val(json.id);
								Tactile.opportunity_id = json.id;
							};
							Om.ModalForm.show();
						});
						break;
					case 'activities':
						$adder.click(function(){
							Om.ModalForm.init('/activities/save', 'New Activity', 'Activity saved successfully');
							Om.ModalForm.$fs = $activity_fs();
							$('#qa_activity_name').val($input.val());
							Om.ModalForm.postSuccess = function(json) {
								$lock.attr('class', 'new_clear clear');
								$input.val(json.name).attr('disabled', 'disabled');
								$idField.val(json.id);
								Tactile.activity_id = json.id;
							};
							Om.ModalForm.show();
						});
						break;
				}
			}
			
			var resultsLoadingTemplate = '<p><span class="sprite sprite-loading">Searching...</span></p>';
			var resultsEmptyTemplate = '<p><span class="sprite sprite-remove">No results found</span></p>';
			var $results = $input.parents('form').find('.search_results').length ?
				$input.parents('form').find('.search_results') :
				$('<div />').addClass('search_results ' + opts.resultsClass).hide().appendTo(!opts.resultsContainer ? $input.parents('form') : opts.resultsContainer);
			
			$input.search = function (query, time) {
				time = time || 0;
				if (query.length >= opts.minChars && query !== opts.subtleText) {
					if (opts.reposition) {
						if ($input.parents('#modal_form').length) {
							$results.css({top: $input.position().top+25, left: $input.position().left-10});
						} else {
							$results.css({top: $input.offset().top+25, left: $input.offset().left-15});
						}
					}
					$input.addClass('has_results loading');
					if (query !== lastQuery) {
						$results.html(resultsLoadingTemplate).show();
						var ajaxOpts = {
							url: opts.url,
							data: {name: query},
							success: function(xhr) {
								if (time < $input.data('lastKeyTime')) {
									return;
								}
								$results.html(xhr);
								if ($results.find('li').length) {
									$results.find('li').hover(function(){
										$(this).addClass('selected');
									},
									function(){
										$(this).removeClass('selected');
									});
									if (opts.lock) {
										$results.find('li').click(function(){
											var id = $(this).attr('id').match(/\d+/);
											$idField.val(id);
											$input.val($(this).text()).attr('disabled', 'disabled');
											$lock.attr('class', 'new_clear clear');
										});
									} else if (opts.tagMode) {
										$results.find('li').click(function(){
											var tag = $(this).text();
											var xval = $input.val().replace(/,?[^,]*$/,'');
											if (xval !== '') {
												$input.val(xval + ', ' + tag);
											} else {
												$input.val(tag);
											}
											$input.focus();
											$results.hide();
										});
									}
								} else {
									$results.html(resultsEmptyTemplate);
									setTimeout(function(){
										$results.slideUp();
									}, 1000);
								}
								$input.removeClass('loading');
								$results.show();
								lastQuery = query;
							}
						};
						ajaxRequest = $.ajax(ajaxOpts);
					} else {
						$input.removeClass('loading');
						$results.show();
					}
				}
			};
			
			$input.keyup(function(){
				var keyTime = new Date();
				var query = $input.val();
				if (opts.tagMode) {
					query = $input.val().match(/[^,]*$/);
					query = query[0];
				}
				query = $.trim(query);
				$input.data('lastKeyTime', keyTime);
				clearTimeout(timeout);
				timeout = setTimeout(function(){
					$input.search(query, keyTime);
				}, opts.delay);
				if (opts.lock) {
					if (query === '' || query === opts.subtleText) {
						$lock.attr('class', 'new_clear');
					} else {
						$lock.attr('class', 'new_clear new');
					}
				}
			});
			$input.focus(function(){
				if ($input.is('.subtle')) {
					$input.removeClass('subtle').val('');
				}
			});
			$input.blur(function(){
				ajaxRequest.abort();
				if ((opts.autoHideResults || $input.is('.loading')) && $input.is('.has_results')) {
					setTimeout(function(){
						$results.slideUp(function(){
							$input.removeClass('has_results').removeClass('loading');
						});
					}, 300);
				}
				if ($input.val() === '' || $input.val() === opts.subtleText) {
					$input.addClass('subtle').val(opts.subtleText);
				}
				if (opts.lock && $lock.is('.new')) {
					var noticeTo = setTimeout(function(){
						if (!$lock.is('.clear') && !$input.siblings('.notice').length) {
							var xType = $input.siblings('label').text();
							var $notice = $('<div class="notice" />').append($('<div />').text('Submitting the form will create a new  ' + xType + ' called "' + $input.val() + '"')).insertAfter($input);
							setTimeout(function(){$notice.fadeOut(function(){$notice.remove();});}, 7000);
						}
					}, 500);
				}
			});
			if (opts.lock) {
				$input.after($lock);
				if ($idField.val() !== '') {
					$lock.attr('class', 'new_clear clear');
					$input.attr('disabled', 'disabled');
				}
			}
			if ($input.val() === '' || $input.val() === opts.subtleText) {
				$input.blur();
			}
		});
	};
	$.fn.autocomplete.defaults = {
		url: null,
		minChars: 2,
		delay: 500,
		subtleText: 'Type to find',
		resultsClass: 'shadow shadow184',
		resultsContainer: null,
		lock: true,
		reposition: true,
		tagMode: false,
		autoHideResults: true
	};
	
	
	$.fn.dedupe = function(url, $sources) {
		var inputs = this;
		var controller_path = url.match(/\/[^\/]+\//);
		return inputs.each(function(){
			var $input = $(this);
			$sources = $sources || $input;
			var $results = $('<div />').addClass('duplicates').css({display: 'none'});
			var pos = $input.offset();
			var new_top = pos.top + 25;
			var new_left = pos.left;
			$results.css({top: new_top, left: new_left});
			$input.after($results);
			$input.attr('autocomplete', 'off');
			
			$input.blur(function(){
				var query = '';
				$.each($sources, function() {
					query += ' ' + $(this).val();
				});
				query = query.replace(/^\s+/, '');
				if (query.length > 0) {
					var options = {
						url: url,
						dataType: 'html',
						data: {name: query},
						success: function(html) {
							$results.hide();
							if (html.match(/<li/)) {
								$results.html(html);
								$results.prepend($('<p class="top"><strong>Existing contacts</strong></p>'));
								$results.find('li').hover(function(){
									$(this).addClass('selected');
								},
								function(){
									$(this).removeClass('selected');
								});
								$results.find('li').click(function(){
									var id = $(this).attr('id').replace('item_', '');
									window.location = controller_path + 'view/' + id;
								});
								$results.slideDown();
							}
						}
					};
					$.ajax(options);
				} else {
					$results.hide();
				}
			});
		});
	};
	
}) (jQuery);
// Note editing
(function($) {
	
	// Event listeners for individual timeline items
	$.fn.timelineItemListeners = function() {
		var items = this;
		return items.each(function(){
			var $item = $(this);
			var $tl = $item.parents('.timeline');
			
			// Timeline attachments
			$item.find('.attached li a').hover(function(){
				var $a = $(this);
				var width = $a.width();
				if (width === 1) {
					$a.parent().siblings().find('a').animate({'width': 1, 'color':'#eee'});
					var $copy = $a.clone();
					$copy.css({width: 'auto', display: 'inline', left: -9999, top: -9999}).appendTo('body');
					$a.animate({'width':$copy.width(), 'color':'#000'}, "normal", null, function() { $(this).css({color:'#000'}); });
					$copy.remove();
				}
			});
			
			// Handle clicking on items
			$item.find('a').click(function(ev){ ev.stopPropagation(); });
			$item.find('.email .actions a.delete').click(function(){
				return confirm("Are you sure you want to delete this email?");
			});
			$item.find('.file .actions a.delete').click(function(){
				return confirm("Are you sure you want to delete this file?");
			});
			$item.find('.note .actions a.delete').deleteNote();
			$item.click(function(){
				if ($tl.is('.list')) {
					$(this).find('.body, .footer, .email_files').toggle('slow');
				}
			});
			
			// Expand truncated notes/emails
			$item.find('a.body_toggle').click(function(){
				$(this).parents('div.body:first').find('p.body_content').toggle();
				return false;
			});
			
			// Email attachment loading
			$item.find('.email_files p a').click(function(){
				var email_id = $(this).parents('li').attr('id').replace(/[^0-9]+/g, '');
				var $a = $(this);
				$.ajax({
					url: '/files/',
					dataType: 'json',
					data: {'email_id': email_id},
					success: function(json) {
						var $ul = $('<ul />');
						$a.parent().fadeOut(function(){$(this).replaceWith($ul);});
						$.each(json.files, function(i, file) {
							var href = '/files/get/' + file.id;
							$ul.append($('<li />').append($('<a />').addClass('sprite sprite-file').attr('href', href).text(file.filename)).append($('<span />').text(' (' + file.size + ' B)')));
						}); 
					}
				});
				return false;
			});
			
			// Note editing
			$item.find('.note.mine .body').noteEditor();
		});
	};
	
	// Deleting a note
	$.fn.deleteNote = function() {
		var triggers = this;
		return triggers.each(function(){
			var $a = $(this);
			$a.click(function(ev){
				ev.preventDefault();
				if (confirm("Are you sure you want to delete this note?")) {
					var options = {
						url: $a.attr('href'),
						type: "post",
						dataType: "json",
						success: function(json) {
							Om.Flash.handleJSONResponse(json, function() {
								$a.parents('li.item').fadeOut(function(){ $(this).remove(); });
							});
						}
					};
					$.ajax(options);
				}
				return false;
			});
		});
	};
	
	// Adding/editing a note
	$.fn.noteEditor = function() {
		var triggers = this;
		
		return triggers.each(function(){
			var $ne = $(this);
			var $tl = $ne.parents('.timeline');
			
			$ne.click(function(ev){
				ev.preventDefault();
				if (!$tl.find('.info a.action').length) {
					return false;
				}
				
				var id = null;
				var title = 'Title';
				var body = 'Body';
				var is_private = false;
				var editing = false;
				var $replacing = null;
				
				if (!$(this).is('a')) {
					// Edit mode
					editing = true;
					$replacing = $ne.parents('li');
					
					if (!$tl.find('form').length) {
						$tl.find('form .cancel').click();
					}
					
					id = $ne.parents('li').attr('id').match(/\d+/);
					title = $ne.parents('.note').find('h4').text();
					body = $ne.find('p.full');
					body.find('a.note_toggle').remove();
					body = $.trim(body.html().replace(/\<br\>/gi, '\r').replace(/\<[^>]+\>/, ''));
					is_private = ($ne.parents('.note').find('.private span').length > 0);
				}
				
				if (!$tl.find('form').length) {
					// Create note
					var template = '<div class="note mine"><div class="type round-left">Note</div><div class="hbf"></div></div>';
					var $note = $(template);
					
					if (editing) {
						var $id = $('<input type="hidden" name="id" />').val(id);
						$note.find('.hbf').append($id);
					}
					
					// Attached to
					$.each(Om.types, function(t,p){
						if (Tactile[t+'_id']) {
							$note.prepend($('<input type="hidden" name="'+t+'_id" />').val(Tactile[t+'_id']));
						}
					});
					
					// Header
					var $title = $('<input name="title" type="text" />').val(title);
					$header = $('<div class="header row" />').append($title);
					$note.find('.hbf').append($header);
					
					// Body
					var $note_body = $('<textarea rows="5" cols="64" name="note" />').val(body);
					var $is_private = $('<label />')
							.append($('<input type="checkbox" name="private" />'))
							.append(" Make private (Only you can see it)");
					if (is_private) {
						$is_private.find('input').attr('checked', 'checked');
					}
					var $save = $('<input type="submit" class="submit" value="Save" />');
					var $cancel = $('<a />').addClass('cancel').text('cancel').click(function(){
						$form.parents('li').remove();
						$tl.find('li.empty').show();
						if (editing) {
							$replacing.show();
						}
					});
					$body = $('<div class="body row" />').append($note_body)
						.append($is_private)
						.append($('<fieldset />').append($save).append('<span class="or"> or </span>').append($cancel));
					$note.find('.hbf').append($body);
					
					// Form
					var $form = $('<form action="'+$tl.find('.info a.action').attr('href')+'" class="saveform" method="post" />').submit(function(ev){
						ev.preventDefault();
						$form.find('input[type=submit]').attr('disabled', 'disabled').val('Working...');
						var options = {
							url: $(this).attr('action'),
							data: $(this).serialize(),
							dataType: 'json',
							success: function(json) {
								Om.Flash.handleJSONResponse(json, function(){
									var $new_note = $('<li />').attr('id', 'tl_note_'+json.note.id).addClass('item mine');
									$new_note.append(json.note_html).find('.body, .footer').attr('style', 'display: block;');
									$form.parents('li').slideUp(function(){
										$(this).remove();
										$tl.find('> ul').prepend($new_note.timelineItemListeners().hide().slideDown());
									});
									if (editing) {
										$replacing.remove();
									}
								}, null, function(){
									$form.find('input[type=submit]').removeAttr('disabled').val('Save');
								});
							}
						};
						$.ajax(options);
					});
					
					$tl.find('li.empty').hide();
					$new_li = $('<li />').addClass('item').append($form.append($note));
					if (editing) {
						// Replace existing li with form
						$replacing.hide().after($new_li);
					} else {
						// Insert form at top
						$tl.find('> ul').prepend($new_li);
					}
					$title.focus();
					$note_body.expandingTextarea();
				}
			});
		});
	};
	
	$.fn.timeline = function() {
		// All the timelines
		var timelines = this;
		
		return timelines.each(function() {
			// An individual timeline
			var $tl = $(this);
			
			// Toggle timeline view type and save preference
			$('#timeline_helper a.view').click(function(){
				if ($(this).is('.selected')) { return false; }
				var view = $(this).is('.block') ? 'block' : 'list';
				$('#timeline_helper a.view').removeClass('selected');
				$(this).addClass('selected');
				$('.timeline').toggleClass('list');
				if (view === 'block') {
					$tl.find('.item .body, .item .footer, .item .email_files').show();
				}
				$.ajax({url: "/magic/save_timeline_view_preference/",data: ({view: view})});
			});
			
			$tl.find('.item').timelineItemListeners();
			$tl.find('div.info a.action, li.empty a.action').noteEditor();
			
			// Open form editor if timeline is empty
			if ($tl.find('li.empty').length) {
				$tl.find('li.empty a.action').click();
			}
		});
		
	};

}) (jQuery);
// Tag adding and deleting
(function($) {
	
	$.fn.tagger = function() {
		var containers = this;
		
		return containers.each(function() {
			var $container = $(this);
						
			Om.Tagger.init($container);
		});
	};
	
}) (jQuery);
// Configurable value tables
(function($) {
	
	$.fn.configTable = function() {
		var tables = this;
		
		return tables.each(function() {
			var $table = $(this);
						
			Om.ConfigTable.init($table);
		});
	};
	
}) (jQuery);
// Link orgs with Freshbooks clients
(function($) {
	
	$.fn.FbLink = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $trigger = $(this);
			
			$trigger.click(function(ev){
				Om.ModalForm.init('/organisations/savefreshbookslink', 'Link with FreshBooks', 'Contact method saved successfully');
				
				// Load client list
				var $clients = $('<select name="freshbooks_id" id="qa_fblink_client" />');
				$clients.append($('<option />').text('Loading...'));
				
				var $fs = Om.ModalForm.$fieldset;
				$fs.append($('<input type="hidden" name="id" />').val(Tactile.id))
					.append($('<div class="row" />')
						.append($('<label for="qa_fblink_client">Client *</label>'))
						.append($clients)
					);
				
				// Add new Client
				var $add_client = $('<input type="submit" />').val('Add New Client').css({'margin-right':'0'});
				Om.ModalForm.$save.find('input')
					.after($add_client).after($('<span />').addClass('or').text(' or '));
				$add_client.click(function(ev){
					ev.preventDefault();
					$(this).FbCreate();
				});
				
				var options = {
					url: '/organisations/freshbooks_client_list',
					dataType: 'json',
					success: function(json) {
						if (json.status == 'success') {
							if (json.no_clients) {
								Om.Flash.addMessages('You have not added any Clients in FreshBooks yet, please create one below.');
								$add_client.click();
							} else {
								$clients.find('option').remove();
								$.each(json.clients, function(id, name) {
									var $option = $('<option />').val(id).text(name);
									$clients.append($option);
								});
							}
						} else {
							Om.Flash.addErrors(['There was a problem communicating with FreshBooks, please try again.']);
						}
					}
				};
				$.ajax(options);

				Om.ModalForm.options.dataType = 'html';
				Om.ModalForm.options.success = function(html) {
					Om.Flash.addMessages('Organisation Linked');
					// Update foldable
					var $foldable = $trigger.parents('div.ajax-foldable');
					var $content = $foldable.find("div:first");
					$content.hide().html(html);
					$content.append($('<p />').html('Want to <a id="freshbooks_reset_' + Tactile.id + '" class="action freshbooks_reset_link">remove this link with FreshBooks</a>?'));
					$content.append($('<p />').html('This will only unlink this Organisation and will NOT delete/change any data in FreshBooks.'));
					$content.append($('<p />').html('<em>(Last updated just now)</em>'));
					$content.slideDown();
					Om.ModalForm.hide();
				};

				Om.ModalForm.show();
				return false; // Prevent bubble-up
			});
		});
	};
	
	
	$.fn.FbCreate = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $trigger = $(this);
			
			Om.ModalForm.hide();
			Om.ModalForm.init('/organisations/add_to_freshbooks', 'New FreshBooks Client', 'Client added successfully');
			
			var $fs = Om.ModalForm.$fieldset;
			$fs.append($('<input />').attr({type:'hidden', name:'id'}).val(Tactile.id));
			var $org = $('<input />').attr({type:'text', name:'organization', id:'qa_fb_org'});
			var $fname = $('<input />').attr({type:'text', name:'first_name', id:'qa_fb_fname'});
			var $lname = $('<input />').attr({type:'text', name:'last_name', id:'qa_fb_lname'});
			var $email = $('<input />').attr({type:'text', name:'email', id:'qa_fb_email'});
			var $phone = $('<input />').attr({type:'text', name:'work_phone', id:'qa_fb_phone'});
			var $street1 = $('<input />').attr({type:'text', name:'p_street1', id:'qa_fb_street1'});
			var $street2 = $('<input />').attr({type:'text', name:'p_street2', id:'qa_fb_street2'});
			var $town = $('<input />').attr({type:'text', name:'p_city', id:'qa_fb_town'});
			var $county = $('<input />').attr({type:'text', name:'p_state', id:'qa_fb_county'});
			var $country = $('<input />').attr({type:'text', name:'p_country', id:'qa_fb_country'});
			var $postcode = $('<input />').attr({type:'text', name:'p_code', id:'qa_fb_postcode'});
			
			$fs.append(Om.ModalForm.formRow($org, 'Organization *'))
				.append(Om.ModalForm.formRow($fname, 'First Name'))
				.append(Om.ModalForm.formRow($lname, 'Last Name'))
				.append(Om.ModalForm.formRow($email, 'Email *'))
				.append(Om.ModalForm.formRow($phone, 'Phone'))
				.append(Om.ModalForm.formRow($street1, 'Street 1'))
				.append(Om.ModalForm.formRow($street2, 'Street 2'))
				.append(Om.ModalForm.formRow($town, 'Town / City'))
				.append(Om.ModalForm.formRow($county, 'County / State'))
				.append(Om.ModalForm.formRow($country, 'Country'))
				.append(Om.ModalForm.formRow($postcode, 'Postcode / ZIP'));
				
			Om.ModalForm.$save.find('input').val('Add to FreshBooks');
			
			var options = {
				url: '/organisations/details_for_freshbooks',
				dataType: 'json',
				data: {id: Tactile.id},
				success: function(json) {
					if (json.client) {
						var c = json.client;
						if (c.organization !== null) {
							$org.val(c.organization);
						}
						if (c.first_name !== null) {
							$fname.val(c.first_name);
						}
						if (c.last_name !== null) {
							$lname.val(c.last_name);
						}
						if (c.email !== null) {
							$email.val(c.email);
						}
						if (c.work_phone !== null) {
							$phone.val(c.work_phone);
						}
						if (c.p_street1 !== null) {
							$street1.val(c.p_street1);
						}
						if (c.p_street2 !== null) {
							$street2.val(c.p_street2);
						}
						if (c.p_city !== null) {
							$town.val(c.p_city);
						}
						if (c.p_county !== null) {
							$county.val(c.p_county);
						}
						if (c.p_country !== null) {
							$country.val(c.p_country);
						}
						if (c.p_code !== null) {
							$postcode.val(c.p_code);
						}
					}
				}
			};
			$.ajax(options);
			
			Om.ModalForm.options.success = function(json) {
				if (json.status != 'success') {
					Om.ModalForm.$save.find('input').removeAttr('disabled');
					Om.Flash.addErrors(json.errors);
				} else {
					// Update foldable
					var $foldable = $('#freshbooks_' + Tactile.id);
					var $content = $foldable.find("div:first");
					$content.hide();
					$.ajax({
						url: '/organisations/freshbooks',
						dataType: 'html',
						data: {id: Tactile.id},
						success: function(html) {
							$content.html(html);
							$content.slideDown();						
						}
					});
					Om.ModalForm.hide();
				}
			};
			
			Om.ModalForm.show();
		});
	};
	
}) (jQuery);
// TactileMail
(function($) {
	
	$.fn.sendEmail = function() {
		var triggers = this;

		return triggers.each(function() {
			var $trigger = $(this);
			
			$trigger.click(function(ev){
				if (Tactile.Account.tactilemail_enabled) {
					ev.preventDefault();
					
					var options = {
						url: '/emails/options/',
						dataType: 'json',
						success: function(json) {
							if (json.status && json.status == 'success') {
								var $from = $('<select id="qa_email_from" name="TactileMail[from_id]" />');
								if (json.emails) {
									var has_emails = false;
									$.each(json.emails, function(k,v) {
										has_emails = true;
										$from.append($('<option />').val(k).text(v));
									});
									
									var $fs;
									if (has_emails) {
										// Show TactileMail form
										Om.ModalForm.init('/emails/send/', 'Compose Email', 'Email Sent successfully');
					
										var $templates = $('<select id="qa_email_templates" />')
											.append($('<option />').val('').text('-- Select to Load --'));
										var $to = $('<input id="qa_email_to" type="hidden" name="TactileMail[to_address]" />')
											.val($trigger.text());
										var $subject = $('<input id="qa_email_subject" type="text" name="TactileMail[subject]" />')
											.css({width: '365px', margin:'0'}).val('Subject').addClass('subtle')
											.focus(function(){
												if ($(this).is('.subtle')) {
													$(this).val('').removeClass('subtle');
												}
											})
											.blur(function(){
												if ($(this).val() === '') {
													$(this).val('Subject').addClass('subtle');
												}
											});
										var $body = $('<textarea id="qa_email_body" name="TactileMail[body]" />')
											.val('Body').addClass('subtle')
											.focus(function(){
												if ($(this).is('.subtle')) {
													$(this).val('').removeClass('subtle');
												}
											})
											.blur(function(){
												if ($(this).val() === '') {
													$(this).val('Body').addClass('subtle');
												}
											});
										
										$fs = Om.ModalForm.$fieldset;
										
										// Opportunities
										var href = decodeURIComponent($trigger.attr('href'));
										var dropbox_action = href.match(/\?bcc=([^@]+)@/);
										if (dropbox_action) {
											dropbox_action = dropbox_action[1];
											var opp_id = dropbox_action.match(/opp\+(\d+)/);
											if (opp_id) {
												opp_id = opp_id[1];
												var $opp_id = $('<input type="hidden" name="opportunity_id" />').val(opp_id);
												$fs.append($opp_id);
											}
										}
										
										$templates.change(function(){
											var id = $(this).val();
											if (id !== '') {
												var options = {
													url: '/emails/template/'+id,
													dataType: 'json',
													success: function(json) {
														if (json.status && json.status == 'success') {
															if (json.template) {
																$subject.val(json.template.subject).removeClass('subtle');
																$body.val(json.template.body).removeClass('subtle').focus().mouseup();
															}
														}
													}
												};
												$.ajax(options);
											}
										});
									
										$templates.empty();
										var has_templates = false;
										if (json.templates) {
											$templates.append($('<option />').text('-- Load a template --'));
											$.each(json.templates, function(i, template) {
												has_templates = true;
												$templates.append($('<option />').val(template.id).text(template.name));
											});
											if (has_templates) {
												$fs.append(Om.ModalForm.formRow($templates, 'Template'));
											}
										}
										
										$fs.append(Om.ModalForm.formRow($from, 'From'))
											.append(Om.ModalForm.formRow($('<span class="false_input" />').text($trigger.text()), 'To'))
											.append($to)
											.append($('<div class="row" />').append($subject))
											.append($('<div class="row" />').append($body));
											
										if (Tactile.Account.is_free) {
											$fs.append($('<div />').addClass('form_help').append($('<p />').html('<a href="/account/change_plan/">Upgrade now</a> to remove the "Sent by Tactile CRM" signature')));
										}
										
										Om.ModalForm.$save.find('input').val('Send');
										$fs.find('textarea').expandingTextarea();
										Om.ModalForm.show();

									} else {
										// Uh oh, no emails!
										Om.ModalForm.init('/preferences/save_email_address/', 'Verify Your Email Address', 'Verification Email Sent');
										
										$name = $('<input id="qa_email_name" type="text" name="TactileEmailAddress[display_name]" />');
										$email = $('<input id="qa_email_address" type="text" name="TactileEmailAddress[email_address]" />');
										if (json.user_email !== '') {
											$email.val(json.user_email);
										}
										if (json.user_name !== '') {
											$name.val(json.user_name);
										}
										
										$fs = Om.ModalForm.$fieldset;
										$fs.append($('<div />').addClass('form_help')
												.append($('<p />').text('In order to compose emails via Tactile CRM, you must first verify an email address from which you would like to send.'))
												.append($('<p />').text('Tactile CRM will send an email to the address below asking the recipient to verify ownership.'))
											)
											.append(Om.ModalForm.formRow($email, 'Your Email Address *'))
											.append(Om.ModalForm.formRow($name, 'Display Name'));
										
										Om.ModalForm.$save.find('input').val('Send Verification Email');
										Om.ModalForm.show();
									}
								}
							}
						}
					};
					$.ajax(options);
				}
			});
		});
	};
	
	$.fn.emailAddressAdder = function(title) {
		var triggers = this;
		
		return triggers.each(function() {
			var $trigger = $(this);
			
			$trigger.click(function(ev) {
				ev.preventDefault();
				
				var options = {
					url: '/emails/options/',
					dataType: 'json',
					success: function(json) {
						Om.ModalForm.init($trigger.attr('href'), title, 'Verification Email Sent');
						Om.ModalForm.$save.find('input').val('Send Verification Email');
				
						var $email = $('<input id="qa_email_address" type="text" name="TactileEmailAddress[email_address]" />'); 
						var $name = $('<input id="qa_email_name" type="text" name="TactileEmailAddress[display_name]" />');
						if (json.user_name !== '') {
							$name.val(json.user_name);
						}
				
						var $fs = Om.ModalForm.$fieldset;
						$fs.append($('<div />').addClass('form_help').append(
								$('<p />').text('Tactile will send an email to this address asking the recipient to verify it.')
							))
							.append(Om.ModalForm.formRow($email, 'Email Address *'))
							.append(Om.ModalForm.formRow($name, 'Display Name'));
						
						Om.ModalForm.location = (title == 'Add Shared Email Address' ? '/setup/email/' : '/preferences/email/' );
						Om.ModalForm.show();
					}
				};
				$.ajax(options);
			});
		});
	};
	
	$.fn.emailTemplateEditor = function() {
		var triggers = this;
		
		return triggers.each(function() {
			var $trigger = $(this);
			
			$trigger.click(function(ev) {
				ev.preventDefault();
				
				Om.ModalForm.init('/templates/save/', 'Create Template', 'Template Saved Successfully');
				
				var $name = $('<input id="qa_template_name" type="text" name="EmailTemplate[name]" />');
				var $subject = $('<input id="qa_template_subject" type="text" name="EmailTemplate[subject]" />');
				var $body = $('<textarea id="qa_template_body" name="EmailTemplate[body]" rows="5" cols="20" />');
				var $fs = Om.ModalForm.$fieldset;
				$fs.append(Om.ModalForm.formRow($name, 'Template Name *'))
					.append(Om.ModalForm.formRow($subject, 'Email Subject *'))
					.append($('<div class="row" />').append($('<label for="qa_template_body">Email Body *</label>')))
					.append($('<div class="row" />').append($body));
				
				$fs.find('textarea').expandingTextarea();
				Om.ModalForm.submitByAjax = false;
				
				if ($trigger.is('.edit')) {
					var options = {
						url: $trigger.attr('href').replace('edit', 'view'),
						dataType: 'json',
						success: function(json) {
							if (json.status && json.status == 'success') {
								if (json.template) {
									$fs.append($('<input type="hidden" name="EmailTemplate[id]" />').val(json.template.id));
									$name.val(json.template.name);
									$subject.val(json.template.subject);
									$body.val(json.template.body);
								}
							}
							Om.ModalForm.show();
						}
					};
					$.ajax(options);
					Om.ModalForm.location = '/setup/email_templates/';
				} else {
					Om.ModalForm.show();
				}
			});
		});
	};
	
}) (jQuery);
// Configurable value tables
(function($) {
	
	$.fn.customvalueTable = function() {
		var tables = this;
		
		return tables.each(function() {
			var $table = $(this);
						
			Om.CustomvalueTable.init($table);
		});
	};
	
}) (jQuery);
// Configurable value tables
(function($) {
	
	$.fn.indexTable = function() {
		var tables = this;
		
		return tables.each(function() {
			var $table = $(this);
			var controller = $table.attr('id').replace('_index', '');
			var human_type = controller.substr(0,1).toUpperCase() + controller.substr(1,controller.length); 
			var $primary = $table.find('thead th.primary:first'); 
			var priText = $primary.text();
			
			var $actions = $('<select name="mass_action" />')
				.append('<option value="">-- Action --</option>');
				
			if (controller != 'activities') {
				$actions.append('<option value="add_activity">Add Activity</option>');
			}
			
			$actions.append('<option value="add_note">Add Note</option>')
				.append('<option value="add_tags">Add Tags</option>')
				.append('<option value="assign_to">Assign To User</option>')
				.append('<option value="delete">Delete</option>')
				.append('<option value="merge">Merge</option>');
			
			if (controller == 'organisations') {
				$actions.append('<option value="change_permissions">Change Access Permissions</option>');
			}
				
			$table.find('.cb input').click(function(){
				if ($(this).parent().is('.master')) {
					$table.find('td.cb input, th.master.cb input').attr('checked', $(this).attr('checked'));
					if (!$(this).is(':checked')) {
						$table.find('tbody tr').removeClass('selected');
					} else {
						$table.find('tbody tr').addClass('selected');
					}			
				} else {
					if (!$(this).is(':checked')) {
						$table.find('th.master.cb input').removeAttr('checked');
						$(this).parents('tr').removeClass('selected');
					} else {
						$(this).parents('tr').addClass('selected');
					}
				}
				if ($table.find('.cb input:checked').length) {
					if (!$table.find('th.primary select').length) {
						priText = $primary.text();
						$primary.empty().append($actions);
						$actions.change(function(){
							var ids = [];
							$table.find('tbody td.cb input:checked').each(function(i){
								ids[i] = $(this).val();
							});
							
							switch ($actions.val()) {
								case 'add_activity':
									Om.ModalForm.init($actions.parents('form').attr('action'), 'Add Activity to Multiple ' + human_type, 'Activities Added Successfully');
									Om.ModalForm.postCancel = function(){
										$actions.val('');
									};
									
									var $fs = Om.ModalForm.$fieldset;
									$fs.append(Om.ModalForm.formRow('Activity[name]', 'Name *')).addClass('activity_fs todo_t');

									$fs.actControls();
									// Assign to user
									$user_select = $('<select name="Activity[assigned_to]" />').append($('<option />').text('Loading...'));
									$fs.append(Om.ModalForm.formRow($user_select, 'Assign To User'));
									$.ajax({
										url: '/organisations/options',
										dataType: 'json',
										success: function(json){
											$user_select.empty();
											$.each(json.organisation_options.assigned_to, function(user, username) {
												var $user_option = $('<option value="'+username+'" />').text(user).appendTo($user_select);
												if (Tactile.Account.username==username) {
													$user_option.attr('selected', 'selected');
												}
											});
										}
									});
									
									$fs.append($('<input type="hidden" name="mass_action" />').val($actions.val()));
										
									Om.ModalForm.preSubmit = function(){
										$table.find('tbody .cb input:checked').each(function(i){
											$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
										});
									};
			
									Om.ModalForm.show();
									break;
								
								case 'add_note':
									Om.ModalForm.init($actions.parents('form').attr('action'), 'Add Note to Multiple ' + human_type, 'Notes Added Successfully');
									Om.ModalForm.postCancel = function(){
										$actions.val('');
									};
									$fs = Om.ModalForm.$fieldset;
									
									var $title = $('<input id="qa_note_title" type="text" name="Note[title]" />')
										.css({width: '365px', margin:'0'}).val('Title *').addClass('subtle')
										.focus(function(){
											if ($(this).is('.subtle')) {
												$(this).val('').removeClass('subtle');
											}
										})
										.blur(function(){
											if ($(this).val() === '') {
												$(this).val('Title *').addClass('subtle');
											}
										});
									var $body = $('<textarea id="qa_note_body" name="Note[note]" />')
										.val('Body *').addClass('subtle')
										.focus(function(){
											if ($(this).is('.subtle')) {
												$(this).val('').removeClass('subtle');
											}
										})
										.blur(function(){
											if ($(this).val() === '') {
												$(this).val('Body *').addClass('subtle');
											}
										});
										
									$fs.append($('<input type="hidden" name="mass_action" />').val($actions.val()))
										.append($('<div class="row" />').append($title))
										.append($('<div class="row" />').append($body))
										.append(Om.ModalForm.formRow('Note[private]', 'Mark notes as Private?', 'checkbox'));
										
									Om.ModalForm.preSubmit = function(){
										$table.find('tbody .cb input:checked').each(function(i){
											$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
										});
									};
			
									Om.ModalForm.show();
									break;
								
								case 'add_tags':
									Om.ModalForm.init($actions.parents('form').attr('action'), 'Tag Multiple ' + human_type, 'Tags Applied Successfully');
									Om.ModalForm.postCancel = function(){
										$actions.val('');
									};
									$fs = Om.ModalForm.$fieldset;
									$fs.append($('<input type="hidden" name="mass_action" />').val($actions.val()))
										.append(Om.ModalForm.formRow('tags'));
									
									Om.ModalForm.preSubmit = function(){
										$table.find('tbody .cb input:checked').each(function(i){
											$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
										});
									};
									
									Om.ModalForm.show();
									break;
								
								case 'delete':
									var n = $table.find('tbody td.cb input:checked').length;
									var msg = 'Are you sure you want to delete ' + (n > 1 ? ('these ' + n + ' items?') : 'this item?') + ' This process cannot be undone.';
									if (confirm(msg)) {
										$actions.unbind('change');
										$actions.parents('form').submit();
									} else {
										$actions.val('');
									}
									break;
								
								case 'merge':
									Om.ModalForm.init($actions.parents('form').attr('action'), 'Merge ' + human_type,  human_type + ' Merged Successfully');
									Om.ModalForm.postCancel = function(){
										$actions.val('');
									};
									$fs = Om.ModalForm.$fieldset;
									
									$master = $('<select name="master_id" />');
									$table.find('tbody .cb input:checked').each(function(i){
										var id = $(this).val();
										var itemName = $(this).parents('tr').find('td.primary a').text();
										$master.append($('<option />').val(id).text(itemName));
									});
									
									var related = 'notes, emails, files, ';
									switch (controller) {
										case 'organisations':
											related = related + 'contact methods, related People, Opportunities, and Activities';
											break;
										case 'people':
											related = related + 'contact methods, related Opportunities, and Activities';
											break;
										case 'opportunities':
											related = related + 'contact methods, and related Activities';
											break;
										case 'activities':
											related = related + 'and contact methods';
											break;
									}
									var help_text = 'Merging ' + human_type + ' will gather all the ' + related + ' under the parent selected below.';
									$fs.append($('<div />').addClass('form_help').append($('<p />').text(help_text)))
										.append($('<input type="hidden" name="mass_action" />').val($actions.val()))
										.append(Om.ModalForm.formRow($master, 'Merge Into'));
									
									// Don't submit via AJAX
									Om.ModalForm.$form.unbind('submit');
									Om.ModalForm.$form.submit(function(){
										$actions.unbind('change');
										Om.ModalForm.$save.find('input').attr('disabled', 'disabled');
										$table.find('tbody .cb input:checked').each(function(i){
											$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
										});
									})
									Om.ModalForm.$save.find('input').val('Merge');
									
									Om.ModalForm.show();
									break;
								
								case 'assign_to':
									Om.ModalForm.init($actions.parents('form').attr('action'), 'Assign ' + human_type, human_type + ' assigned successfully');
									Om.ModalForm.postCancel = function(){
										$actions.val('');
									};
									$fs = Om.ModalForm.$fieldset;
									$user_select = $('<select name="username" />');
									$fs.append($('<input type="hidden" name="mass_action" />').val($actions.val()))
										.append(Om.ModalForm.formRow($user_select, 'Assign To User'));
									
									Om.ModalForm.preSubmit = function(){
										$table.find('tbody .cb input:checked').each(function(i){
											$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
										});
									};
									
									// Don't submit via AJAX
									Om.ModalForm.$form.unbind('submit');
									Om.ModalForm.$form.submit(function(){
										$actions.unbind('change');
										Om.ModalForm.$save.find('input').attr('disabled', 'disabled');
										$table.find('tbody .cb input:checked').each(function(i){
											$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
										});
									})
									
									$.ajax({
										url: '/organisations/options',
										dataType: 'json',
										success: function(json){
											$.each(json.organisation_options.assigned_to, function(user, username) {
												$user_option = $('<option value="'+username+'" />').text(user);
												$user_select.append($user_option);
											});
											Om.ModalForm.show();
										}
									});
									break;
									
								case 'change_permissions':
									Om.ModalForm.init($actions.parents('form').attr('action'), 'Change Permissions', 'Permissions applied successfully');
									Om.ModalForm.postCancel = function(){
										$actions.val('');
									};
									$fs = Om.ModalForm.$fieldset;
									var help_text = "If selecting users, hold down Ctrl when clicking to choose multiple options";
									$fs.addClass('edit_holder');
									$fs.append($('<div />').addClass('form_help').append($('<p />').text(help_text)))
										.append($('<input type="hidden" name="mass_action" />').val($actions.val()));
									
									$read_access_select = $('<select name="Sharing[read][]" multiple="multiple" id="read_roles_ids" />').hide();
									$read_access_options = $('<ul />').addClass('radio_options false_input');
									$read_access_options.append($('<li />').append($('<label />').text('Everyone').prepend($('<input type="radio" name="Sharing[read]" value="everyone" checked="checked" />').addClass('radio checkbox'))))
										.append($('<li />').append($('<label />').text('Just Me (Private)').prepend($('<input type="radio" name="Sharing[read]" value="private" />').addClass('radio checkbox'))))
										.append(
											$('<li />').append($('<label />').text('Select Users...').prepend($('<input type="radio" name="Sharing[read]" value="private" id="read_roles" />').addClass('radio checkbox')))
												.append($read_access_select)
										);
									$fs.append($('<div id="read_access" />').append(Om.ModalForm.formRow($read_access_options, $('<h4 />').text('Read Access').addClass('false_label'))));
									
									$write_access_select = $('<select name="Sharing[write][]" multiple="multiple" id="write_roles_ids" />').hide();
									$write_access_options = $('<ul />').addClass('radio_options false_input');
									$write_access_options.append($('<li />').append($('<label />').text('Everyone').prepend($('<input type="radio" name="Sharing[write]" value="everyone" checked="checked" />').addClass('radio checkbox'))))
										.append($('<li />').append($('<label />').text('Just Me (Private)').prepend($('<input type="radio" name="Sharing[write]" value="private" />').addClass('radio checkbox'))))
										.append(
											$('<li />').append($('<label />').text('Select Users...').prepend($('<input type="radio" name="Sharing[write]" value="private" id="write_roles" />').addClass('radio checkbox')))
												.append($write_access_select)
										);
									$fs.append($('<div id="write_access" />').append(Om.ModalForm.formRow($write_access_options, $('<h4 />').text('Read & Write Access').addClass('false_label'))));
									
									if ($.browser.msie) {
										$fs.find('input.radio').click(function(){
											// IE doesn't fire change() on radios until after blur()
											this.blur().focus();
										});
									}
									$fs.find('input.radio').change(function(){
										var $radio = $(this);
										var $select = $radio.parents('ul.radio_options').find('select'); 
										if ($radio.attr('id').match('roles')) {
											$select.show().removeAttr('disabled');
										} else {
											$select.hide().attr('disabled', 'disabled').find('option').removeAttr('selected');
										}
									});
									
									Om.ModalForm.preSubmit = function(){
										$table.find('tbody .cb input:checked').each(function(i){
											$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
										});
									};
									
									$.ajax({
										url: '/organisations/options',
										dataType: 'json',
										success: function(json) {
											$.each(json.organisation_options.sharing.read.private, function(k, v) {
												if (v.match(/\w+\/\//)) {
													var role = v.replace(/\/\/.*$/, '');
													$read_access_select.append($('<option />').val(k).text(role));
													$write_access_select.append($('<option />').val(k).text(role));
												} else if (v.match(/^[^\/]+$/)) {
													$read_access_select.append($('<option />').val(k).text(v));
													$write_access_select.append($('<option />').val(k).text(v));
												}
											});
											Om.ModalForm.show();
										}
									});
									break;
								
								case '':
									break;
							}
						});
					}
				} else {
					$primary.text(priText);
				}
			});
		});
	};
	
}) (jQuery);
// Advanced search features
(function($) {
	
	$.fn.queryBuilder = function(options) {
		var opts = $.extend({}, $.fn.queryBuilder.defaults, options);
		
		return this.each(function() {
			var $qb = $(this);
			var typeSingular = {'org':'Organisation', 'per':'Person', 'opp':'Opportunity', 'act':'Activity'};
			var qb = Tactile.qb;
			$qb.$filterList = $qb.find('ul');
			$qb.$filterSelect = $qb.find('select.add');
			$qb.$filterSelect.change(function(){
				var fname = $qb.$filterSelect.val();
				var fvalue = ((fname === 'gen_assigned_to' || fname === 'gen_created_by') ? (Tactile.Account.username + '//' + Tactile.Account.site_address) : null);
				$qb.addFilter(fname, null, fvalue);
				$qb.refreshFilterSelect();
				$qb.$filterSelect.find('option[value=""]').attr('selected', 'selected');
			});
			
			// Keeps track of which filters to display in the select box
			$qb.refreshFilterSelect = function() {
				var type = $('#qb_record_type').val();
				$qb.$filterSelect.empty().append('<option value="">-- Add Filter --</option>');
				var $generalGroup = $('<optgroup />').attr('label', 'General Fields');
				var $typeGroup = $('<optgroup />').attr('label', typeSingular[type] + ' Fields');
				var $customGroup = $('<optgroup />').attr('label', typeSingular[type] + ' Custom Fields');
				$.each(Tactile.qb, function(fname, fdata){
					if (!$qb.$filterList.find('#qb_'+fname).length && (fname.match(/^gen_/) || fname.match(new RegExp('^'+type+'_')))) {
						var $opt = $('<option />').val(fname).text(fdata.label);
						if (fname.match(new RegExp('^'+type+'_[0-9]+$'))) {
							$customGroup.append($opt);
						} else if (fname.match(new RegExp('^'+type))) {
							$typeGroup.append($opt);
						} else {
							$generalGroup.append($opt);
						}
						$qb.$filterSelect.append($generalGroup);
						if ($typeGroup.find('option').length) {
							$qb.$filterSelect.append($typeGroup);
						}
						if ($customGroup.find('option').length) {
							$qb.$filterSelect.append($customGroup);
						}
					}
				});
			};
			
			// Add a filter to the list
			$qb.addFilter = function(fname, fop, fvalue) {
				if (fname === '' || Tactile.qb[fname] === undefined) {
					return false;
				}
				var f = Tactile.qb[fname];
				var type = fname.substring(0,3);
				var $li = $('<li class="row '+type+'" />').attr('id', fname).appendTo($qb.$filterList);
				
				// Label
				$li.append($('<label />').attr('for', 'qb_'+fname).text(f.label));
				
				// Operators
				if (f.operators) {
					var $ops = $('<select class="ops" />').attr('name','q['+fname+'][op]').appendTo($li);
					$.each(f.operators, function(i, e){
						$ops.append($('<option />').val(e).text(e));
					});
					$ops.val(fop);
				}
				
				// Value(s)
				var $value = $('<input />').val(fvalue).attr({'id':'qb_'+fname, 'name':'q['+fname+'][value]'});
				switch(f.accept) {
					case 'select':
						$value = $('<select />').attr({'id':'qb_'+fname, 'name':'q['+fname+'][value]'}).appendTo($li);
						$.each(f.options, function(k,v){
							$value.append($('<option />').text(v).val(k));
						});
						$value.val(fvalue);
						break;
					case 'date':
						$value.attr('type','text').addClass('datefield').appendTo($li);
						$value.datepicker();
						break;
					case 'boolean':
						$value = $('<select />').attr({'id':'qb_'+fname, 'name':'q['+fname+'][value]'}).appendTo($li);
						$value.append($('<option />').val('TRUE').text('TRUE')).append($('<option />').val('FALSE').text('FALSE'));
						$value.val(fvalue);
						break;
					case 'numeric':
						$value.attr('type','text').appendTo($li).bind('mouseup, keyup', function(){
							var num = parseFloat($(this).val());
							num = isNaN(num) ? 0 : val;
							$(this).val(num);
						});
						break;
					case 'text':
					default:
						$value.attr('type','text').appendTo($li);
						break;
				}
				$value.addClass('value');
				
				// Filter destructor
				var $del = $('<a class="delete sprite sprite-remove" />').click(function(){
					$qb.removeFilter(fname);
				}).prependTo($li);
			};
			
			// Remove a filter from the list
			$qb.removeFilter = function(fname) {
				$qb.$filterList.find('#qb_'+fname).parent().fadeOut(function(){
					$(this).remove();
					$qb.refreshFilterSelect();
				});
			}
			
			// Handle switching of record type
			$('#qb_record_type').change(function(){
				var newType = $(this).val();
				$qb.$filterList.find('li.org, li.per, li.opp, li.act').not('li.'+newType).remove();
				$qb.refreshFilterSelect();
			});
			
			if (Tactile.q === undefined) {
				// Add a field by default
				$qb.addFilter('gen_name', 'CONTAINS');
			}
			
			// Add options to filter select
			$qb.refreshFilterSelect();
			
			// Attach event handlers to any existing page elements
			$qb.find('li.row a.delete').click(function(){
				$qb.removeFilter($(this).parent().attr('id'));
			});
			$qb.find('div.row a.action').click(function(){
				var $firstFilter = $qb.find('li').not('.record_type').first();
				var searchName = '';
				if ($firstFilter.length) {
					searchName = $firstFilter.find('label').text(); 
					if ($firstFilter.find('select.ops').length) {
						searchName += ' ' + $firstFilter.find('select.ops').val().toLowerCase();
					}
					if ($firstFilter.find('.value').is('select')) {
						searchName += ' ' + $firstFilter.find('.value').find('option:selected').text();
					} else {
						searchName += ' ' + $firstFilter.find('.value').val();
					}
				}
				Om.ModalForm.init('/search/save', 'Save Search', 'Search saved successfully');
				var $fs = Om.ModalForm.$fieldset;
				var q = $qb.parent().serialize();
				var $name = $('<input id="qa_search_name" class="text" type="text" name="name" />').val(searchName);
				$fs.append($('<div class="form_help" />').append($('<p />').text("Saving a search will allow you to store a set of filters for later use. Give the search a name to describe it.")))
					.append($('<input type="hidden" name="form" />').val(q))
					.append(Om.ModalForm.formRow($name));
				Om.ModalForm.postSuccess = function(json){
					// Update foldable?
					$('#saved_searches').find('li.empty').remove();
					var $li = $('<li />').append($('<a href="/search/recall/'+json.id+'" class="sprite sprite-search" />').text(json.name)).append($('<a href="/search/delete/'+json.id+'" class="delete action" />').text('Delete'));
					$('#saved_searches').find('ul').append($li);
				};
				
				Om.ModalForm.show();
			});
		});
	};
	
	$.fn.queryBuilder.defaults = {};
	
}) (jQuery);

$('#queryBuilder').queryBuilder();
$(function() {
	
	// Datepicker
	var datepickerDefaults = {dateFormat: 'd/m/yy'};
	switch (Tactile.DATE_FORMAT) {
		case 'm/d/Y':
			datepickerDefaults.dateFormat = 'm/d/yy';
			break;
	}
	$.datepicker.setDefaults(datepickerDefaults);
	$("input.datefield").datepicker();

	// Flash
	$("#flash").live('click', function(ev) {Om.Flash.clear();});
	setTimeout(function(){Om.Flash.clearMessages();}, 10000);
	
	// Foldables
	$("div.foldable").foldable();
	$("div.ajax-foldable").ajaxFoldable();
	
	// Searches
	$('#header_search').autocomplete({lock:false, subtleText:'Search Everything...', resultsClass:'', reposition:false, autoHideResults:false, resultsContainer:$('#header_search').parent()});
	$('input#organisation_parent, input#person_organisation, input#opportunity_organisation, input#activity_organisation, input#email_organisation').autocomplete({url:'/organisations/filtered_list'});
	$('input#opportunity_person, input#activity_person, input#email_person').autocomplete({url:'/people/filtered_list'});
	$('input#person_person_reports_to').autocomplete({url:'/people/filtered_list'});
	$('input#activity_opportunity, input#email_opportunity').autocomplete({url:'/opportunities/filtered_list'});
	$('div.edit_holder input#organisation_name').dedupe('/organisations/all_companies');
	$('div.edit_holder input#person_surname').dedupe('/people/filtered_list', $('input#person_firstname, input#person_surname'));
	
	// Modals
	$("a#welcome_add_organisation").orgAdder();
	$("a#assign_add_organisation").orgAdder();
	$('a#welcome_add_person').personAdder();
	$('a#assign_add_person').personAdder();
	$("div#related_people a.add_related").personAdder();
	$('a#assign_add_opportunity').oppAdder();
	$("div#related_opportunities a.add_related").oppAdder();
	$('a.opportunity_adder').oppAdder();
	$("div#opportunity_contacts a.add_related").relContactAdder();
	$("div#related_activities a.add_related").actAdder();
	$("div#related_files a.add_related").fileAdder();
	$("div#organisation_contact_methods a.add_related").contactAdder();
	$("div#person_contact_methods a.add_related").contactAdder();
	$('#organisation_addresses a.add_related').addressAdder();
	$('#person_addresses a.add_related').addressAdder();
	$("a#custom_fields_update").customAdder();
	
	// Confirmations
	$('form.delete_form').submit(function(ev){
		if (!confirm("Are you sure you want to delete this?")) {
			ev.preventDefault();
		}
	});
	$('button.delete_link, a.u_sure').live('click', function(ev){
		return confirm("Are you sure you want to delete this?");
	});
	
	// Index Tables
	$('table.index_table').indexTable();
	
	// Timelines
	$(".timeline").timeline();
	
	// Taggers
	$("div#organisations_tags, div#people_tags, div#opportunities_tags, div#activities_tags").tagger();
	
	// Config tables
	$('table.group_values').configTable();
	
	// Custom Value tables
	$('table.custom_values').customvalueTable();
	
	// Expanding textareas
	$('textarea').expandingTextarea();
	
	// Email
	$('a.email, li.email a').sendEmail();
	$('#add_shared_email_address').emailAddressAdder('Add Shared Email Address');
	$('#add_user_email_address').emailAddressAdder('Verify Your Email Address');
	$('#new_email_template').emailTemplateEditor();
	$('#pref_email_templates a.edit').emailTemplateEditor();
	
});
$(function() {
	
	// Keyboard shortcuts
	$(document).bind('keydown', 's', function(){
		$('#search_nav').addClass('expanded').css('backgroundColor', '#fff'); $('#header_search').focus();
	});
	
	
	// Menu tabs
	$('#header .topNav .menu').click(function(ev){
		ev.preventDefault();
		$(this).parent().toggleClass('expanded');
	});
	var tabHoverTo = null;
	$('#header .topNav .level1').hover(function(){
		clearTimeout(tabHoverTo);
		$('#header .topNav .level1').not($(this)).removeClass('hover');
		$(this).addClass('hover');
	}, function(){
		var $l1 = $(this);
		tabHoverTo = setTimeout(function(){
			$l1.removeClass('hover');
		}, 100);
	});
	$('#header .topNav .slow').hover(function(){
		var $tab = $(this);
		tab_to = setTimeout(function(){$tab.addClass('expanded');}, 1000);
	}, function(){
		clearTimeout(tab_to);
		$(this).removeClass('expanded');
	});
	var tabExpandedTo = null;
	$('#header .topNav .fast').hover(function(){
		clearTimeout(tabExpandedTo);
		$('#header .topNav .level1').not($(this)).removeClass('expanded');
		$(this).addClass('expanded');
		if ($(this).is('#search_nav')) {
			$('#header_search').focus();
		}
	}, function(){
		var $l1 = $(this);
		tabExpandedTo = setTimeout(function(){
			$l1.removeClass('expanded');
			$('#header_search').blur();
		}, 100);
	});
	$('#header_search').mouseenter(function(){
		clearTimeout(tabHoverTo);
		clearTimeout(tabExpandedTo);
	});
	
	
	// System Navigation
	$('#system_navbar a.context').click(function(ev){
		ev.preventDefault();
		$(this).parent().toggleClass('expanded');
	});
	$('#system_navbar li, #system_welcome').mouseleave(function(){
		if ($(this).is('.expanded')) {
			$(this).removeClass('expanded');
		}
	});

	// Pagination
	$('.paging .details ul').click(function(){return false;});
	var paging_to;
	$('.paging .details').hover(function(){
		var $paging = $(this);
		paging_to = setTimeout(function(){$paging.addClass('expanded');}, 1000);
	}, function(){
		clearTimeout(paging_to);
	}).click(function(ev){
		$(this).toggleClass('expanded');
	});
	$('.paging .jump select').change(function(){
		url = window.location.href.replace(/&?page=\d+/, '');
		if (!url.match(/\?/)) {
			url = url + '?';
		}
		url = url + (url.match(/\?$/) ? '' : '&') + 'page=' + $(this).val();
		window.location = url;
	});
	$('.paging .jump_az select').change(function(){
		if ($(this).val() !== '') {
			url = window.location.href.replace(/&?page=\d+/, '');
			url = url.replace(/&?name=[^&]+/, '');
			url = url.replace(/&?firstname=[^&]+/, '');
			if (!url.match(/\?/)) {
				url = url + '?';
			}
			if ($(this).is('.firstname')) {
				url = url + (url.match(/\?$/) ? '' : '&') + 'firstname=' + $(this).val() + '*';
			} else {
				url = url + (url.match(/\?$/) ? '' : '&') + 'name=' + $(this).val() + '*';
			}
			window.location = url;
		}
	});
	$('.paging .perpage select').change(function(){
		url = window.location.href.replace(/&?page=\d+/, '');
		url = url.replace(/&?limit=[^&]+/, '');
		if (!url.match(/\?/)) {
			url = url + '?';
		}
		url = url + (url.match(/\?$/) ? '' : '&') + 'limit=' + $(this).val() + '&page=1';
		window.location = url;
	});

	// View page tabulation
	$('div.view_nav h3').click(function(){
		var show_id = $(this).attr('id').replace('show_', '');
		$('#'+show_id).toggle();
		var is_show = $('#'+show_id).is(':visible');
		$(this).toggleClass('selected');
		if ($('div.view_nav h3.selected').length < 1) {
			$('#view_nothing_selected').show();
		} else {
			$('#view_nothing_selected').hide();
		}
		$.ajax({
			url: "/magic/save_view_view_preference/",
			data: ({view: show_id, show: is_show})
		});
	});


	// Option lists
	$('ul.admin_list li h4 a').hover(function(){
		$(this).parents('li').addClass('selected');
	}, function(){
		$(this).parents('li').removeClass('selected');
	});


	// Dashboard
	function loadDashGraph() {
		$.ajax({
			url: '/graphs/',
			dataType: 'json',
			success: function(json) {
				var $img = $('<img />');
				if (json.graph_src) {
					$('#dashboard_graph').parent().find('h3 a:first').text(json.graph_name);
					var src = json.graph_src.replace(/&amp;/g, '&');
					$img.attr('src', src);
				} else {
					$img.attr('src', '/graphics/tactile/sample_graph.png');
				}
				$('#dashboard_graph').append($('<a href="/graphs" />').append($img));
			}
		});
	}
	if ($('#dashboard_graph').length) {
		loadDashGraph();
	}
	$('#graph_stats .detail').mouseleave(function(){
		$(this).parent().removeClass('expanded');
	});
	$('#graph_stats .bubble a').click(function(ev){
		var $a = $(this);
		ev.preventDefault();
		var method = $(this).attr('href').replace(/\/graphs\//, '').replace(/_([a-z])/g, function(t,a){return a.toUpperCase();});
		$.ajax({
			url: '/graphs/pin_to_dashboard',
			data: {chart_method: method},
			success: function(xhr){
				$a.parents('.expanded').removeClass('expanded');
				$('#dashboard_graph').empty();
				loadDashGraph();
			}
		});
	});
	$('#dashboard_report a.context').click(function(ev){
		ev.preventDefault();
		var $a = $(this);
		if ($a.parent().is('.none')) {
			return false;
		}
		$('#dashboard_report .detail').parent().not($a.parent()).removeClass('expanded');
		if ($a.parent().find('.detail').length) {
			$a.parent().toggleClass('expanded');
		} else {
			var type = $a.parents('ul').attr('id').replace(/_stats/, '');
			var controller = Om.plural(type);
			var detailTo = null;
			var $acts = $('<div class="detail" />').mouseenter(function(){ clearTimeout(detailTo); }).mouseleave(function(){
				detailTo = setTimeout(function(){ $acts.parent().removeClass('expanded')}, 600);
			});
			var $ul = $('<ul />');
			$ul.append($('<li />').addClass('last').append($('<span class="sprite sprite-loading" />').text('Loading...')));
			$acts.append($('<div class="bubble" />').append($ul)).appendTo($a.parent());
			$a.parent().addClass('expanded');
			$.ajax({
				url: $a.attr('href'),
				dataType: 'json',
				success: function(json) {
					if (json.status && json.status === 'success') {
						$ul.empty();
						var $li;
						if (json[controller].length < 1) {
							$li = $('<li />').append($('<span class="sprite" />').text('None'));
							$ul.append($li);
						} else {
							if (json.num_pages > 1) {
								$li = $('<li />').append($('<a />').addClass('seeAll').attr('href', $a.attr('href')).append($('<strong />').text('See all ' + json.total)))
								$ul.append($li);
							}
							$.each(json[controller], function(i, o){
								$li = $('<li />');
								var divTo = null;
								var $div = $('<div />').hover(function(){
									divTo = setTimeout(function(){ $div.find('dl').slideDown(); }, 1000);
								}, function(){
									clearTimeout(divTo);
									$div.find('dl').slideUp();
								});
								if (type === 'activity') {
									var $cb = $('<input type="checkbox" class="checkbox" title="Tick to complete" />').appendTo($li).markAsComplete();
									var $desc = $('<dl />').hide().appendTo($div);
									$desc.append($('<dt />').text('Starts:')).append($('<dd />').text(o.date_string));
									if (o.end_date_string) {
										$desc.append($('<dt />').text('Ends:')).append($('<dd />').text(o.end_date_string));
									}
									if (o.type) {
										$desc.append($('<dt />').text('Type:')).append($('<dd />').append($('<span />').attr('title', o.type).text(o.type.substring(0, 50))));
									}
									if (o.person_id) {
										$desc.append($('<dt />').text('With:')).append($('<dd />').append($('<a class="sprite sprite-person" href="/people/view/'+o.person_id+'" />').text(o.person)));
									} else if (o.organisation_id) {
										$desc.append($('<dt />').text('With:')).append($('<dd />').append($('<a class="sprite sprite-organisation" href="/organisations/view/'+o.organisation_id+'" />').text(o.organisation)));
									}
									if (o.description) {
										$desc.append($('<dt />').text('Description:')).append($('<dd />').append($('<span />').attr('title', o.description).text(o.description.substring(0, 100))));
									}
								}
								var $oa = $('<a />').addClass('completable record').text(o.name).attr('href', '/'+controller+'/view/'+o.id);
								$ul.append($li.append($div.prepend($oa)));
							});
						}
						$li.addClass('last');
					}
				}
			});
		}
	});

	// Preferences page
	$('#pref_nav a').click(function(ev){
		ev.preventDefault();
		
		var h = $('#pref_content').height();
		$('#pref_content').css({'min-height':h});
		
		var $tab = $(this);
		var pref = $tab.attr('id').replace('pref_tab_', '');

		$tab.parents('div.edit_holder').find('div.show:visible').animate({opacity:0}, "slow", function() {
			// Hide current
			$(this).animate({height: 0}, "normal", function(){
				$(this).css({display: 'none', height: 'auto', opacity: 100});
				// Show new
				$('#pref_'+pref).css({opacity:0}).slideDown("fast", function(){
					var $prefs = $(this);
					$prefs.css({display: 'block'});
					$('#pref_content').animate({minHeight:0}, "normal", function(){
						$prefs.animate({opacity:100}, "slow");
					});
				});
			});
		});
		$tab.parents('ul').find('li.on').removeClass('on');
		$tab.parents('ul').find('li.arrow').removeClass('on_left').removeClass('on_right');
		$tab.parents('li').addClass('on');
		$tab.parents('li').next('.arrow').addClass('on_left');
		$tab.parents('li').prev('.arrow').addClass('on_right');
	});

	
	// Show/hide fields
	$('a.show_fields').click(function(ev) {
		ev.preventDefault();
		var $a = $(this);
		var fieldset_id = $a.attr('href');
		$a.parent().hide();
		$(fieldset_id).slideDown();
	});
	
	
	// Tagged items pager
	$('#tagged_item_columns div.paging a').live('click', function(ev){
		ev.preventDefault();
		var $a = $(this);
		$a.parents('li').find('div.paging_loading').show();
		var options = {
			url: $(this).attr('href'),
			success: function(xhr) {
				$a.parents('li').html(xhr);
			}
		};
		$.ajax(options);
	});
	
	$('a.invoice_link').click(function(ev) {
		var invoice_id = $(this).attr('id');
		Om.ModalForm.init('/help/submit', 'Duplicate Invoice Request', 'Your invoice request has been sent');
		
		var $p = $('<p />')
			.html('Thanks for your copy invoice request, we\'ll get back to you ASAP. ' +
				'Support hours are 9am-5pm GMT Monday to Friday, ' +
				'requests outside of those times will be dealt with the next business day. ' +
				'Our help pages can be viewed <a href="http://www.tactilecrm.com/help">here</a>.');
		
		var $ta = $('<textarea id="qa_request_body" name="support_request" />');
		var $fs = Om.ModalForm.$fieldset;
		$fs.append($('<div />').addClass('form_help').html($p))
			.append(Om.ModalForm.formRow('email', 'Email copy to: *'))
			.append(Om.ModalForm.formRow('subject', 'Invoice ID *'))
			.append($('<div class="row" />')
				.append($('<label for="qa_request_body">Request Details *</label>'))
				.append($ta)
			);
		Om.ModalForm.$save.find('input').val('Send');
		$ta.expandingTextarea();

		var options = {
			url: '/help/setup',
			dataType: 'json',
			success: function(json) {
				if (json.status && json.status === 'success') {
					$('#qa_request_email').val(json.email);
					$('#qa_request_subject').val('Copy Invoice Request: ' + invoice_id);
					$('#qa_request_body').val('Please resend a copy of Invoice ' + invoice_id + ' to ' + json.email);
				}
			}
		};
		$.ajax(options);

		Om.ModalForm.show();
	});
	
	// Support link
	$('#help_link').click(function(ev){
		ev.preventDefault();
		Om.ModalForm.init('/help/submit', 'Support Request', 'Your support request has been sent');
		
		var $p = $('<p />')
			.html('Let us know what\'s up and we\'ll get back to you ASAP. ' +
				'Support hours are 9am-5pm GMT Monday to Friday, ' +
				'requests outside of those times will be dealt with the next business day. ' +
				'Our help pages can be viewed <a href="http://www.tactilecrm.com/help">here</a>.');
		
		var $ta = $('<textarea id="qa_request_body" name="support_request" />');
		var $fs = Om.ModalForm.$fieldset;
		$fs.append($('<div />').addClass('form_help').html($p))
			.append(Om.ModalForm.formRow('email', 'Email Address *'))
			.append(Om.ModalForm.formRow('subject', 'What\'s the Issue? *'))
			.append($('<div class="row" />')
				.append($('<label for="qa_request_body">Further Details *</label>'))
				.append($ta)
			);
		Om.ModalForm.$save.find('input').val('Send');
		$ta.expandingTextarea();

		var options = {
			url: '/help/setup',
			dataType: 'json',
			success: function(json) {
				if (json.status && json.status === 'success') {
					$('#qa_request_email').val(json.email);
				}
			}
		};
		$.ajax(options);

		Om.ModalForm.show();
	});
	$('#support_link_highlight').click(function(){
		$('#help_link').effect('pulsate');
		return false;
	});
	
	
	// Freshbooks
	$('a.freshbooks_add_link').live('click', function(){
		$(this).FbLink();
		$(this).click();
	});
	$('a.freshbooks_reset_link').live('click', function(){
		// Unlink
		var $a = $(this);
		var sure = confirm('Are you sure you want remove this link?');
		if (sure) {
			var options  = {
				type: 'post',
				dataType: 'json',
				url: '/organisations/freshbooks_reset_link',
				data: {id: Tactile.id},
				success: function(json) {
					Om.Flash.addMessages(json.message);
					$a.parents('div.ajax-foldable').find('div:first')
						.html('<p>Do you want to <a id="freshbooks_add_' + Tactile.id + '" class="action freshbooks_add_link">link this Organisation</a> with a client in FreshBooks?</p>');
				}
			};
			$.ajax(options);
		}
	});
	
	
	// Activities
	if ($('#activity_new_inline').length) {
		// Gotta reload the data from the page into the new elements
		var type = $('#activity_select_class').val();
		var location = $('#activity_location').val();
		var when = $('#activity_select_date').val();
		var start_date = $('#activity_date').val();
		var start_hours = $('#activity_time_hours_hidden').val();
		var start_minutes = $('#activity_time_minutes_hidden').val();
		var end_date = $('#activity_end_date').val();
		var end_hours = $('#activity_end_time_hours_hidden').val();
		var end_minutes = $('#activity_end_time_minutes_hidden').val();
		
		var $fs = $('#activity_new_inline').html('');
		$fs.actControls();

		$('#activity_select_class').val(type);
		if (type !== 'todo') {
			$('#activity_location').val(location);
		}
		$('#activity_select_date').val(when);
		var when_class;
		switch (when) {
			case 'later':
				when_class = 'later';
				break;
			case 'today':
			case 'tomorrow':
				when_class = (type === 'todo' ? 'todo_t' : 'event_t'); 
				break;
			case 'date':
				when_class = (type === 'todo' ? 'todo_date' : 'event_date');
				break;
		}
		$('#when_container').attr('class', when_class);
		$('#activity_date').val(start_date);
		if (start_hours !== '') {
			$('#activity_time_hours_hidden').val(start_hours);
			$('#activity_time_hours').val(start_hours).removeClass('subtle');
		}
		if (start_minutes !== '') {
			$('#activity_time_minutes_hidden').val(start_minutes);
			$('#activity_time_minutes').val(start_minutes).removeClass('subtle');
		}
		$('#activity_end_date').val(end_date);
		if (end_hours !== '') {
			$('#activity_end_time_hours_hidden').val(end_hours);
			$('#activity_end_time_hours').val(end_hours).removeClass('subtle');
		}
		if (end_minutes !== '') {
			$('#activity_end_time_minutes_hidden').val(end_minutes);
			$('#activity_end_time_minutes').val(end_minutes).removeClass('subtle');
		}
	}
	
	
	// Notes and Emails
	$('#activity_timeline form.note input').live('click', function(){
		if($(this).val() === 'Give the note a title') {
			$(this).val('');
		}
	});
	$('#activity_timeline form.note textarea').live('click', function(){
		if($(this).val() === 'Enter your note\'s content') {
			$(this).val('');
		}
	});
	
	
	// Change plan
	$('tfoot#change_plan_options input.checkbox').change(function(){
		if ($(this).is('.notfree') && !$(this).is('.current')) {
			if (!$('#change_plan_card_form').is(':visible')) {
				$('#change_plan_card_form').slideDown();
			}
		} else {
			$('#change_plan_card_form').hide();
		}
	});
	
	
	// Access Permissions
	if ($.browser.msie) {
		$('#read_access input.radio, #write_access input.radio').click(function(){
			// IE doesn't fire change() on radios until after blur()
			this.blur().focus();
		});
	}
	$('#read_access input.radio, #write_access input.radio').change(function(){
		var $radio = $(this);
		var $select = $radio.parents('ul.radio_options').find('select'); 
		if ($radio.attr('id').match('roles')) {
			$select.show().removeAttr('disabled');
		} else {
			$select.hide().attr('disabled', 'disabled').find('option').removeAttr('selected');
		}
	});
	
	
	// Logos and Portraits
	$('table#organisations_index, table#people_index').each(function(){
		var $table = $(this);
		var type = $table.attr('id') === 'people_index' ? 'person' : 'organisation';
		var controller = Om.plural(type);
		var ids = [];
		$table.find('tbody tr').each(function(){
			var id = parseInt($(this).attr('id').match(/[0-9]+/), 10);
			if (!isNaN(id)) {
				ids.push(id);
			}
		});
		var options = {
			url: '/' + controller + '/get_thumbnail_urls/',
			dataType: 'json',
			data: {'ids[]': ids},
			success: function(json) {
				if (json.status === 'success' && json.thumbnails) {
					$.each(json.thumbnails, function(k, v) {
						var bg_image = 'url(' + encodeURI(v) + ')';
						var $a = $('#' + type + '_' + k + ' td.primary a');
						$a.css({'background-image': bg_image, 'background-position': '0 0'});
					});
				} 
			}
		};
		$.ajax(options);
	});
	$('#organisation_view #heading_logo, #person_view #heading_logo').parent().hover(function(){
		var $img = $(this).find('img');
		var $dialog  = $('<div />').addClass('dialog').css({'display':'none'});
		if ($img.is('.custom')) {
			$dialog.text('Delete');
		} else {
			$dialog.text('Upload');
		}
		$dialog.click(function(){$(this).parent().find('img').click();});
		$img.after($dialog);
		$dialog.slideDown();
	}, function(){
		var $img = $(this).find('img');
		$img.siblings('div.dialog').remove();
	});
	$('#organisation_view #heading_logo, #person_view #heading_logo').click(function(){
		var $img = $(this);
		var type = Tactile.person_id ? 'person' : 'organisation';
		var controller = Om.plural(type);
		if ($img.is('.custom')) {
			var sure = confirm('Are you sure you want to delete this image?');
			if (sure) {
				var url = '/files/delete_logo/?' + type + '_id=' + Tactile.id;
				window.location = url;
			}
		} else {
			Om.ModalForm.init('/' + controller + '/save_file/' + Tactile.id, 'Upload ' + (type === 'organisation' ? 'Logo' : 'Portrait'), 'Image Saved');
			Om.ModalForm.$form.attr('enctype', 'multipart/form-data');
		
			var $fs = Om.ModalForm.$fieldset;
			$fs.append($('<div />').addClass('form_help').html('<p>Supported formats: JPG, GIF, PNG</p>'))
				.append($('<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />'))
				.append($('<input type="hidden" name="is_logo" value="yes" />'))
				.append($('<div class="row" />')
					.append($('<input id="qa_file_data" type="file" name="Filedata" />').css({'float':'none'}))
				);
			Om.ModalForm.$form.unbind('submit');
			Om.ModalForm.$form.submit(function(){
				Om.ModalForm.$save.find('input').attr("disabled", "disabled");
			});
	
			Om.ModalForm.show();
		}
	});
	
	
	// Purchase users
	$('#user_quantity').bind('keyup mouseup change', function(){
		var users = parseInt($(this).val(), 10);
		users = isNaN(users) ? 0 : users;
		var total;
		var user_limit = 1;
		if ($('#current_limit').length > 0) {
			user_limit = parseInt($('#current_limit').val(), 10);
			user_limit = isNaN(user_limit) ? 0 : user_limit;
			var pro_rata = parseFloat($('#purchase_users_pro_rata_cost').val());
			pro_rata = isNaN(pro_rata) ? 0 : pro_rata;
			if (pro_rata < 1) {
				pro_rata = 1;
			}
			total = users * pro_rata;
			total = Math.round(total * 100) / 100;
		} else {
			var cpupm = parseInt($('#cpupm').val(), 10);
			cpupm = isNaN(cpupm) ? 0 : cpupm;
			total = users * cpupm;
		}
		total = total + (total.toString().match(/\./) ? '' : '.00');
		total = total + (total.toString().match(/\.[0-9]$/) ? '0' : '');
		total = Tactile.CURRENCY_SYMBOL + total;
		$('#purchase_users_total').text(total);
		$('#total_users').text(users + user_limit);
	});
	$('#purchase_users_form, #per_user_change_plan_form').submit(function(ev) {
		var $confirm = $('#purchase_users_confirm');
		if (!$confirm.find('input').is(':checked')) {
			ev.preventDefault();
			$confirm.effect('pulsate');
		}
	});
	
	
	// Campaign Monitor
	$('#campaignmonitor_setup_form').submit(function(ev){
		var $form = $(this);
		var cm_key = $('#cm_key').val();
		if (cm_key === '') {
			ev.preventDefault();
			Om.Flash.addErrors(['Please enter your API Key']);
		} else if($form.find('input[type=submit]').val() === 'Link') {
			// Submit the form
		} else {
			ev.preventDefault();
			$form.find('input[type=submit]').attr('disabled', 'disabled').val('Connecting...');
			$.ajax({
				url: '/campaignmonitor/get_clients/',
				dataType: 'json',
				data: {cm_key: cm_key},
				success: function(json) {
					Om.Flash.handleJSONResponse(json, function(){
						$form.find('input[type=submit]').removeAttr('disabled').val('Connect');
						var $clients = $('<select type="text" name="cm_client_id" id="cm_clients" />');
						var has_clients = false;
						$.each(json.clients, function(k, v){
							$clients.append($('<option />').val(k).text(v));
							has_clients = true;
						});
						if (has_clients) {
							$form.find('input[type=submit]').val('Link');
							$('#cm_key').parents('.row').after($('<div class="row" />').append($('<label for="cm_clients" />').text('Client')).append($clients));
						} else {
							Om.Flash.addErrors(['Failed to fetch list of clients. Have you created any yet?']);
						}
					}, function(){
						$form.find('input[type=submit]').removeAttr('disabled').val('Connect');
					});
				}
			});
		}
	});
	$('#cm_export_link').click(function(ev){
		ev.preventDefault();
		Om.ModalForm.init($(this).attr('href'), 'Export to Campaign Monitor', 'Export job created');
	
		var $fs = Om.ModalForm.$fieldset;
		var $help = $('<div />').addClass('form_help').append(
			$('<p />').html("People must be marked 'Can Email' and have an email address in order to subscribe them to a list.")
		);
		$lists = $('<select id="qa_cm_list" name="cm_list_id" />').append($('<option />').text('Loading...'));
		
		$fs.append($help)
			.append($('<div class="row" />')
				.append($('<label for="qa_cm_list" />').text('Select List'))
				.append($lists)
			);
		Om.ModalForm.$save.find('input').val('Export');
		
		Om.ModalForm.options.success = function(json) {
			if (json.status === 'success') {
				Om.Flash.addMessages(Om.ModalForm.successMsg);
				Om.ModalForm.$form.find('h3').text('Export in Progress');
				$fs.find('div, label, select').remove();
				$fs.append($('<p />').html("Exports to Campaign Monitor can take <strong>several minutes</strong> to complete.").css({'font-size':'1.2em'}));
				$fs.append($('<p />').text("To save you waiting, we've started the process in the backgound, and will email you with the results when we're finished.").css({'font-size':'1.2em'}));
				Om.ModalForm.$save.find('input').removeAttr("disabled").val('Okay').blur();
				Om.ModalForm.$form.unbind('submit');
				Om.ModalForm.$form.submit(function(ev){
					ev.preventDefault();
					Om.ModalForm.hide();						
				});
				
			} else if(response.status === 'failure') {
				Om.Flash.addErrors(response.errors);
			} else {
				Om.Flash.addErrors(['There was a problem performing the save, please try again']);
			}
			Om.ModalForm.$save.find('input').removeAttr('disabled');
		};
		
		// Get lists
		var options = {
			url: '/campaignmonitor/get_lists/',
			dataType: 'json',
			success: function(json) {
				if (json.status === 'success') {
					var has_lists = false;
					$.each(json.lists, function(k, v){
						$lists.append($('<option />').val(k).text(v));
						has_lists = true;
					});
					if (has_lists) {
						$lists.find('option:first').remove();
					} else {
						$lists.append($('<option value="" />').text('None found'));
						$lists.find('option:first').remove();
						Om.Flash.addErrors(['Failed to fetch subscriber lists. Have you created any yet?']);
					}
				} else {
					Om.Flash.addErrors(['Failed to fetch subscriber lists from Campaign Montior. Please check your integration settings on the Admin page and try again.']);
				}
			}
		};
		$.ajax(options);

		Om.ModalForm.show();
	});
	

	// MOTDs
	$('#motd_dismiss a').click(function(ev){
		var $trigger = $(this);
		ev.preventDefault();
		$.ajax({
			url: $trigger.attr('href'),
			success: function() {
				var $msg = $('#motd_dismiss').parent().parent(); 
				$msg.slideUp(1000, function(){$msg.remove();});
			}
		});
	});
	
	
	// Paging
	$("#page").change(function () {
		$("#paging").submit();
	});
	
	
	// ZenDesk Video
	$('#zendesk_help .video img').click(function(ev){
		ev.preventDefault();
		Om.ModalForm.init('', 'Zendesk', '');
		Om.ModalForm.$save.hide();
		Om.ModalForm.$form.unbind('submit');
		Om.ModalForm.$form.submit(function(ev){
			ev.preventDefault();
			Om.ModalForm.hide();						
		});
		
		var $video = $('<div />');
		$video.append('<object width="370" height="208"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=4627411&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=80B463&amp;fullscreen=1&amp;autoplay=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=4627411&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=80B463&amp;fullscreen=1&amp;autoplay=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="370" height="208"></embed></object>');
		Om.ModalForm.$fieldset.prepend($video);
		Om.ModalForm.show();
	});
	
	
	// CampaignMonitor video
	$('#campaignmonitor_help .video img').click(function(ev){
		ev.preventDefault();
		Om.ModalForm.init('', 'Campaign Monitor', '');
		Om.ModalForm.$save.hide();
		Om.ModalForm.$form.unbind('submit');
		Om.ModalForm.$form.submit(function(ev){
			ev.preventDefault();
			Om.ModalForm.hide();						
		});
		
		var $video = $('<div />');
		$video.append('<object width="370" height="208"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=5505556&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=80B463&amp;fullscreen=1&amp;autoplay=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=5505556&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=80B463&amp;fullscreen=1&amp;autoplay=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="370" height="208"></embed></object>');
		Om.ModalForm.$fieldset.prepend($video);
		Om.ModalForm.show();
	});
	
	
	// New contact methods
	$('li.twitter a').live('click', function(ev) {
		ev.preventDefault();
		
		var twitter_user = $(this).text().replace('@','');
		
		Om.ModalForm.init('', 'Recent Tweets for ' + twitter_user, '');
		Om.ModalForm.$save.find('input').hide()
			.before($('<a href="http://twitter.com/'+twitter_user+'">See the rest</a>'));
		Om.ModalForm.$form.unbind('submit');
		Om.ModalForm.$form.submit(function(ev){
			ev.preventDefault();
			Om.ModalForm.hide();						
		});
		
		var $tweets = $('<ul />').attr({id: 'qa_tweets'})
			.append($('<li />').html('<img src="/graphics/omelette/spinner.gif" /> Loading...'));
		
		$.getJSON("http://twitter.com/statuses/user_timeline.json?count=5&id="+twitter_user+"&callback=?",
			function(data) {
				$tweets.empty();
				$.each(data, function(index, tweet) {
					var $li = $('<li />')
						.html('<img src="' + tweet.user.profile_image_url + '" /><p>' + tweet.text + '<br /><span class="when">' + tweet.created_at + '</span></p>');
					$tweets.append($li);
				});
			}
		);
		
		var $content = $('<div />').append($tweets);
		
		Om.ModalForm.$fieldset.prepend($content);
		Om.ModalForm.show();
	});
	
	
	// CSV Import
	$('#csv_import_assignment select').change(function(ev) {
		$('#csv_imported_headings ul li').each(function() {
			var $heading = $(this);
			var id = $heading.attr('id').replace('csv_heading_','');
			if ($('#csv_import_assignment select[value='+id+']').length) {
				$heading.addClass('yes').removeClass('no');
			} else {
				$heading.addClass('no').removeClass('yes');
			}
		});
	});
	
	
	// Webform
	if ($('#webform_preview_output').length) {
		$('#webform_preview_ouput').load('/form/', null, function() {
			if (!$('#tactile_webform').length) {
				$('#webform_preview_ouput').html('<p>Disabled</p>');
			}
		});
	}
	$('input[name=capture_person]').change(function(){
		var mode = $('input[name=capture_person]:checked').val();
		$('#capture_person_fields').toggle(mode === 'optional' || mode === 'required');
	});
	$('input[name=capture_organisation]').change(function(){
		var mode = $('input[name=capture_organisation]:checked').val();
		$('#capture_organisation_fields').toggle(mode === 'optional' || mode === 'required');
	});
	$('input[name=capture_contact]').change(function(){
		var mode = $('input[name=capture_contact]:checked').val();
		$('#capture_contact_fields').toggle(mode === 'optional' || mode === 'required');
	});
	$('#create_opportunity').change(function(){
		$('#create_opportunity_fields').toggle($('#create_opportunity').is(':checked'));
	});
	$('#create_activity').change(function(){
		$('#create_activity_fields').toggle($('#create_activity').is(':checked'));
	});
	$('#webform_use_captcha').change(function(){
		$('#captcha_fields').toggle($('#webform_use_captcha').is(':checked'));
	});
	
	
	// Farbtastic
	$('#appearance_theme_list li.custom .colour_swatch').click(function(){
		var $swatch = $(this);
		var col = $swatch.css('background-color');
		if (col.match(/^rgb/)) {
			var parts = col.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
			delete (parts[0]);
			var i = 1;
			for (i = 1; i <= 3; ++i) {
			    parts[i] = parseInt(parts[i], 10).toString(16);
			    if (parts[i].length == 1) { 
					parts[i] = '0' + parts[i];
			    }
			}
			col = '#' + parts.join('');
		}
		
		var which = $swatch.is('.a') ? 'primary' : 'secondary';
		
		Om.ModalForm.init('/appearance/save_custom_theme/', (which === 'primary' ? 'Select Primary Colour' : 'Select Secondary Colour'), 'Theme adjusted');
		
		var $farb =  $('<div id="farbtastic" />');
		var ft = $.farbtastic($farb);
		ft.setColor(col);
		
		var $fs = Om.ModalForm.$fieldset;
		$fs.append(
			$('<div class="row" />')
				.append($('<label for="qa_farbtastic_hex" />').text('Hexadecimal'))
				.append($('<input id="qa_farbtastic_hex" />').attr('name', which).val(col))
			).append($farb)
			.append($('<div id="qa_farbtastic_swatch" />').css({'background-color':col}));
		
		Om.ModalForm.$form.unbind('submit');
		Om.ModalForm.show();
		
		// Events
		ft.linkTo(function(hex){
			$('#qa_farbtastic_hex').val(hex);
			$('#qa_farbtastic_swatch').css({'background-color':hex});
		});
		$('#qa_farbtastic_hex').bind('change, keyup, mouseup', function(){
			ft.setColor($(this).val());
		});
	});
	
	
	// Opportunity stats
	$('#opportunity_view #summary_stats #pipeline.can_edit').click(function(){
		var $value = $(this).find('p.value');
		if ($value.is(':visible')) {
			$.ajax({
				url: '/opportunities/options',
				dataType: 'json',
				success: function(json) {
					if (json.status && json.status === 'success') {
						var $select = $('<select />');
						$.each(json.opportunity_options.status, function(status_id, status) {
							var $option = $('<option />').attr('value', status_id).text(status);
							if ($value.text() === status) {
								$option.attr('selected', 'selected');
							}
							$select.append($option);
						});
						$select.find('option[value="'+$value.text()+'"]').attr('selected', 'selected');
						
						$select.change(function(){
							var new_status_id = $(this).val();
							var new_status = $(this).find('option:selected').text();
							$.ajax({
								url: '/opportunities/save',
								dataType: 'json',
								data: {'Opportunity[id]': Tactile.opportunity_id, 'Opportunity[status_id]': new_status_id},
								success: function(json) {
									if (json.status) {
										if (json.status === 'success') {
											Om.Flash.addMessages(json.message);
										} else {
											Om.Flash.addErrors(json.errors);
										}
									} else {
										Om.Flash.addErrors(['There was a problem during the save. Pleas try again.']);
									}
									var new_url = '/opportunities/by_status/?q='+new_status.replace(' ','+');
									$('#opportunity_status .view_data a').html(new_status + ' &raquo;').attr('href', new_url);
									$value.text(new_status).show();
									$select.remove();
								}
							});
						});
						$value.hide().after($select);
					}
				}
			});
		}
	});
	
	// Default Permissions Editor
	$('#pref_default_permissions table a.action').click(function(){
		Om.ModalForm.init('/permissions/save_defaults', 'Set Default Permissions', 'Permissions applied successfully');
		Om.ModalForm.$form.unbind('submit');
		Om.ModalForm.$form.submit(function(){
			Om.ModalForm.$save.find('input').attr("disabled", "disabled");
		});
			
		$fs = Om.ModalForm.$fieldset;
		var help_text = "If selecting users, Ctrl-click to choose multiple options";
		$fs.addClass('edit_holder');
		
		var username = $(this).parents('tr').find('td.primary').text();
		var defaults = Tactile.default_permissions[username];
		
		$fs.append(Om.ModalForm.formRow($('<span class="false_input" />').text(username), $('<span class="false_label" />').text('User')));
		$fs.append($('<input type="hidden" name="username" />').val(username));
		var $fixed = $('<input type="checkbox" name="fixed" id="qa_permissions_fixed" />').addClass('checkbox');
		$fs.append(Om.ModalForm.formRow($fixed, 'Fixed?'));
		if (defaults.fixed) {
			$fixed.attr('checked', 'checked');
		}
		
		$read_access_select = $('<select name="Sharing[read][]" multiple="multiple" id="read_roles_ids" />').hide();
		$read_access_options = $('<ul />').addClass('radio_options false_input');
		var $read_everyone = $('<input type="radio" name="Sharing[read]" value="everyone" />').addClass('radio checkbox');
		var $read_private = $('<input type="radio" name="Sharing[read]" value="private" />').addClass('radio checkbox');
		var $read_users = $('<input type="radio" name="Sharing[read]" value="private" id="read_roles" />').addClass('radio checkbox');
		$read_access_options.append($('<li />').append($('<label />').text('Everyone').prepend($read_everyone)))
			.append($('<li />').append($('<label />').text('Just Me (Private)').prepend($read_private)))
			.append(
				$('<li />').append($('<label />').text('Select Users...').prepend($read_users))
					.append($read_access_select)
			);
		$fs.append($('<div id="read_access" />').append(Om.ModalForm.formRow($read_access_options, $('<h4 />').text('Read Access').addClass('false_label'))));
		
		$write_access_select = $('<select name="Sharing[write][]" multiple="multiple" id="write_roles_ids" />').hide();
		$write_access_options = $('<ul />').addClass('radio_options false_input');
		var $write_everyone = $('<input type="radio" name="Sharing[write]" value="everyone" />').addClass('radio checkbox');
		var $write_private = $('<input type="radio" name="Sharing[write]" value="private" />').addClass('radio checkbox');
		var $write_users = $('<input type="radio" name="Sharing[write]" value="private" id="write_roles" />').addClass('radio checkbox');
		$write_access_options.append($('<li />').append($('<label />').text('Everyone').prepend($write_everyone)))
			.append($('<li />').append($('<label />').text('Just Me (Private)').prepend($write_private)))
			.append(
				$('<li />').append($('<label />').text('Select Users...').prepend($write_users))
					.append($write_access_select)
			);
		$fs.append($('<div id="write_access" />').append(Om.ModalForm.formRow($write_access_options, $('<h4 />').text('Read & Write Access').addClass('false_label'))));
		
		if ($.browser.msie) {
			$fs.find('input.radio').click(function(){
				// IE doesn't fire change() on radios until after blur()
				this.blur().focus();
			});
		}
		$fs.find('input.radio').change(function(){
			var $radio = $(this);
			var $select = $radio.parents('ul.radio_options').find('select'); 
			if ($radio.attr('id').match('roles')) {
				$select.show().removeAttr('disabled');
			} else {
				$select.hide().attr('disabled', 'disabled').find('option').removeAttr('selected');
			}
		});
		
		Om.ModalForm.preSubmit = function(){
			$table.find('tbody .cb input:checked').each(function(i){
				$fs.append($('<input type="hidden" name="ids[]" />').val($(this).val()));
			});
		};
		
		$.ajax({
			url: '/organisations/options',
			dataType: 'json',
			success: function(json) {
				$.each(json.organisation_options.sharing.read['private'], function(k, v) {
					var $read_option;
					var $write_option;
					if (v.match(/\w+\/\//)) {
						var role = v.replace(/\/\/.*$/, '');
						if (username !== role) {
							$read_option = $('<option />').val(k).text(role);
							$write_option = $('<option />').val(k).text(role);
							$read_access_select.append($read_option);
							$write_access_select.append($write_option);
						}
					} else if (v.match(/^[^\/]+$/)) {
						$read_option = $('<option />').val(k).text(v);
						$write_option = $('<option />').val(k).text(v);
						$read_access_select.append($read_option);
						$write_access_select.append($write_option);
					}
				});
				if (defaults.permissions_read === 'everyone') {
					$read_everyone.attr('checked', 'checked');
				} else if (defaults.permissions_read === 'private') {
					$read_private.attr('checked', 'checked');
				} else if (defaults.permissions_read instanceof Array) {
					$read_users.attr('checked', 'checked');
					$read_access_select.show();
					$.each(defaults.permissions_read, function(k, v) {
						$read_access_select.find('option[value='+v+']').attr('selected', 'selected');
					});
				}
				if (defaults.permissions_write === 'everyone') {
					$write_everyone.attr('checked', 'checked');
				} else if (defaults.permissions_write === 'private') {
					$write_private.attr('checked', 'checked');
				} else if (defaults.permissions_write instanceof Array) {
					$write_users.attr('checked', 'checked');
					$write_access_select.show();
					$.each(defaults.permissions_write, function(k, v) {
						$write_access_select.find('option[value='+v+']').attr('selected', 'selected');
					});
				}
				Om.ModalForm.show();
			}
		});
	});
	
	// Inline description editor
	$('#summary_info li.description.inline_edit').each(function(){
		$(this).find('.view_data .blank').html('<em>Click to edit</em>');
	}).click(function(){
		var $desc = $(this);
		if ($desc.find('textarea').length) {
			return;
		}
		var type = $desc.attr('id').replace(/_description/, '');
		var old_desc_text = $desc.find('.blank').length ? '' : $desc.find('.view_data .data').text();
		var old_desc_html = $desc.find('.blank').length ? '' : $desc.find('.view_data .data').html();
		var $ta = $('<textarea cols="40" rows="4" />').hide().val(old_desc_text);
			
		var $save = $('<input type="submit" value="Save" class="submit" />');
		var $cancel = $('<a />').text('Cancel');
		
		$cancel.click(function(){
			var $data = old_desc_html === '' ? $('<span />').html('<em>Click to edit</em>').addClass('blank') : $('<span />').html(old_desc_html).addClass('data');
			$desc.find('.view_data').removeClass('edit').empty().append($('<span />').append($data));
			return false;
		});
		$save.click(function(){
			var key = type.charAt(0).toUpperCase() + type.substr(1);
			var data = {};
			data[key + '[id]'] = Tactile.id;
			data[key + '[description]'] = $ta.val();
			var options = {
				url: '/'+Om.plural(type)+'/save/' + Tactile.id,
				type: 'POST',
				dataType: 'json',
				data: data,
				success: function(json){
					if (json.status && json.status === 'success') {
						var $replace = $('<span />').hide();
						if ($ta.val() === '') {
							$replace.addClass('blank').html('<em>Click to edit</em>');
						} else {
							$replace.addClass('data').html($ta.val().replace(/\n/g,"<br />\n"));
						}
						$desc.find('.view_data').removeClass('edit').empty().append($replace.fadeIn());
					} else {
						Om.Flash.addErrors(json.errors ? json.errors : 'An error occurred, please try again later');
						$cancel.click();
					}
				}
			};
			if ($ta.val() !== old_desc_text) {
				$.ajax(options);
			} else {
				$cancel.click();
			}
			return false;
		});
		$desc.find('.view_data').addClass('edit').empty().append($ta).append($('<div />').addClass('save_or_cancel').append($save).append('<span> or </span>').append($cancel));
		$ta.expandingTextarea().show().focus();
	});
	
	
});
/**
 * Farbtastic Color Picker 1.2
 * © 2008 Steven Wittens
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

jQuery.fn.farbtastic = function (callback) {
  $.farbtastic(this, callback);
  return this;
};

jQuery.farbtastic = function (container, callback) {
  var container = $(container).get(0);
  return container.farbtastic || (container.farbtastic = new jQuery._farbtastic(container, callback));
}

jQuery._farbtastic = function (container, callback) {
  // Store farbtastic object
  var fb = this;

  // Insert markup
  $(container).html('<div class="farbtastic"><div class="color"></div><div class="wheel"></div><div class="overlay"></div><div class="h-marker marker"></div><div class="sl-marker marker"></div></div>');
  var e = $('.farbtastic', container);
  fb.wheel = $('.wheel', container).get(0);
  // Dimensions
  fb.radius = 84;
  fb.square = 100;
  fb.width = 194;

  // Fix background PNGs in IE6
  if (navigator.appVersion.match(/MSIE [0-6]\./)) {
    $('*', e).each(function () {
      if (this.currentStyle.backgroundImage != 'none') {
        var image = this.currentStyle.backgroundImage;
        image = this.currentStyle.backgroundImage.substring(5, image.length - 2);
        $(this).css({
          'backgroundImage': 'none',
          'filter': "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='" + image + "')"
        });
      }
    });
  }

  /**
   * Link to the given element(s) or callback.
   */
  fb.linkTo = function (callback) {
    // Unbind previous nodes
    if (typeof fb.callback == 'object') {
      $(fb.callback).unbind('keyup', fb.updateValue);
    }

    // Reset color
    fb.color = null;

    // Bind callback or elements
    if (typeof callback == 'function') {
      fb.callback = callback;
    }
    else if (typeof callback == 'object' || typeof callback == 'string') {
      fb.callback = $(callback);
      fb.callback.bind('keyup', fb.updateValue);
      if (fb.callback.get(0).value) {
        fb.setColor(fb.callback.get(0).value);
      }
    }
    return this;
  }
  fb.updateValue = function (event) {
    if (this.value && this.value != fb.color) {
      fb.setColor(this.value);
    }
  }

  /**
   * Change color with HTML syntax #123456
   */
  fb.setColor = function (color) {
    var unpack = fb.unpack(color);
    if (fb.color != color && unpack) {
      fb.color = color;
      fb.rgb = unpack;
      fb.hsl = fb.RGBToHSL(fb.rgb);
      fb.updateDisplay();
    }
    return this;
  }

  /**
   * Change color with HSL triplet [0..1, 0..1, 0..1]
   */
  fb.setHSL = function (hsl) {
    fb.hsl = hsl;
    fb.rgb = fb.HSLToRGB(hsl);
    fb.color = fb.pack(fb.rgb);
    fb.updateDisplay();
    return this;
  }

  /////////////////////////////////////////////////////

  /**
   * Retrieve the coordinates of the given event relative to the center
   * of the widget.
   */
  fb.widgetCoords = function (event) {
    var x, y;
    var el = event.target || event.srcElement;
    var reference = fb.wheel;

    if (typeof event.offsetX != 'undefined') {
      // Use offset coordinates and find common offsetParent
      var pos = { x: event.offsetX, y: event.offsetY };

      // Send the coordinates upwards through the offsetParent chain.
      var e = el;
      while (e) {
        e.mouseX = pos.x;
        e.mouseY = pos.y;
        pos.x += e.offsetLeft;
        pos.y += e.offsetTop;
        e = e.offsetParent;
      }

      // Look for the coordinates starting from the wheel widget.
      var e = reference;
      var offset = { x: 0, y: 0 }
      while (e) {
        if (typeof e.mouseX != 'undefined') {
          x = e.mouseX - offset.x;
          y = e.mouseY - offset.y;
          break;
        }
        offset.x += e.offsetLeft;
        offset.y += e.offsetTop;
        e = e.offsetParent;
      }

      // Reset stored coordinates
      e = el;
      while (e) {
        e.mouseX = undefined;
        e.mouseY = undefined;
        e = e.offsetParent;
      }
    }
    else {
      // Use absolute coordinates
      var pos = fb.absolutePosition(reference);
      x = (event.pageX || 0*(event.clientX + $('html').get(0).scrollLeft)) - pos.x;
      y = (event.pageY || 0*(event.clientY + $('html').get(0).scrollTop)) - pos.y;
    }
    // Subtract distance to middle
    return { x: x - fb.width / 2, y: y - fb.width / 2 };
  }

  /**
   * Mousedown handler
   */
  fb.mousedown = function (event) {
    // Capture mouse
    if (!document.dragging) {
      $(document).bind('mousemove', fb.mousemove).bind('mouseup', fb.mouseup);
      document.dragging = true;
    }

    // Check which area is being dragged
    var pos = fb.widgetCoords(event);
    fb.circleDrag = Math.max(Math.abs(pos.x), Math.abs(pos.y)) * 2 > fb.square;

    // Process
    fb.mousemove(event);
    return false;
  }

  /**
   * Mousemove handler
   */
  fb.mousemove = function (event) {
    // Get coordinates relative to color picker center
    var pos = fb.widgetCoords(event);

    // Set new HSL parameters
    if (fb.circleDrag) {
      var hue = Math.atan2(pos.x, -pos.y) / 6.28;
      if (hue < 0) hue += 1;
      fb.setHSL([hue, fb.hsl[1], fb.hsl[2]]);
    }
    else {
      var sat = Math.max(0, Math.min(1, -(pos.x / fb.square) + .5));
      var lum = Math.max(0, Math.min(1, -(pos.y / fb.square) + .5));
      fb.setHSL([fb.hsl[0], sat, lum]);
    }
    return false;
  }

  /**
   * Mouseup handler
   */
  fb.mouseup = function () {
    // Uncapture mouse
    $(document).unbind('mousemove', fb.mousemove);
    $(document).unbind('mouseup', fb.mouseup);
    document.dragging = false;
  }

  /**
   * Update the markers and styles
   */
  fb.updateDisplay = function () {
    // Markers
    var angle = fb.hsl[0] * 6.28;
    $('.h-marker', e).css({
      left: Math.round(Math.sin(angle) * fb.radius + fb.width / 2) + 'px',
      top: Math.round(-Math.cos(angle) * fb.radius + fb.width / 2) + 'px'
    });

    $('.sl-marker', e).css({
      left: Math.round(fb.square * (.5 - fb.hsl[1]) + fb.width / 2) + 'px',
      top: Math.round(fb.square * (.5 - fb.hsl[2]) + fb.width / 2) + 'px'
    });

    // Saturation/Luminance gradient
    $('.color', e).css('backgroundColor', fb.pack(fb.HSLToRGB([fb.hsl[0], 1, 0.5])));

    // Linked elements or callback
    if (typeof fb.callback == 'object') {
      // Set background/foreground color
      $(fb.callback).css({
        backgroundColor: fb.color,
        color: fb.hsl[2] > 0.5 ? '#000' : '#fff'
      });

      // Change linked value
      $(fb.callback).each(function() {
        if (this.value && this.value != fb.color) {
          this.value = fb.color;
        }
      });
    }
    else if (typeof fb.callback == 'function') {
      fb.callback.call(fb, fb.color);
    }
  }

  /**
   * Get absolute position of element
   */
  fb.absolutePosition = function (el) {
    var r = { x: el.offsetLeft, y: el.offsetTop };
    // Resolve relative to offsetParent
    if (el.offsetParent) {
      var tmp = fb.absolutePosition(el.offsetParent);
      r.x += tmp.x;
      r.y += tmp.y;
    }
    return r;
  };

  /* Various color utility functions */
  fb.pack = function (rgb) {
    var r = Math.round(rgb[0] * 255);
    var g = Math.round(rgb[1] * 255);
    var b = Math.round(rgb[2] * 255);
    return '#' + (r < 16 ? '0' : '') + r.toString(16) +
           (g < 16 ? '0' : '') + g.toString(16) +
           (b < 16 ? '0' : '') + b.toString(16);
  }

  fb.unpack = function (color) {
    if (color.length == 7) {
      return [parseInt('0x' + color.substring(1, 3)) / 255,
        parseInt('0x' + color.substring(3, 5)) / 255,
        parseInt('0x' + color.substring(5, 7)) / 255];
    }
    else if (color.length == 4) {
      return [parseInt('0x' + color.substring(1, 2)) / 15,
        parseInt('0x' + color.substring(2, 3)) / 15,
        parseInt('0x' + color.substring(3, 4)) / 15];
    }
  }

  fb.HSLToRGB = function (hsl) {
    var m1, m2, r, g, b;
    var h = hsl[0], s = hsl[1], l = hsl[2];
    m2 = (l <= 0.5) ? l * (s + 1) : l + s - l*s;
    m1 = l * 2 - m2;
    return [this.hueToRGB(m1, m2, h+0.33333),
        this.hueToRGB(m1, m2, h),
        this.hueToRGB(m1, m2, h-0.33333)];
  }

  fb.hueToRGB = function (m1, m2, h) {
    h = (h < 0) ? h + 1 : ((h > 1) ? h - 1 : h);
    if (h * 6 < 1) return m1 + (m2 - m1) * h * 6;
    if (h * 2 < 1) return m2;
    if (h * 3 < 2) return m1 + (m2 - m1) * (0.66666 - h) * 6;
    return m1;
  }

  fb.RGBToHSL = function (rgb) {
    var min, max, delta, h, s, l;
    var r = rgb[0], g = rgb[1], b = rgb[2];
    min = Math.min(r, Math.min(g, b));
    max = Math.max(r, Math.max(g, b));
    delta = max - min;
    l = (min + max) / 2;
    s = 0;
    if (l > 0 && l < 1) {
      s = delta / (l < 0.5 ? (2 * l) : (2 - 2 * l));
    }
    h = 0;
    if (delta > 0) {
      if (max == r && max != g) h += (g - b) / delta;
      if (max == g && max != b) h += (2 + (b - r) / delta);
      if (max == b && max != r) h += (4 + (r - g) / delta);
      h /= 6;
    }
    return [h, s, l];
  }

  // Install mousedown handler (the others are set on the document on-demand)
  $('*', e).mousedown(fb.mousedown);

    // Init color
  fb.setColor('#000000');

  // Set linked elements/callback
  if (callback) {
    fb.linkTo(callback);
  }
}
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

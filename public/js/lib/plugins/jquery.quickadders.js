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

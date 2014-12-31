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

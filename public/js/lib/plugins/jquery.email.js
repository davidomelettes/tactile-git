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

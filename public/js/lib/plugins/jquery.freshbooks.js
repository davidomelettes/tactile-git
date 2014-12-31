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

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

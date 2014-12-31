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

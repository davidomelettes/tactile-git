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

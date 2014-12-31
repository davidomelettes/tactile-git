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

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

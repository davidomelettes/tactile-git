// Configurable value tables
(function($) {
	
	$.fn.configTable = function() {
		var tables = this;
		
		return tables.each(function() {
			var $table = $(this);
						
			Om.ConfigTable.init($table);
		});
	};
	
}) (jQuery);

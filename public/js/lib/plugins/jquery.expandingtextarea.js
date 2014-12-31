// Automatically expanding text areas
(function($) {
	
	$.fn.expandingTextarea = function() {
		var areas = this;
		
		return areas.each(function() {
			var $ta = $(this);
			if (true === $ta.data('isExpanding')) {
				return false;
			}
			$ta.data('isExpanding', true);
			
			var lineHeight = parseInt($ta.css('line-height'), 10);
			lineHeight = isNaN(lineHeight) ? 20 : lineHeight;
			var maxHeight = parseInt($ta.css('max-height'), 10);
			maxHeight = isNaN(maxHeight) ? 700 : maxHeight;
				
			$dummy = $('<div />').appendTo('body').css({
				'font-size'    : $ta.css('font-size'),
				'font-family'  : $ta.css('font-family'),
				'width'        : $ta.css('width'), // A width must be specified!
				'paddingTop'   : $ta.css('paddingTop'),
				'paddingRight' : $ta.css('paddingRight'),
				'paddingBottom': $ta.css('paddingBottom'),
				'paddingLeft'  : $ta.css('paddingLeft'),
				'borderTop'    : $ta.css('borderTop'),
				'borderRight'  : $ta.css('borderRight'),
				'borderBottom' : $ta.css('borderBottom'),
				'borderLeft'   : $ta.css('borderLeft'),
				'line-height'  : $ta.css('line-height'),
				'max-height'   : maxHeight,
				'position'     : 'absolute',
				'top'          : -9999,
				'left'		   : -9999,
				'overflow'     : 'hidden'
			});
			
			var etaInterval = null;
			$ta.focus(function(){
				clearInterval(etaInterval);
				etaInterval = setInterval(function(){
					$ta.checkExpand();
				}, 800);
			}).blur(function(){
				clearInterval(etaInterval);
			});
			$ta.checkExpand = function() {
				if (!$ta.parent().length) {
					// Tidy up
					clearInterval(etaInterval);
					$dummy.remove();
					return false;
				}
				// Grab the contents of the textarea, and HTML-ify it
				var html = $ta.val()
					.replace(/&/, '&amp;')
					.replace(/</, '&lt;')
					.replace(/>/, '&gt;')
					.replace(/[\n]/g, '<br />&nbsp;');
				// Populate the dummy element with the text, and work out the new height
				$dummy.html(html);
				var newHeight = $dummy.height() + (2 * lineHeight);
				if ($ta.height() !== newHeight) {
					if ($dummy.height() >= maxHeight) {
						$ta.css({'overflow-y':'scroll'});
						$ta.height(maxHeight);
					} else {
						$ta.css({'overflow-y':'hidden'});
						$ta.height(newHeight);
					}
				}
			}
			$ta.checkExpand();
		});
	};
}) (jQuery);

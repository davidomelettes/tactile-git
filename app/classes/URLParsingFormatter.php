<?php

/**
 *
 */
class URLParsingFormatter implements FieldFormatter {
	
	public $is_safe = true;

	const PATTERN = '#(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?(\?[=&\w;]+)?)#i';
	
	const REPLACE = '<a class="out" href="$1">$1</a>';
	
	/**
	 * Replaces URLs with an a-tag referencing the URL
	 * 
	 * @param String $value 
	 * @return String 
	 * @see FieldFormatter::format()
	 */
	function format($value) {
		return nl2br(preg_replace(self::PATTERN,self::REPLACE,htmlentities($value,ENT_NOQUOTES,'UTF-8')));
	}
	
}

?>

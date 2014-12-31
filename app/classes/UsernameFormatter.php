<?php

/**
 * Responsible for formatting usernames in such a way that disabled users can be indicated visually
 * 
 * @author gj
 */
class UsernameFormatter implements FieldFormatter {

	/**
	 * Indicates that this formatter escapes HTML itself, so we don't do it again
	 *
	 * @var Boolean
	 */
	public $is_safe = true;
	
	/**
	 * Checks to see whether the user is disabled, 
	 * and if so returns the username wrapped in a span with a classname
	 * 
	 * @param String $value 
	 * @return String 
	 * @see FieldFormatter::format()
	 */
	function format($value) {
		if(empty($value)) {
			return '';
		}
		if(!Omelette_User::UsernameIsEnabled($value)) {
			$value = '<span class="strike">'.h($value).'</span>';
		}
		return $value;
	}
}

?>

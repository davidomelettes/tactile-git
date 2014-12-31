<?php
/**
 * Formatter for the body part of emails, that strips signatures and trailing newlines
 * before passing off to h()
 * 
 * @author gj
 *
 */
class EmailBodyFormatter implements FieldFormatter {

	/**
	 * 
	 * @param String $value 
	 * @return String 
	 * @see FieldFormatter::format()
	 */
	function format($value) {

		$pattern = '#(^--\s?$.*)#sm';
		$replace = '';
		$value = preg_replace($pattern, $replace, $value);
		
		return h(trim($value,"\n"));
	}
}

?>

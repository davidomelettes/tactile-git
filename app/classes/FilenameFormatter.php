<?php

/**
 *
 */
class FilenameFormatter implements FieldFormatter {

	/**
	 * 
	 * @param String $value 
	 * @return String 
	 * @see FieldFormatter::format()
	 */
	function format($value) {
		$length = strlen($value);
		if($length <= 30) {
			return $value;
		}
		$beginning = substr($value,0,25);
		$end = strrchr($value,".");
		return $beginning.'~'.$end;
	}
}

?>

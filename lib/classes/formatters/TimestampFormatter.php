<?php
/*
 * Created on 1 Feb 2007 by Tim Ebenezer
 *
 * TimestampFormatter.php
 */

class TimestampFormatter implements FieldFormatter {
	function format($value) {
		if(empty($value)) {
			return "";
		}
		return date(DATE_TIME_FORMAT,strtotime($value));
	}
}

?>

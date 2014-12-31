<?php
/*
 * Created on 1 Feb 2007 by Tim Ebenezer
 *
 * DateFormatter.php
 */

class DateFormatter implements FieldFormatter {
	function format($value) {
		if (!empty($value))
			return date(DATE_FORMAT,strtotime($value));
		else
			return '';
	}
}

?>

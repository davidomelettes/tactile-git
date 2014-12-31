<?php
/*
 * Classes implementing this will be called upon to format a string in a way suitable for humans to read
 * @author te
 */
interface FieldFormatter {
	/**
	 * Format the given value for human eyes
	 * @param String $value
	 * @return String
	 */
	function format($value);
}

?>

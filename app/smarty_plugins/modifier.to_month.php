<?php
function smarty_modifier_to_month($string) {
	$months =  array(
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	);
	return $months[$string-1];
}
?>
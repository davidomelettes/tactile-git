<?php
function smarty_modifier_threshold($value, $bad, $warn) {
	if($value>=$bad) {
		return 'bad';
	}
	if($value>=$warn) {
		return 'warn';
	}
	return 'good';	
	
}
?>
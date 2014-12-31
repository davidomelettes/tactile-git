<?php
function smarty_modifier_percent_of($string,$divide_by,$dp=0) {
	if($divide_by == 0) {
		return '-';
	}
	return round(100 * ($string /  $divide_by),$dp);
}
?>
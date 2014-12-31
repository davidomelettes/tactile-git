<?php
function smarty_modifier_break($string) {
	if($string!='') {
		return $string.'<br />';
	}
	return '';
}
?>
<?php
class URLFormatter implements FieldFormatter {
	public $is_safe=true;
	function format($value) {
		if(empty($value)) {
			return '';
		}
		return '<a class="website" href="http://'.str_replace('http://', '', $value).'">'.h($value).'</a>';
	}
}
?>
<?php
class NullFormatter implements FieldFormatter {
	
	public function format($value) {
		return $value;
	}
}
?>
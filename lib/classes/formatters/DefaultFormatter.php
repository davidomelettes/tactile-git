<?php
class DefaultFormatter implements FieldFormatter {
	public $is_safe=true;
	public function format($value) {
		return h($value);
	}
}
?>
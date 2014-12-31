<?php
class IntervalFormatter implements FieldFormatter {
	
	public function format($value) {
		return to_working_days($value);
	}
}
?>
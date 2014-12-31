<?php
class FilesizeFormatter implements FieldFormatter {
	
	public function format($value) {
		return sizify($value);
	}
}
?>
<?php
class OmeletteRoleFormatter implements FieldFormatter {
	public function format($value) {
		return str_replace('//'.USER_SPACE,'',$value);
	}
}
?>
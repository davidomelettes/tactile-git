<?php
class UsernameField extends DataField {
	function __get($var) {
		$val = parent::__get($var);
		if($var=='formatted') {
			$val = str_replace('//'.Omelette::getUserSpace(),'',$val);
		}
		return $val;
	}

}

?>
<?php

class AccountNumberHandler extends AutoHandler {
	private $unconditional=false;
	function __construct($unconditional=false) {
		$this->unconditional = $unconditional;
	}
	
	function handle(DataObject $model) {
		$value = $model->accountnumber;
		if(!empty($value)) {
			// Account number already filled in, so just return.
			return false;
		}
		return $model->createAccountNumber();
	}
}
?>
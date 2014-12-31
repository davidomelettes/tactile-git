<?php

class TactileEmailAddressCollection extends DataObjectCollection {
	
	public function __construct($model='TactileEmailAddress') {
		parent::__construct($model);
		$this->_tablename='tactile_email_addresses_overview';
	}
	
}

<?php

class StoreSetup extends DataObject {
	
	function __construct() {
		parent::__construct('store_setup');
		$this->setEnum('perpage',array(10=>10,20=>20,30=>30,50=>50));
		$this->idField='usercompanyid';
	}

}
?>

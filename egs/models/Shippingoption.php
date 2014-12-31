<?php
class Shippingoption extends DataObject {

	function __construct() {
		parent::__construct('shipping_options');
		$this->idField='id';
		
		$this->view='';
		
	}


}
?>

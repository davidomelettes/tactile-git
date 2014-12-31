<?php
class TicketResponseCollection extends DataObjectCollection {	
	public $field;
	
	function __construct() {
		parent::__construct('TicketResponse');
	}
}
?>
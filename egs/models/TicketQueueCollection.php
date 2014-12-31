<?php
class TicketQueueCollection extends DataObjectCollection {	
	public $field;
	
	function __construct() {
		parent::__construct('TicketQueue');
	}
}
?>
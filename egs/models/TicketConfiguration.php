<?php
class TicketConfiguration extends DataObject {
	function __construct() {
		parent::__construct('ticket_configurations');
		$this->idField='id';
	}
	
	function __toString() {
		return $this->name;
	}
}
?>
<?php
class TicketPriority extends DataObject {
	function __construct() {
		parent::__construct('ticket_priorities');
		$this->idField='id';

		$this->orderby = 'index';
		
		$this->hasMany('Ticket', 'ticket_priority_id');
		
		$this->setConcatenation('name', array('index','name'), '-');
	}
	
	function __toString() {
		return $this->index . ' - ' . $this->name;
	}
	
	function __get($key) {
		return parent::__get($key);
	}
}
?>

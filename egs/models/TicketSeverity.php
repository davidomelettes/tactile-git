<?php
class TicketSeverity extends DataObject {
	function __construct() {
		parent::__construct('ticket_severities');
		$this->idField='id';

		$this->orderby = 'index';
		
		$this->hasMany('Ticket', 'ticket_severity_id');
	}

	function __toString() {
		return $this->index . ' - ' . $this->name;
	}
}
?>

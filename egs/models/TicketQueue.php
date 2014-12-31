<?php
class TicketQueue extends DataObject {
	function __construct() {
		parent::__construct('ticket_queues');
		$this->idField='id';
		
		$this->hasMany('Ticket', 'ticket_queue_id');
	}
}
?>
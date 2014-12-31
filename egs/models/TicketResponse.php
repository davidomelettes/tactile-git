<?php
// THIS IS A JOIN TABLE!!! Probably shouldn't have a model.
class TicketResponse extends DataObject {
	function __construct() {
		parent::__construct('ticket_responses');
		$this->idField='id';
		$this->orderby = 'created';
		$this->orderdir = 'asc';
		
		$this->belongsTo('Ticket', 'ticket_id');
	}
}
?>

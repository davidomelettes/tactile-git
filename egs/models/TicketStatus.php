<?php
class TicketStatus extends DataObject {
	function __construct() {
		parent::__construct('ticket_statuses');
		$this->idField='id';

		$this->orderby = 'index';
	}
}
?>

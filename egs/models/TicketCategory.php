<?php
class TicketCategory extends DataObject {
	function __construct() {
		parent::__construct('ticket_categories');
		$this->idField='id';
		
		$this->hasMany('Ticket', 'ticket_category_id');
		
	}
	
	function __toString() {
		return $this->name;
	}
}
?>